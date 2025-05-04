<?php

namespace Tests\Feature\Admin;

use App\Models\ClassRoom;
use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\Lecturer;
use App\Models\Semester;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\Feature\FeatureTestCase;

class ClassScheduleTest extends FeatureTestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $lecturer;
    protected $course;
    protected $classroom;
    protected $semester;
    protected $studyProgram;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles first
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'lecturer']);
        Role::create(['name' => 'student']);

        // Create an admin user for authorization
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->admin->assignRole('admin');

        // Create necessary related models
        $this->lecturer = Lecturer::factory()->create();
        $this->studyProgram = StudyProgram::factory()->create();
        $this->course = Course::factory()->create(['study_program_id' => $this->studyProgram->id]);
        $this->classroom = ClassRoom::factory()->create(['study_program_id' => $this->studyProgram->id]);
        $this->semester = Semester::factory()->active()->create();
    }

    public function test_admin_can_view_schedule_list()
    {
        // Create a class schedule for testing
        $schedule = ClassSchedule::factory()->create();

        // Act as admin and access the index page
        $response = $this->actingAs($this->admin)
            ->get(route('admin.schedules.index'));

        // Assert the response
        $response->assertStatus(200);
        $response->assertViewIs('admin.schedules.index');
        $response->assertViewHas('schedules');
    }

    public function test_admin_can_access_create_schedule_form()
    {
        // Act as admin and access the create form
        $response = $this->actingAs($this->admin)
            ->get(route('admin.schedules.create'));

        // Assert the response
        $response->assertStatus(200);
        $response->assertViewIs('admin.schedules.create');
        $response->assertViewHas(['lecturers', 'days', 'timeSlots', 'classrooms', 'courses', 'semesters']);
    }

    public function test_admin_can_create_schedule()
    {
        // Prepare data for a schedule
        $scheduleData = [
            'course_id' => $this->course->id,
            'lecturer_id' => $this->lecturer->id,
            'classroom_id' => $this->classroom->id,
            'semester_id' => $this->semester->id,
            'study_program_id' => $this->studyProgram->id,
            'room' => 'A101',
            'day' => 'Monday',
            'semester' => 'Ganjil',
            'time_slots' => ['09:00 - 10:00', '10:00 - 11:00'],
            'total_weeks' => 16,
            'meetings_per_week' => 1,
        ];

        // Act as admin and submit the form
        $response = $this->actingAs($this->admin)
            ->post(route('admin.schedules.store'), $scheduleData);

        // Assert the response and database state
        $response->assertRedirect(route('admin.schedules.index'));
        $response->assertSessionHas('success');

        // Check if the schedule was created in the database
        $this->assertDatabaseHas('class_schedules', [
            'course_id' => $scheduleData['course_id'],
            'lecturer_id' => $scheduleData['lecturer_id'],
            'classroom_id' => $scheduleData['classroom_id'],
            'room' => $scheduleData['room'],
            'day' => $scheduleData['day'],
        ]);

        // Check if time slots were created
        $schedule = ClassSchedule::where('course_id', $scheduleData['course_id'])
            ->where('lecturer_id', $scheduleData['lecturer_id'])
            ->where('day', $scheduleData['day'])
            ->first();

        $this->assertNotNull($schedule);
        $this->assertEquals(2, $schedule->timeSlots()->count());
    }

    public function test_admin_can_edit_schedule()
    {
        // Create a schedule to edit
        $schedule = ClassSchedule::factory()->create([
            'course_id' => $this->course->id,
            'lecturer_id' => $this->lecturer->id,
            'classroom_id' => $this->classroom->id,
            'room' => 'B202',
            'day' => 'Tuesday',
        ]);

        // Create time slots for the schedule
        $schedule->timeSlots()->create([
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]);

        // Act as admin and access the edit form
        $response = $this->actingAs($this->admin)
            ->get(route('admin.schedules.edit', $schedule));

        // Assert the response
        $response->assertStatus(200);
        $response->assertViewIs('admin.schedules.edit');
        $response->assertViewHas(['schedule', 'lecturers', 'days', 'timeSlots', 'selectedTimeSlots']);
    }

    public function test_admin_can_update_schedule()
    {
        // Create a schedule to update
        $schedule = ClassSchedule::factory()->create([
            'course_id' => $this->course->id,
            'lecturer_id' => $this->lecturer->id,
            'classroom_id' => $this->classroom->id,
            'room' => 'B202',
            'day' => 'Tuesday',
        ]);

        // Create time slots for the schedule
        $schedule->timeSlots()->create([
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]);

        // Prepare update data
        $updateData = [
            'course_id' => $this->course->id,
            'lecturer_id' => $this->lecturer->id,
            'classroom_id' => $this->classroom->id,
            'semester_id' => $this->semester->id,
            'study_program_id' => $this->studyProgram->id,
            'room' => 'C303',
            'day' => 'Wednesday',
            'semester' => 'Genap',
            'time_slots' => ['13:00 - 14:00', '14:00 - 15:00'],
            'total_weeks' => 16,
            'meetings_per_week' => 1,
        ];

        // Act as admin and submit the update
        $response = $this->actingAs($this->admin)
            ->put(route('admin.schedules.update', $schedule), $updateData);

        // Assert the response and database state
        $response->assertRedirect(route('admin.schedules.index'));
        $response->assertSessionHas('success');

        // Check if the schedule was updated in the database
        $this->assertDatabaseHas('class_schedules', [
            'id' => $schedule->id,
            'room' => 'C303',
            'day' => 'Wednesday',
        ]);

        // Check if time slots were updated
        $schedule->refresh();
        $this->assertEquals(2, $schedule->timeSlots()->count());
        $this->assertEquals('13:00', $schedule->timeSlots->first()->start_time->format('H:i'));
    }

    public function test_admin_can_delete_schedule()
    {
        // Create a schedule to delete
        $schedule = ClassSchedule::factory()->create();

        // Act as admin and delete the schedule
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.schedules.destroy', $schedule));

        // Assert the response and database state
        $response->assertRedirect(route('admin.schedules.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('class_schedules', [
            'id' => $schedule->id,
        ]);
    }

    public function test_admin_can_check_schedule_availability()
    {
        // Create a schedule with time slot
        $schedule = ClassSchedule::factory()->create([
            'room' => 'A101',
            'day' => 'Monday',
        ]);

        $schedule->timeSlots()->create([
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]);

        // Request data
        $requestData = [
            'room' => 'A101',
            'day' => 'Monday',
            'lecturer_id' => $this->lecturer->id,
        ];

        // Act as admin and check availability
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.schedules.check-availability', $requestData));

        // Assert the response
        $response->assertStatus(200);
        $response->assertJsonStructure(['bookedSlots']);

        // Should get at least one booked slot in the response
        $bookedSlots = $response->json('bookedSlots');
        $this->assertNotEmpty($bookedSlots);
    }

    public function test_validation_fails_with_invalid_schedule_data()
    {
        // Prepare invalid data (missing required fields)
        $invalidData = [
            'course_id' => '',
            'lecturer_id' => '',
            'classroom_id' => '',
            'room' => '',
            'day' => 'InvalidDay',
            'time_slots' => [], // Empty time slots
        ];

        // Act as admin and submit the form
        $response = $this->actingAs($this->admin)
            ->post(route('admin.schedules.store'), $invalidData);

        // Assert validation fails
        $response->assertSessionHasErrors(['course_id', 'lecturer_id', 'classroom_id', 'room', 'day', 'time_slots']);
        $response->assertRedirect();
    }

    public function test_non_admin_cannot_access_schedule_management()
    {
        // Create a regular user
        /** @var \App\Models\User $user */
        $user = User::factory()->create(['role' => 'student']);
        $user->assignRole('student');

        // Try to access the schedule management pages
        $indexResponse = $this->actingAs($user)->get(route('admin.schedules.index'));
        $createResponse = $this->actingAs($user)->get(route('admin.schedules.create'));

        // Assert both are forbidden
        $indexResponse->assertForbidden();
        $createResponse->assertForbidden();
    }
}
