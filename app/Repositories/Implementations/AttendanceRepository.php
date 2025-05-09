<?php

namespace App\Repositories\Implementations;

use App\Models\Attendance;
use App\Models\ClassSchedule;
use Illuminate\Support\Collection;
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
            ->whereDate('date', $date)
            ->get();
    }

    public function findByClassStudentAndDate(int $classId, int $studentId, string $date)
    {
        return $this->model->where('class_schedule_id', $classId)
            ->where('student_id', $studentId)
            ->whereDate('date', $date)
            ->first();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function createOrUpdateByClassStudentDate(array $attributes, array $values)
    {
        return $this->model->updateOrCreate($attributes, $values);
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
            ->whereDate('date', $date)
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

    public function getAttendancesByClassAndStudent(int $classId, ?int $studentId = null)
    {
        $query = $this->model->where('class_schedule_id', $classId);

        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        return $query->get();
    }

    public function getCumulativeAttendanceData(int $classScheduleId): Collection
    {
        $classSchedule = ClassSchedule::findOrFail($classScheduleId);
        $students = $classSchedule->students()->with('user')->get();

        $result = collect();

        foreach ($students as $student) {
            // Get all attendance records for this student
            $attendances = $this->model->where('class_schedule_id', $classScheduleId)
                ->where('student_id', $student->id)
                ->get();

            // Calculate cumulative attendance hours
            $hoursPresent = $attendances->sum('hours_present');
            $hoursAbsent = $attendances->sum('hours_absent');
            $hoursPermitted = $attendances->sum('hours_permitted');
            $hoursSick = $attendances->sum('hours_sick');

            $result->push([
                'student' => $student,
                'hours_present' => $hoursPresent,
                'hours_absent' => $hoursAbsent,
                'hours_permitted' => $hoursPermitted,
                'hours_sick' => $hoursSick,
                'total_hours' => $hoursPresent + $hoursAbsent + $hoursPermitted + $hoursSick
            ]);
        }

        return $result;
    }
}
