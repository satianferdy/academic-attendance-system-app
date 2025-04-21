<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudyProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'degree_level',
        'faculty',
        'description',
    ];

    public function classSchedules()
    {
        return $this->hasMany(ClassSchedule::class);
    }

    /**
     * Get the courses for this study program.
     */
    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    /**
     * Get the students enrolled in this study program.
     */
    public function students()
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Get the classrooms associated with this study program.
     */
    public function classrooms()
    {
        return $this->hasMany(ClassRoom::class);
    }
}
