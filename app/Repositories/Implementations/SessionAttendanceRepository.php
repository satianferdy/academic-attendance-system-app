<?php

namespace App\Repositories\Implementations;

use App\Models\SessionAttendance;
use App\Repositories\Interfaces\SessionAttendanceRepositoryInterface;

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
            ->where('session_date', $date)
            ->where('is_active', true)
            ->first();
    }

    public function createOrUpdate(array $attributes, array $values)
    {
        return $this->model->firstOrCreate($attributes, $values);
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
            ->where('session_date', $date)
            ->first();
    }

    public function deactivateSession(int $sessionId)
    {
        return $this->model->where('id', $sessionId)
            ->update(['is_active' => false]);
    }
}
