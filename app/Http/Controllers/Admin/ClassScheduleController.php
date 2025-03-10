<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSchedule;
use App\Models\Lecturer;
use Carbon\Carbon;
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
        $lecturers = Lecturer::with('user')->get();
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $timeSlots = $this->generateTimeSlots();

        return view('admin.schedules.create', compact('lecturers', 'days', 'timeSlots'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_code' => 'required|string|max:20',
            'course_name' => 'required|string|max:100',
            'lecturer_id' => 'required|exists:lecturers,id',
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

        $createdSchedules = [];
        $errors = [];

        // Process each selected time slot
        foreach ($request->time_slots as $timeSlot) {
            // Parse the selected time slot
            list($startTime, $endTime) = explode(' - ', $timeSlot);

            // Check if the time slot is available
            if (!ClassSchedule::isTimeSlotAvailable(
                $request->room,
                $request->day,
                $startTime,
                $endTime
            )) {
                $errors[] = "Time slot $timeSlot is already booked for the selected room and day.";
                continue;
            }

            // Create the schedule
            $schedule = ClassSchedule::create([
                'course_code' => $request->course_code,
                'course_name' => $request->course_name,
                'lecturer_id' => $request->lecturer_id,
                'room' => $request->room,
                'day' => $request->day,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'semester' => $request->semester,
                'academic_year' => $request->academic_year,
            ]);

            $createdSchedules[] = $schedule;
        }

        // If there were errors but some schedules were created
        if (!empty($errors) && !empty($createdSchedules)) {
            return redirect()->route('admin.schedules.index')
                ->with('warning', 'Some schedules were created, but the following errors occurred: ' . implode(' ', $errors));
        }

        // If all schedules failed
        if (empty($createdSchedules)) {
            return redirect()->back()
                ->withErrors(['time_slots' => $errors])
                ->withInput();
        }

        // If all schedules were created successfully
        return redirect()->route('admin.schedules.index')
            ->with('success', count($createdSchedules) . ' class schedule(s) created successfully.');
    }

    public function show(ClassSchedule $schedule)
    {
        return view('admin.schedules.show', compact('schedule'));
    }

    public function edit(ClassSchedule $schedule)
    {
        $lecturers = Lecturer::with('user')->get();
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $timeSlots = $this->generateTimeSlots();
        $selectedTimeSlot = $schedule->start_time->format('H:i') . ' - ' . $schedule->end_time->format('H:i');

        return view('admin.schedules.edit', compact('schedule', 'lecturers', 'days', 'timeSlots', 'selectedTimeSlot'));
    }

    public function update(Request $request, ClassSchedule $schedule)
    {
        $validator = Validator::make($request->all(), [
            'course_code' => 'required|string|max:20',
            'course_name' => 'required|string|max:100',
            'lecturer_id' => 'required|exists:lecturers,id',
            'room' => 'required|string|max:50',
            'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'time_slot' => 'required|string',
            'semester' => 'required|string|max:20',
            'academic_year' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Parse the selected time slot
        list($startTime, $endTime) = explode(' - ', $request->time_slot);

        // Check if the time slot is available (excluding the current schedule)
        if (!ClassSchedule::isTimeSlotAvailable(
            $request->room,
            $request->day,
            $startTime,
            $endTime,
            $schedule->id
        )) {
            return redirect()->back()
                ->withErrors(['time_slot' => 'This time slot is already booked for the selected room and day.'])
                ->withInput();
        }

        $schedule->update([
            'course_code' => $request->course_code,
            'course_name' => $request->course_name,
            'lecturer_id' => $request->lecturer_id,
            'room' => $request->room,
            'day' => $request->day,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'semester' => $request->semester,
            'academic_year' => $request->academic_year,
        ]);

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
        $excludeId = $request->schedule_id;

        $bookedSlots = ClassSchedule::where('room', $room)
            ->where('day', $day);

        if ($excludeId) {
            $bookedSlots->where('id', '!=', $excludeId);
        }

        $bookedSlots = $bookedSlots->get(['start_time', 'end_time', 'lecturer_id']);

        // Get lecturer names for displaying booked slots
        $bookedSlotsWithLecturer = $bookedSlots->map(function($slot) {
            $lecturer = Lecturer::find($slot->lecturer_id);
            $lecturerName = $lecturer ? ($lecturer->user ? $lecturer->user->name : 'Unknown') : 'Unknown';

            return [
                'start_time' => $slot->start_time->format('H:i'),
                'end_time' => $slot->end_time->format('H:i'),
                'lecturer_name' => $lecturerName
            ];
        });

        return response()->json(['bookedSlots' => $bookedSlotsWithLecturer]);
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
