<?php

namespace App\Http\Controllers\Student;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\ClassSchedule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\Interfaces\QRCodeServiceInterface;
use App\Services\Interfaces\AttendanceServiceInterface;
use App\Http\Requests\Attendance\VerifyAttendanceRequest;
use App\Services\Interfaces\FaceRecognitionServiceInterface;
use App\Repositories\Interfaces\SessionAttendanceRepositoryInterface;

class StudentAttendanceController extends Controller
{
    protected $attendanceService;
    protected $qrCodeService;
    protected $faceRecognitionService;
    protected $sessionRepository;

    public function __construct(
        AttendanceServiceInterface $attendanceService,
        QRCodeServiceInterface $qrCodeService,
        FaceRecognitionServiceInterface $faceRecognitionService,
        SessionAttendanceRepositoryInterface $sessionRepository
    ) {
        $this->attendanceService = $attendanceService;
        $this->qrCodeService = $qrCodeService;
        $this->faceRecognitionService = $faceRecognitionService;
        $this->sessionRepository = $sessionRepository;
    }

    public function index()
    {
        // Ensure user is authorized to view their own attendances
        $student = Auth::user()->student;

        if (!$student) {
            return redirect()->route('login')->with('error', 'Student profile not found.');
        }

        // Get attendances with all related data
        $attendances = $this->attendanceService->getStudentAttendances($student->id);

        // For each attendance, fetch the session data to get week/meeting information
        foreach ($attendances as $attendance) {
            $session = $this->sessionRepository->findByClassAndDate(
                $attendance->class_schedule_id,
                $attendance->date->format('Y-m-d')
            );

            if ($session) {
                // Add week and meeting number to attendance object
                $attendance->week = $session->week;
                $attendance->meeting = $session->meetings;
            }
        }

        return view('student.attendance.index', compact('attendances'));
    }

    public function show(Request $request, string $token)
    {
        // Validate token and get attendance data
        $tokenData = $this->qrCodeService->validateToken($token);
        if (!$tokenData) {
            return abort(404, 'Invalid or expired QR code.');
        }

        $student = Auth::user()->student;
        $classId = $tokenData['class_id'];
        $date = $tokenData['date'];

        // Check if student has registered their face
        if (!$student->face_registered) {
            return redirect()->route('student.face.register', ['token' => $token])
                ->with('warning', 'You need to register your face first.');
        }

        // Check if attendance is already marked
        if ($this->attendanceService->isAttendanceAlreadyMarked($student->id, $classId, $date)) {
            return redirect()->route('student.attendance.index')
                ->with('info', 'You have already marked your attendance for this session.');
        }

        // Get class schedule
        $classSchedule = ClassSchedule::with(['course', 'lecturer.user', 'classroom'])
            ->findOrFail($classId);

        $this->authorize('view', $classSchedule);

        // Check if student is enrolled in this class
        if (!$this->attendanceService->isStudentEnrolled($student->id, $classSchedule->id)) {
            return redirect()->route('student.attendance.index')
                ->with('error', 'You are not enrolled in this class.');
        }

        return view('student.attendance.show', compact('classSchedule', 'token', 'date'));
    }

    public function verify(VerifyAttendanceRequest $request)
    {
        // Validate token
        $tokenData = $this->qrCodeService->validateToken($request->token);
        if (!$tokenData) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired QR code.',
            ], 400);
        }

        $student = Auth::user()->student;
        $nim = $student->nim;
        $classId = $tokenData['class_id'];
        $date = $tokenData['date'];

        // Get the session and check its end time
        $session = $this->sessionRepository->findByClassAndDate($classId, $date);

        $currentTime = now()->setTimezone(config('app.timezone'));
        $sessionEndTime = $session->end_time->setTimezone(config('app.timezone'));
        $sessionDate = Carbon::parse($date)->setTimezone(config('app.timezone'));

        // Add this check to verify session date is not in the past
        if ($currentTime->startOfDay()->isAfter($sessionDate)) {
            return response()->json([
                'status' => 'error',
                'message' => 'This session has expired (session date is in the past).',
            ], 400);
        }

        if (!$session || !$session->is_active || $currentTime > $sessionEndTime) {
            return response()->json([
                'status' => 'error',
                'message' => 'This session has expired or is no longer active.',
            ], 400);
        }

        // Check if class is valid and student is enrolled
        try {
            $classSchedule = ClassSchedule::findOrFail($classId);
            $this->authorize('view', $classSchedule);

            if (!$this->attendanceService->isStudentEnrolled($student->id, $classSchedule->id)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not enrolled in this class.',
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid class schedule.',
            ], 404);
        }

        // Check if attendance already marked
        if ($this->attendanceService->isAttendanceAlreadyMarked($student->id, $classId, $date)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You have already marked your attendance for this session.',
            ], 400);
        }

        // Check if session is active
        if (!$this->attendanceService->isSessionActive($classId, $date)) {
            return response()->json([
                'status' => 'error',
                'message' => 'This session is no longer active.',
            ], 400);
        }

        // Verify face
        $result = $this->faceRecognitionService->verifyFace(
            $request->file('image'),
            $classId,
            $nim
        );

        if ($result['status'] === 'success') {
            // Mark attendance
            $attendanceResult = $this->attendanceService->markAttendance($classId, $student->id, $date);

            if ($attendanceResult['status'] === 'success') {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Attendance verified successfully.',
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => $attendanceResult['message'] ?? 'Failed to mark attendance.',
            ], 400);
        }

        return response()->json([
            'status' => 'error',
            'message' => $result['message'] ?? 'Face verification failed.',
            'code' => $result['code'] ?? 'VERIFICATION_ERROR'
        ], 400);
    }
}
