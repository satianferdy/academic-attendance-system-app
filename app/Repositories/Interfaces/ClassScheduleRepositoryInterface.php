<?php

namespace App\Repositories\Interfaces;

use App\Models\ClassSchedule;

interface ClassScheduleRepositoryInterface
{
    public function find(int $id);
    public function getStudents(ClassSchedule $classSchedule);
    public function findConflictingTimeSlots(string $room, string $day, string $startTime, string $endTime, ?int $lecturerId = null, ?int $excludeId = null);
    public function checkTimeOverlap(string $start1, string $end1, string $start2, string $end2): bool;
    public function getSchedulesByRoomAndDay(string $room, string $day, ?int $excludeId = null);
    public function getSchedulesByLecturerAndDay(int $lecturerId, string $day, ?int $excludeId = null);
    public function getAllSchedules(int $perPage = 10);
    public function getSchedulesByLecturerId(int $lecturerId, int $perPage = 10);
    public function createSchedule(array $data);
    public function updateSchedule(int $id, array $data);
    public function deleteSchedule(int $id);
}
