<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Interfaces\AttendanceRepositoryInterface;
use App\Repositories\Interfaces\ClassScheduleRepositoryInterface;
use App\Repositories\Interfaces\SessionAttendanceRepositoryInterface;
use Carbon\Carbon;

class LecturerSessionController extends Controller
{
    protected $attendanceRepository;

    public function __construct(
        AttendanceRepositoryInterface $attendanceRepository,
    ) {
        $this->attendanceRepository = $attendanceRepository;
    }

    public function recentSessions(Request $request)
    {
        // Get course ID filter if provided
        $courseId = $request->input('course_id');
        $date = $request->input('date');

        // Group attendance records by class schedule and date
        $attendances = $this->attendanceRepository->getFilteredAttendances($courseId, $date);

        // Group by class and date
        $sessionSummaries = [];

        foreach ($attendances as $attendance) {
            $classId = $attendance->class_schedule_id;
            $dateStr = $attendance->date->format('Y-m-d');
            $key = $classId . '_' . $dateStr;

            if (!isset($sessionSummaries[$key])) {
                $sessionSummaries[$key] = [
                    'class_schedule' => $attendance->classSchedule,
                    'date' => $attendance->date,
                    'present' => 0,
                    'absent' => 0,
                    'total' => 0,
                ];
            }

            $sessionSummaries[$key]['total']++;

            if ($attendance->status === 'present') {
                $sessionSummaries[$key]['present']++;
            } else {
                $sessionSummaries[$key]['absent']++;
            }
        }

        // Calculate attendance rates
        foreach ($sessionSummaries as &$summary) {
            $summary['rate'] = $summary['total'] > 0
                ? round(($summary['present'] / $summary['total']) * 100, 2)
                : 0;
        }

        // Get courses for filter
        $courses = $this->getCourses();

        return view('lecturer.session.index', [
            'sessionSummaries' => $sessionSummaries,
            'courses' => $courses,
            'selectedCourse' => $courseId,
            'selectedDate' => $date,
        ]);
    }

    private function getCourses()
    {
        // This would typically come from a CourseRepository
        return \App\Models\Course::orderBy('name')->get();
    }
}
