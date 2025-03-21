<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\ClassSchedule;
use App\Models\SessionAttendance;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\User;

class AttendanceListController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Attendance::class);

        $query = Attendance::with(['classSchedule.course', 'classSchedule.lecturer.user', 'student.user']);

        // Filter by course
        if ($request->filled('course_id')) {
            $query->whereHas('classSchedule', function ($q) use ($request) {
                $q->where('course_id', $request->course_id);
            });
        }

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        // Filter by student (NIM)
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Get data for filters - eager load relationships
        $courses = Course::orderBy('name')->get();
        $students = Student::with('user')->get();
        $statuses = ['present', 'absent', 'late', 'excused', 'not_marked'];

        // Get all attendances instead of paginating
        $attendances = $query->orderBy('date', 'desc')->get();

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
            $attendance = Attendance::findOrFail($request->attendance_id);

            // Add authorization check
            $this->authorize('update', $attendance);  // Changed from Attendance::class to $attendance

            $attendance->status = $request->status;
            $attendance->save();

            return response()->json([
                'success' => true,
                'message' => 'Attendance status updated successfully',
                'status' => $attendance->status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

}
