<?php

// Step 1: Create Repository Interfaces
// app/Repositories/Interfaces/AttendanceRepositoryInterface.php
namespace App\Repositories\Interfaces;

use App\Models\Attendance;
use Illuminate\Support\Collection;

interface AttendanceRepositoryInterface
{
    public function findById(int $id);
    public function findByClassAndDate(int $classId, string $date);
    public function findByClassStudentAndDate(int $classId, int $studentId, string $date);
    public function create(array $data);
    public function createOrUpdateByClassStudentDate(array $attributes, array $values);
    public function update(Attendance $attendance, array $data);
    public function getStudentAttendanceByClass(int $classId, string $date);
    public function getStudentAttendances(int $studentId);
    public function getFilteredAttendances($courseId = null, $date = null, $studentId = null, $status = null);
    public function getAttendancesByClassAndStudent(int $classId, ?int $studentId = null);
    public function getCumulativeAttendanceData(int $classScheduleId): Collection;
}
