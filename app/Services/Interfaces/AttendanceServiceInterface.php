<?php

namespace App\Services\Interfaces;

use App\Models\ClassSchedule;

interface AttendanceServiceInterface
{
    public function markAttendance(int $classId, int $studentId, string $date): array;
    public function getAttendanceByClass(int $classId, string $date): array;
    public function generateSessionAttendance(int $classScheduleId, string $date, int $week, int $meetings): array;
    public function isStudentEnrolled(int $studentId, int $classScheduleId): bool;
    public function isAttendanceAlreadyMarked(int $studentId, int $classScheduleId, string $date): bool;
    public function getStudentAttendances(int $studentId);
    public function isSessionActive(int $classScheduleId, string $date): bool;
    public function updateAttendanceStatus($attendance, array $data): array;
    public function getCumulativeAttendance(int $classId, int $studentId = null);
}
