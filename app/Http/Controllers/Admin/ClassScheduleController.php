<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClassSchedule\StoreScheduleRequest;
use App\Http\Requests\ClassSchedule\UpdateScheduleRequest;
use App\Models\ClassSchedule;
use App\Models\Lecturer;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Services\Interfaces\ScheduleServiceInterface;
use Illuminate\Http\Request;

class ClassScheduleController extends Controller
{
    protected $scheduleService;

    public function __construct(
        ScheduleServiceInterface $scheduleService
    ) {
        $this->scheduleService = $scheduleService;
    }

    public function index()
    {
        $this->authorize('viewAny', ClassSchedule::class);

        $schedules = ClassSchedule::with(['lecturer.user', 'course', 'classroom'])->paginate(10);
        return view('admin.schedules.index', compact('schedules'));
    }

    public function create()
    {
        $this->authorize('create', ClassSchedule::class);

        $courses = Course::all();
        $classrooms = ClassRoom::all();
        $lecturers = Lecturer::with('user')->get();
        $days = $this->scheduleService->getWeekdays();
        $timeSlots = $this->scheduleService->generateTimeSlots();

        return view('admin.schedules.create', compact('lecturers', 'days', 'timeSlots', 'classrooms', 'courses'));
    }

    public function store(StoreScheduleRequest $request)
    {
        $this->authorize('create', ClassSchedule::class);

        $validated = $request->validated();

        // Check availability of all time slots
        $availabilityCheck = $this->scheduleService->checkAllTimeSlotsAvailability(
            $validated['room'],
            $validated['day'],
            $validated['time_slots'],
            $validated['lecturer_id']
        );

        if (!$availabilityCheck['available']) {
            return redirect()->back()
                ->withErrors(['time_slots' => $availabilityCheck['message']])
                ->withInput();
        }

        // Create schedule and time slots
        $schedule = $this->scheduleService->createScheduleWithTimeSlots($validated);

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Class schedule created successfully with ' . count($validated['time_slots']) . ' time slots.');
    }

    public function show(ClassSchedule $schedule)
    {
        $this->authorize('view', $schedule);

        $schedule->load(['timeSlots', 'lecturer.user', 'course', 'classroom']);
        return view('admin.schedules.show', compact('schedule'));
    }

    public function edit(ClassSchedule $schedule)
    {
        $this->authorize('update', $schedule);

        $schedule->load('timeSlots');
        $lecturers = Lecturer::with('user')->get();
        $days = $this->scheduleService->getWeekdays();
        $timeSlots = $this->scheduleService->generateTimeSlots();
        $selectedTimeSlots = $schedule->timeSlots->map(function($slot) {
            return $slot->start_time->format('H:i') . ' - ' . $slot->end_time->format('H:i');
        })->toArray();
        $classrooms = ClassRoom::all();
        $courses = Course::all();

        return view('admin.schedules.edit', compact('schedule', 'lecturers', 'days', 'timeSlots', 'selectedTimeSlots', 'classrooms', 'courses'));
    }

    public function update(UpdateScheduleRequest $request, ClassSchedule $schedule)
    {
        $this->authorize('update', $schedule);

        $validated = $request->validated();

        // Check availability of all time slots
        $availabilityCheck = $this->scheduleService->checkAllTimeSlotsAvailability(
            $validated['room'],
            $validated['day'],
            $validated['time_slots'],
            $validated['lecturer_id'],
            $schedule->id
        );

        if (!$availabilityCheck['available']) {
            return redirect()->back()
                ->withErrors(['time_slots' => $availabilityCheck['message']])
                ->withInput();
        }

        // Update schedule and time slots
        $this->scheduleService->updateScheduleWithTimeSlots($schedule, $validated);

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Class schedule updated successfully.');
    }

    public function destroy(ClassSchedule $schedule)
    {
        $this->authorize('delete', $schedule);

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

        $bookedSlots = $this->scheduleService->getBookedTimeSlots($room, $day, $lecturer_id, $excludeId);

        return response()->json(['bookedSlots' => $bookedSlots]);
    }
}
