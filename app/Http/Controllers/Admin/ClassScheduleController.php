<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSchedule;
use App\Models\Lecturer;
use App\Models\ClassRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClassScheduleController extends Controller
{
    public function index()
    {
        $schedules = ClassSchedule::with('lecturer.user')->paginate(10);
        return view('admin.schedules.index', compact('schedules'));
    }

    public function create()
    {
        $classrooms = ClassRoom::all();
        $lecturers = Lecturer::with('user')->get();
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $timeSlots = $this->generateTimeSlots();

        return view('admin.schedules.create', compact('lecturers', 'days', 'timeSlots', 'classrooms'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_code' => 'required|string|max:20',
            'course_name' => 'required|string|max:100',
            'lecturer_id' => 'required|exists:lecturers,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'room' => 'required|string|max:50',
            'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'time_slots' => 'required|array|min:1',
            'semester' => 'required|string|max:20',
            'academic_year' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if all selected time slots are available
        $allSlotsAvailable = true;
        $unavailableSlots = [];
        $conflictType = '';

        foreach ($request->time_slots as $timeSlot) {
            // Parse the selected time slot
            list($startTime, $endTime) = explode(' - ', $timeSlot);

            [$available, $conflict] = ClassSchedule::isTimeSlotAvailable(
                $request->room,
                $request->day,
                $startTime,
                $endTime,
                $request->lecturer_id
            );

            if (!$available) {
                $allSlotsAvailable = false;
                $unavailableSlots[] = $timeSlot;
                $conflictType = $conflict;
            }
        }

        // If any time slots are unavailable, return with errors
        if (!$allSlotsAvailable) {
            $errorMessage = 'The following time slots are already booked';
            if ($conflictType == 'room') {
                $errorMessage .= ' for this room';
            } else if ($conflictType == 'lecturer') {
                $errorMessage .= ' for this lecturer';
            }
            $errorMessage .= ': ' . implode(', ', $unavailableSlots);

            return redirect()->back()
                ->withErrors(['time_slots' => $errorMessage])
                ->withInput();
        }

        // Create a single class schedule
        $schedule = ClassSchedule::create([
            'course_code' => $request->course_code,
            'course_name' => $request->course_name,
            'lecturer_id' => $request->lecturer_id,
            'classroom_id' => $request->classroom_id,
            'room' => $request->room,
            'day' => $request->day,
            'semester' => $request->semester,
            'academic_year' => $request->academic_year,
        ]);

        // Create all the time slots for this schedule
        foreach ($request->time_slots as $timeSlot) {
            list($startTime, $endTime) = explode(' - ', $timeSlot);

            $schedule->timeSlots()->create([
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]);
        }

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Class schedule created successfully with ' . count($request->time_slots) . ' time slots.');
    }

    public function show(ClassSchedule $schedule)
    {
        $schedule->load('timeSlots');
        return view('admin.schedules.show', compact('schedule'));
    }

    public function edit(ClassSchedule $schedule)
    {
        $schedule->load('timeSlots');
        $lecturers = Lecturer::with('user')->get();
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $timeSlots = $this->generateTimeSlots();

        // Get the selected time slots
        $selectedTimeSlots = $schedule->timeSlots->map(function($slot) {
            return $slot->start_time->format('H:i') . ' - ' . $slot->end_time->format('H:i');
        })->toArray();
        $classrooms = ClassRoom::all();

        return view('admin.schedules.edit', compact('schedule', 'lecturers', 'days', 'timeSlots', 'selectedTimeSlots', 'classrooms'));
    }

    public function update(Request $request, ClassSchedule $schedule)
    {
        $validator = Validator::make($request->all(), [
            'course_code' => 'required|string|max:20',
            'course_name' => 'required|string|max:100',
            'lecturer_id' => 'required|exists:lecturers,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'room' => 'required|string|max:50',
            'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'time_slots' => 'required|array|min:1',
            'semester' => 'required|string|max:20',
            'academic_year' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if all selected time slots are available
        $allSlotsAvailable = true;
        $unavailableSlots = [];
        $conflictType = '';

        foreach ($request->time_slots as $timeSlot) {
            // Parse the selected time slot
            list($startTime, $endTime) = explode(' - ', $timeSlot);

            [$available, $conflict] = ClassSchedule::isTimeSlotAvailable(
                $request->room,
                $request->day,
                $startTime,
                $endTime,
                $request->lecturer_id,
                $schedule->id
            );

            if (!$available) {
                $allSlotsAvailable = false;
                $unavailableSlots[] = $timeSlot;
                $conflictType = $conflict;
            }
        }

        // If any time slots are unavailable, return with errors
        if (!$allSlotsAvailable) {
            $errorMessage = 'The following time slots are already booked';
            if ($conflictType == 'room') {
                $errorMessage .= ' for this room';
            } else if ($conflictType == 'lecturer') {
                $errorMessage .= ' for this lecturer';
            }
            $errorMessage .= ': ' . implode(', ', $unavailableSlots);

            return redirect()->back()
                ->withErrors(['time_slots' => $errorMessage])
                ->withInput();
        }

        // Update the schedule
        $schedule->update([
            'course_code' => $request->course_code,
            'course_name' => $request->course_name,
            'lecturer_id' => $request->lecturer_id,
            'classroom_id' => $request->classroom_id,
            'room' => $request->room,
            'day' => $request->day,
            'semester' => $request->semester,
            'academic_year' => $request->academic_year,
        ]);

        // Delete all existing time slots
        $schedule->timeSlots()->delete();

        // Create new time slots
        foreach ($request->time_slots as $timeSlot) {
            list($startTime, $endTime) = explode(' - ', $timeSlot);

            $schedule->timeSlots()->create([
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]);
        }

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Class schedule updated successfully.');
    }

    public function destroy(ClassSchedule $schedule)
    {
        $schedule->delete();
        return redirect()->route('admin.schedules.index')
            ->with('success', 'Class schedule deleted successfully.');
    }

    public function checkAvailability(Request $request)
    {
        $room = $request->room;
        $day = $request->day;
        $lecturer_id = $request->lecturer_id;
        $excludeId = $request->schedule_id;

        $bookedSlots = [];

        // Check room availability
        if ($room && $day) {
            $roomSchedules = ClassSchedule::where('room', $room)
                ->where('day', $day);

            if ($excludeId) {
                $roomSchedules->where('id', '!=', $excludeId);
            }

            $roomSchedules = $roomSchedules->with(['timeSlots', 'lecturer.user'])->get();

            foreach ($roomSchedules as $schedule) {
                $lecturerName = $schedule->lecturer ? ($schedule->lecturer->user ? $schedule->lecturer->user->name : 'Unknown') : 'Unknown';

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
            $lecturerSchedules = ClassSchedule::where('lecturer_id', $lecturer_id)
                ->where('day', $day);

            if ($excludeId) {
                $lecturerSchedules->where('id', '!=', $excludeId);
            }

            $lecturerSchedules = $lecturerSchedules->with(['timeSlots'])->get();

            foreach ($lecturerSchedules as $schedule) {
                $lecturerName = $schedule->lecturer ? ($schedule->lecturer->user ? $schedule->lecturer->user->name : 'Unknown') : 'Unknown';

                foreach ($schedule->timeSlots as $timeSlot) {
                    // Check if this slot isn't already in booked slots (to avoid duplicates)
                    $exists = false;
                    foreach ($bookedSlots as $slot) {
                        if ($slot['start_time'] === $timeSlot->start_time->format('H:i') &&
                            $slot['end_time'] === $timeSlot->end_time->format('H:i')) {
                            $exists = true;
                            break;
                        }
                    }

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

        return response()->json(['bookedSlots' => $bookedSlots]);
    }

    private function generateTimeSlots()
    {
        $slots = [];

        // Generate slots from 07:00 to 16:00
        $startHour = 7;
        $endHour = 16;

        for ($hour = $startHour; $hour < $endHour; $hour++) {
            $start = sprintf('%02d:00', $hour);
            $end = sprintf('%02d:00', $hour + 1);
            $slots[] = $start . ' - ' . $end;
        }

        return $slots;
    }
}
