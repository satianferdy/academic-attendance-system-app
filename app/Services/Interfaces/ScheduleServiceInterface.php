<?php

namespace App\Services\Interfaces;

use App\Models\ClassSchedule;

interface ScheduleServiceInterface
{

    public function getWeekdays();
    public function generateTimeSlots();
    public function parseTimeSlot($timeSlot);
    public function checkAllTimeSlotsAvailability($room, $day, $timeSlots, $lecturer_id, $excludeId = null);
    public function createScheduleWithTimeSlots($data);
    public function updateScheduleWithTimeSlots(ClassSchedule $schedule, $data);
    public function getBookedTimeSlots($room, $day, $lecturer_id = null, $excludeId = null);
}
