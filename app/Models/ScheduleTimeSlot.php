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
}
