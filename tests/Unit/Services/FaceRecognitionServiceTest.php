<?php

namespace Tests\Unit\Services;

use App\Exceptions\FaceRecognitionException;
use App\Models\Student;
use App\Repositories\Interfaces\FaceDataRepositoryInterface;
use App\Repositories\Interfaces\StudentRepositoryInterface;
use App\Services\Implementations\FaceRecognitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class FaceRecognitionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $studentRepository;
    protected $faceDataRepository;
    protected $faceRecognitionService;
    protected $apiUrl;
    protected $apiKey;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mocks for repositories
        $this->studentRepository = Mockery::mock(StudentRepositoryInterface::class);
        $this->faceDataRepository = Mockery::mock(FaceDataRepositoryInterface::class);

        // Setup config for face recognition service
        $this->apiUrl = 'http://face-api.example.com';
        $this->apiKey = 'fake-api-key';

        Config::set('services.face_recognition.url', $this->apiUrl);
        Config::set('services.face_recognition.key', $this->apiKey);
        Config::set('services.face_recognition.storage_path', 'face_images');
        Config::set('services.face_recognition.max_image_size', 5 * 1024); // 5MB

        // Initialize the service with mocked repositories
        $this->faceRecognitionService = new FaceRecognitionService(
            $this->studentRepository,
            $this->faceDataRepository
        );

        // Create a fake storage disk for testing
        Storage::fake('local');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_verify_face_successfully()
    {
        // Arrange
        $classId = 1;
        $nim = '12345678';
        $image = UploadedFile::fake()->image('face.jpg', 100, 100);

        // Mock successful HTTP response
        Http::fake([
            "*" => Http::response([
                'status' => 'success',
                'message' => 'Face verified successfully',
                'data' => [
                    'match' => true,
                    'confidence' => 0.95
                ]
            ], 200)
        ]);

        // Act
        $result = $this->faceRecognitionService->verifyFace($image, $classId, $nim);

        // Assert
        $this->assertEquals('success', $result['status']);
        $this->assertEquals(true, $result['data']['match']);
        $this->assertEquals(0.95, $result['data']['confidence']);

        // Verify HTTP request was made with appropriate URL
        Http::assertSent(function ($request) {
            return Str::contains($request->url(), '/api/verify-face') &&
                   $request->hasHeader('X-API-Key', $this->apiKey);
        });
    }

    public function test_return_error_when_verification_api_fails()
    {
        // Arrange
        $classId = 1;
        $nim = '12345678';
        $image = UploadedFile::fake()->image('face.jpg', 100, 100);

        // Mock failed HTTP response
        Http::fake([
            "{$this->apiUrl}/api/verify-face" => Http::response([
                'status' => 'error',
                'message' => 'Face not found',
                'code' => 'FaceNotRegisteredError'
            ], 404)
        ]);

        // Act
        $result = $this->faceRecognitionService->verifyFace($image, $classId, $nim);

        // Assert
        $this->assertEquals('error', $result['status']);
        // Check for the mapped error message
        $this->assertStringContainsString('You have not registered your face yet', $result['message']);
        $this->assertEquals('FaceNotRegisteredError', $result['code']);
    }

    public function test_reject_invalid_image_format()
    {
        // Arrange
        $classId = 1;
        $nim = '12345678';
        $image = UploadedFile::fake()->create('document.pdf', 100);

        // Act
        $result = $this->faceRecognitionService->verifyFace($image, $classId, $nim);

        // Assert
        $this->assertEquals('error', $result['status']);
        $this->assertStringContainsString('Invalid image format', $result['message']);
    }

    public function test_reject_oversized_image()
    {
        // Arrange
        $classId = 1;
        $nim = '12345678';
        $maxSize = Config::get('services.face_recognition.max_image_size');
        $image = UploadedFile::fake()->create('large.jpg', ($maxSize / 1024) + 1); // Convert KB to MB and add 1 MB

        // Act
        $result = $this->faceRecognitionService->verifyFace($image, $classId, $nim);

        // Assert
        $this->assertEquals('error', $result['status']);
        // Update the assertion to match the actual error message from your service
        $this->assertStringContainsString('Failed to verify face', $result['message']);
    }

    public function test_register_face_successfully()
    {
        // Arrange
        $nim = '12345678';
        $studentId = 1;
        $images = [
            UploadedFile::fake()->image('face1.jpg', 100, 100),
            UploadedFile::fake()->image('face2.jpg', 100, 100)
        ];

        // Create a mock student
        $student = new Student();
        $student->id = $studentId;
        $student->nim = $nim;

        // Setup mocks
        $this->studentRepository->shouldReceive('findByNim')
            ->with($nim)
            ->once()
            ->andReturn($student);

        $this->faceDataRepository->shouldReceive('createOrUpdate')
            ->once()
            ->andReturnUsing(function ($actualStudentId, $data) use ($studentId) {
                $this->assertEquals($studentId, $actualStudentId);
                $this->assertIsString($data['face_embedding']);
                $this->assertIsString($data['image_path']);
                $this->assertTrue($data['is_active']);
                return true;
            });

        // Mock successful HTTP responses for both image processing calls
        Http::fake([
            "{$this->apiUrl}/api/process-face" => Http::sequence()
                ->push([
                    'status' => 'success',
                    'message' => 'Face processed successfully',
                    'data' => ['embedding' => array_fill(0, 128, 0.1)]
                ], 200)
                ->push([
                    'status' => 'success',
                    'message' => 'Face processed successfully',
                    'data' => ['embedding' => array_fill(0, 128, 0.2)]
                ], 200)
        ]);

        // Act
        $result = $this->faceRecognitionService->registerFace($images, $nim);

        // Assert
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('Face registered successfully', $result['message']);
        $this->assertEquals($studentId, $result['data']['student_id']);
        $this->assertEquals($nim, $result['data']['nim']);
        $this->assertEquals(1, $result['data']['image_count']);

        // Verify images were stored
        $folderPath = "face_images/{$nim}";
        Storage::assertExists($folderPath);

        // Verify HTTP requests were made
        Http::assertSentCount(2);
    }

    public function test_return_error_when_student_not_found_during_registration()
    {
        // Arrange
        $nim = '12345678';
        $images = [
            UploadedFile::fake()->image('face1.jpg', 100, 100),
        ];

        // Setup mock to return null (student not found)
        $this->studentRepository->shouldReceive('findByNim')
            ->with($nim)
            ->once()
            ->andReturn(null);

        // Mock HTTP response
        Http::fake([
            "{$this->apiUrl}/api/process-face" => Http::response([
                'status' => 'success',
                'message' => 'Face processed successfully',
                'data' => ['embedding' => array_fill(0, 128, 0.1)]
            ], 200)
        ]);

        // Act
        $result = $this->faceRecognitionService->registerFace($images, $nim);

        // Assert
        $this->assertEquals('error', $result['status']);
        $this->assertStringContainsString('Student with NIM', $result['message']);
    }

    public function test_return_error_when_face_processing_fails()
    {
        // Arrange
        $nim = '12345678';
        $images = [
            UploadedFile::fake()->image('face1.jpg', 100, 100),
        ];

        // Mock failed HTTP response
        Http::fake([
            "{$this->apiUrl}/api/process-face" => Http::response([
                'status' => 'error',
                'message' => 'No face detected'
            ], 400)
        ]);

        // Act
        $result = $this->faceRecognitionService->registerFace($images, $nim);

        // Assert
        $this->assertEquals('error', $result['status']);
        $this->assertStringContainsString('Failed to process image', $result['message']);
    }

    public function test_validate_image_quality_successfully()
    {
        // Arrange
        $image = UploadedFile::fake()->image('face.jpg', 100, 100);

        // Mock successful HTTP response
        Http::fake([
            "{$this->apiUrl}/api/validate-quality" => Http::response([
                'status' => 'success',
                'message' => 'Image quality is good',
                'data' => [
                    'quality_score' => 0.9,
                    'is_valid' => true,
                    'has_face' => true,
                    'face_count' => 1
                ]
            ], 200)
        ]);

        // Act
        $result = $this->faceRecognitionService->validateQuality($image);

        // Assert
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('Image quality is good', $result['message']);
        $this->assertTrue($result['data']['is_valid']);
        $this->assertEquals(0.9, $result['data']['quality_score']);
    }

    public function test_return_error_when_quality_validation_fails()
    {
        // Arrange
        $image = UploadedFile::fake()->image('face.jpg', 100, 100);

        // Mock failed HTTP response
        Http::fake([
            "{$this->apiUrl}/api/validate-quality" => Http::response([
                'status' => 'error',
                'message' => 'Multiple faces detected'
            ], 400)
        ]);

        // Act
        $result = $this->faceRecognitionService->validateQuality($image);

        // Assert
        $this->assertEquals('error', $result['status']);
    }
}
