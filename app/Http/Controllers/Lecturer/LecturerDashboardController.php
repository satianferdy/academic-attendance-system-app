<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassSchedule;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;

class LecturerDashboardController extends Controller
{
    public function index()
    {
        $lecturer = $this->getAuthenticatedLecturer();
        $todaySchedules = $this->getTodaySchedules($lecturer->id);
        $upcomingSchedulesData = $this->getUpcomingSchedules($lecturer->id);
        $recentSessionsStats = $this->getRecentSessionsStats($lecturer);
        $faceRegistrationData = $this->getFaceRegistrationData($lecturer);
        $weeklyAttendanceData = $this->getWeeklyAttendanceData($lecturer->id);
        $averageAttendanceRate = $this->calculateAverageAttendanceRate($recentSessionsStats);

        return view('lecturer.dashboard', [
            'lecturer' => $lecturer,
            'todaySchedules' => $todaySchedules,
            'upcomingDays' => $upcomingSchedulesData['days'],
            'upcomingSchedules' => $upcomingSchedulesData['schedules'],
            'recentSessionsStats' => $recentSessionsStats,
            'totalStudents' => $faceRegistrationData['totalStudents'],
            'studentsWithFace' => $faceRegistrationData['studentsWithFace'],
            'faceRegistrationPercentage' => $faceRegistrationData['percentage'],
            'weekDays' => $weeklyAttendanceData['days'],
            'weeklyAttendanceData' => $weeklyAttendanceData['attendance'],
            'avgAttendanceRate' => $averageAttendanceRate
        ]);
    }

    /**
     * Get the authenticated lecturer
     *
     * @return object
     */
    private function getAuthenticatedLecturer()
    {
        $user = Auth::user();
        return $user->lecturer;
    }

    /**
     * Get today's class schedules for a lecturer
     *
     * @param int $lecturerId
     * @return Collection
     */
    private function getTodaySchedules(int $lecturerId)
    {
        $today = Carbon::now();
        $dayName = strtolower($today->format('l'));

        $dayMapping = [
            'monday' => 'senin',
            'tuesday' => 'selasa',
            'wednesday' => 'rabu',
            'thursday' => 'kamis',
            'friday' => 'jumat',
            'saturday' => 'sabtu',
            'sunday' => 'minggu',
        ];

        $indonesianDay = $dayMapping[$dayName] ?? $dayName;

        return ClassSchedule::with(['course', 'classroom', 'timeSlots'])
            ->where('lecturer_id', $lecturerId)
            ->where('day', $indonesianDay)
            ->take(3)
            ->get();
    }

    /**
     * Get upcoming schedules for the next 7 days
     *
     * @param int $lecturerId
     * @return array
     */
    private function getUpcomingSchedules(int $lecturerId)
    {
        $upcomingDays = [];
        $upcomingSchedules = [];
        $schedulesFound = 0;

        // Pemetaan hari Inggris ke Indonesia (digunakan jika data di database menggunakan bahasa Indonesia)
        $dayMapping = [
            'monday' => 'senin',
            'tuesday' => 'selasa',
            'wednesday' => 'rabu',
            'thursday' => 'kamis',
            'friday' => 'jumat',
            'saturday' => 'sabtu',
            'sunday' => 'minggu',
        ];

        for ($i = 1; $schedulesFound < 3 && $i <= 7; $i++) {
            $date = Carbon::now()->addDays($i);

            // Format hari dalam bahasa Inggris untuk query database
            $englishDay = strtolower($date->format('l'));

            // Gunakan hari dalam bahasa Indonesia jika database menyimpan dalam bahasa Indonesia
            // Jika tidak, gunakan englishDay langsung
            $dayForQuery = $dayMapping[$englishDay] ?? $englishDay;

            $schedules = ClassSchedule::with(['course', 'classroom', 'timeSlots'])
                ->where('lecturer_id', $lecturerId)
                ->where('day', $dayForQuery) // Sesuaikan dengan format yang disimpan di database
                ->get();

            if ($schedules->count() > 0) {
                // Format tanggal dalam bahasa Indonesia untuk tampilan
                // Menggunakan locale('id') dan isoFormat untuk lokalisasi yang tepat
                $indonesianDate = $date->locale('id')->isoFormat('MMM D');

                // Untuk key array, kita bisa tetap gunakan format bahasa Inggris
                // agar tidak ada perubahan pada logika yang memanggil array ini
                $upcomingDays[$englishDay] = $indonesianDate;
                $upcomingSchedules[$englishDay] = $schedules;
                $schedulesFound += $schedules->count();
            }
        }

        return [
            'days' => $upcomingDays,
            'schedules' => $upcomingSchedules
        ];
    }

    /**
     * Get recent attendance sessions with statistics
     *
     * @param object $lecturer
     * @return array
     */
    private function getRecentSessionsStats($lecturer)
    {
        $recentSessions = $lecturer->classSchedules()
            ->with(['attendances' => function($query) {
                $query->select('class_schedule_id', 'date')
                    ->groupBy('class_schedule_id', 'date');
            }])
            ->get()
            ->pluck('attendances')
            ->flatten()
            ->unique(function($item) {
                return $item->class_schedule_id . '-' . $item->date->format('Y-m-d');
            })
            ->sortByDesc('date')
            ->take(5);

        $recentSessionsStats = [];

        foreach ($recentSessions as $session) {
            $classSchedule = ClassSchedule::with('course')->find($session->class_schedule_id);
            $attendances = Attendance::where('class_schedule_id', $session->class_schedule_id)
                ->where('date', $session->date)
                ->get();

            $total = $attendances->count();
            $present = $attendances->where('status', 'present')->count();
            $late = $attendances->where('status', 'late')->count();
            $absent = $attendances->where('status', 'absent')->count();
            $excused = $attendances->where('status', 'excused')->count();

            $presentPercentage = $total > 0 ? round((($present + $late) / $total) * 100) : 0;

            $recentSessionsStats[] = [
                'course' => $classSchedule->course->name,
                'date' => $session->date->format('M d, Y'),
                'day' => $session->date->format('l'),
                'total' => $total,
                'present' => $present,
                'late' => $late,
                'absent' => $absent,
                'excused' => $excused,
                'presentPercentage' => $presentPercentage,
                'course_id' => $classSchedule->course->id,
                'class_schedule_id' => $classSchedule->id
            ];
        }

        return $recentSessionsStats;
    }

    /**
     * Calculate average attendance rate from recent sessions
     *
     * @param array $recentSessionsStats
     * @return int
     */
    private function calculateAverageAttendanceRate(array $recentSessionsStats)
    {
        $attendanceRateSum = 0;
        $attendanceRateCount = 0;

        foreach ($recentSessionsStats as $stat) {
            $attendanceRateSum += $stat['presentPercentage'];
            $attendanceRateCount++;
        }

        return $attendanceRateCount > 0 ? round($attendanceRateSum / $attendanceRateCount) : 0;
    }

    /**
     * Get face registration statistics data
     *
     * @param object $lecturer
     * @return array
     */
    private function getFaceRegistrationData($lecturer)
    {
        $totalStudents = 0;
        $studentsWithFace = 0;

        foreach ($lecturer->classSchedules as $schedule) {
            $students = $schedule->students()->get();
            $totalStudents += $students->count();
            $studentsWithFace += $students->where('face_registered', true)->count();
        }

        $faceRegistrationPercentage = $totalStudents > 0 ? round(($studentsWithFace / $totalStudents) * 100) : 0;

        return [
            'totalStudents' => $totalStudents,
            'studentsWithFace' => $studentsWithFace,
            'percentage' => $faceRegistrationPercentage
        ];
    }

    /**
     * Get weekly attendance data for charts
     *
     * @param int $lecturerId
     * @return array
     */
    private function getWeeklyAttendanceData(int $lecturerId)
    {
        $weekEnd = Carbon::now();
        $weekDays = [];
        $weeklyAttendanceData = [
            'present' => [],
            'late' => [],
            'absent' => [],
            'excused' => []
        ];

        for ($i = 6; $i >= 0; $i--) {
            $date = $weekEnd->copy()->subDays($i);

            // Menggunakan locale('id') dan isoFormat untuk menampilkan tanggal dalam bahasa Indonesia
            // Format: Sen, 13 Mei (hari singkat, tanggal, bulan singkat)
            $weekDays[] = $date->locale('id')->isoFormat('ddd, D MMM');

            $dayAttendances = Attendance::whereHas('classSchedule', function($query) use ($lecturerId) {
                $query->where('lecturer_id', $lecturerId);
            })->whereDate('date', $date->format('Y-m-d'))->get();

            $weeklyAttendanceData['present'][] = $dayAttendances->where('status', 'present')->count();
            $weeklyAttendanceData['late'][] = $dayAttendances->where('status', 'late')->count();
            $weeklyAttendanceData['absent'][] = $dayAttendances->where('status', 'absent')->count();
            $weeklyAttendanceData['excused'][] = $dayAttendances->where('status', 'excused')->count();
        }

        return [
            'days' => $weekDays,
            'attendance' => $weeklyAttendanceData
        ];
    }
}
