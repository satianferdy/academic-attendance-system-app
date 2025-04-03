<?php

namespace App\Services\Implementations;

use App\Models\ClassSchedule;
use App\Models\SessionAttendance;
use App\Services\Interfaces\QRCodeServiceInterface;
use Illuminate\Support\Facades\Crypt;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRCodeService implements QRCodeServiceInterface
{
    public function generateForAttendance(int $classId, string $date): string
    {
        // Create a token with class ID and date
        $token = Crypt::encrypt([
            'class_id' => $classId,
            'date' => $date,
            'timestamp' => now()->timestamp,
        ]);

        // Store the token in session
        $session = SessionAttendance::where('class_schedule_id', $classId)
            ->where('session_date', $date)
            ->first();

        if ($session) {
            $session->update([
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
