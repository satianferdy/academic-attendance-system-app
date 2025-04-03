<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ClassSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'lecturer_id',
        'classroom_id',
        'room',
        'day',
        'semester',
        'academic_year',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class);
    }

    public function classroom()
    {
        return $this->belongsTo(ClassRoom::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function timeSlots()
    {
        return $this->hasMany(ScheduleTimeSlot::class);
    }

    public function students()
    {
        return $this->classroom ? $this->classroom->students() : $this->newCollection();
    }

    // Scope methods for common queries
    public function scopeOnDay($query, $day)
    {
        return $query->where('day', $day);
    }

    public function scopeByRoom($query, $room)
    {
        return $query->where('room', $room);
    }

    public function scopeByLecturer($query, $lecturer_id)
    {
        return $query->where('lecturer_id', $lecturer_id);
    }

    public function scopeExclude($query, $excludeId)
    {
        return $excludeId ? $query->where('id', '!=', $excludeId) : $query;
    }
}
