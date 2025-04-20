<?php

namespace App\Repositories\Implementations;

use App\Models\ClassSchedule;
use App\Repositories\Interfaces\ClassScheduleRepositoryInterface;
use Carbon\Carbon;

class ClassScheduleRepository implements ClassScheduleRepositoryInterface
{
    protected $model;

    public function __construct(ClassSchedule $model)
    {
        $this->model = $model;
    }

    public function find(int $id)
    {
        return $this->model->findOrFail($id);
    }

    public function getStudents(ClassSchedule $classSchedule)
    {
        return $classSchedule->students();
    }

    public function findConflictingTimeSlots(string $room, string $day, string $startTime, string $endTime, ?int $lecturerId = null, ?int $excludeId = null)
    {
        $conflicts = [
            'room' => [],
            'lecturer' => []
        ];

        // Check room conflicts
        $roomSchedules = $this->getSchedulesByRoomAndDay($room, $day, $excludeId);

        foreach ($roomSchedules as $schedule) {
            foreach ($schedule->timeSlots as $timeSlot) {
                if ($this->checkTimeOverlap(
                    $startTime,
                    $endTime,
                    $timeSlot->start_time->format('H:i'),
                    $timeSlot->end_time->format('H:i')
                )) {
                    $conflicts['room'][] = [
                        'slot' => $timeSlot->start_time->format('H:i') . ' - ' . $timeSlot->end_time->format('H:i'),
                        'schedule' => $schedule
                    ];
                }
            }
        }

        // Check lecturer conflicts
        if ($lecturerId) {
            $lecturerSchedules = $this->getSchedulesByLecturerAndDay($lecturerId, $day, $excludeId);

            foreach ($lecturerSchedules as $schedule) {
                foreach ($schedule->timeSlots as $timeSlot) {
                    if ($this->checkTimeOverlap(
                        $startTime,
                        $endTime,
                        $timeSlot->start_time->format('H:i'),
                        $timeSlot->end_time->format('H:i')
                    )) {
                        $conflicts['lecturer'][] = [
                            'slot' => $timeSlot->start_time->format('H:i') . ' - ' . $timeSlot->end_time->format('H:i'),
                            'schedule' => $schedule
                        ];
                    }
                }
            }
        }

        return $conflicts;
    }

    public function checkTimeOverlap(string $start1, string $end1, string $start2, string $end2): bool
    {
        $start1 = Carbon::createFromFormat('H:i', $start1);
        $end1 = Carbon::createFromFormat('H:i', $end1);
        $start2 = Carbon::createFromFormat('H:i', $start2);
        $end2 = Carbon::createFromFormat('H:i', $end2);

        return
            // Start time is within an existing slot
            ($start1->gte($start2) && $start1->lt($end2)) ||
            // End time is within an existing slot
            ($end1->gt($start2) && $end1->lte($end2)) ||
            // Selected time encloses an existing slot
            ($start1->lte($start2) && $end1->gte($end2));
    }

    public function getSchedulesByRoomAndDay(string $room, string $day, ?int $excludeId = null)
    {
        return $this->model->byRoom($room)
            ->onDay($day)
            ->exclude($excludeId)
            ->with(['timeSlots', 'lecturer.user'])
            ->get();
    }

    public function getSchedulesByLecturerAndDay(int $lecturerId, string $day, ?int $excludeId = null)
    {
        return $this->model->byLecturer($lecturerId)
            ->onDay($day)
            ->exclude($excludeId)
            ->with(['timeSlots', 'lecturer.user'])
            ->get();
    }

    public function getAllSchedules(int $perPage = 10)
    {
        return $this->model->with(['lecturer.user', 'course', 'classroom', 'timeSlots'])
                          ->orderBy('day')
                          ->paginate($perPage);
    }

    public function createSchedule(array $data)
    {
        return $this->model->create([
            'course_id' => $data['course_id'],
            'lecturer_id' => $data['lecturer_id'],
            'classroom_id' => $data['classroom_id'],
            'room' => $data['room'],
            'day' => $data['day'],
            'semester' => $data['semester'],
            'academic_year' => $data['academic_year'],
            'total_weeks' => $data['total_weeks'] ?? 16,
            'meetings_per_week' => $data['meetings_per_week'] ?? 1,
        ]);
    }

    public function updateSchedule(int $id, array $data)
    {
        $schedule = $this->find($id);
        $schedule->update([
            'course_id' => $data['course_id'],
            'lecturer_id' => $data['lecturer_id'],
            'classroom_id' => $data['classroom_id'],
            'room' => $data['room'],
            'day' => $data['day'],
            'semester' => $data['semester'],
            'academic_year' => $data['academic_year'],
            'total_weeks' => $data['total_weeks'] ?? $schedule->total_weeks,
            'meetings_per_week' => $data['meetings_per_week'] ?? $schedule->meetings_per_week,
        ]);
        return $schedule;
    }

    public function deleteSchedule(int $id)
    {
        $schedule = $this->find($id);
        return $schedule->delete();
    }
}
