<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\ExtendTimeRequest;
use App\Http\Requests\Attendance\StoreAttendanceRequest;
use App\Http\Requests\Attendance\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Models\ClassSchedule;
use App\Models\SessionAttendance;
use App\Services\Interfaces\AttendanceServiceInterface;
use App\Services\Interfaces\QRCodeServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LecturerAttendanceController extends Controller
{
    protected $attendanceService;
    protected $qrCodeService;

    public function __construct(
        AttendanceServiceInterface $attendanceService,
        QRCodeServiceInterface $qrCodeService
    ) {
        $this->attendanceService = $attendanceService;
        $this->qrCodeService = $qrCodeService;
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

        $schedules = $lecturer->classSchedules()->with('course')->get();
        return view('lecturer.attendance.index', compact('schedules'));
    }

    /**
     * Create a new attendance session.
     */
    public function create(StoreAttendanceRequest $request)
    {
        $validated = $request->validated();
        $classSchedule = ClassSchedule::findOrFail($validated['class_id']);

        $this->authorize('generateQR', $classSchedule);

        $result = $this->attendanceService->generateSessionAttendance(
            $classSchedule,
            $validated['date']
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

        // Check date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return redirect()->back()->with('error', 'Invalid date format.');
        }

        $attendances = Attendance::with('student.user')
            ->where('class_schedule_id', $classSchedule->id)
            ->where('date', $date)
            ->get();

        $sessionExists = SessionAttendance::where('class_schedule_id', $classSchedule->id)
            ->where('session_date', $date)
            ->exists();

        return view('lecturer.attendance.show', compact('classSchedule', 'attendances', 'date', 'sessionExists'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAttendanceRequest $request, Attendance $attendance)
    {
        $this->authorize('update', $attendance);

        $validated = $request->validated();

        $attendance->update([
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
        $session = SessionAttendance::where('class_schedule_id', $classSchedule->id)
            ->where('session_date', $date)
            ->firstOr(function () use ($classSchedule, $date) {
                return redirect()->route('lecturer.attendance.show', [
                    'classSchedule' => $classSchedule->id,
                    'date' => $date
                ])->with('error', 'Attendance session not found.');
            });

        // Generate QR code
        $qrCode = $this->qrCodeService->generateForAttendance($classSchedule->id, $date);

        return view('lecturer.attendance.view_qr', [
            'classSchedule' => $classSchedule,
            'qrCode' => $qrCode,
            'sessionEndTime' => $session->end_time->format('H:i'),
            'date' => $date
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

        $session = SessionAttendance::where('class_schedule_id', $classSchedule->id)
            ->where('session_date', $date)
            ->firstOrFail();

        if ($session->end_time->isPast()) {
            return redirect()->route('lecturer.attendance.view_qr', [
                'classSchedule' => $classSchedule->id,
                'date' => $date
            ])->with('error', 'Attendance session has already ended. Extension is not allowed.');
        }

        $session->update([
            'end_time' => $session->end_time->addMinutes((int)$validated['minutes']),
            'is_active' => true
        ]);

        return redirect()->route('lecturer.attendance.view_qr', [
            'classSchedule' => $classSchedule->id,
            'date' => $date
        ])->with('success', "Session extended by {$validated['minutes']} minutes");
    }
}
