<?php
// app/Services/Interfaces/FaceRecognitionServiceInterface.php
namespace App\Services\Interfaces;

use Illuminate\Http\UploadedFile;

interface FaceRecognitionServiceInterface
{
    public function registerFace(array $image, string $nim): array;
    public function verifyFace(UploadedFile $image, int $classId, string $nim): array;
    public function validateQuality(UploadedFile $image): array;
}
