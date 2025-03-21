<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassSchedule;
use App\Models\SessionAttendance;
use App\Models\Student;
use App\Services\Interfaces\AttendanceServiceInterface;
use App\Services\Interfaces\FaceRecognitionServiceInterface;
use App\Services\Interfaces\QRCodeServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Attendance\VerifyAttendanceRequest;

class StudentAttendanceController extends Controller
{
    protected $attendanceService;
    protected $qrCodeService;
    protected $faceRecognitionService;

    public function __construct(
        AttendanceServiceInterface $attendanceService,
        QRCodeServiceInterface $qrCodeService,
        FaceRecognitionServiceInterface $faceRecognitionService
    ) {
        $this->attendanceService = $attendanceService;
        $this->qrCodeService = $qrCodeService;
        $this->faceRecognitionService = $faceRecognitionService;
    }

    public function index()
    {
        $this->authorize('viewAny', Attendance::class);
        $student = Auth::user()->student;
        $attendances = Attendance::with(['classSchedule.course', 'classSchedule.lecturer.user'])
            ->where('student_id', $student->id)
            ->orderBy('date', 'desc')
            ->paginate(10);

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
        if ($this->isAttendanceAlreadyMarked($student->id, $classId, $date)) {
            return redirect()->route('student.attendance.index')
                ->with('info', 'You have already marked your attendance for this session.');
        }

        // Get class schedule
        $classSchedule = ClassSchedule::with(['course', 'lecturer.user', 'classroom'])
            ->findOrFail($classId);

        $this->authorize('view', $classSchedule);

        // Check if student is enrolled in this class
        if (!$this->isStudentEnrolled($student, $classSchedule)) {
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

        // Check if class is valid and student is enrolled
        try {
            $classSchedule = ClassSchedule::findOrFail($classId);
            $this->authorize('view', $classSchedule);

            if (!$this->isStudentEnrolled($student, $classSchedule)) {
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
        if ($this->isAttendanceAlreadyMarked($student->id, $classId, $date)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You have already marked your attendance for this session.',
            ], 400);
        }

        // Check if session is active
        if (!$this->isSessionActive($classId, $date)) {
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
        ], 400);
    }

    private function isStudentEnrolled(Student $student, ClassSchedule $classSchedule): bool
    {
        return $classSchedule->classroom_id == $student->classroom_id;
    }

    private function isAttendanceAlreadyMarked(int $studentId, int $classId, string $date): bool
    {
        return Attendance::where('class_schedule_id', $classId)
            ->where('student_id', $studentId)
            ->where('date', $date)
            ->where('status', 'present')
            ->exists();
    }

    private function isSessionActive(int $classId, string $date): bool
    {
        return SessionAttendance::where('class_schedule_id', $classId)
            ->where('session_date', $date)
            ->where('is_active', true)
            ->exists();
    }
}
