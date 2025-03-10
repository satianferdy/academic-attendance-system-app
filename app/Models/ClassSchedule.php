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

    // Add the relationship to time slots
    public function timeSlots()
    {
        return $this->hasMany(ScheduleTimeSlot::class);
    }

     // Static method to check for time slot availability
     public static function isTimeSlotAvailable($room, $day, $startTime, $endTime, $excludeId = null)
     {
         // Get all schedules for this room and day
         $schedules = self::where('room', $room)
             ->where('day', $day);

         // Exclude current schedule when updating
         if ($excludeId) {
             $schedules->where('id', '!=', $excludeId);
         }

         $schedules = $schedules->with('timeSlots')->get();

         // Check each schedule's time slots for conflicts
         foreach ($schedules as $schedule) {
             foreach ($schedule->timeSlots as $timeSlot) {
                 // Check for overlapping time slots
                 if (
                     // Start time is within an existing slot
                     ($startTime >= $timeSlot->start_time && $startTime < $timeSlot->end_time) ||
                     // End time is within an existing slot
                     ($endTime > $timeSlot->start_time && $endTime <= $timeSlot->end_time) ||
                     // Selected time encloses an existing slot
                     ($startTime <= $timeSlot->start_time && $endTime >= $timeSlot->end_time)
                 ) {
                     return false; // Time slot is not available
                 }
             }
         }

         return true; // Time slot is available
     }
}
