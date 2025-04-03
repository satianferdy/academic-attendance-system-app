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
        'code'
    ];

    public function classSchedules()
    {
        return $this->hasMany(ClassSchedule::class);
    }
}
