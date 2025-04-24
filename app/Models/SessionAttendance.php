<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SessionAttendance extends Model
{
    use HasFactory;

    protected $table = 'session_attendance';

    protected $fillable = [
        'class_schedule_id',
        'session_date',
        'week',
        'meetings',
        'start_time',
        'end_time',
        'total_hours',
        'tolerance_minutes',
        'qr_code',
        'is_active'
    ];

    protected $casts = [
        'session_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_active' => 'boolean'
    ];

    public function classSchedule()
    {
        return $this->belongsTo(ClassSchedule::class);
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }
}
