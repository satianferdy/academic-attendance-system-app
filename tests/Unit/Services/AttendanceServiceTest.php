<?php

namespace Tests\Unit\Services;

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
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;
use Exception;

class AttendanceServiceTest extends TestCase
{
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
        $session->shouldReceive('getAttribute')->with('end_time')
            ->andReturn(Carbon::now()->addMinutes(10));
        $session->shouldReceive('getAttribute')->with('start_time')  // Add this missing expectation
            ->andReturn(Carbon::now()->subMinutes(10));
        $session->shouldReceive('getAttribute')->with('total_hours')
            ->andReturn(2);
        $session->shouldReceive('getAttribute')->with('tolerance_minutes')
            ->andReturn(15);

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
                return $data['status'] === 'present' &&
                       isset($data['attendance_time']) &&
                       isset($data['hours_present']) &&
                       isset($data['hours_absent']);
            }))
            ->andReturn($attendance);

        // Call the method
        $result = $this->attendanceService->markAttendance($classId, $studentId, $date);

        // Assert the result
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('Attendance marked successfully.', $result['message']);
        $this->assertArrayHasKey('attendance_hours', $result);
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
        $session->shouldReceive('getAttribute')->with('end_time')
            ->andReturn(Carbon::now()->subMinutes(10));

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

        // Create a fixed current time for testing
        $now = Carbon::parse('2023-07-01 12:00:00')->setTimezone(config('app.timezone'));
        Carbon::setTestNow($now);

        // Mock session with proper end_time
        $session = Mockery::mock(SessionAttendance::class);
        $endTime = Carbon::parse('2023-07-01 14:00:00')->setTimezone(config('app.timezone'));
        $session->shouldReceive('getAttribute')->with('end_time')->andReturn($endTime);

        // Setup repository expectations
        $this->sessionRepository->shouldReceive('findActiveByClassAndDate')
            ->once()
            ->with($classId, $date)
            ->andReturn($session);

        // Call the method
        $result = $this->attendanceService->isSessionActive($classId, $date);

        // Assert the result
        $this->assertTrue($result);

        // Clean up test time
        Carbon::setTestNow(null);
    }

    public function test_it_returns_false_when_no_session_found()
    {
        // Test data
        $classId = 1;
        $date = '2023-07-01';

        // Setup repository expectations - no session found
        $this->sessionRepository->shouldReceive('findActiveByClassAndDate')
            ->once()
            ->with($classId, $date)
            ->andReturn(null);

        // Call the method
        $result = $this->attendanceService->isSessionActive($classId, $date);

        // Assert the result
        $this->assertFalse($result);
    }

    public function test_it_returns_false_when_session_is_past()
    {
        // Test data
        $classId = 1;
        $date = '2023-06-30'; // Past date

        // Set current time to one day after session date
        $now = Carbon::parse('2023-07-01 12:00:00')->setTimezone(config('app.timezone'));
        Carbon::setTestNow($now);

        // Mock session
        $session = Mockery::mock(SessionAttendance::class);
        $endTime = Carbon::parse('2023-06-30 14:00:00')->setTimezone(config('app.timezone'));
        $session->shouldReceive('getAttribute')->with('end_time')->andReturn($endTime);

        // Setup repository expectations
        $this->sessionRepository->shouldReceive('findActiveByClassAndDate')
            ->once()
            ->with($classId, $date)
            ->andReturn($session);

        // Call the method
        $result = $this->attendanceService->isSessionActive($classId, $date);

        // Assert the result
        $this->assertFalse($result);

        // Clean up test time
        Carbon::setTestNow(null);
    }

    public function test_it_returns_false_when_current_time_is_after_session_end_time()
    {
        // Test data
        $classId = 1;
        $date = '2023-07-01';

        // Set current time to after session end time
        $now = Carbon::parse('2023-07-01 15:00:00')->setTimezone(config('app.timezone'));
        Carbon::setTestNow($now);

        // Mock session with end_time in the past
        $session = Mockery::mock(SessionAttendance::class);
        $endTime = Carbon::parse('2023-07-01 14:00:00')->setTimezone(config('app.timezone'));
        $session->shouldReceive('getAttribute')->with('end_time')->andReturn($endTime);

        // Setup repository expectations
        $this->sessionRepository->shouldReceive('findActiveByClassAndDate')
            ->once()
            ->with($classId, $date)
            ->andReturn($session);

        // Call the method
        $result = $this->attendanceService->isSessionActive($classId, $date);

        // Assert the result
        $this->assertFalse($result);

        // Clean up test time
        Carbon::setTestNow(null);
    }

    public function test_it_updates_attendance_status()
    {
        // Mock Auth facade
        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('id')->andReturn(10); // Teacher ID

        // Create a real collection for timeSlots
        $timeSlots = new Collection([1, 2]); // Two time slots

        // Create a class schedule instance using a standard class instead of a mock
        $classSchedule = new \stdClass();
        $classSchedule->timeSlots = $timeSlots;

        // Create an attendance model instance with realistic data
        $attendance = new Attendance();
        $attendance->id = 1;
        $attendance->class_schedule_id = 1;
        $attendance->student_id = 1;
        $attendance->date = '2023-07-01';
        $attendance->status = 'present';
        $attendance->hours_present = 2;
        $attendance->hours_absent = 0;
        $attendance->hours_permitted = 0;
        $attendance->hours_sick = 0;
        $attendance->remarks = null;

        // Set the class schedule relation
        $attendance->classSchedule = $classSchedule;

        // Data to update
        $data = [
            'status' => 'absent',
            'hours_present' => 0,
            'hours_absent' => 2,
            'edit_notes' => 'Student was not present'
        ];

        // Setup repository expectations
        $this->attendanceRepository->shouldReceive('update')
            ->once()
            ->withArgs(function($att, $updateData) use ($attendance, $data) {
                return $att === $attendance &&
                       $updateData['status'] === $data['status'] &&
                       $updateData['hours_present'] === $data['hours_present'] &&
                       $updateData['hours_absent'] === $data['hours_absent'] &&
                       $updateData['edit_notes'] === $data['edit_notes'] &&
                       isset($updateData['last_edited_at']) &&
                       $updateData['last_edited_by'] === 10;
            })
            ->andReturn($attendance);

        // Call the method
        $result = $this->attendanceService->updateAttendanceStatus($attendance, $data);

        // Assert the result
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('Attendance status updated successfully', $result['message']);
    }

    public function test_update_attendance_status_handles_exceptions()
    {
        // Mock the attendance repository
        $attendanceRepository = Mockery::mock(AttendanceRepositoryInterface::class);

        // Mock the exception scenario
        $attendanceRepository->shouldReceive('update')
            ->once()
            ->andThrow(new Exception('Database connection error'));

        // Create service instance with mocked repository
        $service = $this->createAttendanceServiceWithMockedRepository($attendanceRepository);

        // Create test data
        $attendance = new Attendance();
        $attendance->classSchedule = new ClassSchedule();

        // Prepare input data
        $data = [
            'status' => 'present',
            'remarks' => 'Test remarks',
            'hours_present' => 2,
            'hours_absent' => 1,
            'hours_permitted' => 0,
            'hours_sick' => 0
        ];

        // Call the method that should now throw an exception
        $result = $service->updateAttendanceStatus($attendance, $data);

        // Assert that the method returns an error response
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Failed to update attendance status: Database connection error', $result['message']);
    }

    private function createAttendanceServiceWithMockedRepository($attendanceRepository)
    {
        // Create the other required repositories (mocked but not expecting calls)
        $sessionRepository = Mockery::mock('App\Repositories\Interfaces\SessionAttendanceRepositoryInterface');
        $classScheduleRepository = Mockery::mock('App\Repositories\Interfaces\ClassScheduleRepositoryInterface');
        $studentRepository = Mockery::mock('App\Repositories\Interfaces\StudentRepositoryInterface');

        // Return service instance with mocked repositories
        return new AttendanceService(
            $attendanceRepository,
            $sessionRepository,
            $classScheduleRepository,
            $studentRepository
        );
    }

    public function test_it_generates_session_attendance_successfully()
    {
        // Use a cleaner approach to mock DB facade
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();
        DB::shouldReceive('rollBack')->never();

        // Test data
        $classId = 1;
        $date = '2023-07-01';
        $week = 1;
        $meetings = 1;
        $totalHours = 2;
        $toleranceMinutes = 15;

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

        $this->sessionRepository->shouldReceive('sessionExistsForDate')
            ->once()
            ->with($classId, $date)
            ->andReturn(false);

        $this->sessionRepository->shouldReceive('findByClassWeekAndMeeting')
            ->once()
            ->with($classId, $week, $meetings)
            ->andReturn(null);

        $this->sessionRepository->shouldReceive('createOrUpdate')
            ->once()
            ->with(
                Mockery::on(function ($attributes) use ($classId, $date, $week, $meetings) {
                    return $attributes['class_schedule_id'] == $classId &&
                           $attributes['session_date'] == $date &&
                           $attributes['week'] == $week &&
                           $attributes['meetings'] == $meetings;
                }),
                Mockery::on(function ($values) {
                    return isset($values['start_time']) &&
                           isset($values['end_time']) &&
                           $values['is_active'] === true &&
                           isset($values['total_hours']) &&
                           isset($values['tolerance_minutes']);
                })
            )
            ->andReturn($session);

        $this->attendanceRepository->shouldReceive('createOrUpdateByClassStudentDate')
            ->twice()
            ->andReturn(Mockery::mock(Attendance::class));

        // Call the method
        $result = $this->attendanceService->generateSessionAttendance($classId, $date, $week, $meetings, $totalHours, $toleranceMinutes);

        // Assert the result
        $this->assertEquals('success', $result['status']);
        $this->assertStringContainsString('Session and attendances generated successfully', $result['message']);
        $this->assertEquals(1, $result['session_id']);
    }

    public function test_it_fails_to_generate_session_attendance_without_students()
    {
        // Use a cleaner approach to mock DB facade
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();
        DB::shouldReceive('commit')->never();

        // Test data
        $classId = 1;
        $date = '2023-07-01';
        $week = 1;
        $meetings = 1;
        $totalHours = 2;
        $toleranceMinutes = 15;

        // Mock class schedule
        $classSchedule = Mockery::mock(ClassSchedule::class);
        $classSchedule->shouldReceive('getAttribute')->with('id')->andReturn($classId);
        $classSchedule->shouldReceive('getAttribute')->with('students')->andReturn(new Collection([]));
        $classSchedule->shouldIgnoreMissing();

        // Setup repository expectations
        $this->classScheduleRepository->shouldReceive('find')
            ->once()
            ->with($classId)
            ->andReturn($classSchedule);

        $this->sessionRepository->shouldReceive('sessionExistsForDate')
            ->once()
            ->with($classId, $date)
            ->andReturn(false);

        $this->sessionRepository->shouldReceive('findByClassWeekAndMeeting')
            ->once()
            ->with($classId, $week, $meetings)
            ->andReturn(null);

        // Call the method
        $result = $this->attendanceService->generateSessionAttendance($classId, $date, $week, $meetings, $totalHours, $toleranceMinutes);

        // Assert the result
        $this->assertEquals('error', $result['status']);
        $this->assertStringContainsString('Failed to generate attendances', $result['message']);
    }

    public function test_it_calculates_cumulative_attendance()
    {
        // Test data
        $classId = 1;
        $studentId = 1;

        // Mock attendance records
        $attendances = new Collection([
            (object)[
                'hours_present' => 2,
                'hours_absent' => 0,
                'hours_permitted' => 0,
                'hours_sick' => 0
            ],
            (object)[
                'hours_present' => 1,
                'hours_absent' => 1,
                'hours_permitted' => 0,
                'hours_sick' => 0
            ],
            (object)[
                'hours_present' => 0,
                'hours_absent' => 0,
                'hours_permitted' => 2,
                'hours_sick' => 0
            ]
        ]);

        // Setup repository expectations
        $this->attendanceRepository->shouldReceive('getAttendancesByClassAndStudent')
            ->once()
            ->with($classId, $studentId)
            ->andReturn($attendances);

        // Call the method
        $result = $this->attendanceService->getCumulativeAttendance($classId, $studentId);

        // Assert the result
        $this->assertEquals(3, $result['total_present']);
        $this->assertEquals(1, $result['total_absent']);
        $this->assertEquals(2, $result['total_permitted']);
        $this->assertEquals(0, $result['total_sick']);
    }

    public function test_it_calculates_hourly_attendance_based_on_arrival_time()
    {
        // Create a new instance of the service to call the protected method
        $service = new AttendanceService(
            $this->attendanceRepository,
            $this->sessionRepository,
            $this->classScheduleRepository,
            $this->studentRepository
        );

        // Test data - class starts at 9:00 AM, 2 hour session, 15 min tolerance
        $startTime = Carbon::parse('09:00:00');
        $totalHours = 2;
        $toleranceMinutes = 15;

        // Test case 1: Student arrives on time (9:05 AM)
        $arrivalTime1 = Carbon::parse('09:05:00');
        $result1 = $service->calculateHourlyAttendance($startTime, $arrivalTime1, $totalHours, $toleranceMinutes);

        $this->assertEquals(2, $result1['hours_present'], 'Student arriving at 9:05 should get 2 hours present');
        $this->assertEquals(0, $result1['hours_absent'], 'Student arriving at 9:05 should get 0 hours absent');

        // Test case 2: Student arrives late but within tolerance (9:14 AM)
        $arrivalTime2 = Carbon::parse('09:14:00');
        $result2 = $service->calculateHourlyAttendance($startTime, $arrivalTime2, $totalHours, $toleranceMinutes);

        $this->assertEquals(2, $result2['hours_present'], 'Student arriving at 9:14 should get 2 hours present');
        $this->assertEquals(0, $result2['hours_absent'], 'Student arriving at 9:14 should get 0 hours absent');

        // Test case 3: Student arrives after tolerance for first hour (9:20 AM)
        $arrivalTime3 = Carbon::parse('09:20:00');
        $result3 = $service->calculateHourlyAttendance($startTime, $arrivalTime3, $totalHours, $toleranceMinutes);

        $this->assertEquals(1, $result3['hours_present'], 'Student arriving at 9:20 should get 1 hour present');
        $this->assertEquals(1, $result3['hours_absent'], 'Student arriving at 9:20 should get 1 hour absent');

        // Test case 4: Student arrives very late (10:20 AM)
        $arrivalTime4 = Carbon::parse('10:20:00');
        $result4 = $service->calculateHourlyAttendance($startTime, $arrivalTime4, $totalHours, $toleranceMinutes);

        $this->assertEquals(0, $result4['hours_present'], 'Student arriving at 10:20 should get 0 hours present');
        $this->assertEquals(2, $result4['hours_absent'], 'Student arriving at 10:20 should get 2 hours absent');
    }
}
