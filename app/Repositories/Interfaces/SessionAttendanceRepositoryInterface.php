<?php

namespace App\Repositories\Interfaces;

use App\Models\SessionAttendance;

interface SessionAttendanceRepositoryInterface
{
    public function findActiveByClassAndDate(int $classId, string $date);
    public function createOrUpdate(array $attributes, array $values);
    public function create(array $data);
    public function update(SessionAttendance $session, array $data);
    public function findByClassAndDate(int $classId, string $date);
    public function deactivateSession(int $sessionId);
    public function getSessionsByClassSchedule(int $classScheduleId);
    public function findByClassWeekAndMeeting(int $classId, int $week, int $meeting);
    public function getSessionsByLecturer(
        int $lecturerId,
        ?int $courseId = null,
        ?string $date = null,
        ?int $week = null,
        ?int $studyProgramId = null,
        ?int $classroomId = null,
        ?int $semesterId = null
    );
    public function sessionExistsForDate(int $classId, string $date): bool;
    public function findByQrCode(string $qrCode);
    public function findById(int $id);
}
