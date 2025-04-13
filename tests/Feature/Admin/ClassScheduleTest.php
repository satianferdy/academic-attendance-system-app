<?php

namespace Tests\Feature\Admin;

use App\Models\ClassRoom;
use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\Lecturer;
use App\Models\ScheduleTimeSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClassScheduleTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $course;
    protected $lecturer;
    protected $classroom;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user
        $this->admin = User::factory()->create(['role' => 'admin']);

        // Create test data
        $this->course = Course::factory()->create();
        $lecturerUser = User::factory()->create();
        $this->lecturer = Lecturer::factory()->create(['user_id' => $lecturerUser->id]);
        $this->classroom = ClassRoom::factory()->create();
    }

    public function test_admin_can_view_schedules_list()
    {
        // Create some class schedules
        ClassSchedule::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
                         ->get(route('admin.schedules.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.schedules.index');
        $response->assertViewHas('schedules');
    }

    public function test_admin_can_view_schedule_create_form()
    {
        $response = $this->actingAs($this->admin)
                         ->get(route('admin.schedules.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.schedules.create');
        $response->assertViewHasAll([
            'courses',
            'classrooms',
            'lecturers',
            'days',
            'timeSlots'
        ]);
    }

    public function test_admin_can_create_class_schedule()
    {
        $scheduleData = [
            'course_id' => $this->course->id,
            'lecturer_id' => $this->lecturer->id,
            'classroom_id' => $this->classroom->id,
            'room' => 'Room 101',
            'day' => 'Monday',
            'semester' => 'Fall',
            'academic_year' => '2024/2025',
            'time_slots' => ['08:00 - 09:00', '09:00 - 10:00']
        ];

        $response = $this->actingAs($this->admin)
                         ->post(route('admin.schedules.store'), $scheduleData);

        $response->assertRedirect(route('admin.schedules.index'));
        $response->assertSessionHas('success', 'Class schedule created successfully with 2 time slots.');

        // Check that the schedule and time slots are saved in the database
        $this->assertDatabaseHas('class_schedules', [
            'course_id' => $this->course->id,
            'lecturer_id' => $this->lecturer->id,
            'classroom_id' => $this->classroom->id,
            'room' => 'Room 101',
            'day' => 'Monday',
        ]);

        $schedule = ClassSchedule::where('room', 'Room 101')->first();

        $this->assertDatabaseHas('schedule_time_slots', [
            'class_schedule_id' => $schedule->id,
            'start_time' => '08:00',
        ]);

        $this->assertDatabaseHas('schedule_time_slots', [
            'class_schedule_id' => $schedule->id,
            'start_time' => '09:00',
        ]);
    }

    public function test_admin_can_view_schedule_details()
    {
        $schedule = ClassSchedule::factory()->create([
            'course_id' => $this->course->id,
            'lecturer_id' => $this->lecturer->id,
            'classroom_id' => $this->classroom->id,
        ]);

        // Create some time slots for this schedule
        ScheduleTimeSlot::factory()->create([
            'class_schedule_id' => $schedule->id,
            'start_time' => '08:00',
            'end_time' => '09:00',
        ]);

        $response = $this->actingAs($this->admin)
                         ->get(route('admin.schedules.show', $schedule));

        $response->assertStatus(200);
        $response->assertViewIs('admin.schedules.show');
        $response->assertViewHas('schedule');
    }

    public function test_admin_can_view_schedule_edit_form()
    {
        $schedule = ClassSchedule::factory()->create([
            'course_id' => $this->course->id,
            'lecturer_id' => $this->lecturer->id,
            'classroom_id' => $this->classroom->id,
        ]);

        // Create some time slots for this schedule
        ScheduleTimeSlot::factory()->create([
            'class_schedule_id' => $schedule->id,
            'start_time' => '08:00',
            'end_time' => '09:00',
        ]);

        $response = $this->actingAs($this->admin)
                         ->get(route('admin.schedules.edit', $schedule));

        $response->assertStatus(200);
        $response->assertViewIs('admin.schedules.edit');
        $response->assertViewHasAll([
            'schedule',
            'lecturers',
            'days',
            'timeSlots',
            'selectedTimeSlots',
            'classrooms',
            'courses'
        ]);
    }

    public function test_admin_can_update_class_schedule()
    {
        $schedule = ClassSchedule::factory()->create([
            'course_id' => $this->course->id,
            'lecturer_id' => $this->lecturer->id,
            'classroom_id' => $this->classroom->id,
            'room' => 'Room 101',
            'day' => 'Monday',
        ]);

        // Create original time slot
        ScheduleTimeSlot::factory()->create([
            'class_schedule_id' => $schedule->id,
            'start_time' => '08:00',
            'end_time' => '09:00',
        ]);

        $updatedData = [
            'course_id' => $this->course->id,
            'lecturer_id' => $this->lecturer->id,
            'classroom_id' => $this->classroom->id,
            'room' => 'Room 102', // Changed room
            'day' => 'Tuesday',   // Changed day
            'semester' => 'Spring',
            'academic_year' => '2024/2025',
            'time_slots' => ['10:00 - 11:00', '11:00 - 12:00'] // Changed time slots
        ];

        $response = $this->actingAs($this->admin)
                         ->put(route('admin.schedules.update', $schedule), $updatedData);

        $response->assertRedirect(route('admin.schedules.index'));
        $response->assertSessionHas('success', 'Class schedule updated successfully.');

        // Check that the schedule is updated in the database
        $this->assertDatabaseHas('class_schedules', [
            'id' => $schedule->id,
            'room' => 'Room 102',
            'day' => 'Tuesday',
        ]);

        // Check that old time slots are removed and new ones are added
        $this->assertDatabaseMissing('schedule_time_slots', [
            'class_schedule_id' => $schedule->id,
            'start_time' => '08:00',
        ]);

        $this->assertDatabaseHas('schedule_time_slots', [
            'class_schedule_id' => $schedule->id,
            'start_time' => '10:00',
        ]);

        $this->assertDatabaseHas('schedule_time_slots', [
            'class_schedule_id' => $schedule->id,
            'start_time' => '11:00',
        ]);
    }

    public function test_admin_can_delete_class_schedule()
    {
        $schedule = ClassSchedule::factory()->create([
            'course_id' => $this->course->id,
            'lecturer_id' => $this->lecturer->id,
            'classroom_id' => $this->classroom->id,
        ]);

        // Create some time slots for this schedule
        $timeSlot = ScheduleTimeSlot::factory()->create([
            'class_schedule_id' => $schedule->id,
            'start_time' => '08:00',
            'end_time' => '09:00',
        ]);

        $response = $this->actingAs($this->admin)
                         ->delete(route('admin.schedules.destroy', $schedule));

        $response->assertRedirect(route('admin.schedules.index'));
        $response->assertSessionHas('success', 'Class schedule deleted successfully.');

        // Check that the schedule and its time slots are deleted from the database
        $this->assertDatabaseMissing('class_schedules', [
            'id' => $schedule->id
        ]);

        $this->assertDatabaseMissing('schedule_time_slots', [
            'id' => $timeSlot->id
        ]);
    }

    public function test_system_detects_time_slot_conflicts_when_creating_schedule()
    {
        // First create a schedule
        $existingSchedule = ClassSchedule::factory()->create([
            'room' => 'Room 101',
            'day' => 'Monday',
            'lecturer_id' => $this->lecturer->id,
        ]);

        // Add a time slot to it (9:00 - 10:00)
        ScheduleTimeSlot::factory()->create([
            'class_schedule_id' => $existingSchedule->id,
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]);

        // Now try to create a new schedule with conflicting time
        $newScheduleData = [
            'course_id' => $this->course->id,
            'lecturer_id' => $this->lecturer->id,
            'classroom_id' => $this->classroom->id,
            'room' => 'Room 101', // Same room
            'day' => 'Monday',    // Same day
            'semester' => 'Fall',
            'academic_year' => '2024/2025',
            'time_slots' => ['09:30 - 10:30'] // Overlapping time
        ];

        $response = $this->actingAs($this->admin)
                         ->post(route('admin.schedules.store'), $newScheduleData);

        $response->assertRedirect();
        $response->assertSessionHasErrors('time_slots');
        $this->assertDatabaseCount('class_schedules', 1); // Only the existing schedule
    }

    public function test_system_detects_lecturer_conflicts_when_creating_schedule()
    {
        // First create a schedule for the lecturer
        $existingSchedule = ClassSchedule::factory()->create([
            'room' => 'Room 101',
            'day' => 'Monday',
            'lecturer_id' => $this->lecturer->id,
        ]);

        // Add a time slot to it (9:00 - 10:00)
        ScheduleTimeSlot::factory()->create([
            'class_schedule_id' => $existingSchedule->id,
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]);

        // Now try to create a new schedule with conflicting lecturer time
        $newScheduleData = [
            'course_id' => $this->course->id,
            'lecturer_id' => $this->lecturer->id, // Same lecturer
            'classroom_id' => $this->classroom->id,
            'room' => 'Room 102', // Different room
            'day' => 'Monday',    // Same day
            'semester' => 'Fall',
            'academic_year' => '2024/2025',
            'time_slots' => ['09:30 - 10:30'] // Overlapping time
        ];

        $response = $this->actingAs($this->admin)
                         ->post(route('admin.schedules.store'), $newScheduleData);

        $response->assertRedirect();
        $response->assertSessionHasErrors('time_slots');
        $this->assertDatabaseCount('class_schedules', 1); // Only the existing schedule
    }

    public function test_admin_can_check_availability_via_ajax()
    {
        // Create a schedule with a time slot
        $schedule = ClassSchedule::factory()->create([
            'room' => 'Room 101',
            'day' => 'Monday',
            'lecturer_id' => $this->lecturer->id,
        ]);

        ScheduleTimeSlot::factory()->create([
            'class_schedule_id' => $schedule->id,
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]);

        // Check availability via AJAX
        $response = $this->actingAs($this->admin)
                         ->getJson(route('admin.schedules.check-availability', [
                             'room' => 'Room 101',
                             'day' => 'Monday',
                             'lecturer_id' => $this->lecturer->id,
                         ]));

        $response->assertStatus(200);
        $response->assertJsonStructure(['bookedSlots']);

        $data = $response->json();
        $this->assertNotEmpty($data['bookedSlots']);
        $this->assertEquals('09:00', $data['bookedSlots'][0]['start_time']);
        $this->assertEquals('10:00', $data['bookedSlots'][0]['end_time']);
    }

    public function test_non_admin_cannot_access_schedule_management()
    {
        /** @var \App\Models\User $regularUser */
        $regularUser = User::factory()->create(['role' => 'student']);

        // Try to access the schedules index page
        $response = $this->actingAs($regularUser)
                        ->get(route('admin.schedules.index'));

        $response->assertStatus(403); // Forbidden
    }
}
