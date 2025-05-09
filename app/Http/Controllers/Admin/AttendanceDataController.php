<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attendance;
use App\Models\StudyProgram;
use Illuminate\Http\Request;
use App\Models\ClassSchedule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Interfaces\AttendanceRepositoryInterface;
use App\Repositories\Interfaces\SessionAttendanceRepositoryInterface;

class AttendanceDataController extends Controller
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
     * Display the attendance management dashboard
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Attendance::class);

        // Get all study programs for the dropdown
        $studyPrograms = StudyProgram::orderBy('name')->get();

        // Get selected study program
        $selectedProgramId = $request->input('study_program_id');

        // Get class schedules based on selected study program
        $classSchedules = collect([]);
        if ($selectedProgramId) {
            $classSchedules = ClassSchedule::with(['course', 'classroom'])
                ->where('study_program_id', $selectedProgramId)
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
            // Get all attendance sessions for the selected class schedule
            $sessionsList = $this->sessionRepository->getSessionsByClassSchedule($selectedScheduleId);

            // Get cumulative attendance data for all students in the class
            $cumulativeData = $this->attendanceRepository->getCumulativeAttendanceData($selectedScheduleId);
        }

        return view('admin.attendance.index', compact(
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

        if (!$session) {
            return redirect()->route('admin.attendance.index')
                ->with('error', 'Attendance session not found');
        }

        $this->authorize('update', $session->classSchedule);

        // Get student attendance records for this session
        $attendances = $this->attendanceRepository->findByClassAndDate(
            $session->class_schedule_id,
            $session->session_date->format('Y-m-d')
        );

        return view('admin.attendance.edit', compact(
            'session',
            'attendances'
        ));
    }

    public function updateStatus(Request $request)
    {
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

                // Add authorization check if needed
                // $this->authorize('update', $attendance);

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
                    'last_edited_by' => Auth::user() ? Auth::user()->id : null,
                ]);

                $successCount++;
            }

            DB::commit();

            // Modified response formatting to match test expectations
            $message = "{$successCount} attendance records updated successfully" .
                    ($errorCount > 0 ? ", {$errorCount} failed" : "");

            return response()->json([
                'success' => true,
                'message' => $message,
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
