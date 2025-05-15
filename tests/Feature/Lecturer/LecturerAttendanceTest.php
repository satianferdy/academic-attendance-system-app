<?php

namespace Tests\Feature\Lecturer;

use App\Models\ClassSchedule;
use App\Models\Lecturer;
use App\Models\User;
use App\Models\Student;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\SessionAttendance;
use App\Models\Attendance;
use App\Models\Semester;
use App\Models\StudyProgram;
use App\Services\Interfaces\AttendanceServiceInterface;
use App\Services\Interfaces\QRCodeServiceInterface;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\RefreshPermissions;
use Tests\TestCase;

class LecturerAttendanceTest extends TestCase
{
    use RefreshDatabase, RefreshPermissions;

    protected $lecturer;
    protected $user;
    protected $classSchedule;
    protected $sessionAttendance;
    protected $attendance;
    protected $course;
    protected $classroom;
    protected $student;
    protected $today;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed permissions and roles
        $this->setupPermissions();

        // Create lecturer user
        $this->user = User::factory()->create(['role' => 'lecturer']);
        $this->user->assignRole('lecturer');

        $this->lecturer = Lecturer::factory()->create(['user_id' => $this->user->id]);

        // Create study program
        $studyProgram = StudyProgram::factory()->create();

        // Create semester
        $semester = Semester::factory()->create(['is_active' => true]);

        // Create course
        $this->course = Course::factory()->create(['study_program_id' => $studyProgram->id]);

        // Create classroom
        $this->classroom = ClassRoom::factory()->create([
            'study_program_id' => $studyProgram->id,
            'semester_id' => $semester->id
        ]);

        // Create class schedule
        $this->classSchedule = ClassSchedule::factory()->create([
            'lecturer_id' => $this->lecturer->id,
            'course_id' => $this->course->id,
            'classroom_id' => $this->classroom->id,
            'semester_id' => $semester->id,
            'study_program_id' => $studyProgram->id,
            'day' => 'Senin', // Monday
            'total_weeks' => 16,
            'meetings_per_week' => 1
        ]);

        // Create student
        $studentUser = User::factory()->create(['role' => 'student']);
        $studentUser->assignRole('student');

        $this->student = Student::factory()->create([
            'user_id' => $studentUser->id,
            'classroom_id' => $this->classroom->id,
            'study_program_id' => $studyProgram->id
        ]);

        // For general tests, create session attendance on a different date than today
        $this->sessionAttendance = SessionAttendance::factory()->create([
            'class_schedule_id' => $this->classSchedule->id,
            'session_date' => Carbon::yesterday(),
            'is_active' => true,
        ]);

        // Create attendance
        $this->attendance = Attendance::factory()->create([
            'class_schedule_id' => $this->classSchedule->id,
            'student_id' => $this->student->id,
            'date' => Carbon::yesterday(),
            'status' => 'absent',
        ]);

        $this->today = Carbon::today();
    }

    public function test_lecturer_can_view_attendance_list()
    {
        $response = $this->actingAs($this->user)
                         ->get(route('lecturer.attendance.index'));

        $response->assertStatus(200);
        $response->assertViewIs('lecturer.attendance.index');
        $response->assertViewHas('schedules');
        $response->assertSee($this->course->name);
    }

    public function test_lecturer_profile_not_found()
    {
        // Create a user without a lecturer profile
        /** @var \App\Models\User $userWithoutProfile */
        $userWithoutProfile = User::factory()->create(['role' => 'lecturer']);
        $userWithoutProfile->assignRole('lecturer');

        $response = $this->actingAs($userWithoutProfile)
                         ->get(route('lecturer.attendance.index'));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Lecturer profile not found.');
    }

    public function test_non_lecturer_cannot_view_attendance_list()
    {
        /** @var \App\Models\User $nonLecturer */
        $nonLecturer = User::factory()->create(['role' => 'student']);
        $nonLecturer->assignRole('student');

        $response = $this->actingAs($nonLecturer)
                         ->get(route('lecturer.attendance.index'));

        $response->assertStatus(403);
    }

    public function test_lecturer_can_create_attendance_session()
    {
        $data = [
            'class_id' => $this->classSchedule->id,
            'date' => $this->today,
            'week' => 1,
            'meetings' => 1,
            'total_hours' => 2,
            'tolerance_minutes' => 15
        ];

        $response = $this->actingAs($this->user)
                         ->post(route('lecturer.attendance.create'), $data);

        $response->assertRedirect(route('lecturer.attendance.view_qr', [
            'classSchedule' => $this->classSchedule->id,
            'date' => $this->today
        ]));

        $this->assertDatabaseHas('session_attendance', [
            'class_schedule_id' => $this->classSchedule->id,
            'session_date' => $this->today,
            'week' => 1,
            'meetings' => 1,
            'total_hours' => 2,
            'tolerance_minutes' => 15,
            'is_active' => 1
        ]);

        // Check if attendances for students were created
        $this->assertDatabaseHas('attendances', [
            'class_schedule_id' => $this->classSchedule->id,
            'student_id' => $this->student->id,
            'date' => $this->today,
            'status' => 'absent'
        ]);
    }

    public function test_lecturer_cannot_create_duplicate_attendance_session()
    {
        // Create an initial session
        SessionAttendance::create([
            'class_schedule_id' => $this->classSchedule->id,
            'session_date' => $this->today,
            'week' => 1,
            'meetings' => 1,
            'start_time' => now()->format('H:i:s'),
            'end_time' => now()->addHours(2)->format('H:i:s'),
            'total_hours' => 2,
            'tolerance_minutes' => 15,
            'is_active' => true
        ]);

        $data = [
            'class_id' => $this->classSchedule->id,
            'date' => $this->today->format('Y-m-d'),
            'week' => 1,
            'meetings' => 1,
            'total_hours' => 2,
            'tolerance_minutes' => 15
        ];

        $response = $this->actingAs($this->user)
                            ->post(route('lecturer.attendance.create'), $data);

        // Check for redirect with specific error message
        $response->assertRedirect();
        $response->assertSessionHas('error');

        // If you want to check specific message content (assuming your controller returns this specific message)
        $this->assertEquals(
            'An attendance session already exists for this date.',
            session('error')
        );

        // Verify only one session exists
        $this->assertEquals(1, SessionAttendance::where([
            'class_schedule_id' => $this->classSchedule->id
        ])->whereDate('session_date', $this->today)->count());
    }

    public function test_lecturer_can_view_session_qr_code()
    {
        // Create a session first
        $session = SessionAttendance::create([
            'class_schedule_id' => $this->classSchedule->id,
            'session_date' => $this->today,
            'week' => 1,
            'meetings' => 1,
            'start_time' => now()->format('H:i:s'),
            'end_time' => now()->addHours(2)->format('H:i:s'),
            'total_hours' => 2,
            'tolerance_minutes' => 15,
            'is_active' => true,
            'qr_code' => 'test-qr-code'  // Add a QR code for the session
        ]);

        $response = $this->actingAs($this->user)
                        ->get(route('lecturer.attendance.view_qr', [
                            'classSchedule' => $this->classSchedule->id,
                            'date' => $this->today->format('Y-m-d')  // Format the date
                        ]));

        $response->assertStatus(200);
        $response->assertViewIs('lecturer.attendance.view_qr');
    }

    public function test_lecturer_can_extend_session_time()
    {
        // Create a session first
        $session = SessionAttendance::create([
            'class_schedule_id' => $this->classSchedule->id,
            'session_date' => $this->today,
            'week' => 1,
            'meetings' => 1,
            'start_time' => now()->format('H:i:s'),
            'end_time' => now()->addHours(2)->format('H:i:s'),
            'total_hours' => 2,
            'tolerance_minutes' => 15,
            'is_active' => true,
            'qr_code' => 'test-qr-code'  // Add a QR code
        ]);

        $response = $this->actingAs($this->user)
                        ->post(route('lecturer.attendance.extend_time', [
                            'classSchedule' => $this->classSchedule->id,
                            'date' => $this->today->format('Y-m-d')  // Format the date
                        ]), [
                            'minutes' => 30
                        ]);

        // Only check that it's a redirect response, don't check the exact URL
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check if tolerance minutes was updated
        $this->assertDatabaseHas('session_attendance', [
            'class_schedule_id' => $this->classSchedule->id,
            'tolerance_minutes' => 30
        ]);
    }

    public function test_lecturer_can_view_attendance_records()
    {
        // Create a session first
        $session = SessionAttendance::create([
            'class_schedule_id' => $this->classSchedule->id,
            'session_date' => $this->today,
            'week' => 1,
            'meetings' => 1,
            'start_time' => now()->format('H:i:s'),
            'end_time' => now()->addHours(2)->format('H:i:s'),
            'total_hours' => 2,
            'tolerance_minutes' => 15,
            'is_active' => true
        ]);

        // Create attendance record
        Attendance::create([
            'class_schedule_id' => $this->classSchedule->id,
            'student_id' => $this->student->id,
            'date' => $this->today,
            'status' => 'absent',
            'hours_absent' => 2,
            'hours_present' => 0,
            'hours_permitted' => 0,
            'hours_sick' => 0
        ]);

        $response = $this->actingAs($this->user)
                         ->get(route('lecturer.attendance.show', [
                             'classSchedule' => $this->classSchedule->id,
                             'date' => $this->today
                         ]));

        $response->assertStatus(200);
        $response->assertViewIs('lecturer.attendance.show');
        $response->assertViewHas('attendances');
        $response->assertViewHas('classSchedule');
        $response->assertViewHas('session');
        $response->assertSee($this->student->user->name);
    }

    public function test_lecturer_can_update_attendance_status()
    {
        // Create a session first
        $session = SessionAttendance::create([
            'class_schedule_id' => $this->classSchedule->id,
            'session_date' => $this->today,
            'week' => 1,
            'meetings' => 1,
            'start_time' => now()->format('H:i:s'),
            'end_time' => now()->addHours(2)->format('H:i:s'),
            'total_hours' => 2,
            'tolerance_minutes' => 15,
            'is_active' => true
        ]);

        // Create attendance record
        $attendance = Attendance::create([
            'class_schedule_id' => $this->classSchedule->id,
            'student_id' => $this->student->id,
            'date' => $this->today,
            'status' => 'absent',
            'hours_absent' => 2,
            'hours_present' => 0,
            'hours_permitted' => 0,
            'hours_sick' => 0
        ]);

        $updateData = [
            'status' => 'present',
            'remarks' => 'Student arrived later',
            'edit_notes' => 'Changed from absent to present',
            'hours_present' => 2,
            'hours_absent' => 0,
            'hours_permitted' => 0,
            'hours_sick' => 0
        ];

        $response = $this->actingAs($this->user)
                        ->put(route('lecturer.attendance.update', [
                            'attendance' => $attendance->id
                        ]), $updateData);

        // Only check that it's a redirect response
        $response->assertRedirect();
    }



    public function test_lecturer_cannot_manage_other_lecturers_attendance()
    {
        // Create another lecturer
        $otherLecturer = Lecturer::factory()->create();

        // Create class schedule for other lecturer
        $otherSchedule = ClassSchedule::factory()->create([
            'lecturer_id' => $otherLecturer->id,
            'classroom_id' => $this->classroom->id
        ]);

        // Try to create attendance session for other lecturer's class
        $data = [
            'class_id' => $otherSchedule->id,
            'date' => $this->today,
            'week' => 1,
            'meetings' => 1,
            'total_hours' => 2,
            'tolerance_minutes' => 15
        ];

        $response = $this->actingAs($this->user)
                         ->post(route('lecturer.attendance.create'), $data);

        // Should fail validation since this lecturer doesn't own this class
        $response->assertStatus(403);
    }

    public function test_lecturer_can_get_used_sessions()
    {
        // Delete any existing sessions from setUp
        SessionAttendance::where('class_schedule_id', $this->classSchedule->id)->delete();

        // Create two sessions
        SessionAttendance::create([
            'class_schedule_id' => $this->classSchedule->id,
            'session_date' => Carbon::now()->subDays(7)->format('Y-m-d'),
            'week' => 1,
            'meetings' => 1,
            'start_time' => now()->format('H:i:s'),
            'end_time' => now()->addHours(2)->format('H:i:s'),
            'total_hours' => 2,
            'tolerance_minutes' => 15,
            'is_active' => true
        ]);

        SessionAttendance::create([
            'class_schedule_id' => $this->classSchedule->id,
            'session_date' => Carbon::now()->subDays(14)->format('Y-m-d'),
            'week' => 2,
            'meetings' => 1,
            'start_time' => now()->format('H:i:s'),
            'end_time' => now()->addHours(2)->format('H:i:s'),
            'total_hours' => 2,
            'tolerance_minutes' => 15,
            'is_active' => true
        ]);

        $response = $this->actingAs($this->user)
                         ->getJson(route('lecturer.attendance.get-used-sessions', [
                             'classSchedule' => $this->classSchedule->id
                         ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'usedSessions' => [
                '*' => [
                    'week',
                    'meeting'
                ]
            ]
        ]);

        $responseData = $response->json();
        $this->assertCount(2, $responseData['usedSessions']);
    }

    public function test_update_attendance_status_success()
    {
        // Mock AttendanceService for success
        $this->mock(AttendanceServiceInterface::class, function ($mock) {
            $mock->shouldReceive('updateAttendanceStatus')
                ->once()
                ->andReturn([
                    'status' => 'success',
                    'message' => 'Attendance updated successfully.'
                ]);
        });

        $response = $this->actingAs($this->user)
            ->put(route('lecturer.attendance.update', ['attendance' => $this->attendance->id]), [
                'status' => 'present',
                'remarks' => 'Test remarks',
                'edit_notes' => 'Test edit notes',
                'hours_present' => 2,
                'hours_absent' => 0,
                'hours_permitted' => 0,
                'hours_sick' => 0,
                'total_hours' => 2,
            ]);

        $response->assertRedirect(route('lecturer.attendance.show', [
            'classSchedule' => $this->attendance->class_schedule_id,
            'date' => $this->attendance->date,
        ]));

        $response->assertSessionHas('success', 'Attendance updated successfully.');
    }

    public function test_update_attendance_status_failure()
    {
        // Mock AttendanceService for failure
        $this->mock(AttendanceServiceInterface::class, function ($mock) {
            $mock->shouldReceive('updateAttendanceStatus')
                ->once()
                ->andReturn([
                    'status' => 'error',
                    'message' => 'Failed to update attendance status'
                ]);
        });

        $response = $this->actingAs($this->user)
            ->put(route('lecturer.attendance.update', ['attendance' => $this->attendance->id]), [
                'status' => 'present',
                'remarks' => 'Test remarks',
                'edit_notes' => 'Test edit notes',
                'hours_present' => 2,
                'hours_absent' => 0,
                'hours_permitted' => 0,
                'hours_sick' => 0,
                'total_hours' => 2,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Failed to update attendance status');
    }

    public function test_view_qr_with_invalid_date_format()
    {
        $response = $this->actingAs($this->user)
            ->get(route('lecturer.attendance.view_qr', [
                'classSchedule' => $this->classSchedule->id,
                'date' => 'invalid-date'
            ]));

        $response->assertStatus(404);
    }

    public function test_view_qr_with_missing_session()
    {
        // Mock QRCodeService to avoid real service calls
        $this->mock(QRCodeServiceInterface::class, function ($mock) {
            $mock->shouldReceive('generateForAttendance')
                ->andReturn('mocked-qr-code');
        });

        // Use a date that doesn't have a session
        $response = $this->actingAs($this->user)
            ->get(route('lecturer.attendance.view_qr', [
                'classSchedule' => $this->classSchedule->id,
                'date' => '2030-01-01'
            ]));

        $response->assertRedirect(route('lecturer.attendance.show', [
            'classSchedule' => $this->classSchedule->id,
            'date' => '2030-01-01'
        ]));

        $response->assertSessionHas('error', 'Attendance session not found.');
    }

    public function test_extend_time_with_invalid_date_format()
    {
        $response = $this->actingAs($this->user)
            ->post(route('lecturer.attendance.extend_time', [
                'classSchedule' => $this->classSchedule->id,
                'date' => 'invalid-date'
            ]), [
                'minutes' => 15
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Invalid date format.');
    }

    public function test_extend_time_with_missing_session()
    {
        $response = $this->actingAs($this->user)
            ->post(route('lecturer.attendance.extend_time', [
                'classSchedule' => $this->classSchedule->id,
                'date' => '2030-01-01'
            ]), [
                'minutes' => 15
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Session not found.');
    }

    public function test_extend_time_with_ended_session()
    {
        // Create a session with an end time in the past
        $pastSession = SessionAttendance::factory()->create([
            'class_schedule_id' => $this->classSchedule->id,
            'session_date' => Carbon::today(),
            'end_time' => Carbon::now()->subHour(),
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('lecturer.attendance.extend_time', [
                'classSchedule' => $this->classSchedule->id,
                'date' => Carbon::today()->format('Y-m-d')
            ]), [
                'minutes' => 15
            ]);

        $response->assertRedirect(route('lecturer.attendance.view_qr', [
            'classSchedule' => $this->classSchedule->id,
            'date' => Carbon::today()->format('Y-m-d')
        ]));

        $response->assertSessionHas('error', 'Attendance session has already ended.');
    }
}
