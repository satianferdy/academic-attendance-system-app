<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SessionAttendance extends Model
{
    use HasFactory;
    protected $fillable = [
        'class_schedule_id',
        'session_date',
        'start_time',
        'end_time',
        'qr_code',
        'is_active'
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
