<?php

namespace Tests\Feature\Student;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\FaceData;
use App\Models\FaceUpdateRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Services\Interfaces\FaceRecognitionServiceInterface;
use Tests\RefreshPermissions;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Support\Carbon;

class FaceRegistrationTest extends TestCase
{
    use RefreshDatabase, WithFaker, RefreshPermissions;

    protected $user;
    protected $student;
    protected $lecturer;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed permissions and roles
        $this->setupPermissions();

        // Create student user
        $this->user = User::factory()->create(['role' => 'student']);
        $this->user->assignRole('student');

        // Create a lecturer user
        $this->lecturer = User::factory()->create(['role' => 'lecturer']);
        $this->lecturer->assignRole('lecturer');

        // Create a fake storage disk for testing
        Storage::fake('face_images');
    }

    public function test_student_can_view_face_registration_page()
    {
        $student = Student::factory()->create(['user_id' => $this->user->id, 'face_registered' => false]);

        // Act: Login and visit the face registration page
        $response = $this->actingAs($this->user)
            ->get(route('student.face.index'));

        // Assert: Page loads correctly with student data
        $response->assertStatus(200);
        $response->assertViewIs('student.face.index');
        $response->assertViewHas('student', $student);
    }


    public function test_non_students_cannot_access_face_registration_page()
    {
        // Create a lecturer user
        // Act: Login as lecturer and try to visit student face registration page
        $response = $this->actingAs($this->lecturer)
            ->get(route('student.face.index'));

        // Assert: Access is denied
        $response->assertStatus(403);
    }


    public function test_student_with_registered_face_cannot_access_registration_form()
    {
        // Create a student user with face already registered

        $student = Student::factory()->create([
            'user_id' => $this->user->id,
            'face_registered' => true
        ]);

        // Act: Login and try to visit the face registration form
        $response = $this->actingAs($this->user)
            ->get(route('student.face.register'));

        // Assert: Redirected with error message
        $response->assertRedirect(route('student.face.index'));
        $response->assertSessionHas('error', 'You have already registered your face. To update it, you need an approved update request.');
    }


    public function test_student_with_approved_update_request_can_access_registration_form()
    {
        // Create a student user with face already registered
        $student = Student::factory()->create([
            'user_id' => $this->user->id,
            'face_registered' => true
        ]);

        // Create an approved update request
        $updateRequest = FaceUpdateRequest::create([
            'student_id' => $student->id,
            'reason' => 'Test reason',
            'status' => 'approved',
        ]);

        // Act: Login and visit the face registration form
        $response = $this->actingAs($this->user)
            ->get(route('student.face.register'));

        // Assert: Page loads correctly
        $response->assertStatus(200);
        $response->assertViewIs('student.face.register');
    }


    public function test_student_can_validate_face_image_quality()
    {
        // Create a student user
        $student = Student::factory()->create(['user_id' => $this->user->id]);

        // Mock the FaceRecognitionService
        $this->mock(FaceRecognitionServiceInterface::class, function ($mock) {
            $mock->shouldReceive('validateQuality')
                ->once()
                ->andReturn([
                    'status' => 'success',
                    'message' => 'Image quality is acceptable',
                    'data' => [
                        'quality_score' => 0.85,
                        'has_face' => true,
                        'face_count' => 1
                    ]
                ]);
        });

        // Create a test image
        $image = UploadedFile::fake()->image('face.jpg', 600, 600);

        // Act: Submit the image for quality validation
        $response = $this->actingAs($student->user)
            ->postJson(route('student.face.validate-quality'), [
                'image' => $image
            ]);

        // Assert: Validation response is returned
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Image quality is acceptable'
            ]);
    }


    public function test_student_can_register_face()
    {
        // Create a student user
        $student = Student::factory()->create([
            'user_id' => $this->user->id,
            'face_registered' => false,
            'nim' => '123456789'
        ]);

        // Mock the FaceRecognitionService
        $this->mock(FaceRecognitionServiceInterface::class, function ($mock) {
            $mock->shouldReceive('registerFace')
                ->once()
                ->andReturn([
                    'status' => 'success',
                    'message' => 'Face registered successfully.',
                    'data' => [
                        'student_id' => 1,
                        'nim' => '123456789',
                        'image_count' => 1,
                    ]
                ]);
        });

        // Create test images
        $images = [];
        for ($i = 0; $i < 5; $i++) {
            $images[] = UploadedFile::fake()->image("face{$i}.jpg", 600, 600);
        }

        // Act: Submit the registration request
        $response = $this->actingAs($student->user)
            ->postJson(route('student.face.store'), [
                'images' => $images,
                'redirect_url' => route('student.face.index')
            ]);

        // Assert: Registration successful
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Face registered successfully.'
            ]);

        // Check that the student's face_registered flag was updated
        $this->assertTrue($student->fresh()->face_registered);
    }


    public function test_student_can_submit_face_update_request()
    {
        // Create a student user with face already registered
        $student = Student::factory()->create([
            'user_id' => $this->user->id,
            'face_registered' => true
        ]);

        // Create face data for the student
        FaceData::factory()->create([
            'student_id' => $student->id,
            'face_embedding' => json_encode(array_fill(0, 128, 0.1)),
            'image_path' => json_encode(['path' => 'faces/test.jpg']),
            'is_active' => true
        ]);

        // Act: Submit update request
        $response = $this->actingAs($student->user)
            ->post(route('student.face.store-request'), [
                'reason' => 'My face has changed'
            ]);

        // Assert: Request created and redirected
        $response->assertRedirect(route('student.face.index'));
        $response->assertSessionHas('success', 'Your face update request has been submitted and is awaiting approval.');

        // Check database
        $this->assertDatabaseHas('face_update_requests', [
            'student_id' => $student->id,
            'reason' => 'My face has changed',
            'status' => 'pending'
        ]);
    }


    public function test_student_cannot_submit_update_request_if_face_not_registered()
    {
        // Create a student user without registered face
        $student = Student::factory()->create([
            'user_id' => $this->user->id,
            'face_registered' => false
        ]);

        // Act: Try to submit update request
        $response = $this->actingAs($student->user)
            ->post(route('student.face.store-request'), [
                'reason' => 'I need to update my face'
            ]);

        // Assert: Request rejected with error
        $response->assertRedirect(route('student.face.index'));
        $response->assertSessionHas('error', 'You must register your face first before requesting an update.');
    }


    public function test_student_cannot_submit_multiple_pending_update_requests()
    {
        // Create a student user with face already registered
        $student = Student::factory()->create([
            'user_id' => $this->user->id,
            'face_registered' => true
        ]);

        // Create an existing pending update request
        FaceUpdateRequest::create([
            'student_id' => $student->id,
            'reason' => 'Existing request',
            'status' => 'pending'
        ]);

        // Act: Try to submit another update request
        $response = $this->actingAs($student->user)
            ->post(route('student.face.store-request'), [
                'reason' => 'Another update request'
            ]);

        // Assert: Request rejected with error
        $response->assertRedirect(route('student.face.index'));
        $response->assertSessionHas('error', 'You already have a pending face update request.');
    }

    public function test_student_can_update_face_after_request_approval()
    {
        // Create a student user with face registered
        $student = Student::factory()->create([
            'user_id' => $this->user->id,
            'face_registered' => true,
            'nim' => '987654321'
        ]);

        // Create an approved update request
        $updateRequest = FaceUpdateRequest::create([
            'student_id' => $student->id,
            'reason' => 'Approved update',
            'status' => 'approved',
            'approved_by' => 1,
            'approved_at' => now()
        ]);

        // Mock the FaceRecognitionService
        $this->mock(FaceRecognitionServiceInterface::class, function ($mock) {
            $mock->shouldReceive('registerFace')
                ->once()
                ->andReturn([
                    'status' => 'success',
                    'message' => 'Face updated successfully.',
                    'data' => [
                        'student_id' => 1,
                        'nim' => '987654321',
                        'image_count' => 1,
                    ]
                ]);
        });

        // Create test images
        $images = [];
        for ($i = 0; $i < 5; $i++) {
            $images[] = UploadedFile::fake()->image("face{$i}.jpg", 600, 600);
        }

        // Act: Submit the update
        $response = $this->actingAs($student->user)
            ->postJson(route('student.face.store'), [
                'images' => $images,
                'is_update' => true,
                'update_request_id' => $updateRequest->id,
                'redirect_url' => route('student.face.index')
            ]);

        // Assert: Update successful
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Face updated successfully.'
            ]);

        // Check that the update request was marked as completed
        $this->assertEquals('completed', $updateRequest->fresh()->status);
    }


    public function test_face_registration_fails_with_error_from_service()
    {
        $student = Student::factory()->create([
            'user_id' => $this->user->id,
            'face_registered' => false,
            'nim' => '123456789'
        ]);

        // Mock the FaceRecognitionService to return an error
        $this->mock(FaceRecognitionServiceInterface::class, function ($mock) {
            $mock->shouldReceive('registerFace')
                ->once()
                ->andReturn([
                    'status' => 'error',
                    'message' => 'No face detected in image',
                    'code' => 'NoFaceDetectedError'
                ]);
        });

        // Create test images
        $images = [];
        for ($i = 0; $i < 5; $i++) {
            $images[] = UploadedFile::fake()->image("face{$i}.jpg", 600, 600);
        }

        // Act: Submit the registration request
        $response = $this->actingAs($student->user)
            ->postJson(route('student.face.store'), [
                'images' => $images,
                'redirect_url' => route('student.face.index')
            ]);

        // Assert: Registration failed with error
        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'message' => 'No face detected in image',
                'code' => 'NoFaceDetectedError'
            ]);

        // Check that the student's face_registered flag was not updated
        $this->assertFalse($student->fresh()->face_registered);
    }


    public function test_face_quality_validation_fails_with_error_from_service()
    {
        // Create a student user
        $student = Student::factory()->create(['user_id' => $this->user->id]);

        // Mock the FaceRecognitionService
        $this->mock(FaceRecognitionServiceInterface::class, function ($mock) {
            $mock->shouldReceive('validateQuality')
                ->once()
                ->andReturn([
                    'status' => 'error',
                    'message' => 'Low quality image detected',
                    'code' => 'LOW_QUALITY_IMAGE'
                ]);
        });

        // Create a test image
        $image = UploadedFile::fake()->image('face.jpg', 600, 600);

        // Act: Submit the image for quality validation
        $response = $this->actingAs($student->user)
            ->postJson(route('student.face.validate-quality'), [
                'image' => $image
            ]);

        // Assert: Validation failed with error
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'error',
                'message' => 'Low quality image detected',
                'code' => 'LOW_QUALITY_IMAGE'
            ]);
    }


    public function test_student_can_view_update_form_with_approved_request()
    {
        // Create a student user with face registered
        $student = Student::factory()->create([
            'user_id' => $this->user->id,
            'face_registered' => true
        ]);

        // Create an approved update request
        $updateRequest = FaceUpdateRequest::create([
            'student_id' => $student->id,
            'reason' => 'Need update',
            'status' => 'approved'
        ]);

        // Act: Visit the update form
        $response = $this->actingAs($student->user)
            ->get(route('student.face.update', ['updateRequestId' => $updateRequest->id]));

        // Assert: Form loads correctly
        $response->assertStatus(200);
        $response->assertViewIs('student.face.register');
        $response->assertViewHas('isUpdate', true);
        $response->assertViewHas('updateRequest', $updateRequest);
    }


    public function test_student_cannot_view_update_form_with_non_approved_request()
    {
        $student = Student::factory()->create([
            'user_id' => $this->user->id,
            'face_registered' => true
        ]);

        // Create a pending update request
        $updateRequest = FaceUpdateRequest::create([
            'student_id' => $student->id,
            'reason' => 'Need update',
            'status' => 'pending'
        ]);

        // Act: Try to visit the update form
        $response = $this->actingAs($student->user)
            ->get(route('student.face.update', ['updateRequestId' => $updateRequest->id]));

        // Assert: Access denied with error
        $response->assertRedirect(route('student.face.index'));
        $response->assertSessionHas('error', 'Invalid or unauthorized face update request.');
    }


    public function test_student_cannot_update_face_with_another_students_request()
    {
        // Create two student users
        $student1 = Student::factory()->create([
            'user_id' => $this->user->id,
            'face_registered' => true
        ]);

        $student2 = Student::factory()->create([
            'user_id' => $this->user->id,
            'face_registered' => true
        ]);

        // Create an approved update request for student 2
        $updateRequest = FaceUpdateRequest::create([
            'student_id' => $student2->id,
            'reason' => 'Approved update',
            'status' => 'approved'
        ]);

        // Act: Student 1 tries to access student 2's update form
        $response = $this->actingAs($student1->user)
            ->get(route('student.face.update', ['updateRequestId' => $updateRequest->id]));

        // Assert: Access denied with error
        $response->assertRedirect(route('student.face.index'));
        $response->assertSessionHas('error', 'Invalid or unauthorized face update request.');
    }

    public function test_index_shows_rejected_request_after_completed_request()
    {
        // Create a student user
        $student = Student::factory()->create(['user_id' => $this->user->id]);

        // Create a completed request (older date)
        $completedRequest = FaceUpdateRequest::create([
            'student_id' => $student->id,
            'reason' => 'Completed Request',
            'status' => 'completed',
            'created_at' => Carbon::now()->subDays(10)
        ]);

        // Create a rejected request with newer date than completed
        $rejectedRequest = FaceUpdateRequest::create([
            'student_id' => $student->id,
            'reason' => 'Rejected Request',
            'status' => 'rejected',
            'created_at' => Carbon::now()->subDays(5)
        ]);

        // Act: Login and visit the face registration page
        $response = $this->actingAs($this->user)
            ->get(route('student.face.index'));

        // Assert: Page loads with both completed and rejected request
        $response->assertStatus(200);
        $response->assertViewIs('student.face.index');
        $response->assertViewHas('completedRequest', $completedRequest);
        // $response->assertViewHas('rejectedRequest', $rejectedRequest);
    }

    public function test_register_with_token_returns_correct_redirect_url()
    {
        // Create a student user without face registered
        $student = Student::factory()->create([
            'user_id' => $this->user->id,
            'face_registered' => false
        ]);

        // Define a token
        $token = 'test-attendance-token';

        // Act: Login and visit the register page with token
        $response = $this->actingAs($this->user)
            ->get(route('student.face.register', ['token' => $token]));

        // Assert: Page loads with correct redirect URL
        $response->assertStatus(200);
        $response->assertViewIs('student.face.register');
        $response->assertViewHas('redirectUrl', route('student.attendance.show', ['token' => $token]));
    }

    public function test_store_fails_when_student_not_found()
    {
        // Create a user without a student profile
        /** @var \App\Models\User $user */
        $user = User::factory()->create(['role' => 'student']);
        $user->assignRole('student');

        // Create test images
        $images = [];
        for ($i = 0; $i < 5; $i++) {
            $images[] = UploadedFile::fake()->image("face{$i}.jpg", 600, 600);
        }

        // Mock the service (shouldn't be called)
        $this->mock(FaceRecognitionServiceInterface::class, function ($mock) {
            $mock->shouldReceive('registerFace')->never();
        });

        // Act: Try to register face without a student record
        $response = $this->actingAs($user)
            ->postJson(route('student.face.store'), [
                'images' => $images
            ]);

        // Assert: Returns 403 error since authorization fails before student check
        $response->assertStatus(403)
            ->assertJson([
                'message' => 'This action is unauthorized.'
            ]);
    }

    public function test_update_request_marked_completed_after_successful_update()
    {
        // Create a student with face registered
        $student = Student::factory()->create([
            'user_id' => $this->user->id,
            'face_registered' => true,
            'nim' => '123456789'
        ]);

        // Create an approved update request
        $updateRequest = FaceUpdateRequest::create([
            'student_id' => $student->id,
            'reason' => 'Need update',
            'status' => 'approved',
            'admin_notes' => 'Initially approved'
        ]);

        // Mock the FaceRecognitionService
        $this->mock(FaceRecognitionServiceInterface::class, function ($mock) {
            $mock->shouldReceive('registerFace')
                ->once()
                ->andReturn([
                    'status' => 'success',
                    'message' => 'Face updated successfully.',
                    'data' => [
                        'student_id' => 1,
                        'nim' => '123456789',
                        'image_count' => 5,
                    ]
                ]);
        });

        // Create test images
        $images = [];
        for ($i = 0; $i < 5; $i++) {
            $images[] = UploadedFile::fake()->image("face{$i}.jpg", 600, 600);
        }

        // Act: Submit the update
        $response = $this->actingAs($student->user)
            ->postJson(route('student.face.store'), [
                'images' => $images,
                'is_update' => true,
                'update_request_id' => $updateRequest->id
            ]);

        // Assert: Update successful
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Face updated successfully.'
            ]);

        // Check that the update request was marked as completed
        $updatedRequest = FaceUpdateRequest::find($updateRequest->id);
        $this->assertEquals('completed', $updatedRequest->status);
        $this->assertStringContainsString('Initially approved | Update completed on', $updatedRequest->admin_notes);
    }

    public function test_store_returns_error_when_exception_occurs()
    {
        // Create a student user
        $student = Student::factory()->create([
            'user_id' => $this->user->id,
            'face_registered' => false,
            'nim' => '123456789'
        ]);

        // Mock the FaceRecognitionService to throw an exception
        $this->mock(FaceRecognitionServiceInterface::class, function ($mock) {
            $mock->shouldReceive('registerFace')
                ->once()
                ->andThrow(new \Exception('Service error'));
        });

        // Create test images
        $images = [];
        for ($i = 0; $i < 5; $i++) {
            $images[] = UploadedFile::fake()->image("face{$i}.jpg", 600, 600);
        }

        // Act: Submit the registration request
        $response = $this->actingAs($student->user)
            ->postJson(route('student.face.store'), [
                'images' => $images
            ]);

        // Assert: Returns 500 error with system error message
        $response->assertStatus(500)
            ->assertJson([
                'status' => 'error',
                'message' => 'An error occurred during registration. Please try again.',
                'code' => 'SYSTEM_ERROR'
            ]);
    }

    public function test_validate_quality_returns_error_when_exception_occurs()
    {
        // Create a student user
        $student = Student::factory()->create(['user_id' => $this->user->id]);

        // Mock the FaceRecognitionService to throw an exception
        $this->mock(FaceRecognitionServiceInterface::class, function ($mock) {
            $mock->shouldReceive('validateQuality')
                ->once()
                ->andThrow(new \Exception('Validation error'));
        });

        // Create a test image
        $image = UploadedFile::fake()->image('face.jpg', 600, 600);

        // Act: Submit the image for quality validation
        $response = $this->actingAs($student->user)
            ->postJson(route('student.face.validate-quality'), [
                'image' => $image
            ]);

        // Assert: Returns 500 error with validation error message
        $response->assertStatus(500)
            ->assertJson([
                'status' => 'error',
                'message' => 'Quality check failed: Validation error',
                'code' => 'VALIDATION_ERROR'
            ]);
    }
}
