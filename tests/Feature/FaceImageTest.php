<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\FaceData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\RefreshPermissions;

class FaceImageTest extends TestCase
{
    use RefreshDatabase, RefreshPermissions;

    protected $admin;
    protected $student;
    protected $studentUser;
    protected $faceData;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup permissions and roles
        $this->setupPermissions();

        // Create a fake storage disk for testing
        Storage::fake('local');

        // Create an admin user
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->admin->assignRole('admin');

        // Create a student user
        $this->studentUser = User::factory()->create(['role' => 'student']);
        $this->studentUser->assignRole('student');

        // Create a student with face data
        $this->student = Student::factory()->create([
            'user_id' => $this->studentUser->id,
            'face_registered' => true
        ]);

        // Create face data
        $this->faceData = FaceData::factory()->create([
            'student_id' => $this->student->id,
            'face_embedding' => json_encode(array_fill(0, 128, 0.1)),
            'image_path' => json_encode('face_images/test.jpg'),
            'is_active' => true
        ]);

        // Store a fake image
        Storage::put('face_images/test.jpg', 'fake image content');
    }

    public function test_admin_can_view_face_image()
    {
        // Act: Admin requests to view the student's face image
        $response = $this->actingAs($this->admin)
            ->get(route('face-images.show', ['studentId' => $this->student->id]));

        // Assert: Image is returned successfully
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/jpeg');
        $response->assertHeader('Content-Disposition', 'inline; filename="face-image.jpg"');
    }

    public function test_student_cannot_view_face_image()
    {
        // Act: Student tries to view their own face image
        $response = $this->actingAs($this->studentUser)
            ->get(route('face-images.show', ['studentId' => $this->student->id]));

        // Assert: Access is denied
        $response->assertStatus(403);
    }

    public function test_unauthorized_user_cannot_view_face_image()
    {
        // Act: Unauthenticated request
        $response = $this->get(route('face-images.show', ['studentId' => $this->student->id]));

        // Assert: Redirected to login
        $response->assertRedirect(route('login'));
    }

    public function test_returns_404_for_nonexistent_student()
    {
        // Act: Admin requests to view a non-existent student's face image
        $response = $this->actingAs($this->admin)
            ->get(route('face-images.show', ['studentId' => 999999]));

        // Assert: 404 Not Found
        $response->assertStatus(404);
    }

    public function test_returns_404_for_student_without_face_data()
    {
        // Create student without face data
        $studentWithoutFace = Student::factory()->create([
            'face_registered' => false
        ]);

        // Act: Admin requests to view a student's face image who has no face data
        $response = $this->actingAs($this->admin)
            ->get(route('face-images.show', ['studentId' => $studentWithoutFace->id]));

        // Assert: 404 Not Found
        $response->assertStatus(404);
    }

    public function test_returns_404_when_image_file_not_found()
    {
        // Modify face data to point to non-existent file
        $this->faceData->update([
            'image_path' => json_encode('face_images/nonexistent.jpg')
        ]);

        // Act: Admin requests to view the student's face image
        $response = $this->actingAs($this->admin)
            ->get(route('face-images.show', ['studentId' => $this->student->id]));

        // Assert: 404 Not Found
        $response->assertStatus(404);
    }
}
