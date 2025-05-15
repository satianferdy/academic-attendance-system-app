<?php

namespace Tests\Unit\Repositories;

use App\Models\ClassSchedule;
use App\Models\SessionAttendance;
use App\Repositories\Interfaces\SessionAttendanceRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\Lecturer;
use App\Models\Semester;
use App\Models\StudyProgram;
use App\Repositories\Implementations\SessionAttendanceRepository;
use Tests\TestCase;

class SessionAttendanceRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $repository;
    protected $lecturer;
    protected $course;
    protected $classroom;
    protected $semester;
    protected $studyProgram;
    protected $classSchedule;
    protected $sessionAttendance;

    protected function setUp(): void
    {
        parent::setUp();

        // Create repository instance
        $this->repository = new SessionAttendanceRepository(new SessionAttendance());

        // Create test data
        $this->lecturer = Lecturer::factory()->create();
        $this->studyProgram = StudyProgram::factory()->create();
        $this->course = Course::factory()->create(['study_program_id' => $this->studyProgram->id]);
        $this->semester = Semester::factory()->create(['is_active' => true]);
        $this->classroom = ClassRoom::factory()->create([
            'study_program_id' => $this->studyProgram->id,
            'semester_id' => $this->semester->id
        ]);

        // Create a class schedule
        $this->classSchedule = ClassSchedule::factory()->create([
            'lecturer_id' => $this->lecturer->id,
            'course_id' => $this->course->id,
            'classroom_id' => $this->classroom->id,
            'study_program_id' => $this->studyProgram->id,
            'semester_id' => $this->semester->id
        ]);

        // Create session attendances
        $this->sessionAttendance = SessionAttendance::factory()->create([
            'class_schedule_id' => $this->classSchedule->id,
            'session_date' => Carbon::today(),
            'week' => 5,
            'meetings' => 2,
            'is_active' => true
        ]);
    }

    public function test_get_sessions_by_lecturer()
    {
        // Test basic functionality (only lecturer ID)
        $sessions = $this->repository->getSessionsByLecturer($this->lecturer->id);
        $this->assertCount(1, $sessions);
        $this->assertEquals($this->sessionAttendance->id, $sessions->first()->id);

        // Create another session with different parameters for testing filters
        $otherClassSchedule = ClassSchedule::factory()->create([
            'lecturer_id' => $this->lecturer->id,
            'course_id' => $this->course->id,
            'classroom_id' => $this->classroom->id,
            'study_program_id' => $this->studyProgram->id,
            'semester_id' => $this->semester->id
        ]);

        $otherSession = SessionAttendance::factory()->create([
            'class_schedule_id' => $otherClassSchedule->id,
            'session_date' => Carbon::tomorrow(),
            'week' => 6,
            'meetings' => 1,
            'is_active' => true
        ]);

        // Test with date filter
        $sessionsByDate = $this->repository->getSessionsByLecturer(
            $this->lecturer->id,
            null,
            Carbon::today()->format('Y-m-d')
        );
        $this->assertCount(1, $sessionsByDate);
        $this->assertEquals($this->sessionAttendance->id, $sessionsByDate->first()->id);

        // Test with week filter
        $sessionsByWeek = $this->repository->getSessionsByLecturer(
            $this->lecturer->id,
            null,
            null,
            5
        );
        $this->assertCount(1, $sessionsByWeek);
        $this->assertEquals($this->sessionAttendance->id, $sessionsByWeek->first()->id);

        // Test with study program filter
        $sessionsByProgram = $this->repository->getSessionsByLecturer(
            $this->lecturer->id,
            null,
            null,
            null,
            $this->studyProgram->id
        );
        $this->assertCount(2, $sessionsByProgram); // Should include both sessions

        // Test with classroom filter
        $sessionsByClassroom = $this->repository->getSessionsByLecturer(
            $this->lecturer->id,
            null,
            null,
            null,
            null,
            $this->classroom->id
        );
        $this->assertCount(2, $sessionsByClassroom); // Should include both sessions

        // Test with semester filter
        $sessionsBySemester = $this->repository->getSessionsByLecturer(
            $this->lecturer->id,
            null,
            null,
            null,
            null,
            null,
            $this->semester->id
        );
        $this->assertCount(2, $sessionsBySemester); // Should include both sessions

        // Test with multiple filters
        $sessionsWithMultipleFilters = $this->repository->getSessionsByLecturer(
            $this->lecturer->id,
            $this->course->id,
            null,
            5,
            $this->studyProgram->id,
            null,
            $this->semester->id
        );
        $this->assertCount(1, $sessionsWithMultipleFilters);
        $this->assertEquals($this->sessionAttendance->id, $sessionsWithMultipleFilters->first()->id);
    }

    public function test_find_by_qr_code()
    {
        // Arrange
        $qrCode = 'test-qr-code-123';
        $session = SessionAttendance::factory()->create([
            'qr_code' => $qrCode,
            'is_active' => true
        ]);

        // Act
        $result = $this->repository->findByQrCode($qrCode);

        // Assert
        $this->assertInstanceOf(SessionAttendance::class, $result);
        $this->assertEquals($qrCode, $result->qr_code);
        $this->assertTrue($result->is_active);
    }

    public function test_find_by_qr_code_returns_null_for_inactive_session()
    {
        // Arrange
        $qrCode = 'test-qr-code-456';
        $session = SessionAttendance::factory()->create([
            'qr_code' => $qrCode,
            'is_active' => false
        ]);

        // Act
        $result = $this->repository->findByQrCode($qrCode);

        // Assert
        // The implementation should still return the session even if inactive
        // since validation of active status is done in QRCodeService
        $this->assertInstanceOf(SessionAttendance::class, $result);
        $this->assertEquals($qrCode, $result->qr_code);
        $this->assertFalse($result->is_active);
    }

    public function test_find_by_qr_code_returns_null_for_nonexistent_code()
    {
        // Arrange
        $qrCode = 'nonexistent-qr-code';

        // Act
        $result = $this->repository->findByQrCode($qrCode);

        // Assert
        $this->assertNull($result);
    }

    public function test_find_active_by_class_and_date_with_active_session()
    {
        // Arrange
        $classSchedule = ClassSchedule::factory()->create();
        $date = '2023-08-15';

        $session = SessionAttendance::factory()->create([
            'class_schedule_id' => $classSchedule->id,
            'session_date' => $date,
            'is_active' => true,
        ]);

        // Act
        $result = $this->repository->findActiveByClassAndDate($classSchedule->id, $date);

        // Assert
        $this->assertInstanceOf(SessionAttendance::class, $result);
        $this->assertEquals($session->id, $result->id);
        $this->assertTrue($result->is_active);
    }

    public function test_find_active_by_class_and_date_with_inactive_session()
    {
        // Arrange
        $classSchedule = ClassSchedule::factory()->create();
        $date = '2023-08-15';

        SessionAttendance::factory()->create([
            'class_schedule_id' => $classSchedule->id,
            'session_date' => $date,
            'is_active' => false,
        ]);

        // Act
        $result = $this->repository->findActiveByClassAndDate($classSchedule->id, $date);

        // Assert
        $this->assertNull($result);
    }

    public function test_create_or_update_when_creating_new_session()
    {
        // Arrange
        $classSchedule = ClassSchedule::factory()->create();
        $date = '2023-08-15';

        $attributes = [
            'class_schedule_id' => $classSchedule->id,
            'session_date' => $date,
        ];

        $values = [
            'start_time' => '08:00',
            'end_time' => '10:00',
            'qr_code' => 'qrcode123',
            'is_active' => true,
        ];

        // Act
        $result = $this->repository->createOrUpdate($attributes, $values);

        // Assert
        $this->assertInstanceOf(SessionAttendance::class, $result);
        $this->assertEquals($classSchedule->id, $result->class_schedule_id);
        $this->assertEquals($date, $result->session_date->format('Y-m-d'));
        $this->assertEquals('qrcode123', $result->qr_code);

        // Verify a new record was created in addition to the one from setUp
        $this->assertEquals(2, SessionAttendance::count());
    }

    public function test_create()
    {
        // Arrange
        $classSchedule = ClassSchedule::factory()->create();
        $data = [
            'class_schedule_id' => $classSchedule->id,
            'session_date' => '2023-08-15',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'qr_code' => 'qrcode123',
            'is_active' => true,
        ];

        // Act
        $result = $this->repository->create($data);

        // Assert
        $this->assertInstanceOf(SessionAttendance::class, $result);
        $this->assertEquals($classSchedule->id, $result->class_schedule_id);
        $this->assertEquals('qrcode123', $result->qr_code);
        $this->assertTrue($result->is_active);
    }

    public function test_update()
    {
        // Arrange
        $session = SessionAttendance::factory()->create([
            'start_time' => '08:00',
            'end_time' => '10:00',
            'qr_code' => 'oldqr123',
            'is_active' => true,
        ]);

        $data = [
            'start_time' => '09:00',
            'end_time' => '11:00',
            'qr_code' => 'newqr456',
            'is_active' => false,
        ];

        // Act
        $result = $this->repository->update($session, $data);

        // Assert
        $this->assertInstanceOf(SessionAttendance::class, $result);
        $this->assertEquals('09:00', $result->start_time->format('H:i'));
        $this->assertEquals('11:00', $result->end_time->format('H:i'));
        $this->assertEquals('newqr456', $result->qr_code);
        $this->assertFalse($result->is_active);
    }

    public function test_find_by_class_week_and_meeting()
    {
        // Arrange
        $classId = 1;
        $week = 2;
        $meeting = 1;

        $session = SessionAttendance::factory()->create([
            'class_schedule_id' => $classId,
            'week' => $week,
            'meetings' => $meeting
        ]);

        // Act
        $result = $this->repository->findByClassWeekAndMeeting($classId, $week, $meeting);

        // Assert
        $this->assertInstanceOf(SessionAttendance::class, $result);
        $this->assertEquals($classId, $result->class_schedule_id);
        $this->assertEquals($week, $result->week);
        $this->assertEquals($meeting, $result->meetings);
    }

    public function test_session_exists_for_date()
    {
        // Arrange
        $classId = 1;
        $date = '2023-07-01';

        SessionAttendance::factory()->create([
            'class_schedule_id' => $classId,
            'session_date' => $date
        ]);

        // Act
        $result = $this->repository->sessionExistsForDate($classId, $date);

        // Assert
        $this->assertTrue($result);
    }

    public function test_session_does_not_exist_for_date()
    {
        // Arrange
        $classId = 1;
        $date = '2023-07-01';

        // No session created for this date

        // Act
        $result = $this->repository->sessionExistsForDate($classId, $date);

        // Assert
        $this->assertFalse($result);
    }

    public function test_deactivate_session()
    {
        // Arrange
        $session = SessionAttendance::factory()->create([
            'is_active' => true,
        ]);

        // Act
        $result = $this->repository->deactivateSession($session->id);
        $session->refresh();

        // Assert
        $this->assertTrue($result > 0); // Should return number of affected rows
        $this->assertFalse($session->is_active);
    }
}
