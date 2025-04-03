<?php

namespace App\Repositories\Interfaces;

interface StudentRepositoryInterface
{
    public function getAll();
    public function findById(int $id);
    public function findByNim(string $nim);
    public function updateFaceRegistered(int $id, bool $status);
    public function isEnrolledInClass(int $studentId, int $classScheduleId): bool;
}
