<?php

namespace Tests\Unit\Repositories;

use App\Models\Attendance;
use App\Models\ClassSchedule;
use App\Models\Student;
use App\Repositories\Interfaces\AttendanceRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(AttendanceRepositoryInterface::class);
    }

    public function test_find_by_id()
    {
        // Arrange
        $attendance = Attendance::factory()->create();

        // Act
        $result = $this->repository->findById($attendance->id);

        // Assert
        $this->assertInstanceOf(Attendance::class, $result);
        $this->assertEquals($attendance->id, $result->id);
    }

    public function test_find_by_class_and_date()
    {
        // Arrange
        $classSchedule = ClassSchedule::factory()->create();
        $date = '2023-08-15';

        // Create 3 attendance records for the same class and date
        $attendances = Attendance::factory()->count(3)->create([
            'class_schedule_id' => $classSchedule->id,
            'date' => $date
        ]);

        // Create 2 attendance records for the same class but different date
        Attendance::factory()->count(2)->create([
            'class_schedule_id' => $classSchedule->id,
            'date' => '2023-08-16'
        ]);

        // Act
        $result = $this->repository->findByClassAndDate($classSchedule->id, $date);

        // Assert
        $this->assertCount(3, $result);
        $this->assertEquals($date, $result->first()->date->format('Y-m-d'));
    }

    public function test_find_by_class_student_and_date()
    {
        // Arrange
        $classSchedule = ClassSchedule::factory()->create();
        $student = Student::factory()->create();
        $date = '2023-08-15';

        $attendance = Attendance::factory()->create([
            'class_schedule_id' => $classSchedule->id,
            'student_id' => $student->id,
            'date' => $date
        ]);

        // Act
        $result = $this->repository->findByClassStudentAndDate(
            $classSchedule->id,
            $student->id,
            $date
        );

        // Assert
        $this->assertInstanceOf(Attendance::class, $result);
        $this->assertEquals($attendance->id, $result->id);
    }

    public function test_create()
    {
        // Arrange
        $classSchedule = ClassSchedule::factory()->create();
        $student = Student::factory()->create();
        $data = [
            'class_schedule_id' => $classSchedule->id,
            'student_id' => $student->id,
            'date' => '2023-08-15',
            'status' => 'present',
            'remarks' => 'On time',
        ];

        // Act
        $result = $this->repository->create($data);

        // Assert
        $this->assertInstanceOf(Attendance::class, $result);
        $this->assertEquals($classSchedule->id, $result->class_schedule_id);
        $this->assertEquals($student->id, $result->student_id);
        $this->assertEquals('present', $result->status);
    }

    public function test_create_or_update_by_class_student_date_create_new()
    {
        // Arrange
        $classSchedule = ClassSchedule::factory()->create();
        $student = Student::factory()->create();
        $date = '2023-08-15';

        $attributes = [
            'class_schedule_id' => $classSchedule->id,
            'student_id' => $student->id,
            'date' => $date,
        ];

        $values = [
            'status' => 'present',
            'remarks' => 'On time',
        ];

        // Act
        $result = $this->repository->createOrUpdateByClassStudentDate($attributes, $values);

        // Assert
        $this->assertInstanceOf(Attendance::class, $result);
        $this->assertEquals($classSchedule->id, $result->class_schedule_id);
        $this->assertEquals($student->id, $result->student_id);
        $this->assertEquals('present', $result->status);

        // Verify a new record was created
        $this->assertEquals(1, Attendance::count());
    }

    public function test_create_or_update_by_class_student_date_with_existing_record()
    {
        // Arrange
        $classSchedule = ClassSchedule::factory()->create();
        $student = Student::factory()->create();
        $date = '2023-08-15';

        // Create an existing attendance record
        Attendance::factory()->create([
            'class_schedule_id' => $classSchedule->id,
            'student_id' => $student->id,
            'date' => $date,
            'status' => 'absent',
        ]);

        $attributes = [
            'class_schedule_id' => $classSchedule->id,
            'student_id' => $student->id,
            'date' => $date,
        ];

        $values = [
            'status' => 'present',
            'remarks' => 'Late arrival',
        ];

        // Act
        $result = $this->repository->createOrUpdateByClassStudentDate($attributes, $values);

        // Assert
        $this->assertInstanceOf(Attendance::class, $result);
        $this->assertEquals('absent', $result->status); // Status remains the same because we use firstOrCreate

        // Verify no new record was created
        $this->assertEquals(1, Attendance::count());
    }

    public function test_update()
    {
        // Arrange
        $attendance = Attendance::factory()->create([
            'status' => 'absent',
            'remarks' => null,
        ]);

        $data = [
            'status' => 'present',
            'remarks' => 'Updated after verification',
        ];

        // Act
        $result = $this->repository->update($attendance, $data);

        // Assert
        $this->assertInstanceOf(Attendance::class, $result);
        $this->assertEquals('present', $result->status);
        $this->assertEquals('Updated after verification', $result->remarks);
    }

    public function test_get_student_attendance_by_class()
    {
        // Arrange
        $classSchedule = ClassSchedule::factory()->create();
        $date = '2023-08-15';

        // Create 3 attendance records for the same class and date
        Attendance::factory()->count(3)->create([
            'class_schedule_id' => $classSchedule->id,
            'date' => $date
        ]);

        // Act
        $result = $this->repository->getStudentAttendanceByClass($classSchedule->id, $date);

        // Assert
        $this->assertCount(3, $result);
    }

    public function test_get_student_attendances()
    {
        // Arrange
        $student = Student::factory()->create();

        // Create 5 attendance records for this student
        Attendance::factory()->count(5)->create([
            'student_id' => $student->id,
        ]);

        // Create 3 attendance records for other students
        Attendance::factory()->count(3)->create();

        // Act
        $result = $this->repository->getStudentAttendances($student->id);

        // Assert
        $this->assertCount(5, $result);
    }

    public function test_get_filtered_attendances_with_course_filter()
    {
        // Arrange
        $classSchedule = ClassSchedule::factory()->create();

        // Create 3 attendance records for this course
        Attendance::factory()->count(3)->create([
            'class_schedule_id' => $classSchedule->id,
        ]);

        // Create 2 attendance records for other courses
        Attendance::factory()->count(2)->create();

        // Act
        $result = $this->repository->getFilteredAttendances(
            $classSchedule->course_id,
            null,
            null,
            null
        );

        // Assert
        $this->assertCount(3, $result);
    }

    public function test_get_filtered_attendances_with_date_filter()
    {
        // Arrange
        $date = '2023-08-15';

        // Create 4 attendance records for this date
        Attendance::factory()->count(4)->create([
            'date' => $date,
        ]);

        // Create 3 attendance records for other dates
        Attendance::factory()->count(3)->create([
            'date' => '2023-08-16',
        ]);

        // Act
        $result = $this->repository->getFilteredAttendances(
            null,
            $date,
            null,
            null
        );

        // Assert
        $this->assertCount(4, $result);
    }

    public function test_get_filtered_attendances_with_student_filter()
    {
        // Arrange
        $student = Student::factory()->create();

        // Create 2 attendance records for this student
        Attendance::factory()->count(2)->create([
            'student_id' => $student->id,
        ]);

        // Create 3 attendance records for other students
        Attendance::factory()->count(3)->create();

        // Act
        $result = $this->repository->getFilteredAttendances(
            null,
            null,
            $student->id,
            null
        );

        // Assert
        $this->assertCount(2, $result);
    }

    public function test_get_filtered_attendances_with_status_filter()
    {
        // Arrange

        // Create 3 present attendance records
        Attendance::factory()->count(3)->create([
            'status' => 'present',
        ]);

        // Create 2 absent attendance records
        Attendance::factory()->count(2)->create([
            'status' => 'absent',
        ]);

        // Act
        $result = $this->repository->getFilteredAttendances(
            null,
            null,
            null,
            'present'
        );

        // Assert
        $this->assertCount(3, $result);
    }

    public function test_get_filtered_attendances_with_multiple_filters()
    {
        // Arrange
        $classSchedule = ClassSchedule::factory()->create();
        $student = Student::factory()->create();
        $date = '2023-08-15';

        // Create 1 matching attendance record
        Attendance::factory()->create([
            'class_schedule_id' => $classSchedule->id,
            'student_id' => $student->id,
            'date' => $date,
            'status' => 'present',
        ]);

        // Create 5 non-matching attendance records
        Attendance::factory()->count(5)->create();

        // Act
        $result = $this->repository->getFilteredAttendances(
            $classSchedule->course_id,
            $date,
            $student->id,
            'present'
        );

        // Assert
        $this->assertCount(1, $result);
    }
}
