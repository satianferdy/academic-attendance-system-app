<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClassSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_code',
        'course_name',
        'lecturer_id',
        'room',
        'day',
        'start_time',
        'end_time',
        'semester',
        'academic_year',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    // public function sessions()
    // {
    //     return $this->hasMany(Session::class);
    // }
}
