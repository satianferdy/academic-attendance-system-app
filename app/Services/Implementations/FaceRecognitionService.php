<?php

namespace App\Services\Implementations;

use App\Services\Interfaces\FaceRecognitionServiceInterface;
use App\Repositories\Interfaces\StudentRepositoryInterface;
use App\Repositories\Interfaces\FaceDataRepositoryInterface;
use App\Exceptions\FaceRecognitionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Student;
use App\Models\FaceData;

class FaceRecognitionService implements FaceRecognitionServiceInterface
{
    protected $apiUrl;
    protected $apiKey;
    protected $imageFolderPath;
    protected $studentRepository;
    protected $faceDataRepository;

    public function __construct(
        // Inject repositories if needed
        StudentRepositoryInterface $studentRepository,
        FaceDataRepositoryInterface $faceDataRepository
    )
    {
        $this->apiUrl = config('services.face_recognition.url');
        $this->apiKey = config('services.face_recognition.key');
        $this->imageFolderPath = config('services.face_recognition.storage_path', 'face_images');
        $this->studentRepository = $studentRepository;
        $this->faceDataRepository = $faceDataRepository;
    }

    public function verifyFace(UploadedFile $image, int $classId, string $nim): array
    {
        try {
            $this->validateImage($image);

            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
            ])->attach(
                'image',
                $image->get(),
                $image->getClientOriginalName()
            )->post("{$this->apiUrl}/api/verify-face", [
                'class_id' => $classId,
                'nim' => $nim,
            ]);

            // Check if the response is successful and contains JSON
            if ($response->successful() && $response->json() !== null) {
                return $response->json();
            }

            // Handle unsuccessful responses or invalid JSON
            Log::error('Face verification API error: ' . $response->body());
            return [
                'status' => 'error',
                'message' => 'Invalid response from face recognition service. Status: ' . $response->status(),
            ];
        } catch (FaceRecognitionException $e) {
            Log::error('Face verification validation error', [
                'message' => $e->getMessage(),
                'nim' => $nim
            ]);
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            Log::error('Face recognition verification error', [
                'message' => $e->getMessage(),
                'nim' => $nim
            ]);
            return [
                'status' => 'error',
                'message' => 'Failed to verify face. Please try again later.',
            ];
        }
    }

   // FaceRecognitionService.php
   public function registerFace(array $images, string $nim): array
   {
       try {
           $embeddings = [];
           $imagePaths = null;

           // Process each image
           foreach ($images as $index => $image) {
               $this->validateImage($image);

               // Send to Flask for embedding extraction
               $response = Http::withHeaders([
                   'X-API-Key' => $this->apiKey,
               ])
               ->timeout(30)
               ->attach(
                   'image',
                   $image->get(),
                   $image->getClientOriginalName()
               )
               ->post("{$this->apiUrl}/api/process-face", [
                   'nim' => $nim,
               ]);

               if (!$response->successful()) {
                   throw new FaceRecognitionException("Failed to process image: " . $response->body());
               }

               // Validate response from face recognition service
               $responseData = $response->json();
               if (!isset($responseData['data']['embedding'])) {
                   throw new FaceRecognitionException("Invalid response from face recognition service");
               }

               // Store embedding
               $embeddings[] = $responseData['data']['embedding'];

               // Store image to storage
               if ($index === 0) {
                   $imagePaths = $this->storeImage($image, $nim);
               }
           }

           // Calculate average embedding
           $averageEmbedding = $this->averageEmbeddings($embeddings);

           // Save to database
           $student = $this->studentRepository->findByNim($nim);

            if (!$student) {
                throw new FaceRecognitionException("Student with NIM {$nim} not found");
            }

           $this->faceDataRepository->createOrUpdate(
                $student->id,
                [
                    'face_embedding' => json_encode($averageEmbedding),
                    'image_path' => json_encode($imagePaths),
                    'is_active' => true
                ]
           );

           return [
               'status' => 'success',
               'message' => 'Face registered successfully',
               'data' => [
                   'student_id' => $student->id,
                   'nim' => $nim,
                   'image_count' => 1,
               ]
           ];

       } catch (FaceRecognitionException $e) {
           Log::error('Face registration validation error', [
               'message' => $e->getMessage(),
               'nim' => $nim
           ]);
           return [
               'status' => 'error',
               'message' => $e->getMessage()
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

    public function validateQuality(UploadedFile $image): array
    {
        try {
            $this->validateImage($image);

            // Kirim gambar ke Flask untuk validasi kualitas
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
            ])->attach(
                'image',
                $image->get(),
                $image->getClientOriginalName()
            )->post("{$this->apiUrl}/api/validate-quality");

            // Handle response
            if ($response->successful() && $response->json('status') === 'success') {
                return $response->json();
            }

            // Handle error response
            Log::error('Face quality validation failed: ' . $response->body());
            return [
                'status' => 'error',
                'message' => 'Failed to validate image quality. Please try again.',
            ];
        } catch (FaceRecognitionException $e) {
            Log::error('Face quality validation parameter error', [
                'message' => $e->getMessage()
            ]);
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            Log::error('Face quality validation error', [
                'message' => $e->getMessage()
            ]);
            return [
                'status' => 'error',
                'message' => 'An error occurred during quality validation. Please try again.',
            ];
        }
    }

    private function storeImage(UploadedFile $image, string $nim): string
    {
        try {
            // Create folder if it doesn't exist
            $folderPath = "{$this->imageFolderPath}/{$nim}";
            if (!Storage::exists($folderPath)) {
                Storage::makeDirectory($folderPath);
            }

            // Store image
            $fileName = Str::uuid() . '.jpg';
            $filePath = "{$folderPath}/{$fileName}";

            // Use Laravel's storage mechanisms rather than raw file_get_contents
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
        $maxSize = config('services.face_recognition.max_image_size', 5 * 1024); // 5MB default

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
}
