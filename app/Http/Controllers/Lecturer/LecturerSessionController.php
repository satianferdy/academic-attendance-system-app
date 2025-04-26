<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Interfaces\AttendanceRepositoryInterface;
use App\Repositories\Interfaces\ClassScheduleRepositoryInterface;
use App\Repositories\Interfaces\SessionAttendanceRepositoryInterface;
use App\Models\StudyProgram;
use App\Models\ClassRoom;
use App\Models\Semester;
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

        // Get active semester as default if no semester is selected
        $activeSemester = Semester::where('is_active', true)->first();
        $defaultSemesterId = $activeSemester ? $activeSemester->id : null;

        // Get filters if provided
        $courseId = $request->input('course_id');
        $date = $request->input('date');
        $week = $request->input('week');
        $studyProgramId = $request->input('study_program_id');
        $classroomId = $request->input('classroom_id');
        $semesterId = $request->input('semester_id', $defaultSemesterId);

        // Get sessions with attendance counts
        $sessions = $this->sessionAttendanceRepository->getSessionsByLecturer(
            $lecturerId,
            $courseId,
            $date,
            $week,
            $studyProgramId,
            $classroomId,
            $semesterId
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
                'rate' => $total > 0 ? round(($present / $total) * 100) : 0
            ];
        })->toArray();

        // Get courses for filter (only those taught by logged-in lecturer)
        $courses = $this->getCoursesForLecturer($lecturerId);

        // Get study programs for filter
        $studyPrograms = StudyProgram::orderBy('name')->get();

        // Get classrooms for filter
        $classrooms = $this->getClassroomsForLecturer($lecturerId);

        // Get semesters for filter (ordered by recency)
        $semesters = Semester::orderBy('is_active', 'desc')
            ->orderBy('start_date', 'desc')
            ->get();

        // Get available weeks for filter
        $maxWeeks = $this->getMaxWeeksForLecturer($lecturerId);

        return view('lecturer.session.index', [
            'sessionSummaries' => $sessionSummaries,
            'courses' => $courses,
            'studyPrograms' => $studyPrograms,
            'classrooms' => $classrooms,
            'semesters' => $semesters,
            'selectedCourse' => $courseId,
            'selectedDate' => $date,
            'selectedWeek' => $week,
            'selectedProgram' => $studyProgramId,
            'selectedClassroom' => $classroomId,
            'selectedSemester' => $semesterId,
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

    // get classrooms for lecturer
    private function getClassroomsForLecturer($lecturerId)
    {
        // Get unique classrooms for this lecturer
        return ClassRoom::whereHas('schedules', function ($query) use ($lecturerId) {
            $query->where('lecturer_id', $lecturerId);
        })->orderBy('name')->get();
    }
}
