<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_schedule_id',
        'student_id',
        'date',
        'status',
        'remarks',
        'qr_token',
        'attendance_time',
    ];

    protected $casts = [
        'date' => 'date',
        'attendance_time' => 'datetime',
    ];

    public function classSchedule()
    {
        return $this->belongsTo(ClassSchedule::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
