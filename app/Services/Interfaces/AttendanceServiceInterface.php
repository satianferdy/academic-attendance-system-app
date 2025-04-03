<?php

namespace App\Services\Interfaces;

use App\Models\ClassSchedule;
use App\Models\Student;

interface AttendanceServiceInterface
{
    public function markAttendance(int $classId, int $studentId, string $date): array;
    public function getAttendanceByClass(int $classId, string $date): array;
    public function generateSessionAttendance(ClassSchedule $classSchedule, string $date): array;
}
