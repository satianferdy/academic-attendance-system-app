<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\Interfaces\FaceRecognitionServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Face\RegistrationRequest;
use App\Http\Requests\Face\QualityValidationRequest;

class FaceRegistrationController extends Controller
{
    protected $faceRecognitionService;
    protected const REQUIRED_IMAGES = 5;

    public function __construct(FaceRecognitionServiceInterface $faceRecognitionService)
    {
        $this->faceRecognitionService = $faceRecognitionService;
    }

    public function index()
    {
        $student = Auth::user()->student;
        return view('student.face.index', compact('student'));
    }

    public function register(Request $request, $token = null)
    {
        $student = Auth::user()->student;
        $this->authorize('update', $student);

        $redirectUrl = $token
            ? route('student.attendance.show', ['token' => $token])
            : route('student.face.index');

        $remainingShots = self::REQUIRED_IMAGES;

        return view('student.face.register', compact('redirectUrl', 'remainingShots'));
    }

    public function store(RegistrationRequest $request)
    {
        $student = Auth::user()->student;
        $this->authorize('update', $student);

        if (!$student) {
            return response()->json([
                'status' => 'error',
                'message' => 'Student data not found.',
            ], 404);
        }

        $nim = $student->nim;

        try {
            $result = $this->faceRecognitionService->registerFace(
                $request->file('images'),
                $nim
            );

            if ($result['status'] === 'success') {
                // Update student status
                $student->update(['face_registered' => true]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Face registered successfully.',
                    'redirect_url' => $request->redirect_url ?? route('student.face.index'),
                    'data' => $result['data']
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => $result['message'] ?? 'Face registration failed.',
            ], 400);

        } catch (\Exception $e) {
            Log::error('Face registration error', [
                'error' => $e->getMessage(),
                'student_id' => $student->id,
                'nim' => $nim
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred during registration. Please try again.',
            ], 500);
        }
    }

    public function validateQuality(QualityValidationRequest $request)
    {
        try {
            $result = $this->faceRecognitionService->validateQuality(
                $request->file('image')
            );

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Face quality validation error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Quality check failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
