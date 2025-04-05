<?php

namespace Tests\Unit\Services;

use App\Exceptions\AttendanceException;
use App\Models\Attendance;
use App\Models\ClassSchedule;
use App\Models\SessionAttendance;
use App\Models\Student;
use App\Repositories\Interfaces\AttendanceRepositoryInterface;
use App\Repositories\Interfaces\ClassScheduleRepositoryInterface;
use App\Repositories\Interfaces\SessionAttendanceRepositoryInterface;
use App\Repositories\Interfaces\StudentRepositoryInterface;
use App\Services\Implementations\AttendanceService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class AttendanceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $attendanceRepository;
    protected $sessionRepository;
    protected $classScheduleRepository;
    protected $studentRepository;
    protected $attendanceService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mocks for repositories
        $this->attendanceRepository = Mockery::mock(AttendanceRepositoryInterface::class);
        $this->sessionRepository = Mockery::mock(SessionAttendanceRepositoryInterface::class);
        $this->classScheduleRepository = Mockery::mock(ClassScheduleRepositoryInterface::class);
        $this->studentRepository = Mockery::mock(StudentRepositoryInterface::class);

        // Inject mocks into service
        $this->attendanceService = new AttendanceService(
            $this->attendanceRepository,
            $this->sessionRepository,
            $this->classScheduleRepository,
            $this->studentRepository
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_marks_attendance_successfully()
    {
        // Test data
        $classId = 1;
        $studentId = 1;
        $date = '2023-07-01';

        // Mock active session with future end time
        $session = Mockery::mock(SessionAttendance::class);
        $session->shouldReceive('getAttribute')->with('end_time')->andReturn(Carbon::now()->addMinutes(10));

        // Mock attendance record
        $attendance = Mockery::mock(Attendance::class);

        // Setup repository expectations
        $this->sessionRepository->shouldReceive('findActiveByClassAndDate')
            ->once()
            ->with($classId, $date)
            ->andReturn($session);

        $this->attendanceRepository->shouldReceive('findByClassStudentAndDate')
            ->once()
            ->with($classId, $studentId, $date)
            ->andReturn($attendance);

        $this->attendanceRepository->shouldReceive('update')
            ->once()
            ->with($attendance, Mockery::on(function ($data) {
                return $data['status'] === 'present' && isset($data['attendance_time']);
            }))
            ->andReturn($attendance);

        // Call the method
        $result = $this->attendanceService->markAttendance($classId, $studentId, $date);

        // Assert the result
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('Attendance marked successfully.', $result['message']);
    }

    public function test_it_fails_to_mark_attendance_without_active_session()
    {
        // Test data
        $classId = 1;
        $studentId = 1;
        $date = '2023-07-01';

        // Setup repository expectations
        $this->sessionRepository->shouldReceive('findActiveByClassAndDate')
            ->once()
            ->with($classId, $date)
            ->andReturn(null);

        // Call the method
        $result = $this->attendanceService->markAttendance($classId, $studentId, $date);

        // Assert the result
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Failed to mark attendance: No active attendance session found.', $result['message']);
    }

    public function test_it_fails_to_mark_attendance_with_expired_session()
    {
        // Test data
        $classId = 1;
        $studentId = 1;
        $date = '2023-07-01';

        // Mock active session with past end time
        $session = Mockery::mock(SessionAttendance::class);
        $session->shouldReceive('getAttribute')->with('end_time')->andReturn(Carbon::now()->subMinutes(10));

        // Setup repository expectations
        $this->sessionRepository->shouldReceive('findActiveByClassAndDate')
            ->once()
            ->with($classId, $date)
            ->andReturn($session);

        // Call the method
        $result = $this->attendanceService->markAttendance($classId, $studentId, $date);

        // Assert the result
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Failed to mark attendance: Attendance session has expired.', $result['message']);
    }

    public function test_it_gets_attendance_by_class()
    {
        // Test data
        $classId = 1;
        $date = '2023-07-01';

        // Mock collection of attendances
        $attendances = new Collection([
            (object)['id' => 1, 'student_id' => 1, 'status' => 'present'],
            (object)['id' => 2, 'student_id' => 2, 'status' => 'absent']
        ]);

        // Setup repository expectations
        $this->attendanceRepository->shouldReceive('getStudentAttendanceByClass')
            ->once()
            ->with($classId, $date)
            ->andReturn($attendances);

        // Call the method
        $result = $this->attendanceService->getAttendanceByClass($classId, $date);

        // Assert the result
        $this->assertCount(2, $result);
        $this->assertEquals(1, $result[0]->id);
        $this->assertEquals(2, $result[1]->id);
    }

    public function test_it_checks_if_student_is_enrolled_in_class()
    {
        // Test data
        $studentId = 1;
        $classId = 1;

        // Setup repository expectations
        $this->studentRepository->shouldReceive('isEnrolledInClass')
            ->once()
            ->with($studentId, $classId)
            ->andReturn(true);

        // Call the method
        $result = $this->attendanceService->isStudentEnrolled($studentId, $classId);

        // Assert the result
        $this->assertTrue($result);
    }

    public function test_it_checks_if_attendance_is_already_marked()
    {
        // Test data
        $studentId = 1;
        $classId = 1;
        $date = '2023-07-01';

        // Mock attendance
        $attendance = Mockery::mock(Attendance::class);
        $attendance->shouldReceive('getAttribute')->with('status')->andReturn('present');

        // Setup repository expectations
        $this->attendanceRepository->shouldReceive('findByClassStudentAndDate')
            ->once()
            ->with($classId, $studentId, $date)
            ->andReturn($attendance);

        // Call the method
        $result = $this->attendanceService->isAttendanceAlreadyMarked($studentId, $classId, $date);

        // Assert the result
        $this->assertTrue($result);
    }

    public function test_it_checks_if_session_is_active()
    {
        // Test data
        $classId = 1;
        $date = '2023-07-01';

        // Mock session
        $session = Mockery::mock(SessionAttendance::class);

        // Setup repository expectations
        $this->sessionRepository->shouldReceive('findActiveByClassAndDate')
            ->once()
            ->with($classId, $date)
            ->andReturn($session);

        // Call the method
        $result = $this->attendanceService->isSessionActive($classId, $date);

        // Assert the result
        $this->assertTrue($result);
    }

    public function test_it_updates_attendance_status()
    {
        // Mock attendance
        $attendance = Mockery::mock(Attendance::class);
        $status = 'absent';

        // Setup repository expectations
        $this->attendanceRepository->shouldReceive('update')
            ->once()
            ->with($attendance, ['status' => $status])
            ->andReturn($attendance);

        // Call the method
        $result = $this->attendanceService->updateAttendanceStatus($attendance, $status);

        // Assert the result
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('Attendance status updated successfully', $result['message']);
    }

    public function test_it_generates_session_attendance_successfully()
    {
        // Mock DB facade
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();

        // Test data
        $classId = 1;
        $date = '2023-07-01';

        // Mock class schedule
        $classSchedule = Mockery::mock(ClassSchedule::class);
        $classSchedule->shouldReceive('getAttribute')->with('id')->andReturn($classId);

        // Mock students collection
        $student1 = Mockery::mock(Student::class);
        $student1->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $student2 = Mockery::mock(Student::class);
        $student2->shouldReceive('getAttribute')->with('id')->andReturn(2);
        $students = new Collection([$student1, $student2]);

        $classSchedule->shouldReceive('getAttribute')->with('students')->andReturn($students);
        $classSchedule->shouldIgnoreMissing();

        // Mock session
        $session = Mockery::mock(SessionAttendance::class);
        $session->shouldReceive('getAttribute')->with('id')->andReturn(1);

        // Setup repository expectations
        $this->classScheduleRepository->shouldReceive('find')
            ->once()
            ->with($classId)
            ->andReturn($classSchedule);

        $this->sessionRepository->shouldReceive('createOrUpdate')
            ->once()
            ->andReturn($session);

        $this->attendanceRepository->shouldReceive('createOrUpdateByClassStudentDate')
            ->twice()
            ->andReturn(Mockery::mock(Attendance::class));

        // Call the method
        $result = $this->attendanceService->generateSessionAttendance($classId, $date);

        // Assert the result
        $this->assertEquals('success', $result['status']);
        $this->assertStringContainsString('Session and attendances generated successfully', $result['message']);
        $this->assertEquals(1, $result['session_id']);
    }

    public function test_it_fails_to_generate_session_attendance_without_students()
    {
        // Mock DB facade
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();

        // Test data
        $classId = 1;
        $date = '2023-07-01';

        // Mock class schedule
        $classSchedule = Mockery::mock(ClassSchedule::class);
        $classSchedule->shouldReceive('getAttribute')->with('students')->andReturn(new Collection([]));
        $classSchedule->shouldIgnoreMissing();

        // Setup repository expectations
        $this->classScheduleRepository->shouldReceive('find')
            ->once()
            ->with($classId)
            ->andReturn($classSchedule);

        // Call the method
        $result = $this->attendanceService->generateSessionAttendance($classId, $date);

        // Assert the result
        $this->assertEquals('error', $result['status']);
        $this->assertStringContainsString('Failed to generate attendances', $result['message']);
    }
}
