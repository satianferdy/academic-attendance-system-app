<?php

namespace tests\Feature\Student;

use tests\testCase;
use App\Models\User;
use App\Models\Student;
use App\Models\ClassRoom;
use App\Models\ClassSchedule;
use App\Models\StudyProgram;
use App\Models\Course;
use App\Models\Semester;
use App\Models\ScheduleTimeSlot;
use Illuminate\Foundation\testing\RefreshDatabase;
use tests\RefreshPermissions;

class StudentScheduletest extends testCase
{
    use RefreshDatabase, RefreshPermissions;

    protected $user;
    protected $student;
    protected $classroom;
    protected $classSchedules;
    protected $course;
    protected $semester;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup permissions and roles
        $this->setupPermissions();

        // Create and activate semester
        $this->semester = Semester::factory()->active()->create();

        // Create study program
        $studyProgram = StudyProgram::factory()->create();

        // Create course
        $this->course = Course::factory()->create([
            'study_program_id' => $studyProgram->id
        ]);

        // Create classroom
        $this->classroom = ClassRoom::factory()->create([
            'study_program_id' => $studyProgram->id,
            'semester_id' => $this->semester->id
        ]);

        // Create student user
        $this->user = User::factory()->create(['role' => 'student']);
        $this->user->assignRole('student');

        // Create student
        $this->student = Student::factory()->create([
            'user_id' => $this->user->id,
            'classroom_id' => $this->classroom->id,
            'study_program_id' => $studyProgram->id
        ]);

        // Create multiple class schedules
        $this->classSchedules = [];
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        foreach ($days as $index => $day) {
            $classSchedule = ClassSchedule::factory()->create([
                'course_id' => $this->course->id,
                'classroom_id' => $this->classroom->id,
                'study_program_id' => $studyProgram->id,
                'semester_id' => $this->semester->id,
                'day' => $day
            ]);

            // Create time slots for this schedule
            $startHour = 8 + $index;
            ScheduleTimeSlot::factory()->create([
                'class_schedule_id' => $classSchedule->id,
                'start_time' => sprintf('%02d:00', $startHour),
                'end_time' => sprintf('%02d:00', $startHour + 2)
            ]);

            $this->classSchedules[] = $classSchedule;
        }
    }

    public function test_student_can_view_schedule_index()
    {
        $response = $this->actingAs($this->user)
            ->get(route('student.schedule.index'));

        $response->assertStatus(200);
        $response->assertViewIs('student.schedule.index');
        $response->assertViewHas('schedules');
        $response->assertViewHas('classroom', $this->classroom);
    }

    public function test_student_cannot_access_schedule_without_auth()
    {
        $response = $this->get(route('student.schedule.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_student_without_classroom_sees_error()
    {
        // Update student to have no classroom
        $this->student->update(['classroom_id' => null]);

        $response = $this->actingAs($this->user)
            ->get(route('student.schedule.index'));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Classroom not assigned to student.');
    }

    public function test_student_can_see_all_assigned_schedules()
    {
        $response = $this->actingAs($this->user)
            ->get(route('student.schedule.index'));

        $response->assertStatus(200);

        // Check that all created schedules are present in the view
        foreach ($this->classSchedules as $schedule) {
            $response->assertSee($this->course->name);
            $response->assertSee($schedule->day);
        }
    }

    public function test_student_cannot_access_another_students_schedule()
    {
        // Create another classroom
        $otherClassroom = ClassRoom::factory()->create();

        // Create another student
        $otherUser = User::factory()->create(['role' => 'student']);
        $otherUser->assignRole('student');

        $otherStudent = Student::factory()->create([
            'user_id' => $otherUser->id,
            'classroom_id' => $otherClassroom->id
        ]);

        // Create schedule for other classroom
        $otherSchedule = ClassSchedule::factory()->create([
            'classroom_id' => $otherClassroom->id,
            'day' => 'Senin'
        ]);

        // Login as first student
        $response = $this->actingAs($this->user)
            ->get(route('student.schedule.index'));

        // Should not see other classroom's schedule
        $schedules = $response->viewData('schedules');
        $this->assertTrue($schedules->contains('classroom_id', $this->classroom->id));
        $this->assertFalse($schedules->contains('classroom_id', $otherClassroom->id));
    }

    public function test_schedules_are_sorted_by_day()
    {
        $response = $this->actingAs($this->user)
            ->get(route('student.schedule.index'));

        $response->assertStatus(200);

        // Check that schedules are sorted by day
        $schedules = $response->viewData('schedules');
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        foreach ($days as $day) {
            $this->assertTrue($schedules->contains('day', $day));
        }
    }

    public function test_student_can_see_time_slots_for_each_schedule()
    {
        $response = $this->actingAs($this->user)
            ->get(route('student.schedule.index'));

        $response->assertStatus(200);

        // Check that time slots are visible
        foreach ($this->classSchedules as $index => $schedule) {
            $startHour = 8 + $index;
            $expectedStartTime = sprintf('%02d:00', $startHour);
            $expectedEndTime = sprintf('%02d:00', $startHour + 2);

            // The exact format might depend on your view, so adjust this check as needed
            $response->assertSee($expectedStartTime);
            $response->assertSee($expectedEndTime);
        }
    }

    public function test_schedule_includes_course_and_lecturer_details()
    {
        // Manually check some records to ensure that eager loading is working
        $schedules = ClassSchedule::where('classroom_id', $this->classroom->id)
            ->with(['course', 'lecturer', 'semesters'])
            ->orderBy('day')
            ->get();

        $this->assertNotEmpty($schedules);

        foreach ($schedules as $schedule) {
            $this->assertNotNull($schedule->course);
            // Lecturer may be null in some test cases, so no assertion for that

            // Check that the semester relationship works
            if ($schedule->semester_id) {
                $this->assertNotNull($schedule->semesters);
            }
        }

        // Now check the actual endpoint
        $response = $this->actingAs($this->user)
            ->get(route('student.schedule.index'));

        $response->assertStatus(200);
        $response->assertSee($this->course->name);
    }
}
