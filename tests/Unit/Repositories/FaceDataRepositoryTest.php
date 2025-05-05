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
        $faceEmbedding = [0.1, 0.2, 0.3];
        $imagePath = ['path' => 'path/to/image.jpg'];

        $faceData = FaceData::factory()->create([
            'student_id' => $student->id,
            'face_embedding' => json_encode($faceEmbedding),
            'image_path' => json_encode($imagePath),
        ]);

        // Act
        $result = $this->repository->findByStudentId($student->id);

        // Assert
        $this->assertInstanceOf(FaceData::class, $result);
        $this->assertEquals($student->id, $result->student_id);

        // Compare decoded values instead of JSON strings
        $this->assertEquals($faceEmbedding, json_decode($result->face_embedding, true));
        $this->assertEquals($imagePath, json_decode($result->image_path, true));
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
        $faceEmbedding = [0.1, 0.2, 0.3];
        $imagePath = ['path' => 'path/to/image.jpg'];

        $faceDataValues = [
            'face_embedding' => json_encode($faceEmbedding),
            'image_path' => json_encode($imagePath),
        ];

        // Act
        $result = $this->repository->createOrUpdate($student->id, $faceDataValues);

        // Assert
        $this->assertInstanceOf(FaceData::class, $result);

        // Compare decoded values
        $this->assertEquals($faceEmbedding, json_decode($result->face_embedding, true));
        $this->assertEquals($imagePath, json_decode($result->image_path, true));
    }

    public function test_update_existing_face_data()
    {
        // Arrange
        $student = Student::factory()->create();
        $initialEmbedding = [0.1, 0.2, 0.3];
        $initialImagePath = ['path' => 'old/path.jpg'];

        $faceData = FaceData::factory()->create([
            'student_id' => $student->id,
            'face_embedding' => json_encode($initialEmbedding),
            'image_path' => json_encode($initialImagePath),
        ]);

        $newEmbedding = [0.4, 0.5, 0.6];
        $newImagePath = ['path' => 'new/path.jpg'];

        $updatedValues = [
            'face_embedding' => json_encode($newEmbedding),
            'image_path' => json_encode($newImagePath),
        ];

        // Act
        $result = $this->repository->createOrUpdate($student->id, $updatedValues);

        // Assert
        // Compare decoded values
        $this->assertEquals($newEmbedding, json_decode($result->face_embedding, true));
        $this->assertEquals($newImagePath, json_decode($result->image_path, true));
    }
}
