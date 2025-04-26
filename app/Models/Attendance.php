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
        'edit_notes',
        'hours_present',
        'hours_absent',
        'hours_permitted',
        'hours_sick',
        'qr_token',
        'attendance_time',
        'last_edited_by',
        'last_edited_at',
    ];

    protected $casts = [
        'date' => 'date',
        'attendance_time' => 'datetime',
        'last_edited_at' => 'datetime',
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
