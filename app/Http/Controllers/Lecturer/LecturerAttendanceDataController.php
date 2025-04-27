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
                return redirect()->route('lecturer.attendance.data.index')
                    ->with('error', 'Unauthorized access to this class schedule');
            }

            // Get all attendance sessions for the selected class schedule
            $sessionsList = $this->sessionRepository->getSessionsByClassSchedule($selectedScheduleId);

            // Get cumulative attendance data for all students in the class
            $cumulativeData = $this->getCumulativeAttendanceData($selectedScheduleId);
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
     * Get attendance data for a specific student
     */
    public function getStudentAttendance(Request $request, $studentId, $classScheduleId)
    {
        $lecturer = Auth::user()->lecturer;

        // Verify the selected schedule belongs to this lecturer
        $schedule = ClassSchedule::find($classScheduleId);
        if (!$schedule || $schedule->lecturer_id != $lecturer->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this class schedule'
            ], 403);
        }

        // Get student
        $student = Student::find($studentId);
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        }

        // Get attendance records
        $attendances = Attendance::with(['classSchedule.course'])
            ->where('class_schedule_id', $classScheduleId)
            ->where('student_id', $studentId)
            ->orderBy('date', 'desc')
            ->get();

        // Calculate statistics
        $totalHours = $attendances->sum('hours_present') +
                     $attendances->sum('hours_absent') +
                     $attendances->sum('hours_permitted') +
                     $attendances->sum('hours_sick');

        $presentPercentage = $totalHours > 0
            ? round(($attendances->sum('hours_present') / $totalHours) * 100)
            : 0;

        return response()->json([
            'success' => true,
            'student' => $student,
            'attendances' => $attendances,
            'statistics' => [
                'total_hours' => $totalHours,
                'hours_present' => $attendances->sum('hours_present'),
                'hours_absent' => $attendances->sum('hours_absent'),
                'hours_permitted' => $attendances->sum('hours_permitted'),
                'hours_sick' => $attendances->sum('hours_sick'),
                'present_percentage' => $presentPercentage
            ]
        ]);
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

    /**
     * Export attendance data for a class
     */
    // public function exportAttendance(Request $request, $classScheduleId)
    // {
    //     $lecturer = Auth::user()->lecturer;

    //     // Verify the selected schedule belongs to this lecturer
    //     $schedule = ClassSchedule::with(['course', 'classroom', 'students.user'])
    //         ->find($classScheduleId);

    //     if (!$schedule || $schedule->lecturer_id != $lecturer->id) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Unauthorized access to this class schedule'
    //         ], 403);
    //     }

    //     // Get all sessions
    //     $sessions = $this->sessionRepository->getSessionsByClassSchedule($classScheduleId);

    //     // Get all students
    //     $students = $schedule->students()->with('user')->get();

    //     // Prepare data for export
    //     $exportData = [];

    //     foreach ($students as $student) {
    //         $studentData = [
    //             'student_id' => $student->nim,
    //             'name' => $student->user->name,
    //             'sessions' => []
    //         ];

    //         foreach ($sessions as $session) {
    //             $attendance = Attendance::where('class_schedule_id', $classScheduleId)
    //                 ->where('student_id', $student->id)
    //                 ->whereDate('date', $session->session_date)
    //                 ->first();

    //             $sessionData = [
    //                 'date' => $session->session_date->format('Y-m-d'),
    //                 'week' => $session->week,
    //                 'meeting' => $session->meetings,
    //                 'status' => $attendance ? $attendance->status : 'absent',
    //                 'hours_present' => $attendance ? $attendance->hours_present : 0,
    //                 'hours_absent' => $attendance ? $attendance->hours_absent : $session->total_hours,
    //                 'hours_permitted' => $attendance ? $attendance->hours_permitted : 0,
    //                 'hours_sick' => $attendance ? $attendance->hours_sick : 0,
    //             ];

    //             $studentData['sessions'][] = $sessionData;
    //         }

    //         $exportData[] = $studentData;
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'course' => $schedule->course->name,
    //         'classroom' => $schedule->classroom->name,
    //         'data' => $exportData
    //     ]);
    // }
}
