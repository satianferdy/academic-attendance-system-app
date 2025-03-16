<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\Interfaces\FaceRecognitionServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class FaceRegistrationController extends Controller
{
    protected $faceRecognitionService;

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
        $redirectUrl = $token ? route('student.attendance.show', ['token' => $token]) : route('student.face.index');

        // Hitung sisa gambar yang bisa diambil
        $student = Auth::user()->student;
        $remainingShots = 5; // Default 5 gambar

        return view('student.face.register', compact('redirectUrl', 'remainingShots'));
    }

    // FaceRegistrationController.php
    public function store(Request $request)
    {
        // Validasi request
        $validator = Validator::make($request->all(), [
            'images' => 'required|array|min:5|max:5', // Pastikan 5 gambar
            'images.*' => 'required|image|max:5120', // Setiap gambar harus valid
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 400);
        }

        // Dapatkan student yang sedang login
        $student = Auth::user()->student;
        if (!$student) {
            return response()->json([
                'status' => 'error',
                'message' => 'Student data not found.',
            ], 404);
        }

        $nim = $student->nim;

        // Proses registrasi wajah
        try {
            $result = $this->faceRecognitionService->registerFace(
                $request->file('images'), // Kirim array gambar
                $nim
            );

            if ($result['status'] === 'success') {
                // Update status student
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
            Log::error('Face registration error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred during registration. Please try again.',
            ], 500);
        }
    }

    // FaceRegistrationController.php
    public function validateQuality(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 400);
        }

        try {
            $result = $this->faceRecognitionService->validateQuality(
                $request->file('image')
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Quality check failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
