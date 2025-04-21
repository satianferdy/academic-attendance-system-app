<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClassRoom extends Model
{
    use HasFactory;

    protected $table = 'classrooms';

    protected $fillable = [
        'name',
        'study_program_id',  // Add study_program_id
        'capacity',
        'semester_id',       // Add semester_id for specific semester classes     // Add academic year for filtering
    ];

    public function students()
    {
        return $this->hasMany(Student::class, 'classroom_id');
    }

    public function schedules()
    {
        return $this->hasMany(ClassSchedule::class);
    }

    public function studyProgram()
    {
        return $this->belongsTo(StudyProgram::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
}
