<?php

namespace Tests\Unit\Repositories;

use App\Models\Student;
use App\Models\User;
use App\Models\ClassSchedule;
use App\Models\ClassRoom;
use App\Repositories\Interfaces\StudentRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(StudentRepositoryInterface::class);
    }

    public function test_get_all_students()
    {
        // Arrange
        $students = Student::factory()->count(3)->create();

        // Act
        $result = $this->repository->getAll();

        // Assert
        $this->assertCount(3, $result);
        $this->assertInstanceOf(Student::class, $result->first());
    }

    public function test_find_by_id()
    {
        // Arrange
        $student = Student::factory()->create();

        // Act
        $result = $this->repository->findById($student->id);

        // Assert
        $this->assertInstanceOf(Student::class, $result);
        $this->assertEquals($student->id, $result->id);
    }

    public function test_find_by_nim()
    {
        // Arrange
        $student = Student::factory()->create(['nim' => '12345678']);

        // Act
        $result = $this->repository->findByNim('12345678');

        // Assert
        $this->assertInstanceOf(Student::class, $result);
        $this->assertEquals('12345678', $result->nim);
    }

    public function test_update_face_registered()
    {
        // Arrange
        $student = Student::factory()->create(['face_registered' => false]);

        // Act
        $this->repository->updateFaceRegistered($student->id, true);
        $student->refresh();

        // Assert
        $this->assertTrue($student->face_registered);
    }

    public function test_is_enrolled_in_class_when_student_is_in_class()
    {
        // Arrange
        $classroom = ClassRoom::factory()->create();
        $student = Student::factory()->create(['classroom_id' => $classroom->id]);
        $classSchedule = ClassSchedule::factory()->create(['classroom_id' => $classroom->id]);

        // Act
        $result = $this->repository->isEnrolledInClass($student->id, $classSchedule->id);

        // Assert
        $this->assertTrue($result);
    }

    public function test_is_enrolled_in_class_when_student_is_not_in_class()
    {
        // Arrange
        $student = Student::factory()->create(['classroom_id' => ClassRoom::factory()->create()->id]);
        $classSchedule = ClassSchedule::factory()->create(['classroom_id' => ClassRoom::factory()->create()->id]);

        // Act
        $result = $this->repository->isEnrolledInClass($student->id, $classSchedule->id);

        // Assert
        $this->assertFalse($result);
    }

    public function test_is_enrolled_in_class_with_invalid_student_id()
    {
        // Arrange
        $classSchedule = ClassSchedule::factory()->create();

        // Act
        $result = $this->repository->isEnrolledInClass(999, $classSchedule->id);

        // Assert
        $this->assertFalse($result);
    }

    public function test_is_enrolled_in_class_with_invalid_class_schedule_id()
    {
        // Arrange
        $student = Student::factory()->create();

        // Act
        $result = $this->repository->isEnrolledInClass($student->id, 999);

        // Assert
        $this->assertFalse($result);
    }
}
