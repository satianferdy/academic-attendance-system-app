<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Interfaces\AttendanceRepositoryInterface;
use App\Repositories\Interfaces\ClassScheduleRepositoryInterface;
use App\Repositories\Interfaces\SessionAttendanceRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LecturerSessionController extends Controller
{
    protected $attendanceRepository;
    protected $classScheduleRepository;
    protected $sessionAttendanceRepository;

    public function __construct(
        AttendanceRepositoryInterface $attendanceRepository,
        ClassScheduleRepositoryInterface $classScheduleRepository,
        SessionAttendanceRepositoryInterface $sessionAttendanceRepository
    ) {
        $this->attendanceRepository = $attendanceRepository;
        $this->classScheduleRepository = $classScheduleRepository;
        $this->sessionAttendanceRepository = $sessionAttendanceRepository;
    }

    public function recentSessions(Request $request)
    {
        // Get lecturer ID from authenticated user
        $lecturerId = Auth::user()->lecturer->id;

        // Get filters if provided
        $courseId = $request->input('course_id');
        $date = $request->input('date');
        $week = $request->input('week');

        // Get sessions with attendance counts
        $sessions = $this->sessionAttendanceRepository->getSessionsByLecturer(
            $lecturerId,
            $courseId,
            $date,
            $week
        );

        // Process sessions into summary format
        $sessionSummaries = $sessions->map(function($session) {
            $total = $session->total_count ?: 0;
            $present = $session->present_count ?: 0;
            $absent = $session->absent_count ?: 0;

            return [
                'class_schedule' => $session->classSchedule,
                'date' => $session->session_date,
                'week' => $session->week,
                'meeting' => $session->meetings,
                'present' => $present,
                'absent' => $absent,
                'total' => $total,
                'rate' => $total > 0 ? round(($present / $total) * 100, 2) : 0
            ];
        })->toArray();

        // Get courses for filter (only those taught by logged-in lecturer)
        $courses = $this->getCoursesForLecturer($lecturerId);

        // Get available weeks for filter
        $maxWeeks = $this->getMaxWeeksForLecturer($lecturerId);

        return view('lecturer.session.index', [
            'sessionSummaries' => $sessionSummaries,
            'courses' => $courses,
            'selectedCourse' => $courseId,
            'selectedDate' => $date,
            'selectedWeek' => $week,
            'maxWeeks' => $maxWeeks,
        ]);
    }

    private function getCoursesForLecturer($lecturerId)
    {
        // Get unique courses taught by this lecturer
        return \App\Models\Course::whereHas('classSchedules', function ($query) use ($lecturerId) {
            $query->where('lecturer_id', $lecturerId);
        })->orderBy('name')->get();
    }

    private function getMaxWeeksForLecturer($lecturerId)
    {
        // Find the maximum number of weeks for any class taught by this lecturer
        $maxWeeks = \App\Models\ClassSchedule::where('lecturer_id', $lecturerId)
            ->max('total_weeks');

        return $maxWeeks ?: 16; // Default to 16 weeks if no value found
    }
}
