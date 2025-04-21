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
        'semester_id',        // Add semester_id
        'study_program_id',   // Add study_program_id
        'room',
        'day',
        'semester',           // This will be deprecated in favor of semester_id
        'total_weeks',
        'meetings_per_week',
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

    public function semesters()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function studyProgram()
    {
        return $this->belongsTo(StudyProgram::class);
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

    public function scopeCurrentSemester($query)
    {
        if ($activeSemester = Semester::where('is_active', true)->first()) {
            return $query->where('semester_id', $activeSemester->id);
        }
        return $query;
    }
}
