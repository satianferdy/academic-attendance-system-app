<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScheduleTimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_schedule_id',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function classSchedule()
    {
        return $this->belongsTo(ClassSchedule::class);
    }

    // Add a scope to find overlapping time slots
    public function scopeOverlappingWith($query, $startTime, $endTime)
    {
        return $query->where(function($query) use ($startTime, $endTime) {
            // Start time is within an existing slot
            $query->where(function($q) use ($startTime, $endTime) {
                $q->where('start_time', '<=', $startTime)
                  ->where('end_time', '>', $startTime);
            })
            // End time is within an existing slot
            ->orWhere(function($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>=', $endTime);
            })
            // Selected time encloses an existing slot
            ->orWhere(function($q) use ($startTime, $endTime) {
                $q->where('start_time', '>=', $startTime)
                  ->where('end_time', '<=', $endTime);
            });
        });
    }
}
