<?php

namespace Tests\Unit\Repositories;

use App\Models\FaceData;
use App\Models\Student;
use App\Repositories\Interfaces\FaceDataRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaceDataRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(FaceDataRepositoryInterface::class);
    }

    public function test_find_by_student_id_with_existing_data()
    {
        // Arrange
        $student = Student::factory()->create();
        $faceData = FaceData::factory()->create(['student_id' => $student->id]);

        // Act
        $result = $this->repository->findByStudentId($student->id);

        // Assert
        $this->assertInstanceOf(FaceData::class, $result);
        $this->assertEquals($student->id, $result->student_id);
    }

    public function test_find_by_student_id_with_nonexistent_data()
    {
        // Arrange
        $student = Student::factory()->create();

        // Act
        $result = $this->repository->findByStudentId($student->id);

        // Assert
        $this->assertNull($result);
    }

    public function test_create_new_face_data()
    {
        // Arrange
        $student = Student::factory()->create();
        $faceDataValues = [
            'face_embedding' => [0.1, 0.2, 0.3], // Array, not JSON string
            'image_path' => ['path' => 'path/to/image.jpg'], // Array structure
        ];

        // Act
        $result = $this->repository->createOrUpdate($student->id, $faceDataValues);

        // Assert
        $this->assertInstanceOf(FaceData::class, $result);
        $this->assertEquals([0.1, 0.2, 0.3], $result->face_embedding); // Direct array comparison
        $this->assertEquals(['path' => 'path/to/image.jpg'], $result->image_path);
    }

    public function test_update_existing_face_data()
    {
        // Arrange
        $student = Student::factory()->create();
        $faceData = FaceData::factory()->create([
            'student_id' => $student->id,
            'face_embedding' => [0.1, 0.2, 0.3],
            'image_path' => ['path' => 'old/path.jpg'],
        ]);

        $updatedValues = [
            'face_embedding' => [0.4, 0.5, 0.6],
            'image_path' => ['path' => 'new/path.jpg'],
        ];

        // Act
        $result = $this->repository->createOrUpdate($student->id, $updatedValues);

        // Assert
        $this->assertEquals([0.4, 0.5, 0.6], $result->face_embedding);
        $this->assertEquals(['path' => 'new/path.jpg'], $result->image_path);
    }
}
