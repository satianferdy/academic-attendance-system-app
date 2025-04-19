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
        'department',
        'faculty',
        'capacity',
    ];

    public function students()
    {
        return $this->hasMany(Student::class, 'classroom_id');
    }

    public function schedules()
    {
        return $this->hasMany(ClassSchedule::class);
    }
}
