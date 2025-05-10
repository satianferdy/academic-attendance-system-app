<?php

namespace App\Http\Controllers\Lecturer;

use App\Models\Student;
use App\Models\Attendance;
use App\Models\StudyProgram;
use Illuminate\Http\Request;
use App\Models\ClassSchedule;
use App\Models\SessionAttendance;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Interfaces\AttendanceRepositoryInterface;
use App\Repositories\Interfaces\SessionAttendanceRepositoryInterface;

class LecturerAttendanceDataController extends Controller
{
    protected $attendanceRepository;
    protected $sessionRepository;

    public function __construct(
        AttendanceRepositoryInterface $attendanceRepository,
        SessionAttendanceRepositoryInterface $sessionRepository
    ) {
        $this->attendanceRepository = $attendanceRepository;
        $this->sessionRepository = $sessionRepository;
    }

    /**
     * Display the attendance management dashboard for lecturer
     */
    public function index(Request $request)
    {
        // Get the authenticated lecturer
        $lecturer = Auth::user()->lecturer;

        if (!$lecturer) {
            return redirect()->back()->with('error', 'Lecturer profile not found.');
        }

        // Get all study programs related to this lecturer's classes
        $studyPrograms = StudyProgram::whereHas('classSchedules', function($query) use ($lecturer) {
            $query->where('lecturer_id', $lecturer->id);
        })->orderBy('name')->get();

        // Get selected study program
        $selectedProgramId = $request->input('study_program_id');

        // Get class schedules based on selected study program and this lecturer
        $classSchedules = collect([]);
        if ($selectedProgramId) {
            $classSchedules = ClassSchedule::with(['course', 'classroom'])
                ->where('study_program_id', $selectedProgramId)
                ->where('lecturer_id', $lecturer->id)
                ->get()
                ->map(function ($schedule) {
                    $schedule->combined_name = "{$schedule->course->name} - {$schedule->classroom->name}";
                    return $schedule;
                })
                ->sortBy('combined_name');
        } else {
            // If no program selected, show all classes for this lecturer
            $classSchedules = ClassSchedule::with(['course', 'classroom'])
                ->where('lecturer_id', $lecturer->id)
                ->get()
                ->map(function ($schedule) {
                    $schedule->combined_name = "{$schedule->course->name} - {$schedule->classroom->name}";
                    return $schedule;
                })
                ->sortBy('combined_name');
        }

        // Get selected class schedule
        $selectedScheduleId = $request->input('class_schedule_id');

        // Data for both tables
        $sessionsList = collect([]);
        $cumulativeData = collect([]);

        if ($selectedScheduleId) {
            // Verify the selected schedule belongs to this lecturer
            $schedule = ClassSchedule::find($selectedScheduleId);
            if (!$schedule || $schedule->lecturer_id != $lecturer->id) {
                return redirect()->route('lecturer.attendance-data.index')
                    ->with('error', 'Unauthorized access to this class schedule');
            }

            // Get all attendance sessions for the selected class schedule
            $sessionsList = $this->sessionRepository->getSessionsByClassSchedule($selectedScheduleId);

            // Get cumulative attendance data for all students in the class
            $cumulativeData = $this->attendanceRepository->getCumulativeAttendanceData($selectedScheduleId);
        }

        // dd($studyPrograms);

        return view('lecturer.attendance-data.index', compact(
            'studyPrograms',
            'classSchedules',
            'selectedProgramId',
            'selectedScheduleId',
            'sessionsList',
            'cumulativeData'
        ));
    }

    /**
     * Show attendance edit form for a specific session
     */
    public function editSession(Request $request, $sessionId)
    {
        $session = $this->sessionRepository->findById($sessionId);
        $lecturer = Auth::user()->lecturer;

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance session not found'
            ], 404);
        }

        // Ensure the lecturer owns this session
        if ($session->classSchedule->lecturer_id != $lecturer->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to access this session'
            ], 403);
        }

        // Get student attendance records for this session
        $attendances = $this->attendanceRepository->findByClassAndDate(
            $session->class_schedule_id,
            $session->session_date->format('Y-m-d')
        );

        return view('lecturer.attendance-data.edit', compact(
            'session',
            'attendances'
        ));
    }

    /**
     * Update attendance status (bulk update)
     */
    public function updateStatus(Request $request)
    {
        $lecturer = Auth::user()->lecturer;

        $request->validate([
            'attendances' => 'required|array',
            'attendances.*.attendance_id' => 'required|exists:attendances,id',
            'attendances.*.status' => 'required|in:present,absent,late,excused',
            'attendances.*.hours_present' => 'required|integer|min:0',
            'attendances.*.hours_absent' => 'required|integer|min:0',
            'attendances.*.hours_permitted' => 'required|integer|min:0',
            'attendances.*.hours_sick' => 'required|integer|min:0',
        ]);

        $successCount = 0;
        $errorCount = 0;

        DB::beginTransaction();
        try {
            foreach ($request->attendances as $data) {
                $attendance = $this->attendanceRepository->findById($data['attendance_id']);

                // Check if this attendance belongs to a class owned by this lecturer
                if (!$attendance || $attendance->classSchedule->lecturer_id != $lecturer->id) {
                    $errorCount++;
                    continue;
                }

                $totalHours = $data['hours_present'] + $data['hours_absent'] +
                            $data['hours_permitted'] + $data['hours_sick'];

                $sessionHours = $this->sessionRepository->getSessionTotalHours(
                        $attendance->class_schedule_id,
                        $attendance->date
                    );

                if ($totalHours != $sessionHours) {
                    $errorCount++;
                    continue;
                }

                // Update attendance
                $this->attendanceRepository->update($attendance, [
                    'status' => $data['status'],
                    'hours_present' => $data['hours_present'],
                    'hours_absent' => $data['hours_absent'],
                    'hours_permitted' => $data['hours_permitted'],
                    'hours_sick' => $data['hours_sick'],
                    'remarks' => $data['remarks'] ?? null,
                    'last_edited_at' => now(),
                    'last_edited_by' => Auth::user()->id,
                ]);

                $successCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$successCount} attendance records updated successfully" .
                            ($errorCount > 0 ? ", {$errorCount} failed" : ""),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }
}
