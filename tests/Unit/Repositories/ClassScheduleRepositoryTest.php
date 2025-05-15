<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Student;
use App\Models\Lecturer;
use App\Models\ClassRoom;
use App\Models\ClassSchedule;
use App\Models\ScheduleTimeSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Repositories\Implementations\ClassScheduleRepository;
use PHPUnit\Framework\Attributes\Test;

class ClassScheduleRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ClassScheduleRepository(new ClassSchedule());
    }

    public function test_it_can_get_schedules_by_lecturer()
    {
        // Create a lecturer
        $lecturer = Lecturer::factory()->create();

        // Create schedules for the lecturer
        $schedules = ClassSchedule::factory()->count(3)->create([
            'lecturer_id' => $lecturer->id
        ]);

        // Get schedules by lecturer
        $lecturerSchedules = $this->repository->getSchedulesByLecturerId($lecturer->id);

        // Assert
        $this->assertEquals($schedules->count(), $lecturerSchedules->count());
    }

    public function test_it_can_find_a_schedule_by_id()
    {
        // Create a class schedule
        $schedule = ClassSchedule::factory()->create();

        // Find the schedule
        $foundSchedule = $this->repository->find($schedule->id);

        // Assert
        $this->assertInstanceOf(ClassSchedule::class, $foundSchedule);
        $this->assertEquals($schedule->id, $foundSchedule->id);
    }


    public function test_it_can_get_students_from_a_class_schedule()
    {
        // Create classroom with students
        $classroom = ClassRoom::factory()->create();
        $students = Student::factory()->count(3)->create([
            'classroom_id' => $classroom->id
        ]);

        // Create schedule with the classroom
        $schedule = ClassSchedule::factory()->create([
            'classroom_id' => $classroom->id
        ]);

        // Get students
        $classStudents = $this->repository->getStudents($schedule);

        // Assert
        $this->assertEquals($students->count(), $classStudents->count());
    }


    public function test_it_can_detect_room_time_conflicts()
    {
        // Create a schedule with time slot
        $schedule = ClassSchedule::factory()->create([
            'room' => 'A101',
            'day' => 'Monday'
        ]);

        // Create a time slot
        ScheduleTimeSlot::factory()->create([
            'class_schedule_id' => $schedule->id,
            'start_time' => '09:00',
            'end_time' => '10:30'
        ]);

        // Check for conflict with overlapping time
        $conflicts = $this->repository->findConflictingTimeSlots(
            'A101',
            'Monday',
            '10:00',
            '11:00'
        );

        // Assert
        $this->assertNotEmpty($conflicts['room']);
        $this->assertEquals(1, count($conflicts['room']));
    }


    public function test_it_can_detect_lecturer_time_conflicts()
    {
        // Create a lecturer
        $lecturer = Lecturer::factory()->create();

        // Create a schedule with time slot for this lecturer
        $schedule = ClassSchedule::factory()->create([
            'lecturer_id' => $lecturer->id,
            'day' => 'Tuesday'
        ]);

        // Create a time slot
        ScheduleTimeSlot::factory()->create([
            'class_schedule_id' => $schedule->id,
            'start_time' => '13:00',
            'end_time' => '14:30'
        ]);

        // Check for conflict with overlapping time
        $conflicts = $this->repository->findConflictingTimeSlots(
            'Different-Room', // Different room but same lecturer
            'Tuesday',
            '14:00',
            '15:00',
            $lecturer->id
        );

        // Assert
        $this->assertEmpty($conflicts['room']); // No room conflict
        $this->assertNotEmpty($conflicts['lecturer']); // But lecturer has conflict
        $this->assertEquals(1, count($conflicts['lecturer']));
    }


    public function test_it_can_check_time_overlap_correctly()
    {
        // Test cases for time overlap
        $testCases = [
            // Exact same time (complete overlap)
            ['09:00', '10:00', '09:00', '10:00', true],

            // No overlap (end1 = start2)
            ['09:00', '10:00', '10:00', '11:00', false],

            // No overlap (end2 = start1)
            ['10:00', '11:00', '09:00', '10:00', false],

            // Partial overlap (start1 inside slot2)
            ['09:30', '10:30', '09:00', '10:00', true],

            // Partial overlap (end1 inside slot2)
            ['08:30', '09:30', '09:00', '10:00', true],

            // Slot1 completely inside slot2
            ['09:30', '09:45', '09:00', '10:00', true],

            // Slot2 completely inside slot1
            ['09:00', '10:00', '09:30', '09:45', true],

            // No overlap (slot1 before slot2)
            ['08:00', '09:00', '10:00', '11:00', false],

            // No overlap (slot1 after slot2)
            ['11:00', '12:00', '09:00', '10:00', false]
        ];

        foreach ($testCases as $index => [$start1, $end1, $start2, $end2, $expected]) {
            $result = $this->repository->checkTimeOverlap($start1, $end1, $start2, $end2);
            $this->assertEquals($expected, $result, "Failed on test case $index: [$start1, $end1, $start2, $end2]");
        }
    }


    public function test_it_can_get_schedules_by_room_and_day()
    {
        // Create schedules for the same room on different days
        $roomA101 = 'A101';

        // Monday schedule
        $mondaySchedule = ClassSchedule::factory()->create([
            'room' => $roomA101,
            'day' => 'Monday'
        ]);

        // Tuesday schedule
        $tuesdaySchedule = ClassSchedule::factory()->create([
            'room' => $roomA101,
            'day' => 'Tuesday'
        ]);

        // Get Monday schedules for room A101
        $schedules = $this->repository->getSchedulesByRoomAndDay($roomA101, 'Monday');

        // Assert
        $this->assertEquals(1, $schedules->count());
        $this->assertEquals($mondaySchedule->id, $schedules->first()->id);
    }


    public function test_it_can_get_schedules_by_lecturer_and_day()
    {
        // Create a lecturer
        $lecturer = Lecturer::factory()->create();

        // Create schedules for the same lecturer on different days
        $mondaySchedule = ClassSchedule::factory()->create([
            'lecturer_id' => $lecturer->id,
            'day' => 'Monday'
        ]);

        $wednesdaySchedule = ClassSchedule::factory()->create([
            'lecturer_id' => $lecturer->id,
            'day' => 'Wednesday'
        ]);

        // Get Monday schedules for this lecturer
        $schedules = $this->repository->getSchedulesByLecturerAndDay($lecturer->id, 'Monday');

        // Assert
        $this->assertEquals(1, $schedules->count());
        $this->assertEquals($mondaySchedule->id, $schedules->first()->id);
    }


    public function test_it_can_create_a_new_schedule()
    {
        // Create related models
        $course = Course::factory()->create();
        $lecturer = Lecturer::factory()->create();
        $classroom = ClassRoom::factory()->create();

        // Schedule data
        $scheduleData = [
            'course_id' => $course->id,
            'lecturer_id' => $lecturer->id,
            'classroom_id' => $classroom->id,
            'room' => 'B202',
            'day' => 'Wednesday',
            'semester' => 'Fall',
            'academic_year' => '2024/2025',
        ];

        // Create schedule
        $schedule = $this->repository->createSchedule($scheduleData);

        // Assert
        $this->assertInstanceOf(ClassSchedule::class, $schedule);
        $this->assertEquals($scheduleData['room'], $schedule->room);
        $this->assertEquals($scheduleData['day'], $schedule->day);
        $this->assertEquals($scheduleData['semester'], $schedule->semester);
    }


    public function test_it_can_update_a_schedule()
    {
        // Create a schedule
        $schedule = ClassSchedule::factory()->create([
            'room' => 'C303',
            'day' => 'Thursday'
        ]);

        // New course for update
        $newCourse = Course::factory()->create();

        // Update data
        $updateData = [
            'course_id' => $newCourse->id,
            'lecturer_id' => $schedule->lecturer_id,
            'classroom_id' => $schedule->classroom_id,
            'room' => 'D404',
            'day' => 'Friday',
            'semester' => 'Spring',
            'academic_year' => '2025/2026',
        ];

        // Update schedule
        $updatedSchedule = $this->repository->updateSchedule($schedule->id, $updateData);

        // Refresh from database
        $updatedSchedule->refresh();

        // Assert
        $this->assertEquals($updateData['room'], $updatedSchedule->room);
        $this->assertEquals($updateData['day'], $updatedSchedule->day);
        $this->assertEquals($updateData['semester'], $updatedSchedule->semester);
        $this->assertEquals($newCourse->id, $updatedSchedule->course_id);
    }


    public function test_it_can_delete_a_schedule()
    {
        // Create a schedule
        $schedule = ClassSchedule::factory()->create();
        $scheduleId = $schedule->id;

        // Delete schedule
        $result = $this->repository->deleteSchedule($scheduleId);

        // Assert
        $this->assertTrue($result);
        $this->assertNull(ClassSchedule::find($scheduleId));
    }


    public function test_it_can_exclude_a_specific_schedule_from_conflict_check()
    {
        // Create two schedules in the same room on the same day
        $roomA101 = 'A101';
        $day = 'Monday';

        // First schedule with time slot
        $schedule1 = ClassSchedule::factory()->create([
            'room' => $roomA101,
            'day' => $day
        ]);

        ScheduleTimeSlot::factory()->create([
            'class_schedule_id' => $schedule1->id,
            'start_time' => '09:00',
            'end_time' => '10:00'
        ]);

        // Second schedule with time slot
        $schedule2 = ClassSchedule::factory()->create([
            'room' => $roomA101,
            'day' => $day
        ]);

        ScheduleTimeSlot::factory()->create([
            'class_schedule_id' => $schedule2->id,
            'start_time' => '11:00',
            'end_time' => '12:00'
        ]);

        // Check for conflicts with schedule2 excluded
        $conflicts = $this->repository->findConflictingTimeSlots(
            $roomA101,
            $day,
            '11:30',
            '12:30',
            null,
            $schedule2->id // Exclude schedule2
        );

        // Assert that schedule2 is excluded from conflicts
        $this->assertEmpty($conflicts['room']);
    }


    public function test_it_can_get_all_schedules()
    {
        // Create several schedules
        $schedules = ClassSchedule::factory()->count(5)->create();

        // Get all schedules
        $allSchedules = $this->repository->getAllSchedules();

        // Assert
        $this->assertEquals($schedules->count(), $allSchedules->count());
        $this->assertInstanceOf(ClassSchedule::class, $allSchedules->first());
    }
}
