<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Student;
use App\Models\Lecturer;
use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\ClassSchedule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin.dashboard', [
            'statistics' => $this->getStatistics(),
            'faceRegistration' => $this->getFaceRegistrationData(),
            'attendanceData' => $this->getAttendanceData(),
            'recentAttendances' => $this->getRecentAttendances(),
        ]);
    }

    /**
     * Get basic statistics for the dashboard.
     *
     * @return array
     */
    private function getStatistics(): array
    {
        return [
            'totalStudents' => Student::count(),
            'totalLecturers' => Lecturer::count(),
            'totalCourses' => Course::count(),
            'totalClassrooms' => ClassRoom::count(),
            'totalSchedules' => ClassSchedule::count(),
            'todayAttendanceCount' => Attendance::whereDate('date', Carbon::today())->count(),
        ];
    }

    /**
     * Get face registration statistics.
     *
     * @return array
     */
    private function getFaceRegistrationData(): array
    {
        $totalStudents = Student::count();
        $studentsWithFace = Student::where('face_registered', true)->count();

        return [
            'totalStudents' => $totalStudents,
            'studentsWithFace' => $studentsWithFace,
            'faceRegistrationPercentage' => $totalStudents > 0
                ? round(($studentsWithFace / $totalStudents) * 100)
                : 0,
        ];
    }

    /**
     * Get attendance data for the weekly chart (past week) with status breakdown.
     *
     * @return array
     */
    private function getAttendanceData(): array
    {
        $endOfWeek = Carbon::now(); // Hari ini
        $startOfWeek = Carbon::now()->subDays(6); // 6 hari sebelumnya

        // Get attendance records grouped by date and status
        $weeklyAttendance = Attendance::whereBetween('date', [$startOfWeek, $endOfWeek])
            ->select(
                DB::raw('DATE(date) as attendance_date'),
                'status',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('attendance_date', 'status')
            ->get();

        $weekDays = [];
        $presentCounts = [];
        $absentCounts = [];
        $lateCounts = [];
        $excusedCounts = [];

        // Array terjemahan hari dan bulan ke bahasa Indonesia
        $dayTranslations = [
            'Mon' => 'Sen',
            'Tue' => 'Sel',
            'Wed' => 'Rab',
            'Thu' => 'Kam',
            'Fri' => 'Jum',
            'Sat' => 'Sab',
            'Sun' => 'Min',
        ];

        $monthTranslations = [
            'Jan' => 'Jan',
            'Feb' => 'Feb',
            'Mar' => 'Mar',
            'Apr' => 'Apr',
            'May' => 'Mei',
            'Jun' => 'Jun',
            'Jul' => 'Jul',
            'Aug' => 'Agt',
            'Sep' => 'Sep',
            'Oct' => 'Okt',
            'Nov' => 'Nov',
            'Dec' => 'Des',
        ];

        // Prepare dates for the past 7 days
        for ($i = 0; $i < 7; $i++) {
            $currentDate = $startOfWeek->copy()->addDays($i);
            $formattedDate = $currentDate->format('Y-m-d');

            // Format tampilan dengan bahasa Indonesia
            $dayCode = $currentDate->format('D');
            $monthCode = $currentDate->format('M');

            $indonesianDay = $dayTranslations[$dayCode] ?? $dayCode;
            $indonesianMonth = $monthTranslations[$monthCode] ?? $monthCode;

            $displayDate = $indonesianDay . ' (' . $currentDate->format('j') . ' ' . $indonesianMonth . ')';

            $weekDays[] = $displayDate;

            // Initialize counts for each status
            $presentCounts[$formattedDate] = 0;
            $absentCounts[$formattedDate] = 0;
            $lateCounts[$formattedDate] = 0;
            $excusedCounts[$formattedDate] = 0;
        }

        // Fill in actual counts from database
        foreach ($weeklyAttendance as $record) {
            $date = $record->attendance_date;
            $status = $record->status;
            $count = $record->count;

            switch ($status) {
                case 'present':
                    $presentCounts[$date] = $count;
                    break;
                case 'absent':
                    $absentCounts[$date] = $count;
                    break;
                case 'late':
                    $lateCounts[$date] = $count;
                    break;
                case 'excused':
                    $excusedCounts[$date] = $count;
                    break;
            }
        }

        // Format data for chart
        return [
            'weekDays' => $weekDays,
            'series' => [
                [
                    'name' => 'Hadir', // Diubah dari 'Present'
                    'data' => array_values($presentCounts),
                    'color' => '#4CAF50', // Green
                ],
                [
                    'name' => 'Tidak Hadir', // Diubah dari 'Absent'
                    'data' => array_values($absentCounts),
                    'color' => '#F44336', // Red
                ],
                [
                    'name' => 'Terlambat', // Diubah dari 'Late'
                    'data' => array_values($lateCounts),
                    'color' => '#FFC107', // Amber/Yellow
                ],
                [
                    'name' => 'Izin', // Diubah dari 'Excused'
                    'data' => array_values($excusedCounts),
                    'color' => '#2196F3', // Blue
                ],
            ],
        ];
    }

    /**
     * Get recent attendance records.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getRecentAttendances(int $limit = 5)
    {
        return Attendance::with(['student.user', 'classSchedule.course'])
            ->latest('date')
            ->take($limit)
            ->get();
    }
}
