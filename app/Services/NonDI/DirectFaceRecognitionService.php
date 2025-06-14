<?php
// app/Services/NonDI/DirectFaceRecognitionService.php

namespace App\Services\NonDI;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Student;
use App\Models\FaceData;
use App\Exceptions\FaceRecognitionException;

class DirectFaceRecognitionService
{
    protected $apiUrl;
    protected $apiKey;
    protected $imageFolderPath;

    public function __construct()
    {
        $this->apiUrl = config('services.face_recognition.url');
        $this->apiKey = config('services.face_recognition.key');
        $this->imageFolderPath = config('services.face_recognition.storage_path', 'face_images');
    }

    public function registerFace(array $images, string $nim): array
    {
        try {
            $embeddings = [];
            $imagePaths = null;

            // Direct database access - no repository pattern
            $student = Student::where('nim', $nim)->firstOrFail();

            foreach ($images as $index => $image) {
                $this->validateImage($image);

                // Direct HTTP call - no interface abstraction
                $response = Http::withHeaders([
                    'X-API-Key' => $this->apiKey,
                ])
                ->timeout(30)
                ->attach('image', $image->get(), $image->getClientOriginalName())
                ->post("{$this->apiUrl}/api/process-face", [
                    'nim' => $nim,
                ]);

                if (!$response->successful()) {
                    throw new FaceRecognitionException("Failed to process image: " . $response->body());
                }

                $responseData = $response->json();
                if (!isset($responseData['data']['embedding'])) {
                    throw new FaceRecognitionException("Invalid response from face recognition service");
                }

                $embeddings[] = $responseData['data']['embedding'];

                if ($index === 0) {
                    $imagePaths = $this->storeImage($image, $nim);
                }
            }

            $averageEmbedding = $this->averageEmbeddings($embeddings);

            // Direct database access - no repository
            FaceData::updateOrCreate(
                ['student_id' => $student->id],
                [
                    'face_embedding' => json_encode($averageEmbedding),
                    'image_path' => json_encode($imagePaths),
                    'is_active' => true
                ]
            );

            // Direct database update - no repository
            $student->update(['face_registered' => true]);

            return [
                'status' => 'success',
                'message' => 'Face registered successfully',
                'data' => [
                    'student_id' => $student->id,
                    'nim' => $nim,
                    'image_count' => 1,
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Face registration error', [
                'message' => $e->getMessage(),
                'nim' => $nim
            ]);
            return [
                'status' => 'error',
                'message' => 'An error occurred during face registration. Please try again.'
            ];
        }
    }

    public function verifyFace(UploadedFile $image, int $classId, string $nim): array
    {
        try {
            $this->validateImage($image);

            // Direct HTTP call - no abstraction
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
            ])->attach('image', $image->get(), $image->getClientOriginalName())
            ->post("{$this->apiUrl}/api/verify-face", [
                'class_id' => $classId,
                'nim' => $nim,
            ]);

            if ($response->successful() && $response->json('status') === 'success') {
                return $response->json();
            }

            $errorData = $response->json();
            $errorCode = $errorData['code'] ?? 'UNKNOWN_ERROR';
            $errorMessage = $this->mapErrorCodeToMessage($errorCode, $errorData['message'] ?? 'Face verification failed');

            Log::error('Face verification API error: ' . $response->body());
            return [
                'status' => 'error',
                'message' => $errorMessage,
                'code' => $errorCode
            ];
        } catch (\Exception $e) {
            Log::error('Face recognition verification error', [
                'message' => $e->getMessage(),
                'nim' => $nim
            ]);
            return [
                'status' => 'error',
                'message' => 'Failed to verify face. Please try again later.',
                'code' => 'SYSTEM_ERROR'
            ];
        }
    }

    public function validateQuality(UploadedFile $image): array
    {
        try {
            $this->validateImage($image);

            // Direct HTTP call
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
            ])->attach('image', $image->get(), $image->getClientOriginalName())
            ->post("{$this->apiUrl}/api/validate-quality");

            if ($response->successful() && $response->json('status') === 'success') {
                return $response->json();
            }

            $errorData = $response->json();
            $errorCode = $errorData['code'] ?? 'UNKNOWN_ERROR';
            $errorMessage = $this->mapErrorCodeToMessage($errorCode, $errorData['message'] ?? 'Unknown error occurred');

            Log::error('Face quality validation failed: ' . $response->body());
            return [
                'status' => 'error',
                'message' => $errorMessage,
                'code' => $errorCode
            ];
        } catch (\Exception $e) {
            Log::error('Face quality validation error', [
                'message' => $e->getMessage()
            ]);
            return [
                'status' => 'error',
                'message' => 'An error occurred during quality validation. Please try again.',
                'code' => 'SYSTEM_ERROR'
            ];
        }
    }

    private function storeImage(UploadedFile $image, string $nim): string
    {
        try {
            $folderPath = "{$this->imageFolderPath}/{$nim}";
            if (!Storage::exists($folderPath)) {
                Storage::makeDirectory($folderPath);
            }

            $fileName = Str::uuid() . '.jpg';
            $filePath = "{$folderPath}/{$fileName}";

            if (!Storage::put($filePath, $image->get())) {
                throw new FaceRecognitionException("Failed to store image");
            }

            return $filePath;
        } catch (\Exception $e) {
            Log::error("Failed to store image", [
                'nim' => $nim,
                'error' => $e->getMessage()
            ]);
            throw new FaceRecognitionException("Failed to store image. Please try again.");
        }
    }

    private function averageEmbeddings(array $embeddings): array
    {
        if (empty($embeddings)) {
            throw new FaceRecognitionException("No embeddings to average");
        }

        $embeddingCount = count($embeddings);
        $embeddingLength = count($embeddings[0]);
        $sum = array_fill(0, $embeddingLength, 0);

        foreach ($embeddings as $embedding) {
            if (count($embedding) !== $embeddingLength) {
                throw new FaceRecognitionException("Inconsistent embedding dimensions");
            }

            foreach ($embedding as $i => $value) {
                $sum[$i] += $value;
            }
        }

        return array_map(function($val) use ($embeddingCount) {
            return $val / $embeddingCount;
        }, $sum);
    }

    private function validateImage(UploadedFile $image): void
    {
        $maxSize = config('services.face_recognition.max_image_size', 5 * 1024);

        if (!$image->isValid()) {
            throw new FaceRecognitionException('Invalid image file');
        }

        if ($image->getSize() > $maxSize * 1024) {
            throw new FaceRecognitionException("Image size exceeds maximum allowed ({$maxSize}KB)");
        }

        $mimeType = $image->getMimeType();
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];

        if (!in_array($mimeType, $allowedTypes)) {
            throw new FaceRecognitionException('Invalid image format. Only JPEG and PNG are supported');
        }
    }

    private function mapErrorCodeToMessage(string $errorCode, string $defaultMessage): string
    {
        $errorMessages = [
            'NoFaceDetectedError' => 'No face detected in the image. Please ensure your face is clearly visible.',
            'MultipleFacesError' => 'Multiple faces detected. Please ensure only your face is in the frame.',
            'FaceDetectionError' => 'Could not properly detect face features. Please try with better lighting.',
            'LOW_QUALITY_IMAGE' => 'The image quality is too low. Please try again with better lighting and less blur.',
            'StudentNotFoundError' => 'Student record not found. Please contact administrator.',
            'FaceNotRegisteredError' => 'You have not registered your face yet. Please register first.',
            'PROCESSING_ERROR' => 'There was an error processing your face image. Please try again.',
            'QUALITY_VALIDATION_ERROR' => 'There was an error validating the image quality. Please try again.'
        ];

        return $errorMessages[$errorCode] ?? $defaultMessage;
    }
}
