<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use App\Models\Student;
use App\Models\Lecturer;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    public function getAllUsers(): Collection;
    public function findUserById(int $id): ?User;
    public function createUser(array $userData): User;
    public function createStudent(array $studentData): Student;
    public function createLecturer(array $lecturerData): Lecturer;
    public function updateUser(User $user, array $userData): bool;
    public function updateStudent(Student $student, array $studentData): bool;
    public function updateLecturer(Lecturer $lecturer, array $lecturerData): bool;
    public function deleteUser(User $user): ?bool;
}
