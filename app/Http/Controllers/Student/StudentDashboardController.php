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

    public function getAttendanceStatistics($studentId)
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

    public function getMonthlyAttendanceData($studentId)
    {
        // Menentukan rentang waktu - 6 bulan terakhir
        $now = Carbon::now();
        $sixMonthsAgo = $now->copy()->subMonths(5)->startOfMonth();

        // Mendapatkan semua status attendance dalam satu query dengan pengelompokan
        $attendances = Attendance::where('student_id', $studentId)
            ->where('date', '>=', $sixMonthsAgo)
            ->selectRaw('MONTH(date) as month, YEAR(date) as year, status, COUNT(*) as count')
            ->groupBy('year', 'month', 'status')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Terjemahan nama bulan ke bahasa Indonesia
        $monthNames = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agt',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des',
        ];

        // Menyiapkan array bulan untuk 6 bulan terakhir (oldest to newest)
        $months = [];
        $presentData = [];
        $lateData = [];
        $absentData = [];
        $excusedData = [];

        // Pre-fill arrays with zeros
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = $now->copy()->subMonths($i);
            $monthNumber = (int)$monthDate->format('n');
            $months[] = $monthNames[$monthNumber];

            $yearMonth = $monthDate->format('Y-m');
            $presentData[$yearMonth] = 0;
            $lateData[$yearMonth] = 0;
            $absentData[$yearMonth] = 0;
            $excusedData[$yearMonth] = 0;
        }

        // Populate with actual data
        foreach ($attendances as $record) {
            $yearMonth = "{$record->year}-" . str_pad($record->month, 2, '0', STR_PAD_LEFT);

            if (isset($presentData[$yearMonth]) && $record->status === 'present') {
                $presentData[$yearMonth] = $record->count;
            } elseif (isset($lateData[$yearMonth]) && $record->status === 'late') {
                $lateData[$yearMonth] = $record->count;
            } elseif (isset($absentData[$yearMonth]) && $record->status === 'absent') {
                $absentData[$yearMonth] = $record->count;
            } elseif (isset($excusedData[$yearMonth]) && $record->status === 'excused') {
                $excusedData[$yearMonth] = $record->count;
            }
        }

        // Convert associative arrays to indexed arrays for the chart
        $presentValues = array_values($presentData);
        $lateValues = array_values($lateData);
        $absentValues = array_values($absentData);
        $excusedValues = array_values($excusedData);

        return [
            'months' => $months,
            'series' => [
                ['name' => 'Hadir', 'data' => $presentValues],
                ['name' => 'Terlambat', 'data' => $lateValues],
                ['name' => 'Tidak Hadir', 'data' => $absentValues],
                ['name' => 'Izin', 'data' => $excusedValues]
            ]
        ];
    }

    public function getTodayClasses($student)
    {
        $today = Carbon::now();
        $englishDay = strtolower($today->format('l'));

        // Pemetaan hari dari Inggris ke Indonesia
        $dayMapping = [
            'monday' => 'senin',
            'tuesday' => 'selasa',
            'wednesday' => 'rabu',
            'thursday' => 'kamis',
            'friday' => 'jumat',
            'saturday' => 'sabtu',
            'sunday' => 'minggu',
        ];

        // Dapatkan nama hari dalam bahasa Indonesia
        $indonesianDay = $dayMapping[$englishDay] ?? $englishDay;

        return ClassSchedule::with(['course', 'timeSlots', 'lecturer.user'])
            ->where('classroom_id', $student->classroom_id)
            ->where('day', $indonesianDay) // Gunakan hari dalam bahasa Indonesia
            ->get();
    }

    public function getUpcomingClasses($student)
    {
        $today = Carbon::now();
        $dayOfWeek = strtolower($today->format('l'));
        $daysOfWeek = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

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
