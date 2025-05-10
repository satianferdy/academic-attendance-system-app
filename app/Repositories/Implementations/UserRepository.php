<?php

namespace App\Repositories\Implementations;

use App\Models\User;
use App\Models\Student;
use App\Models\Lecturer;
use Illuminate\Support\Collection;
use App\Repositories\Interfaces\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    protected $userModel;
    protected $studentModel;
    protected $lecturerModel;

    public function __construct(User $userModel, Student $studentModel, Lecturer $lecturerModel)
    {
        $this->userModel = $userModel;
        $this->studentModel = $studentModel;
        $this->lecturerModel = $lecturerModel;
    }

    public function getAllUsers(): Collection
    {
        return $this->userModel->with(['student', 'lecturer'])->get();
    }

    public function findUserById(int $id): ?User
    {
        return $this->userModel->with(['student', 'lecturer'])->find($id);
    }

    public function createUser(array $userData): User
    {
        return $this->userModel->create($userData);
    }

    public function createStudent(array $studentData): Student
    {
        return $this->studentModel->create($studentData);
    }

    public function createLecturer(array $lecturerData): Lecturer
    {
        return $this->lecturerModel->create($lecturerData);
    }

    public function updateUser(User $user, array $userData): bool
    {
        return $user->update($userData);
    }

    public function updateStudent(Student $student, array $studentData): bool
    {
        return $student->update($studentData);
    }

    public function updateLecturer(Lecturer $lecturer, array $lecturerData): bool
    {
        return $lecturer->update($lecturerData);
    }

    public function deleteUser(User $user): ?bool
    {
        return $user->delete();
    }
}
