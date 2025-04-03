<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ClassSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'lecturer_id',
        'classroom_id',
        'room',
        'day',
        'semester',
        'academic_year',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class);
    }

    public function classroom()
    {
        return $this->belongsTo(ClassRoom::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function timeSlots()
    {
        return $this->hasMany(ScheduleTimeSlot::class);
    }

    public function students()
    {
        return $this->classroom ? $this->classroom->students() : $this->newCollection();
    }

    // Scope methods for common queries
    public function scopeOnDay($query, $day)
    {
        return $query->where('day', $day);
    }

    public function scopeByRoom($query, $room)
    {
        return $query->where('room', $room);
    }

    public function scopeByLecturer($query, $lecturer_id)
    {
        return $query->where('lecturer_id', $lecturer_id);
    }

    public function scopeExclude($query, $excludeId)
    {
        return $excludeId ? $query->where('id', '!=', $excludeId) : $query;
    }

    // Static method to check for time slot availability
    public static function checkTimeOverlap($start1, $end1, $start2, $end2)
    {
        $start1 = Carbon::createFromFormat('H:i', $start1);
        $end1 = Carbon::createFromFormat('H:i', $end1);
        $start2 = Carbon::createFromFormat('H:i', $start2);
        $end2 = Carbon::createFromFormat('H:i', $end2);

        return
            // Start time is within an existing slot
            ($start1->gte($start2) && $start1->lt($end2)) ||
            // End time is within an existing slot
            ($end1->gt($start2) && $end1->lte($end2)) ||
            // Selected time encloses an existing slot
            ($start1->lte($start2) && $end1->gte($end2));
    }

    public static function findConflictingTimeSlots($room, $day, $startTime, $endTime, $lecturer_id = null, $excludeId = null)
    {
        $conflicts = [
            'room' => [],
            'lecturer' => []
        ];

        // Check room conflicts
        $roomSchedules = self::byRoom($room)
            ->onDay($day)
            ->exclude($excludeId)
            ->with(['timeSlots', 'lecturer.user'])
            ->get();

        foreach ($roomSchedules as $schedule) {
            foreach ($schedule->timeSlots as $timeSlot) {
                if (self::checkTimeOverlap(
                    $startTime,
                    $endTime,
                    $timeSlot->start_time->format('H:i'),
                    $timeSlot->end_time->format('H:i')
                )) {
                    $conflicts['room'][] = [
                        'slot' => $timeSlot->start_time->format('H:i') . ' - ' . $timeSlot->end_time->format('H:i'),
                        'schedule' => $schedule
                    ];
                }
            }
        }

        // Check lecturer conflicts
        if ($lecturer_id) {
            $lecturerSchedules = self::byLecturer($lecturer_id)
                ->onDay($day)
                ->exclude($excludeId)
                ->with(['timeSlots', 'lecturer.user'])
                ->get();

            foreach ($lecturerSchedules as $schedule) {
                foreach ($schedule->timeSlots as $timeSlot) {
                    if (self::checkTimeOverlap(
                        $startTime,
                        $endTime,
                        $timeSlot->start_time->format('H:i'),
                        $timeSlot->end_time->format('H:i')
                    )) {
                        $conflicts['lecturer'][] = [
                            'slot' => $timeSlot->start_time->format('H:i') . ' - ' . $timeSlot->end_time->format('H:i'),
                            'schedule' => $schedule
                        ];
                    }
                }
            }
        }

        return $conflicts;
    }
}
