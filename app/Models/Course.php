<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Course extends Model
{
    use hasFactory;

    protected $table = 'courses';

    protected $fillable = [
        'name',
        'code',
        'study_program_id',
        'credits',          // Add course credits (SKS)
        'description',
    ];

    public function classSchedules()
    {
        return $this->hasMany(ClassSchedule::class);
    }

    public function studyProgram()
    {
        return $this->belongsTo(StudyProgram::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
