<?php
// app/Http/Controllers/NonDI/DirectFaceRegistrationController.php

namespace App\Http\Controllers\NonDI;

use App\Http\Controllers\Controller;
use App\Models\FaceUpdateRequest;
use App\Services\NonDI\DirectFaceRecognitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Face\RegistrationRequest;
use App\Http\Requests\Face\QualityValidationRequest;
use App\Models\Student;

class DirectFaceRegistrationController extends Controller
{
    protected const REQUIRED_IMAGES = 5;

    public function register(Request $request, $token = null)
    {
        $student = Auth::user()->student;
        $this->authorize('update', $student);

        if ($student->face_registered) {
            $approvedRequest = FaceUpdateRequest::where('student_id', $student->id)
                ->where('status', 'approved')
                ->first();

            if (!$approvedRequest) {
                return redirect()->route('student.face.index')
                    ->with('error', 'You have already registered your face. To update it, you need an approved update request.');
            }
        }

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
        $isUpdate = $request->input('is_update', false);
        $updateRequestId = $request->input('update_request_id');

        try {
            // Direct instantiation - no dependency injection
            $faceService = new DirectFaceRecognitionService();

            $result = $faceService->registerFace(
                $request->file('images'),
                $nim
            );

            if ($result['status'] === 'success') {
                if ($isUpdate && $updateRequestId) {
                    $updateRequest = FaceUpdateRequest::where('id', $updateRequestId)
                        ->where('student_id', $student->id)
                        ->where('status', 'approved')
                        ->first();

                    if ($updateRequest) {
                        $updateRequest->update([
                            'status' => 'completed',
                            'admin_notes' => ($updateRequest->admin_notes ? $updateRequest->admin_notes . ' | ' : '') . 'Update completed on ' . now()->format('Y-m-d H:i:s'),
                        ]);
                    }
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'Face ' . ($isUpdate ? 'updated' : 'registered') . ' successfully.',
                    'redirect_url' => $request->redirect_url ?? route('student.face.index'),
                    'data' => $result['data']
                ]);
            }

            $errorCode = $result['code'] ?? null;

            return response()->json([
                'status' => 'error',
                'message' => $result['message'] ?? 'Face registration failed.',
                'code' => $errorCode
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred during registration. Please try again.',
                'code' => 'SYSTEM_ERROR'
            ], 500);
        }
    }

    public function validateQuality(QualityValidationRequest $request)
    {
        try {
            // Direct instantiation - no dependency injection
            $faceService = new DirectFaceRecognitionService();

            $result = $faceService->validateQuality(
                $request->file('image')
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Quality check failed: ' . $e->getMessage(),
                'code' => 'VALIDATION_ERROR'
            ], 500);
        }
    }
}
