<?php

namespace App\Services\Implementations;

use App\Repositories\Interfaces\SessionAttendanceRepositoryInterface;
use App\Services\Interfaces\QRCodeServiceInterface;
use Illuminate\Support\Facades\Crypt;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRCodeService implements QRCodeServiceInterface
{
    protected $sessionRepository;

    public function __construct(SessionAttendanceRepositoryInterface $sessionRepository)
    {
        $this->sessionRepository = $sessionRepository;
    }

    public function generateForAttendance(int $classId, string $date): string
    {
        // Create a token with class ID and date
        $token = Crypt::encrypt([
            'class_id' => $classId,
            'date' => $date,
            'timestamp' => now()->timestamp,
        ]);

        // Store the token in session
        $session = $this->sessionRepository->findByClassAndDate($classId, $date);

        if ($session) {
            $this->sessionRepository->update($session, [
                'qr_code' => $token,
                'is_active' => true,
            ]);
        }

        // Generate QR code with the token
        $qrSize = config('services.qrcode.size', 300);
        return QrCode::size($qrSize)->generate(route('student.attendance.show', ['token' => $token]));
    }

    public function validateToken(string $token): ?array
    {
        try {
            $data = Crypt::decrypt($token);

            // Check if token is valid (not expired)
            $timestamp = $data['timestamp'] ?? 0;
            $expiryTime = config('services.qrcode.expiry_time', 30); // minutes

            if (now()->subMinutes($expiryTime)->timestamp > $timestamp) {
                return null; // Token expired
            }

            return $data;
        } catch (\Exception $e) {
            return null;
        }
    }
}
