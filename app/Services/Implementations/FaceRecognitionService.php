<?php

namespace App\Services\Implementations;

use App\Services\Interfaces\FaceRecognitionServiceInterface;
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

    public function __construct()
    {
        $this->apiUrl = config('services.face_recognition.url');
        $this->apiKey = config('services.face_recognition.key');
    }

    public function verifyFace(UploadedFile $image, int $classId, string $nim): array
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
            ])->attach(
                'image',
                file_get_contents($image->getPathname()),
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
        } catch (\Exception $e) {
            Log::error('Face recognition verification error: ' . $e->getMessage());
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
            $imagePaths = [];

            // Proses setiap gambar
            foreach ($images as $image) {
                // Kirim ke Flask untuk ekstraksi embedding
                try {
                    $response = Http::withHeaders([
                        'X-API-Key' => $this->apiKey,
                    ])->timeout(30) // Timeout 30 detik
                    ->attach(
                        'image',
                        file_get_contents($image->getPathname()),
                        $image->getClientOriginalName()
                    )->post("{$this->apiUrl}/api/process-face", [
                        'nim' => $nim,
                    ]);

                    if (!$response->successful()) {
                        throw new \Exception("Failed to process image: " . $response->body());
                    }

                    // Validasi response dari Flask
                    $responseData = $response->json();
                    if (!isset($responseData['data']['embedding'])) {
                        throw new \Exception("Invalid response from face recognition service");
                    }

                    // Simpan embedding
                    $embeddings[] = $responseData['data']['embedding'];

                    // Simpan gambar ke storage
                    $imagePaths[] = $this->storeImage($image, $nim);

                } catch (\Exception $e) {
                    Log::error("Failed to process image for NIM {$nim}: " . $e->getMessage());
                    throw new \Exception("Failed to process one or more images. Please try again.");
                }
            }

            // Hitung rata-rata embedding
            $averageEmbedding = $this->averageEmbeddings($embeddings);

            // Simpan ke database
            $student = Student::where('nim', $nim)->first();
            if (!$student) {
                throw new \Exception("Student not found");
            }

            FaceData::updateOrCreate(
                ['student_id' => $student->id],
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
                    'image_path' => $imagePaths
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Face registration error: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function validateQuality(UploadedFile $image): array
    {
        try {
            // Kirim gambar ke Flask untuk validasi kualitas
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
            ])->attach(
                'image',
                file_get_contents($image->getPathname()),
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
        } catch (\Exception $e) {
            Log::error('Face quality validation error: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'An error occurred during quality validation.  Please try again.',
            ];
        }
    }

    private function storeImage($image, string $nim): string
    {
        try {
            // Buat folder jika belum ada
            $folderPath = "face_images/{$nim}";
            if (!Storage::exists($folderPath)) {
                Storage::makeDirectory($folderPath);
            }

            // Simpan gambar
            $fileName = Str::uuid() . '.jpg';
            $filePath = "{$folderPath}/{$fileName}";
            Storage::put($filePath, file_get_contents($image->getPathname()));

            return $filePath;

        } catch (\Exception $e) {
            Log::error("Failed to store image for NIM {$nim}: " . $e->getMessage());
            throw new \Exception("Failed to store image. Please try again.");
        }
    }

    private function averageEmbeddings(array $embeddings): array
    {
        if (empty($embeddings)) {
            throw new \Exception("No embeddings to average");
        }

        $sum = array_fill(0, count($embeddings[0]), 0);
        foreach ($embeddings as $embedding) {
            foreach ($embedding as $i => $value) {
                $sum[$i] += $value;
            }
        }

        return array_map(function($val) use ($embeddings) {
            return $val / count($embeddings);
        }, $sum);
    }
}
