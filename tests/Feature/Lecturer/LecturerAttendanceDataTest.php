<?php

namespace Tests\Feature\Lecturer;

use App\Models\Attendance;
use App\Models\ClassSchedule;
use App\Models\SessionAttendance;
use App\Models\Student;
use App\Models\StudyProgram;
use App\Models\User;
use App\Models\Lecturer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Tests\Feature\FeatureTestCase;

class LecturerAttendanceDataTest extends FeatureTestCase
{
    use RefreshDatabase, WithFaker;

    protected $lecturer;
    protected $lecturerUser;
    protected $otherLecturerUser;
    protected $studyProgram;
    protected $classSchedule;
    protected $otherClassSchedule;
    protected $sessionAttendance;
    protected $student;
    protected $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles if they don't exist
        if (!Role::where('name', 'lecturer')->exists()) {
            Role::create(['name' => 'lecturer']);
        }
        if (!Role::where('name', 'student')->exists()) {
            Role::create(['name' => 'student']);
        }

        // Create lecturer user
        $this->lecturerUser = User::factory()->create(['role' => 'lecturer']);
        $this->lecturerUser->assignRole('lecturer');
        $this->lecturer = Lecturer::factory()->create(['user_id' => $this->lecturerUser->id]);

        // Create another lecturer for testing unauthorized access
        $this->otherLecturerUser = User::factory()->create(['role' => 'lecturer']);
        $this->otherLecturerUser->assignRole('lecturer');
        $otherLecturer = Lecturer::factory()->create(['user_id' => $this->otherLecturerUser->id]);

        // Create test data for attendance
        $this->studyProgram = StudyProgram::factory()->create();
        $this->classSchedule = ClassSchedule::factory()->create([
            'study_program_id' => $this->studyProgram->id,
            'lecturer_id' => $this->lecturer->id // Assigned to our test lecturer
        ]);

        // Create a class schedule for the other lecturer
        $this->otherClassSchedule = ClassSchedule::factory()->create([
            'study_program_id' => $this->studyProgram->id,
            'lecturer_id' => $otherLecturer->id
        ]);

        // Create session attendance
        $this->sessionAttendance = SessionAttendance::factory()->create([
            'class_schedule_id' => $this->classSchedule->id,
            'total_hours' => 4
        ]);

        // Create session for the other lecturer
        $otherSession = SessionAttendance::factory()->create([
            'class_schedule_id' => $this->otherClassSchedule->id,
            'total_hours' => 4
        ]);

        // Create student
        $this->student = Student::factory()->create([
            'study_program_id' => $this->studyProgram->id,
            'classroom_id' => $this->classSchedule->classroom_id
        ]);

        // Create attendance record
        $this->attendance = Attendance::factory()->create([
            'class_schedule_id' => $this->classSchedule->id,
            'student_id' => $this->student->id,
            'date' => $this->sessionAttendance->session_date,
            'status' => 'present',
            'hours_present' => 3,
            'hours_absent' => 1,
            'hours_permitted' => 0,
            'hours_sick' => 0
        ]);
    }

    public function test_lecturer_can_view_attendance_index_page()
    {
        // Act as lecturer and access the attendance index page
        $response = $this->actingAs($this->lecturerUser)
            ->get(route('lecturer.attendance-data.index'));

        // Assert the response
        $response->assertStatus(200);
        $response->assertViewIs('lecturer.attendance-data.index');
        $response->assertViewHas(['studyPrograms', 'classSchedules']);
    }

    public function test_lecturer_without_profile_gets_redirected()
    {
        // Create a lecturer user without an associated lecturer profile
        /** @var \App\Models\User $userWithoutProfile */
        $userWithoutProfile = User::factory()->create(['role' => 'lecturer']);
        $userWithoutProfile->assignRole('lecturer');

        // Act as this user and access the attendance index page
        $response = $this->actingAs($userWithoutProfile)
            ->get(route('lecturer.attendance-data.index'));

        // Assert the response redirects with an error message
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Lecturer profile not found.');
    }

    public function test_lecturer_can_filter_class_schedules_by_study_program()
    {
        // Act as lecturer and filter by study program
        $response = $this->actingAs($this->lecturerUser)
            ->get(route('lecturer.attendance-data.index', [
                'study_program_id' => $this->studyProgram->id
            ]));

        // Assert the response
        $response->assertStatus(200);
        $response->assertViewHas('classSchedules');
        $response->assertViewHas('selectedProgramId', $this->studyProgram->id);
    }

    public function test_lecturer_can_view_attendance_data_for_own_class()
    {
        // Act as lecturer and view attendance data for their own class
        $response = $this->actingAs($this->lecturerUser)
            ->get(route('lecturer.attendance-data.index', [
                'study_program_id' => $this->studyProgram->id,
                'class_schedule_id' => $this->classSchedule->id
            ]));

        // Assert the response
        $response->assertStatus(200);
        $response->assertViewHas('sessionsList');
        $response->assertViewHas('cumulativeData');
    }

    public function test_lecturer_cannot_view_attendance_data_for_other_lecturers_class()
    {
        // Act as lecturer and try to view attendance data for another lecturer's class
        $response = $this->actingAs($this->lecturerUser)
            ->get(route('lecturer.attendance-data.index', [
                'study_program_id' => $this->studyProgram->id,
                'class_schedule_id' => $this->otherClassSchedule->id
            ]));

        // Assert the response redirects with an error message
        $response->assertRedirect(route('lecturer.attendance-data.index'));
        $response->assertSessionHas('error', 'Unauthorized access to this class schedule');
    }

    public function test_lecturer_can_edit_own_attendance_session()
    {
        // Act as lecturer and view the edit session page for their own class
        $response = $this->actingAs($this->lecturerUser)
            ->get(route('lecturer.attendance-data.edit-session', [
                'session' => $this->sessionAttendance->id
            ]));

        // Assert the response
        $response->assertStatus(200);
        $response->assertViewIs('lecturer.attendance-data.edit');
        $response->assertViewHas(['session', 'attendances']);
    }

    public function test_lecturer_cannot_edit_other_lecturers_session()
    {
        // Create a session for the other lecturer's class
        $otherSession = SessionAttendance::factory()->create([
            'class_schedule_id' => $this->otherClassSchedule->id
        ]);

        // Act as lecturer and try to edit another lecturer's session
        $response = $this->actingAs($this->lecturerUser)
            ->getJson(route('lecturer.attendance-data.edit-session', [
                'session' => $otherSession->id
            ]));

        // Assert the response
        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'You are not authorized to access this session'
        ]);
    }

    public function test_lecturer_can_update_attendance_statuses()
    {
        // Prepare update data
        $updateData = [
            'attendances' => [
                [
                    'attendance_id' => $this->attendance->id,
                    'status' => 'late',
                    'hours_present' => 2,
                    'hours_absent' => 1,
                    'hours_permitted' => 1,
                    'hours_sick' => 0,
                    'remarks' => 'Updated by lecturer'
                ]
            ]
        ];

        // Act as lecturer and update the attendance
        $response = $this->actingAs($this->lecturerUser)
            ->postJson(route('lecturer.attendance-data.update-status'), $updateData);

        // Assert the response
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify the database was updated
        $this->assertDatabaseHas('attendances', [
            'id' => $this->attendance->id,
            'status' => 'late',
            'hours_present' => 2,
            'hours_absent' => 1,
            'hours_permitted' => 1,
            'remarks' => 'Updated by lecturer',
            'last_edited_by' => $this->lecturerUser->id
        ]);
    }

    public function test_lecturer_cannot_update_other_lecturers_attendance_records()
    {
        // Create attendance for the other lecturer's class
        $otherAttendance = Attendance::factory()->create([
            'class_schedule_id' => $this->otherClassSchedule->id,
            'student_id' => $this->student->id
        ]);

        // Prepare update data
        $updateData = [
            'attendances' => [
                [
                    'attendance_id' => $otherAttendance->id,
                    'status' => 'late',
                    'hours_present' => 2,
                    'hours_absent' => 1,
                    'hours_permitted' => 1,
                    'hours_sick' => 0
                ]
            ]
        ];

        // Act as lecturer and try to update another lecturer's attendance
        $response = $this->actingAs($this->lecturerUser)
            ->postJson(route('lecturer.attendance-data.update-status'), $updateData);

        // The update should succeed without errors but the count of updated records should be 0
        $response->assertStatus(200);
        $response->assertJsonPath('message', '0 attendance records updated successfully, 1 failed');

        // Verify the database was not updated
        $this->assertDatabaseMissing('attendances', [
            'id' => $otherAttendance->id,
            'status' => 'late'
        ]);
    }

    public function test_lecturer_cannot_update_with_invalid_hours_total()
    {
        // Prepare invalid update data (total hours doesn't match session hours)
        $updateData = [
            'attendances' => [
                [
                    'attendance_id' => $this->attendance->id,
                    'status' => 'present',
                    'hours_present' => 5, // Total exceeds session hours (4)
                    'hours_absent' => 0,
                    'hours_permitted' => 0,
                    'hours_sick' => 0
                ]
            ]
        ];

        // Act as lecturer and attempt to update the attendance
        $response = $this->actingAs($this->lecturerUser)
            ->postJson(route('lecturer.attendance-data.update-status'), $updateData);

        // Assert the response
        $response->assertStatus(200);
        $response->assertJsonPath('message', '0 attendance records updated successfully, 1 failed');

        // Verify the database was not updated
        $this->assertDatabaseHas('attendances', [
            'id' => $this->attendance->id,
            'status' => 'present',
            'hours_present' => 3 // Original value unchanged
        ]);
    }

    public function test_non_lecturer_cannot_access_attendance_management()
    {
        // Create a student user
        /** @var \App\Models\User $studentUser */
        $studentUser = User::factory()->create(['role' => 'student']);
        $studentUser->assignRole('student');

        // Act as student and try to access the lecturer attendance page
        $response = $this->actingAs($studentUser)
            ->get(route('lecturer.attendance-data.index'));

        // Assert the response is a 403 Forbidden
        $response->assertStatus(403);
    }
}
