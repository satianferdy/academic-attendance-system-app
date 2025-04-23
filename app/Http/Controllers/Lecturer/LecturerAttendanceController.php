<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\ExtendTimeRequest;
use App\Http\Requests\Attendance\StoreAttendanceRequest;
use App\Http\Requests\Attendance\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Models\ClassSchedule;
use App\Repositories\Interfaces\AttendanceRepositoryInterface;
use App\Repositories\Interfaces\ClassScheduleRepositoryInterface;
use App\Repositories\Interfaces\SessionAttendanceRepositoryInterface;
use App\Services\Interfaces\AttendanceServiceInterface;
use App\Services\Interfaces\QRCodeServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LecturerAttendanceController extends Controller
{
    protected $attendanceService;
    protected $qrCodeService;
    protected $attendanceRepository;
    protected $sessionRepository;
    protected $classScheduleRepository;

    public function __construct(
        AttendanceServiceInterface $attendanceService,
        QRCodeServiceInterface $qrCodeService,
        AttendanceRepositoryInterface $attendanceRepository,
        SessionAttendanceRepositoryInterface $sessionRepository,
        ClassScheduleRepositoryInterface $classScheduleRepository
    ) {
        $this->attendanceService = $attendanceService;
        $this->qrCodeService = $qrCodeService;
        $this->attendanceRepository = $attendanceRepository;
        $this->sessionRepository = $sessionRepository;
        $this->classScheduleRepository = $classScheduleRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', ClassSchedule::class);

        $lecturer = Auth::user()->lecturer;

        if (!$lecturer) {
            return redirect()->back()->with('error', 'Lecturer profile not found.');
        }

        $schedules = $lecturer->classSchedules()->with('course', 'semesters')->get();
        return view('lecturer.attendance.index', compact('schedules'));
    }

    /**
     * Create a new attendance session.
     */
    public function create(StoreAttendanceRequest $request)
    {
        $validated = $request->validated();
        $classSchedule = $this->classScheduleRepository->find($validated['class_id']);

        $this->authorize('generateQR', $classSchedule);

        $result = $this->attendanceService->generateSessionAttendance(
            $classSchedule->id,
            $validated['date'],
            $validated['week'],
            $validated['meetings']

        );

        if ($result['status'] === 'error') {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->route('lecturer.attendance.view_qr', [
            'classSchedule' => $classSchedule->id,
            'date' => $validated['date']
        ]);
    }

    /**
     * Display attendance records for a specific class and date.
     */
    public function show(Request $request, ClassSchedule $classSchedule)
    {
        $this->authorize('view', $classSchedule);

        $date = $request->query('date', date('Y-m-d'));
        $week = $request->query('week');
        $meeting = $request->query('meeting');

        // Check date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return redirect()->back()->with('error', 'Invalid date format.');
        }

        // Get the specific session attendance record if week and meeting are provided
        $session = null;
        if ($week && $meeting) {
            $session = $this->sessionRepository->findByClassWeekAndMeeting($classSchedule->id, $week, $meeting);
            // If session found but dates don't match, update the date parameter to match the session date
            if ($session && $session->session_date->format('Y-m-d') !== $date) {
                return redirect()->route('lecturer.attendance.show', [
                    'classSchedule' => $classSchedule->id,
                    'date' => $session->session_date->format('Y-m-d'),
                    'week' => $week,
                    'meeting' => $meeting
                ]);
            }
        }

        // If no specific session found by week/meeting, try to find by date
        if (!$session) {
            $session = $this->sessionRepository->findByClassAndDate($classSchedule->id, $date);
            // Update week and meeting values from the found session
            $week = $session ? $session->week : null;
            $meeting = $session ? $session->meetings : null;
        }

        // Get attendances for this specific session
        $attendances = collect([]);
        if ($session) {
            $attendances = $this->attendanceRepository->findByClassAndDate($classSchedule->id, $session->session_date->format('Y-m-d'));
        }

        $sessionExists = $session !== null;
        $weekNumber = $week;
        $meetingNumber = $meeting;

        // If session exists, use its date for consistency
        if ($session) {
            $date = $session->session_date->format('Y-m-d');
        }

        return view('lecturer.attendance.show', compact(
            'classSchedule',
            'attendances',
            'date',
            'sessionExists',
            'weekNumber',
            'meetingNumber'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAttendanceRequest $request, Attendance $attendance)
    {
        $this->authorize('update', $attendance);

        $validated = $request->validated();

        $this->attendanceRepository->update($attendance, [
            'status' => $validated['status'],
            'remarks' => $validated['remarks'],
        ]);

        return redirect()->route('lecturer.attendance.show', [
                'classSchedule' => $attendance->class_schedule_id,
                'date' => $attendance->date,
            ])
            ->with('success', 'Attendance updated successfully.');
    }

    /**
     * View QR Code without resetting the session time.
     */
    public function viewQR(ClassSchedule $classSchedule, $date)
    {
        $this->authorize('generateQR', $classSchedule);

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return redirect()->back()->with('error', 'Invalid date format.');
        }

        // Get current session data
        $session = $this->sessionRepository->findByClassAndDate($classSchedule->id, $date);

        if (!$session) {
            return redirect()->route('lecturer.attendance.show', [
                'classSchedule' => $classSchedule->id,
                'date' => $date
            ])->with('error', 'Attendance session not found.');
        }

        // Generate QR code
        $qrCode = $this->qrCodeService->generateForAttendance($classSchedule->id, $date);

        return view('lecturer.attendance.view_qr', [
            'classSchedule' => $classSchedule,
            'qrCode' => $qrCode,
            'sessionEndTime' => $session->end_time->format('H:i'),
            'date' => $date,
            'weekNumber' => $session->week,
            'meetingNumber' => $session->meetings
        ]);
    }

    /**
     * Extend the session time for attendance.
     */
    public function extendTime(ExtendTimeRequest $request, ClassSchedule $classSchedule, $date)
    {
        $this->authorize('extendTime', $classSchedule);

        $validated = $request->validated();

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return redirect()->back()->with('error', 'Invalid date format.');
        }

        $session = $this->sessionRepository->findByClassAndDate($classSchedule->id, $date);

        if (!$session) {
            return redirect()->back()->with('error', 'Session not found.');
        }

        if ($session->end_time->isPast()) {
            return redirect()->route('lecturer.attendance.view_qr', [
                'classSchedule' => $classSchedule->id,
                'date' => $date
            ])->with('error', 'Attendance session has already ended. Extension is not allowed.');
        }

        $this->sessionRepository->update($session, [
            'end_time' => $session->end_time->addMinutes((int)$validated['minutes']),
            'is_active' => true
        ]);

        return redirect()->route('lecturer.attendance.view_qr', [
            'classSchedule' => $classSchedule->id,
            'date' => $date
        ])->with('success', "Session extended by {$validated['minutes']} minutes");
    }

    public function getUsedSessions(ClassSchedule $classSchedule)
    {
        $this->authorize('view', $classSchedule);

        // Get all sessions that have been created for this class schedule
        $usedSessions = $this->sessionRepository->getSessionsByClassSchedule($classSchedule->id);

        return response()->json([
            'usedSessions' => $usedSessions->map(function($session) {
                return [
                    'week' => $session->week,
                    'meeting' => $session->meeting
                ];
            })
        ]);
    }
}
