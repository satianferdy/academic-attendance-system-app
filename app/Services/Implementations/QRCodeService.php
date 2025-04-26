<?php

namespace App\Services\Implementations;

use App\Repositories\Interfaces\SessionAttendanceRepositoryInterface;
use App\Services\Interfaces\QRCodeServiceInterface;
use Illuminate\Support\Facades\Crypt;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

class QRCodeService implements QRCodeServiceInterface
{
    protected $sessionRepository;

    public function __construct(SessionAttendanceRepositoryInterface $sessionRepository)
    {
        $this->sessionRepository = $sessionRepository;
    }

    public function generateForAttendance(int $classId, string $date): string
    {
         // Generate a short UUID (or use a random string)
        $token = (string) Str::uuid();

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
           $session = $this->sessionRepository->findByQrCode($token);

            if (!$session || !$session->is_active) {
                return null; // Session not found or not active
            }

            // Check if current time is past the session end time
            if (now() > $session->end_time) {
                return null; // Session has expired
            }

            return [
                'class_id' => $session->class_schedule_id,
                'date' => $session->session_date,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}
