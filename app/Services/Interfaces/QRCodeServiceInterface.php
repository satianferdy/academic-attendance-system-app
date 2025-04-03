<?php

namespace App\Services\Interfaces;

interface QRCodeServiceInterface
{
    public function generateForAttendance(int $classId, string $date): string;
    public function validateToken(string $token): ?array;
}
