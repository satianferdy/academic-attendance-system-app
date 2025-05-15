<?php

namespace Tests\Feature\Student;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\ClassRoom;
use App\Models\ClassSchedule;
use App\Models\StudyProgram;
use App\Models\Attendance;
use App\Models\SessionAttendance;
use App\Models\FaceData;
use App\Services\Interfaces\QRCodeServiceInterface;
use App\Services\Interfaces\FaceRecognitionServiceInterface;
use App\Services\Interfaces\AttendanceServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Tests\RefreshPermissions;

class StudentAttendanceTest extends TestCase
{
    use RefreshDatabase, RefreshPermissions;

    protected $user;
    protected $student;
    protected $classroom;
    protected $classSchedule;
    protected $sessionAttendance;
    protected $faceData;
    protected $qrToken = 'test-token-12345';

    protected function setUp(): void
    {
        parent::setUp();

        // Setup permissions and roles
        $this->setupPermissions();

        // Create student user
        $this->user = User::factory()->create(['role' => 'student']);
        $this->user->assignRole('student');

        // Create study program
        $studyProgram = StudyProgram::factory()->create();

        // Create classroom
        $this->classroom = ClassRoom::factory()->create([
            'study_program_id' => $studyProgram->id
        ]);

        // Create student
        $this->student = Student::factory()->create([
            'user_id' => $this->user->id,
            'classroom_id' => $this->classroom->id,
            'study_program_id' => $studyProgram->id,
            'face_registered' => true,
            'nim' => '123456789'
        ]);

        // Create face data directly in the database to avoid model casting issues
        $this->faceData = FaceData::factory()->create([
            'student_id' => $this->student->id,
            'face_embedding' => json_encode(array_fill(0, 128, 0.1)),
            'image_path' => json_encode(['path' => 'faces/test.jpg']),
            'is_active' => 1
        ]);

        // Create class schedule
        $this->classSchedule = ClassSchedule::factory()->create([
            'classroom_id' => $this->classroom->id,
            'study_program_id' => $studyProgram->id
        ]);

        // Create session attendance
        $this->sessionAttendance = SessionAttendance::create([
            'class_schedule_id' => $this->classSchedule->id,
            'session_date' => Carbon::today(),
            'start_time' => Carbon::now()->subHour(),
            'end_time' => Carbon::now()->addHour(),
            'week' => 1,
            'meetings' => 1,
            'total_hours' => 2,
            'tolerance_minutes' => 15,
            'qr_code' => $this->qrToken,
            'is_active' => true
        ]);

        // Create attendance record
        Attendance::create([
            'class_schedule_id' => $this->classSchedule->id,
            'student_id' => $this->student->id,
            'date' => Carbon::today(),
            'status' => 'absent',
            'hours_absent' => 2,
            'hours_present' => 0,
            'hours_permitted' => 0,
            'hours_sick' => 0
        ]);

        // Create fake storage disk for testing
        Storage::fake('local');
    }

    public function test_student_can_access_attendance_index()
    {
        $response = $this->actingAs($this->user)
            ->get(route('student.attendance.index'));

        $response->assertStatus(200);
        $response->assertViewIs('student.attendance.index');
        $response->assertViewHas('attendances');
    }

    public function test_student_cannot_access_attendance_without_auth()
    {
        $response = $this->get(route('student.attendance.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_student_can_view_qr_code_attendance_page()
    {
        // Mock QRCodeService
        $this->mock(QRCodeServiceInterface::class, function ($mock) {
            $mock->shouldReceive('validateToken')
                ->with($this->qrToken)
                ->once()
                ->andReturn([
                    'class_id' => $this->classSchedule->id,
                    'date' => Carbon::today()->format('Y-m-d')
                ]);
        });

        $response = $this->actingAs($this->user)
            ->get(route('student.attendance.show', ['token' => $this->qrToken]));

        $response->assertStatus(200);
        $response->assertViewIs('student.attendance.show');
        $response->assertViewHas('classSchedule');
        $response->assertViewHas('token', $this->qrToken);
    }

    public function test_student_is_redirected_if_face_not_registered()
    {
        // Update student to have face not registered
        $this->student->update(['face_registered' => false]);

        // Mock QRCodeService
        $this->mock(QRCodeServiceInterface::class, function ($mock) {
            $mock->shouldReceive('validateToken')
                ->with($this->qrToken)
                ->once()
                ->andReturn([
                    'class_id' => $this->classSchedule->id,
                    'date' => Carbon::today()->format('Y-m-d')
                ]);
        });

        $response = $this->actingAs($this->user)
            ->get(route('student.attendance.show', ['token' => $this->qrToken]));

        $response->assertRedirect(route('student.face.register', ['token' => $this->qrToken]));
        $response->assertSessionHas('warning', 'You need to register your face first.');
    }

    public function test_student_is_redirected_if_already_marked_present()
    {
        // Update attendance to present
        Attendance::where('student_id', $this->student->id)
            ->where('class_schedule_id', $this->classSchedule->id)
            ->update(['status' => 'present']);

        // Mock QRCodeService
        $this->mock(QRCodeServiceInterface::class, function ($mock) {
            $mock->shouldReceive('validateToken')
                ->with($this->qrToken)
                ->once()
                ->andReturn([
                    'class_id' => $this->classSchedule->id,
                    'date' => Carbon::today()->format('Y-m-d')
                ]);
        });

        // Mock AttendanceService
        $this->mock(AttendanceServiceInterface::class, function ($mock) {
            $mock->shouldReceive('isAttendanceAlreadyMarked')
                ->once()
                ->andReturn(true);
        });

        $response = $this->actingAs($this->user)
            ->get(route('student.attendance.show', ['token' => $this->qrToken]));

        $response->assertRedirect(route('student.attendance.index'));
        $response->assertSessionHas('info', 'You have already marked your attendance for this session.');
    }

    public function test_student_cannot_access_with_invalid_token()
    {
        // Mock QRCodeService
        $this->mock(QRCodeServiceInterface::class, function ($mock) {
            $mock->shouldReceive('validateToken')
                ->with('invalid-token')
                ->once()
                ->andReturn(null);
        });

        $response = $this->actingAs($this->user)
            ->get(route('student.attendance.show', ['token' => 'invalid-token']));

        $response->assertStatus(404);
    }

    public function test_student_can_verify_attendance()
    {
        // Mock QRCodeService
        $this->mock(QRCodeServiceInterface::class, function ($mock) {
            $mock->shouldReceive('validateToken')
                ->with($this->qrToken)
                ->once()
                ->andReturn([
                    'class_id' => $this->classSchedule->id,
                    'date' => Carbon::today()->format('Y-m-d')
                ]);
        });

        // Mock FaceRecognitionService
        $this->mock(FaceRecognitionServiceInterface::class, function ($mock) {
            $mock->shouldReceive('verifyFace')
                ->once()
                ->andReturn([
                    'status' => 'success',
                    'message' => 'Face verified successfully'
                ]);
        });

        // Mock AttendanceService
        $this->mock(AttendanceServiceInterface::class, function ($mock) {
            $mock->shouldReceive('isStudentEnrolled')
                ->once()
                ->andReturn(true);

            $mock->shouldReceive('isAttendanceAlreadyMarked')
                ->once()
                ->andReturn(false);

            $mock->shouldReceive('isSessionActive')
                ->once()
                ->andReturn(true);

            $mock->shouldReceive('markAttendance')
                ->once()
                ->andReturn([
                    'status' => 'success',
                    'message' => 'Attendance verified successfully.'
                ]);
        });

        $image = UploadedFile::fake()->image('face.jpg');

        $response = $this->actingAs($this->user)
            ->postJson(route('student.attendance.verify'), [
                'token' => $this->qrToken,
                'image' => $image
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Attendance verified successfully.'
        ]);
    }

    public function test_attendance_verification_fails_with_face_mismatch()
    {
        // Mock QRCodeService
        $this->mock(QRCodeServiceInterface::class, function ($mock) {
            $mock->shouldReceive('validateToken')
                ->with($this->qrToken)
                ->once()
                ->andReturn([
                    'class_id' => $this->classSchedule->id,
                    'date' => Carbon::today()->format('Y-m-d')
                ]);
        });

        // Mock FaceRecognitionService to return error
        $this->mock(FaceRecognitionServiceInterface::class, function ($mock) {
            $mock->shouldReceive('verifyFace')
                ->once()
                ->andReturn([
                    'status' => 'error',
                    'message' => 'Face verification failed',
                    'code' => 'VERIFICATION_ERROR'
                ]);
        });

        // Mock AttendanceService
        $this->mock(AttendanceServiceInterface::class, function ($mock) {
            $mock->shouldReceive('isStudentEnrolled')
                ->once()
                ->andReturn(true);

            $mock->shouldReceive('isAttendanceAlreadyMarked')
                ->once()
                ->andReturn(false);

            $mock->shouldReceive('isSessionActive')
                ->once()
                ->andReturn(true);
        });

        $image = UploadedFile::fake()->image('face.jpg');

        $response = $this->actingAs($this->user)
            ->postJson(route('student.attendance.verify'), [
                'token' => $this->qrToken,
                'image' => $image
            ]);

        $response->assertStatus(400);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Face verification failed',
            'code' => 'VERIFICATION_ERROR'
        ]);
    }

    public function test_attendance_verification_fails_when_session_expired()
    {
        // Mock QRCodeService first to avoid "end_time on null" error
        $this->mock(QRCodeServiceInterface::class, function ($mock) {
            $mock->shouldReceive('validateToken')
                ->with($this->qrToken)
                ->once()
                ->andReturn([
                    'class_id' => $this->classSchedule->id,
                    'date' => Carbon::today()->format('Y-m-d')
                ]);
        });

        // Mock AttendanceService
        $this->mock(AttendanceServiceInterface::class, function ($mock) {
            $mock->shouldReceive('isStudentEnrolled')
                ->once()
                ->andReturn(true);

            $mock->shouldReceive('isAttendanceAlreadyMarked')
                ->once()
                ->andReturn(false);

            $mock->shouldReceive('isSessionActive')
                ->once()
                ->andReturn(false);
        });

        $image = UploadedFile::fake()->image('face.jpg');

        $response = $this->actingAs($this->user)
            ->postJson(route('student.attendance.verify'), [
                'token' => $this->qrToken,
                'image' => $image
            ]);

        $response->assertStatus(400);
        $response->assertJson([
            'status' => 'error',
            'message' => 'This session is no longer active.'
        ]);
    }

    public function test_attendance_verification_fails_with_invalid_token()
    {
        // Mock QRCodeService
        $this->mock(QRCodeServiceInterface::class, function ($mock) {
            $mock->shouldReceive('validateToken')
                ->with('invalid-token')
                ->once()
                ->andReturn(null);
        });

        $image = UploadedFile::fake()->image('face.jpg');

        $response = $this->actingAs($this->user)
            ->postJson(route('student.attendance.verify'), [
                'token' => 'invalid-token',
                'image' => $image
            ]);

        $response->assertStatus(400);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Invalid or expired QR code.'
        ]);
    }

    public function test_attendance_verification_requires_image()
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('student.attendance.verify'), [
                'token' => $this->qrToken
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['image']);
    }

    public function test_student_not_enrolled_in_class()
    {
        // Mock the QRCodeService first to pass token validation
        $this->mock(QRCodeServiceInterface::class, function ($mock) {
            $mock->shouldReceive('validateToken')
                ->once()
                ->with('valid_token')
                ->andReturn([
                    'class_id' => $this->classSchedule->id,
                    'date' => Carbon::today()->format('Y-m-d')
                ]);
        });

        // Mock the AttendanceService
        $this->mock(AttendanceServiceInterface::class, function ($mock) {
            $mock->shouldReceive('isStudentEnrolled')
                ->once()
                ->with($this->student->id, $this->classSchedule->id)
                ->andReturn(false);

            // Prevent other method calls from failing
            $mock->shouldReceive('isAttendanceAlreadyMarked')->andReturn(false);
            $mock->shouldReceive('isSessionActive')->andReturn(true);
        });

        // Create a session for today
        $this->sessionAttendance = SessionAttendance::factory()->create([
            'class_schedule_id' => $this->classSchedule->id,
            'session_date' => Carbon::today(),
            'is_active' => true
        ]);

        // Act: Use the real route with a valid token
        $response = $this->actingAs($this->user)
            ->get(route('student.attendance.show', ['token' => 'valid_token']));

        // Assert: Should redirect with the correct error message
        $response->assertRedirect(route('student.attendance.index'));
        $response->assertSessionHas('error', 'You are not enrolled in this class.');
    }

    public function test_session_date_in_past()
    {
        // Setup: Create past date session
        $pastSession = SessionAttendance::factory()->create([
            'class_schedule_id' => $this->classSchedule->id,
            'session_date' => Carbon::yesterday(),
            'is_active' => true
        ]);

        // Mock services
        $this->mockAttendanceService(true, false);
        $this->mockQRCodeService(null, $this->classSchedule->id, Carbon::yesterday()->format('Y-m-d'));

        // Act: Try to verify attendance with past date
        $response = $this->actingAs($this->user)
            ->postJson(route('student.attendance.verify'), [
                'token' => 'valid_token',
                'image' => UploadedFile::fake()->image('face.jpg')
            ]);

        // Assert
        $response->assertStatus(400);
        $response->assertJson([
            'status' => 'error',
            'message' => 'This session has expired (session date is in the past).'
        ]);
    }

    public function test_session_expired_or_inactive()
    {
        // Setup: Create inactive session
        $inactiveSession = SessionAttendance::factory()->create([
            'class_schedule_id' => $this->classSchedule->id,
            'session_date' => Carbon::today(),
            'is_active' => false
        ]);

        // Mock services
        $this->mockAttendanceService(true, false);
        $this->mockQRCodeService(null, $this->classSchedule->id, Carbon::today()->format('Y-m-d'));

        // Mock the session repository to return inactive session
        $this->mock(\App\Repositories\Interfaces\SessionAttendanceRepositoryInterface::class, function ($mock) use ($inactiveSession) {
            $mock->shouldReceive('findByClassAndDate')
                ->andReturn($inactiveSession);
        });

        // Act
        $response = $this->actingAs($this->user)
            ->postJson(route('student.attendance.verify'), [
                'token' => 'valid_token',
                'image' => UploadedFile::fake()->image('face.jpg')
            ]);

        // Assert
        $response->assertStatus(400);
        $response->assertJson([
            'status' => 'error',
            'message' => 'This session has expired or is no longer active.'
        ]);
    }

    public function test_attendance_already_marked()
    {
        // Mock services
        $this->mockAttendanceService(true, true);
        $this->mockQRCodeService();

        // Act
        $response = $this->actingAs($this->user)
            ->postJson(route('student.attendance.verify'), [
                'token' => 'valid_token',
                'image' => UploadedFile::fake()->image('face.jpg')
            ]);

        // Assert
        $response->assertStatus(400);
        $response->assertJson([
            'status' => 'error',
            'message' => 'You have already marked your attendance for this session.'
        ]);
    }

    public function test_failed_to_mark_attendance()
    {
        // Mock services for a successful path until marking attendance
        $this->mockAttendanceService(true, false, true);
        $this->mockQRCodeService();

        // Mock face recognition to succeed
        $this->mock(FaceRecognitionServiceInterface::class, function ($mock) {
            $mock->shouldReceive('verifyFace')
                ->once()
                ->andReturn(['status' => 'success']);
        });

        // Act
        $response = $this->actingAs($this->user)
            ->postJson(route('student.attendance.verify'), [
                'token' => 'valid_token',
                'image' => UploadedFile::fake()->image('face.jpg')
            ]);

        // Assert
        $response->assertStatus(400);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Connection error'
        ]);
    }

    // Helper methods to reduce code duplication
    private function mockAttendanceService($isEnrolled = true, $alreadyMarked = false, $failMarkAttendance = false)
    {
        $this->mock(AttendanceServiceInterface::class, function ($mock) use ($isEnrolled, $alreadyMarked, $failMarkAttendance) {
            $mock->shouldReceive('isStudentEnrolled')
                ->andReturn($isEnrolled);

            $mock->shouldReceive('isAttendanceAlreadyMarked')
                ->andReturn($alreadyMarked);

            $mock->shouldReceive('isSessionActive')
                ->andReturn(true);

            if ($failMarkAttendance) {
                $mock->shouldReceive('markAttendance')
                    ->andReturn([
                        'status' => 'error',
                        'message' => 'Connection error'
                    ]);
            }
        });
    }

    private function mockQRCodeService($mock = null, $classId = null, $date = null)
    {
        if (!$mock) {
            $this->mock(QRCodeServiceInterface::class, function ($mock) use ($classId, $date) {
                $mock->shouldReceive('validateToken')
                    ->andReturn([
                        'class_id' => $classId ?? $this->classSchedule->id,
                        'date' => $date ?? Carbon::today()->format('Y-m-d')
                    ]);
            });
        } else {
            $mock->shouldReceive('validateToken')
                ->andReturn([
                    'class_id' => $classId ?? $this->classSchedule->id,
                    'date' => $date ?? Carbon::today()->format('Y-m-d')
                ]);
        }
    }

}
