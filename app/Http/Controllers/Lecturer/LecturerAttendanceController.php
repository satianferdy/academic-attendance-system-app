<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
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

        if ($lecturer === null) {
            return redirect()->back()->with('error', 'Lecturer profile not found.');
        }

        // $schedules = $lecturer->classSchedules()->with('course', 'semesters')->get();
        $schedules = $this->classScheduleRepository->getSchedulesByLecturerId($lecturer->id);
        return view('lecturer.attendance.index', compact('schedules'));
    }

    /**
     * Create a new attendance session.
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:class_schedules,id',
            'date' => 'required|date|after_or_equal:today',
            'week' => 'required|integer|min:1|max:24',
            'meetings' => 'required|integer|min:1|max:7',
            'total_hours' => 'required|integer|min:1|max:8',
            'tolerance_minutes' => 'required|in:15,20,30',
        ]);

        $classSchedule = $this->classScheduleRepository->find($validated['class_id']);
        $this->authorize('generateQR', $classSchedule);

        $existingSession = $this->sessionRepository->sessionExistsForDate($classSchedule->id, $validated['date']);
        if ($existingSession) {
            return redirect()->back()->with('error', 'An attendance session already exists for this date.');
        }

        $result = $this->attendanceService->generateSessionAttendance(
            $classSchedule->id,
            $validated['date'],
            $validated['week'],
            $validated['meetings'],
            $validated['total_hours'], // Added total_hours parameter
            $validated['tolerance_minutes'] // Added tolerance_minutes parameter
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

        // Get the specific session attendance record
        $session = $this->sessionRepository->findByClassAndDate($classSchedule->id, $date);

        // Get attendances for this specific session
        $attendances = collect([]);
        if ($session) {
            $attendances = $this->attendanceRepository->findByClassAndDate($classSchedule->id, $session->session_date->format('Y-m-d'));
        }

        // Get cumulative attendance data for all students in this class
        $cumulativeData = [];
        foreach ($attendances as $attendance) {
            $cumulativeData[$attendance->student_id] = $this->attendanceService->getCumulativeAttendance(
                $classSchedule->id,
                $attendance->student_id
            );
        }

        $sessionExists = $session !== null;
        $toleranceMinutes = $session ? $session->tolerance_minutes : 15;
        $totalHours = $session ? $session->total_hours : 4;

        return view('lecturer.attendance.show', compact(
            'session',
            'classSchedule',
            'attendances',
            'date',
            'sessionExists',
            'cumulativeData',
            'toleranceMinutes',
            'totalHours'
        ));
    }

    /**
     * Update attendance with hourly breakdown
     */
    public function update(UpdateAttendanceRequest $request, Attendance $attendance)
    {
        $this->authorize('update', $attendance);

        $validated = $request->validated();

        // Add validation for hourly data
        $totalHours = $request->input('total_hours', 4);
        $totalAttendanceHours =
            $request->input('hours_present', 0) +
            $request->input('hours_absent', 0) +
            $request->input('hours_permitted', 0) +
            $request->input('hours_sick', 0);

        if ($totalAttendanceHours != $totalHours) {
            return redirect()->back()->with('error', 'Total attendance hours must equal total class hours (' . $totalHours . ')');
        }

        $result = $this->attendanceService->updateAttendanceStatus($attendance, [
            'status' => $validated['status'],
            'remarks' => $validated['remarks'],
            'edit_notes' => $validated['edit_notes'] ?? null,
            'hours_present' => $request->input('hours_present', 0),
            'hours_absent' => $request->input('hours_absent', 0),
            'hours_permitted' => $request->input('hours_permitted', 0),
            'hours_sick' => $request->input('hours_sick', 0),
        ]);

        if ($result['status'] === 'success') {
            return redirect()->route('lecturer.attendance.show', [
                'classSchedule' => $attendance->class_schedule_id,
                'date' => $attendance->date,
            ])->with('success', 'Attendance updated successfully.');
        }

        return redirect()->back()->with('error', $result['message']);
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
            'session' => $session,
            'classSchedule' => $classSchedule,
            'qrCode' => $qrCode,
            'date' => $date,
        ]);
    }

    /**
     * Extend the session time for attendance.
     */
    public function extendTime(Request $request, ClassSchedule $classSchedule, $date)
    {
        $this->authorize('extendTime', $classSchedule);

        $request->validate([
            'minutes' => 'required|in:15,20,30',
        ]);

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return redirect()->back()->with('error', 'Invalid date format.');
        }

        $session = $this->sessionRepository->findByClassAndDate($classSchedule->id, $date);

        if (!$session) {
            return redirect()->back()->with('error', 'Session not found.');
        }

        if ($session->end_time->setTimezone(config('app.timezone'))->isPast()) {
            return redirect()->route('lecturer.attendance.view_qr', [
                'classSchedule' => $classSchedule->id,
                'date' => $date
            ])->with('error', 'Attendance session has already ended.');
        }

        // Update tolerance minutes instead of extending end time
        $this->sessionRepository->update($session, [
            'tolerance_minutes' => (int)$request->minutes,
            'is_active' => true
        ]);

        return redirect()->route('lecturer.attendance.view_qr', [
            'classSchedule' => $classSchedule->id,
            'date' => $date
        ])->with('success', "Tolerance time set to {$request->minutes} minutes");
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
