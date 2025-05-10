<?php

namespace App\Repositories\Implementations;

use App\Models\SessionAttendance;
use App\Repositories\Interfaces\SessionAttendanceRepositoryInterface;
use Illuminate\Support\Facades\DB;

class SessionAttendanceRepository implements SessionAttendanceRepositoryInterface
{
    protected $model;

    public function __construct(SessionAttendance $model)
    {
        $this->model = $model;
    }

    public function findActiveByClassAndDate(int $classId, string $date)
    {
        return $this->model->where('class_schedule_id', $classId)
            ->whereDate('session_date', $date)
            ->where('is_active', true)
            ->first();
    }

    public function createOrUpdate(array $attributes, array $values)
    {
        // Ensure tolerance_minutes is set
        if (!isset($values['tolerance_minutes'])) {
            $values['tolerance_minutes'] = 15; // Default tolerance
        }

        // Ensure total_hours is set
        if (!isset($values['total_hours'])) {
            $values['total_hours'] = 4; // Default hours
        }

        return $this->model->updateOrCreate($attributes, $values);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(SessionAttendance $session, array $data)
    {
        $session->update($data);
        return $session;
    }

    public function findByClassAndDate(int $classId, string $date)
    {
        return $this->model->where('class_schedule_id', $classId)
            ->whereDate('session_date', $date)
            ->first();
    }

    public function deactivateSession(int $sessionId)
    {
        return $this->model->where('id', $sessionId)
            ->update(['is_active' => false]);
    }

    public function getSessionsByClassSchedule(int $classScheduleId)
    {
        return $this->model->where('class_schedule_id', $classScheduleId)
            // Select all columns explicitly to ensure all data is retrieved
            ->select('id', 'class_schedule_id', 'session_date', 'week', 'meetings',
                    'start_time', 'end_time', 'total_hours', 'tolerance_minutes',
                    'qr_code', 'is_active')
            ->with(['classSchedule.course', 'classSchedule.classroom']) // Include relationships
            ->get();
    }

    public function findByClassWeekAndMeeting(int $classId, int $week, int $meetings)
    {
        return $this->model->where('class_schedule_id', $classId)
            ->where('week', $week)
            ->where('meetings', $meetings)
            ->first();
    }

    public function getSessionsByLecturer(
        int $lecturerId,
        ?int $courseId = null,
        ?string $date = null,
        ?int $week = null,
        ?int $studyProgramId = null,
        ?int $classroomId = null,
        ?int $semesterId = null
    ) {
        $query = $this->model
            ->select('session_attendance.*',
                     DB::raw('COUNT(CASE WHEN attendances.status = "present" THEN 1 END) as present_count'),
                     DB::raw('COUNT(CASE WHEN attendances.status = "absent" THEN 1 END) as absent_count'),
                     DB::raw('COUNT(attendances.id) as total_count'))
            ->join('class_schedules', 'session_attendance.class_schedule_id', '=', 'class_schedules.id')
            ->leftJoin('attendances', function($join) {
                $join->on('attendances.class_schedule_id', '=', 'session_attendance.class_schedule_id')
                     ->whereRaw('DATE(attendances.date) = DATE(session_attendance.session_date)');
            })
            ->where('class_schedules.lecturer_id', $lecturerId)
            ->groupBy('session_attendance.id');

        // Apply filters
        if ($courseId) {
            $query->whereHas('classSchedule', function ($q) use ($courseId) {
                $q->where('course_id', $courseId);
            });
        }

        if ($date) {
            $query->whereDate('session_date', $date);
        }

        if ($week) {
            $query->where('week', $week);
        }

        // Add new filters
        if ($studyProgramId) {
            $query->whereHas('classSchedule', function ($q) use ($studyProgramId) {
                $q->where('study_program_id', $studyProgramId);
            });
        }

        if ($classroomId) {
            $query->whereHas('classSchedule', function ($q) use ($classroomId) {
                $q->where('classroom_id', $classroomId);
            });
        }

        if ($semesterId) {
            $query->whereHas('classSchedule', function ($q) use ($semesterId) {
                $q->where('semester_id', $semesterId);
            });
        }

        return $query->with(['classSchedule.course', 'classSchedule.classroom.studyProgram'])
            ->orderBy('session_date', 'desc')
            ->get();
    }

    public function sessionExistsForDate(int $classId, string $date): bool
    {
        return $this->model
            ->where('class_schedule_id', $classId)
            ->whereDate('session_date', $date)
            ->exists();
    }

    public function findByQrCode(string $qrCode)
    {
        return $this->model->where('qr_code', $qrCode)
            ->first();
    }

    public function findById(int $id)
    {
        return $this->model->with(['classSchedule.course', 'classSchedule.classroom'])->findOrFail($id);
    }

    public function getSessionTotalHours(int $classScheduleId, string $date): int
    {
        $session = $this->findByClassAndDate($classScheduleId, $date);
        return $session ? $session->total_hours : 4; // Default to 4 if session not found
    }
}


