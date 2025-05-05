<?php

namespace App\Repositories\Implementations;

use App\Models\FaceData;
use App\Repositories\Interfaces\FaceDataRepositoryInterface;

class FaceDataRepository implements FaceDataRepositoryInterface
{
    protected $model;

    public function __construct(FaceData $model)
    {
        $this->model = $model;
    }

    public function findByStudentId(int $studentId)
    {
        return $this->model->where('student_id', $studentId)->first();
    }

    public function createOrUpdate(int $studentId, array $data)
    {
        return $this->model->updateOrCreate(
            ['student_id' => $studentId],
            $data
        );
    }
}
