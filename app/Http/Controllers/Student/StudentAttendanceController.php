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
use Illuminate\Support\Facades\Validator;

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
        $student = Auth::user()->student;
        $attendances = Attendance::with(['classSchedule.course', 'classSchedule.lecturer.user'])
            ->where('student_id', $student->id)
            ->orderBy('date', 'desc')
            ->paginate(10);

        return view('student.attendance.index', compact('attendances'));
    }

    public function show(Request $request, $token)
    {
        // Validate token
        $data = $this->qrCodeService->validateToken($token);

        if (!$data) {
            return abort(404, 'Invalid or expired QR code.');
        }

        $student = Auth::user()->student;

        // Check if student has registered their face
        if (!$student->face_registered) {
            return redirect()->route('student.face.register', ['token' => $token])
                ->with('warning', 'You need to register your face first.');
        }

        $classId = $data['class_id'];
        $date = $data['date'];

        $classSchedule = ClassSchedule::with(['course', 'lecturer.user', 'classroom'])
            ->findOrFail($classId);

        // Check if student is enrolled in this class
        if ($classSchedule->classroom_id != $student->classroom_id) {
            return redirect()->route('student.attendance.index')
                ->with('error', 'You are not enrolled in this class.');
        }

        return view('student.attendance.show', compact('classSchedule', 'token', 'date'));
    }

    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'image' => 'required|image|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 400);
        }

        // Validate token
        $data = $this->qrCodeService->validateToken($request->token);

        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired QR code.',
            ], 400);
        }

        $student = Auth::user()->student;
        $nim = $student->nim;
        $classId = $data['class_id'];

        // Check if student is enrolled in this class
        $classSchedule = ClassSchedule::findOrFail($classId);
        if ($classSchedule->classroom_id != $student->classroom_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not enrolled in this class.',
            ], 403);
        }

        // Check if session is active
        $session = SessionAttendance::where('class_schedule_id', $classId)
            ->where('session_date', $data['date'])
            ->where('is_active', true)
            ->first();

        if (!$session) {
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
            $this->attendanceService->markAttendance($classId, $student->id, $data['date']);

            return response()->json([
                'status' => 'success',
                'message' => 'Attendance verified successfully.',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => $result['message'] ?? 'Face verification failed.',
        ], 400);
    }
}
