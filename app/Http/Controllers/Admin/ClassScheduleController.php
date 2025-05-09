<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClassSchedule\StoreScheduleRequest;
use App\Http\Requests\ClassSchedule\UpdateScheduleRequest;
use App\Models\ClassSchedule;
use App\Models\Lecturer;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\Semester;
use App\Services\Interfaces\ScheduleServiceInterface;
use App\Repositories\Interfaces\ClassScheduleRepositoryInterface;
use Illuminate\Http\Request;

class ClassScheduleController extends Controller
{
    protected $scheduleService;
    protected $classScheduleRepository;

    public function __construct(
        ScheduleServiceInterface $scheduleService,
        ClassScheduleRepositoryInterface $classScheduleRepository
    ) {
        $this->scheduleService = $scheduleService;
        $this->classScheduleRepository = $classScheduleRepository;
    }

    public function index()
    {
        $this->authorize('viewAny', ClassSchedule::class);

        $schedules = $this->classScheduleRepository->getAllSchedules();
        return view('admin.schedules.index', compact('schedules'));
    }

    public function create()
    {
        $this->authorize('create', ClassSchedule::class);

        $courses = Course::all();
        $classrooms = ClassRoom::with('studyProgram')->get();
        $lecturers = Lecturer::with('user')->get();
        $days = $this->scheduleService->getWeekdays();
        $timeSlots = $this->scheduleService->generateTimeSlots();
        $semesters = Semester::orderBy('is_active', 'desc')->orderBy('start_date', 'desc')->get();

        return view('admin.schedules.create', compact('lecturers', 'days', 'timeSlots', 'classrooms', 'courses', 'semesters'));
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

        $schedule->load(['lecturer.user', 'course', 'classroom', 'timeSlots', 'semesters', 'studyProgram']);
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
        $classrooms = ClassRoom::with('studyProgram')->get();
        $courses = Course::all();
        $semesters = Semester::orderBy('is_active', 'desc')->orderBy('start_date', 'desc')->get();


        return view('admin.schedules.edit', compact('schedule', 'lecturers', 'days', 'timeSlots', 'selectedTimeSlots', 'classrooms', 'courses', 'semesters'));
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

        $this->classScheduleRepository->deleteSchedule($schedule->id);
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
