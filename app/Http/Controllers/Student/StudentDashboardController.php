<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ClassSchedule;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $student = Auth::user()->student;
        $today = Carbon::now();

        // Get attendance statistics
        $attendanceStats = $this->getAttendanceStatistics($student->id);

        // Get today's class schedules
        $todayClasses = $this->getTodayClasses($student);

        // Get upcoming class schedules (next 7 days)
        $upcomingClasses = $this->getUpcomingClasses($student);

        // Get recent attendance records
        $recentAttendances = $this->getRecentAttendances($student->id);

        // Get recent face recognition logs
        $faceRecognitionStatus = $this->getFaceRecognitionStatus($student);

        return view('student.dashboard', compact(
            'attendanceStats',
            'todayClasses',
            'upcomingClasses',
            'recentAttendances',
            'faceRecognitionStatus'
        ));
    }

    private function getAttendanceStatistics($studentId)
    {
        // Get all attendance records for the student
        $allAttendances = Attendance::where('student_id', $studentId)->get();

        // Calculate attendance rate
        $totalClasses = $allAttendances->count();
        $presentCount = $allAttendances->where('status', 'present')->count();
        $lateCount = $allAttendances->where('status', 'late')->count();
        $absentCount = $allAttendances->where('status', 'absent')->count();
        $excusedCount = $allAttendances->where('status', 'excused')->count();

        // Avoid division by zero
        $attendanceRate = $totalClasses > 0
            ? round((($presentCount + $lateCount) / $totalClasses) * 100, 1)
            : 0;

        // Get monthly attendance data for chart
        $monthlyData = $this->getMonthlyAttendanceData($studentId);

        return [
            'totalClasses' => $totalClasses,
            'presentCount' => $presentCount,
            'lateCount' => $lateCount,
            'absentCount' => $absentCount,
            'excusedCount' => $excusedCount,
            'attendanceRate' => $attendanceRate,
            'monthlyData' => $monthlyData
        ];
    }

    private function getMonthlyAttendanceData($studentId)
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Get last 6 months
        $months = [];
        $presentData = [];
        $lateData = [];
        $absentData = [];
        $excusedData = [];

        for ($i = 0; $i < 6; $i++) {
            $date = Carbon::create($currentYear, $currentMonth)->subMonths($i);
            $monthName = $date->format('M');
            $months[] = $monthName;

            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();

            $monthlyAttendances = Attendance::where('student_id', $studentId)
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->get();

            $presentData[] = $monthlyAttendances->where('status', 'present')->count();
            $lateData[] = $monthlyAttendances->where('status', 'late')->count();
            $absentData[] = $monthlyAttendances->where('status', 'absent')->count();
            $excusedData[] = $monthlyAttendances->where('status', 'excused')->count();
        }

        // Reverse arrays to show oldest to newest
        $months = array_reverse($months);
        $presentData = array_reverse($presentData);
        $lateData = array_reverse($lateData);
        $absentData = array_reverse($absentData);
        $excusedData = array_reverse($excusedData);

        return [
            'months' => $months,
            'series' => [
                ['name' => 'Present', 'data' => $presentData],
                ['name' => 'Late', 'data' => $lateData],
                ['name' => 'Absent', 'data' => $absentData],
                ['name' => 'Excused', 'data' => $excusedData]
            ]
        ];
    }

    private function getTodayClasses($student)
    {
        $today = Carbon::now();
        $dayOfWeek = strtolower($today->format('l'));

        return ClassSchedule::with(['course', 'timeSlots', 'lecturer.user'])
            ->where('classroom_id', $student->classroom_id)
            ->where('day', $dayOfWeek)
            ->get();
    }

    private function getUpcomingClasses($student)
    {
        $today = Carbon::now();
        $dayOfWeek = strtolower($today->format('l'));
        $daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        // Find the index of today
        $todayIndex = array_search($dayOfWeek, $daysOfWeek);

        // Get the next 7 days (excluding today)
        $upcomingDays = [];
        for ($i = 1; $i <= 7; $i++) {
            $upcomingDays[] = $daysOfWeek[($todayIndex + $i) % 7];
        }

        return ClassSchedule::with(['course', 'timeSlots', 'lecturer.user'])
            ->where('classroom_id', $student->classroom_id)
            ->whereIn('day', $upcomingDays)
            ->orderByRaw("FIELD(day, '" . implode("','", $upcomingDays) . "')")
            ->get()
            ->groupBy('day');
    }

    private function getRecentAttendances($studentId)
    {
        return Attendance::with(['classSchedule.course'])
            ->where('student_id', $studentId)
            ->orderBy('date', 'desc')
            ->take(5)
            ->get();
    }

    private function getFaceRecognitionStatus($student)
    {
        $faceData = $student->faceData;

        return [
            'isRegistered' => $student->face_registered,
            'lastUpdate' => $faceData ? $faceData->updated_at : null,
            'isActive' => $faceData ? $faceData->is_active : false
        ];
    }
}
