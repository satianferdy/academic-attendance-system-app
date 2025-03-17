<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassSchedule;
use App\Models\SessionAttendance;
use App\Services\Interfaces\AttendanceServiceInterface;
use App\Services\Interfaces\QRCodeServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
        $user = Auth::user();
        $lecturer = $user->lecturer;  // Get the associated lecturer model

        if (!$lecturer) {
            return redirect()->back()->with('error', 'Lecturer profile not found.');
        }

        // dd($lecturer->classSchedules);

        $schedules = ClassSchedule::where('lecturer_id', $lecturer->id)->get();
        return view('lecturer.attendance.index', compact('schedules'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:class_schedules,id',
            'date' => 'required|date|after_or_equal:today',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $classSchedule = ClassSchedule::findOrFail($request->class_id);

        // Authorization check
        if ($classSchedule->lecturer_id != Auth::user()->lecturer->id) {
            return redirect()->back()
                ->with('error', 'You do not have permission to manage this class.');
        }

        // Generate attendance session
        $result = $this->attendanceService->generateSessionAttendance(
            $classSchedule,
            $request->date
        );

        if ($result['status'] === 'error') {
            return redirect()->back()
                ->with('error', $result['message']);
        }

        return redirect()->route('lecturer.attendance.view_qr', [
            'classSchedule' => $classSchedule->id,  // Gunakan ID untuk model binding
            'date' => $request->date
        ]);
    }


    public function show(Request $request, $id)
    {
        $date = $request->query('date', date('Y-m-d'));
        $classSchedule = ClassSchedule::findOrFail($id);

        // Check if the lecturer owns this class
        if ($classSchedule->lecturer_id != Auth::user()->lecturer->id) {
            return redirect()->back()
                ->with('error', 'You do not have permission to view this class.');
        }

        $attendances = Attendance::with('student.user')
            ->where('class_schedule_id', $id)
            ->where('date', $date)
            ->get();

        // check if session exists for the date
        $sessionExists = SessionAttendance::where('class_schedule_id', $id)
            ->where('session_date', $date)
            ->exists();

        return view('lecturer.attendance.show', compact('classSchedule', 'attendances', 'date', 'sessionExists'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        $attendance = Attendance::with('student.user', 'classSchedule')
            ->findOrFail($id);

        // Check if the lecturer owns this class
        if ($attendance->classSchedule->lecturer_id != Auth::user()->lecturer->id) {
            return redirect()->back()
                ->with('error', 'You do not have permission to edit this attendance.');
        }

        return view('lecturer.attendance.edit', compact('attendance'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:present,absent,late,excused',
            'remarks' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $attendance = Attendance::findOrFail($id);

        // Check if the lecturer owns this class
        if ($attendance->classSchedule->lecturer_id != Auth::user()->lecturer->id) {
            return redirect()->back()
                ->with('error', 'You do not have permission to update this attendance.');
        }

        $attendance->update([
            'status' => $request->status,
            'remarks' => $request->remarks,
        ]);

        return redirect()->route('lecturer.attendance.show', [
                'id' => $attendance->class_schedule_id,
                'date' => $attendance->date,
            ])
            ->with('success', 'Attendance updated successfully.');
    }

    /**
     * View QR Code without resetting the session time.
     */
    public function viewQR(ClassSchedule $classSchedule, $date)
    {
        // Validasi format tanggal
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            abort(400, 'Invalid date format');
        }

        // Check lecturer ownership
        if ($classSchedule->lecturer_id != Auth::user()->lecturer->id) {
            abort(403, 'Unauthorized');
        }

        // Get current session data
        $session = SessionAttendance::where('class_schedule_id', $classSchedule->id)
            ->where('session_date', $date)
            ->firstOrFail();

        // Check if session exists
        if (!$session) {
            return redirect()->route('lecturer.attendance.show', [
                'id' => $classSchedule->id,
                'date' => $date
            ])->with('error', 'Attendance session for this date has not been created yet. Please generate a new session first.');
        }

        // Generate QR code
        $qrCode = $this->qrCodeService->generateForAttendance($classSchedule->id, $date);

        return view('lecturer.attendance.view_qr', [
            'classSchedule' => $classSchedule,
            'qrCode' => $qrCode,
            'sessionEndTime' => $session->end_time->format('H:i'),
            'date' => $date // Pass date ke view untuk link
        ]);
    }

    public function extendTime(Request $request, ClassSchedule $classSchedule, $date)
    {
        $validator = Validator::make($request->all(), [
            'minutes' => 'required|in:10,20,30',
        ]);

        // Validasi format tanggal
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            abort(400, 'Invalid date format');
        }

        // Check lecturer ownership
        if ($classSchedule->lecturer_id != Auth::user()->lecturer->id) {
            abort(403, 'Unauthorized');
        }

        $session = SessionAttendance::where('class_schedule_id', $classSchedule->id)
            ->where('session_date', $date)
            ->firstOrFail();

        $currentDateTime = now();
        $sessionDateTime = $session->session_date->setTimeFromTimeString($session->end_time->format('H:i:s'));

        // Check if current date-time is after session end date-time
        if ($currentDateTime > $sessionDateTime) {
            return redirect()->route('lecturer.attendance.view_qr', [
                'classSchedule' => $classSchedule->id,
                'date' => $date
            ])->with('error', 'Attendance session has already ended (past the end time). Extension is not allowed.');
        }

        $session->update([
            'end_time' => $session->end_time->addMinutes((int)$request->minutes),
            'is_active' => true
        ]);

        return redirect()->route('lecturer.attendance.view_qr', [
            'classSchedule' => $classSchedule->id,
            'date' => $date
        ])->with('success', "Session extended by {$request->minutes} minutes");
    }
}
