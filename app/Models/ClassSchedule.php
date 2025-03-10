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
    public static function isTimeSlotAvailable($room, $day, $startTime, $endTime, $lecturer_id = null, $excludeId = null)
    {
        // First check for room conflicts
        $roomConflicts = self::where('room', $room)
            ->where('day', $day);

        if ($excludeId) {
            $roomConflicts->where('id', '!=', $excludeId);
        }

        $roomConflicts = $roomConflicts->with('timeSlots')->get();

        foreach ($roomConflicts as $schedule) {
            foreach ($schedule->timeSlots as $timeSlot) {
                // Check for overlapping time slots
                if (
                    // Start time is within an existing slot
                    ($startTime >= $timeSlot->start_time->format('H:i') && $startTime < $timeSlot->end_time->format('H:i')) ||
                    // End time is within an existing slot
                    ($endTime > $timeSlot->start_time->format('H:i') && $endTime <= $timeSlot->end_time->format('H:i')) ||
                    // Selected time encloses an existing slot
                    ($startTime <= $timeSlot->start_time->format('H:i') && $endTime >= $timeSlot->end_time->format('H:i'))
                ) {
                    return [false, 'room']; // Room conflict
                }
            }
        }

        // If lecturer_id is provided, check for lecturer conflicts
        if ($lecturer_id) {
            $lecturerConflicts = self::where('lecturer_id', $lecturer_id)
                ->where('day', $day);

            if ($excludeId) {
                $lecturerConflicts->where('id', '!=', $excludeId);
            }

            $lecturerConflicts = $lecturerConflicts->with('timeSlots')->get();

            foreach ($lecturerConflicts as $schedule) {
                foreach ($schedule->timeSlots as $timeSlot) {
                    // Check for overlapping time slots
                    if (
                        // Start time is within an existing slot
                        ($startTime >= $timeSlot->start_time->format('H:i') && $startTime < $timeSlot->end_time->format('H:i')) ||
                        // End time is within an existing slot
                        ($endTime > $timeSlot->start_time->format('H:i') && $endTime <= $timeSlot->end_time->format('H:i')) ||
                        // Selected time encloses an existing slot
                        ($startTime <= $timeSlot->start_time->format('H:i') && $endTime >= $timeSlot->end_time->format('H:i'))
                    ) {
                        return [false, 'lecturer']; // Lecturer conflict
                    }
                }
            }
        }

        return [true, null]; // Time slot is available
    }
}
