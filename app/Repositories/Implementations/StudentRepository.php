<?php

namespace App\Repositories\Implementations;

use App\Models\Student;
use App\Models\ClassSchedule;
use App\Repositories\Interfaces\StudentRepositoryInterface;

class StudentRepository implements StudentRepositoryInterface
{

    protected $model;

    public function __construct(Student $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model->with('user')->get();
    }

    public function findById(int $id)
    {
        // return Student::find($id);
        return $this->model->find($id);
    }

    public function findByNim(string $nim)
    {
        // return Student::where('nim', $nim)->first();
        return $this->model->where('nim', $nim)->firstorFail();
    }

    public function updateFaceRegistered(int $id, bool $status)
    {
        // return Student::where('id', $id)->update(['face_registered' => $status]);
        return $this->model->where('id', $id)->update(['face_registered' => $status]);
    }

    public function isEnrolledInClass(int $studentId, int $classScheduleId): bool
    {
        $student = $this->findById($studentId);
        $classSchedule = ClassSchedule::find($classScheduleId);

        if (!$student || !$classSchedule) {
            return false;
        }

        return $student->classroom_id === $classSchedule->classroom_id;
    }
}
