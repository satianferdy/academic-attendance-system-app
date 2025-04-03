<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Student;
use App\Models\Attendance;
use App\Services\Interfaces\AttendanceServiceInterface;
use App\Repositories\Interfaces\AttendanceRepositoryInterface;
use App\Repositories\Interfaces\StudentRepositoryInterface;

class AttendanceListController extends Controller
{
    protected $attendanceService;
    protected $attendanceRepository;
    protected $studentRepository;

    public function __construct(
        AttendanceServiceInterface $attendanceService,
        AttendanceRepositoryInterface $attendanceRepository,
        StudentRepositoryInterface $studentRepository
    ) {
        $this->attendanceService = $attendanceService;
        $this->attendanceRepository = $attendanceRepository;
        $this->studentRepository = $studentRepository;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Attendance::class);

        // Get courses directly from the Course model
        $courses = Course::orderBy('name')->get();

        // Get students using the repository
        $students = $this->studentRepository->getAll();

        $statuses = ['present', 'absent', 'late', 'excused', 'not_marked'];

        // Get filtered attendances using repository
        $attendances = $this->attendanceRepository->getFilteredAttendances(
            $request->course_id ?? null,
            $request->date ?? null,
            $request->student_id ?? null,
            $request->status ?? null
        );

        return view('admin.attendance.index', compact(
            'attendances',
            'courses',
            'students',
            'statuses',
            'request' // Pass request to maintain filter values
        ));
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'attendance_id' => 'required|exists:attendances,id',
            'status' => 'required|in:present,absent,late,excused,not_marked',
        ]);

        try {
            $attendance = $this->attendanceRepository->findById($request->attendance_id);

            // Add authorization check
            $this->authorize('update', $attendance);

            // Use service to update status
            $result = $this->attendanceService->updateAttendanceStatus(
                $attendance,
                $request->status
            );

            if ($result['status'] === 'success') {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'status' => $attendance->status
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

}
