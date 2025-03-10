<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    // Check if a time slot is available for the specific room and day
    public static function isTimeSlotAvailable($room, $day, $startTime, $endTime, $excludeId = null)
    {
        $query = self::where('room', $room)
            ->where('day', $day)
            ->where(function ($query) use ($startTime, $endTime) {
                // Check for overlapping time slots
                $query->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '>=', $startTime)
                      ->where('start_time', '<', $endTime);
                })->orWhere(function ($q) use ($startTime, $endTime) {
                    $q->where('end_time', '>', $startTime)
                      ->where('end_time', '<=', $endTime);
                })->orWhere(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<=', $startTime)
                      ->where('end_time', '>=', $endTime);
                });
            });

        // Exclude current schedule when updating
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        // If any record found, time slot is not available
        return $query->count() === 0;
    }
}
