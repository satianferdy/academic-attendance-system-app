<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'academic_year',
        'term',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the class schedules for this semester.
     */
    public function classSchedules()
    {
        return $this->hasMany(ClassSchedule::class);
    }

    public function classrooms()
    {
        return $this->hasMany(ClassRoom::class);
    }

    /**
     * Set the active semester and deactivate all others.
     */
    public static function setActive($id)
    {
        self::where('id', '!=', $id)->update(['is_active' => false]);
        return self::where('id', $id)->update(['is_active' => true]);
    }

    /**
     * Get the currently active semester.
     */
    public static function getActive()
    {
        return self::where('is_active', true)->first();
    }
}
