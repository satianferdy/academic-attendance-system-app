<?php

namespace App\Repositories\Implementations;

use App\Models\Attendance;
use App\Repositories\Interfaces\AttendanceRepositoryInterface;

class AttendanceRepository implements AttendanceRepositoryInterface
{
    protected $model;

    public function __construct(Attendance $model)
    {
        $this->model = $model;
    }

    public function findById(int $id)
    {
        return $this->model->findOrFail($id);
    }

    public function findByClassAndDate(int $classId, string $date)
    {
        return $this->model->with('student.user')
            ->where('class_schedule_id', $classId)
            ->where('date', $date)
            ->get();
    }

    public function findByClassStudentAndDate(int $classId, int $studentId, string $date)
    {
        return $this->model->where('class_schedule_id', $classId)
            ->where('student_id', $studentId)
            ->where('date', $date)
            ->first();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function createOrUpdateByClassStudentDate(array $attributes, array $values)
    {
        return $this->model->firstOrCreate($attributes, $values);
    }

    public function update(Attendance $attendance, array $data)
    {
        $attendance->update($data);
        return $attendance;
    }

    public function getStudentAttendanceByClass(int $classId, string $date)
    {
        return $this->model->with('student.user')
            ->where('class_schedule_id', $classId)
            ->where('date', $date)
            ->get();
    }

    public function getStudentAttendances(int $studentId)
    {
        return $this->model->with('classSchedule.course', 'classSchedule.lecturer.user')
            ->where('student_id', $studentId)
            ->orderBy('date', 'desc')
            ->get();
    }

    public function getFilteredAttendances($courseId = null, $date = null, $studentId = null, $status = null)
    {
        $query = $this->model->with(['classSchedule.course', 'classSchedule.lecturer.user', 'student.user']);

        // Filter by course
        if ($courseId) {
            $query->whereHas('classSchedule', function ($q) use ($courseId) {
                $q->where('course_id', $courseId);
            });
        }

        // Filter by date
        if ($date) {
            $query->whereDate('date', $date);
        }

        // Filter by student
        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        // Filter by status
        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('date', 'desc')->get();
    }
}
