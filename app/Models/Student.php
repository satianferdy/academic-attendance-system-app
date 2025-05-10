<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'classroom_id',
        'study_program_id',
        'nim',
        'face_registered',
    ];

    protected $casts = [
        'face_registered' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function classroom()
    {
        return $this->belongsTo(ClassRoom::class);
    }

    public function studyProgram()
    {
        return $this->belongsTo(StudyProgram::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function faceData()
    {
        return $this->hasOne(FaceData::class, 'student_id');
    }

    public function faceUpdateRequests()
    {
        return $this->hasMany(FaceUpdateRequest::class);
    }

    /**
     * Check if student is enrolled in a class schedule
     *
     * @param int $classScheduleId
     * @return bool
     */
    public function isEnrolledInClass($classScheduleId)
    {
        $classSchedule = ClassSchedule::find($classScheduleId);

        if (!$classSchedule) {
            return false;
        }

        // Check if student belongs to the classroom assigned to this schedule
        return $this->classroom_id === $classSchedule->classroom_id;
    }
}
