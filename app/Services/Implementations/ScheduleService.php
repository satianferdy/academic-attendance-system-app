<?php

namespace App\Services\Implementations;

use App\Models\ClassSchedule;
use Illuminate\Support\Facades\DB;
use App\Services\Interfaces\ScheduleServiceInterface;
use App\Repositories\Interfaces\ClassScheduleRepositoryInterface;

class ScheduleService implements ScheduleServiceInterface
{
    protected $classScheduleRepository;

    public function __construct(ClassScheduleRepositoryInterface $classScheduleRepository)
    {
        $this->classScheduleRepository = $classScheduleRepository;
    }

    public function getWeekdays()
    {
        return ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
    }

    public function generateTimeSlots()
    {
        $slots = [];
        $startHour = 7;
        $endHour = 16;

        for ($hour = $startHour; $hour < $endHour; $hour++) {
            $start = sprintf('%02d:00', $hour);
            $end = sprintf('%02d:00', $hour + 1);
            $slots[] = $start . ' - ' . $end;
        }

        return $slots;
    }

    public function parseTimeSlot($timeSlot)
    {
        list($startTime, $endTime) = explode(' - ', $timeSlot);
        return [$startTime, $endTime];
    }

    public function checkAllTimeSlotsAvailability($room, $day, $timeSlots, $lecturer_id, $excludeId = null)
    {
        $unavailableSlots = [];
        $conflictType = '';

        foreach ($timeSlots as $timeSlot) {
            list($startTime, $endTime) = $this->parseTimeSlot($timeSlot);

            $conflicts = $this->classScheduleRepository->findConflictingTimeSlots(
                $room,
                $day,
                $startTime,
                $endTime,
                $lecturer_id,
                $excludeId
            );

            if (!empty($conflicts['room'])) {
                $unavailableSlots[] = $timeSlot;
                $conflictType = 'room';
            } elseif (!empty($conflicts['lecturer'])) {
                $unavailableSlots[] = $timeSlot;
                $conflictType = 'lecturer';
            }
        }

        if (!empty($unavailableSlots)) {
            $errorMessage = 'The following time slots are already booked';
            if ($conflictType == 'room') {
                $errorMessage .= ' for this room';
            } else if ($conflictType == 'lecturer') {
                $errorMessage .= ' for this lecturer';
            }
            $errorMessage .= ': ' . implode(', ', $unavailableSlots);

            return [
                'available' => false,
                'message' => $errorMessage,
                'unavailableSlots' => $unavailableSlots,
                'conflictType' => $conflictType
            ];
        }

        return ['available' => true];
    }

    public function createScheduleWithTimeSlots($data)
    {
        return DB::transaction(function () use ($data) {
            // Create schedule using repository
            $schedule = $this->classScheduleRepository->createSchedule($data);

            // Create time slots
            $this->createTimeSlots($schedule, $data['time_slots']);

            return $schedule;
        });
    }

    public function updateScheduleWithTimeSlots(ClassSchedule $schedule, $data)
    {
        return DB::transaction(function () use ($schedule, $data) {
            // Update schedule using repository
            $this->classScheduleRepository->updateSchedule($schedule->id, $data);

            // Delete existing time slots
            $schedule->timeSlots()->delete();

            // Create new time slots
            $this->createTimeSlots($schedule, $data['time_slots']);

            return $schedule;
        });
    }

    private function createTimeSlots(ClassSchedule $schedule, $timeSlots)
    {
        foreach ($timeSlots as $timeSlot) {
            list($startTime, $endTime) = $this->parseTimeSlot($timeSlot);

            $schedule->timeSlots()->create([
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]);
        }
    }

    public function getBookedTimeSlots($room, $day, $lecturer_id = null, $excludeId = null)
    {
        $bookedSlots = [];

        // Check room availability
        if ($room && $day) {
            $roomSchedules = $this->classScheduleRepository->getSchedulesByRoomAndDay($room, $day, $excludeId);

            foreach ($roomSchedules as $schedule) {
                $lecturerName = $schedule->lecturer ?
                    ($schedule->lecturer->user ? $schedule->lecturer->user->name : 'Unknown') :
                    'Unknown';

                foreach ($schedule->timeSlots as $timeSlot) {
                    $bookedSlots[] = [
                        'start_time' => $timeSlot->start_time->format('H:i'),
                        'end_time' => $timeSlot->end_time->format('H:i'),
                        'lecturer_name' => $lecturerName,
                        'type' => 'room'
                    ];
                }
            }
        }

        // Check lecturer availability
        if ($lecturer_id && $day) {
            $lecturerSchedules = $this->classScheduleRepository->getSchedulesByLecturerAndDay($lecturer_id, $day, $excludeId);

            foreach ($lecturerSchedules as $schedule) {
                $lecturerName = $schedule->lecturer ?
                    ($schedule->lecturer->user ? $schedule->lecturer->user->name : 'Unknown') :
                    'Unknown';

                foreach ($schedule->timeSlots as $timeSlot) {
                    // Check if this slot isn't already in booked slots (to avoid duplicates)
                    $exists = $this->slotExistsInArray(
                        $bookedSlots,
                        $timeSlot->start_time->format('H:i'),
                        $timeSlot->end_time->format('H:i')
                    );

                    if (!$exists) {
                        $bookedSlots[] = [
                            'start_time' => $timeSlot->start_time->format('H:i'),
                            'end_time' => $timeSlot->end_time->format('H:i'),
                            'lecturer_name' => $lecturerName,
                            'type' => 'lecturer',
                            'room' => $schedule->room
                        ];
                    }
                }
            }
        }

        return $bookedSlots;
    }

    private function slotExistsInArray($slots, $startTime, $endTime)
    {
        foreach ($slots as $slot) {
            if ($slot['start_time'] === $startTime && $slot['end_time'] === $endTime) {
                return true;
            }
        }
        return false;
    }
}
