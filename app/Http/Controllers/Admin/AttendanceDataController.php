<?php

namespace App\Http\Controllers\Admin;

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
            $cumulativeData = $this->getCumulativeAttendanceData($selectedScheduleId);
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

    /**
     * Update attendance status
     */
    // public function updateStatus(Request $request)
    // {
    //     $request->validate([
    //         'attendance_id' => 'required|exists:attendances,id',
    //         'status' => 'required|in:present,absent,late,excused',
    //         'hours_present' => 'required|integer|min:0',
    //         'hours_absent' => 'required|integer|min:0',
    //         'hours_permitted' => 'required|integer|min:0',
    //         'hours_sick' => 'required|integer|min:0',
    //     ]);

    //     try {
    //         $attendance = $this->attendanceRepository->findById($request->attendance_id);

    //         // Add authorization check
    //         $this->authorize('update', $attendance);

    //         $totalHours = $request->hours_present + $request->hours_absent +
    //                       $request->hours_permitted + $request->hours_sick;

    //         $sessionHours = $this->getSessionTotalHours($attendance->class_schedule_id, $attendance->date);

    //         if ($totalHours != $sessionHours) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => "Total hours must equal session hours ({$sessionHours})"
    //             ], 422);
    //         }

    //         // Update attendance
    //         $this->attendanceRepository->update($attendance, [
    //             'status' => $request->status,
    //             'hours_present' => $request->hours_present,
    //             'hours_absent' => $request->hours_absent,
    //             'hours_permitted' => $request->hours_permitted,
    //             'hours_sick' => $request->hours_sick,
    //             'remarks' => $request->remarks,
    //             'last_edited_at' => now(),
    //             'last_edited_by' => Auth::user()->id,
    //         ]);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Attendance updated successfully',
    //             'status' => $attendance->status
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to update status: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

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

                $sessionHours = $this->getSessionTotalHours($attendance->class_schedule_id, $attendance->date);

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

    /**
     * Get cumulative attendance data for all students in a class
     */
    private function getCumulativeAttendanceData($classScheduleId)
    {
        $classSchedule = ClassSchedule::findOrFail($classScheduleId);
        $students = $classSchedule->students()->with('user')->get();

        $result = collect();

        foreach ($students as $student) {
            // Get all attendance records for this student
            $attendances = Attendance::where('class_schedule_id', $classScheduleId)
                ->where('student_id', $student->id)
                ->get();

            // Calculate cumulative attendance hours
            $hoursPresent = $attendances->sum('hours_present');
            $hoursAbsent = $attendances->sum('hours_absent');
            $hoursPermitted = $attendances->sum('hours_permitted');
            $hoursSick = $attendances->sum('hours_sick');

            $result->push([
                'student' => $student,
                'hours_present' => $hoursPresent,
                'hours_absent' => $hoursAbsent,
                'hours_permitted' => $hoursPermitted,
                'hours_sick' => $hoursSick,
                'total_hours' => $hoursPresent + $hoursAbsent + $hoursPermitted + $hoursSick
            ]);
        }

        return $result;
    }

    /**
     * Get total hours for a session
     */
    private function getSessionTotalHours($classScheduleId, $date)
    {
        $session = $this->sessionRepository->findByClassAndDate($classScheduleId, $date);
        return $session ? $session->total_hours : 4; // Default to 4 if session not found
    }
}
