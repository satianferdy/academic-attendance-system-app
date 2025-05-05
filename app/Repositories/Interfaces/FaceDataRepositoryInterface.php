<?php

namespace App\Repositories\Interfaces;

interface FaceDataRepositoryInterface
{
    public function findByStudentId(int $studentId);
    public function createOrUpdate(int $studentId, array $data);
}
