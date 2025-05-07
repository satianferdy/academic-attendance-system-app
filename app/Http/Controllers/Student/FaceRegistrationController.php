<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\FaceUpdateRequest;
use App\Services\Interfaces\FaceRecognitionServiceInterface;
use App\Repositories\Interfaces\StudentRepositoryInterface;
use App\Repositories\Interfaces\FaceDataRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Face\RegistrationRequest;
use App\Http\Requests\Face\QualityValidationRequest;

class FaceRegistrationController extends Controller
{
    protected $faceRecognitionService;
    protected $studentRepository;
    protected $faceDataRepository;
    protected const REQUIRED_IMAGES = 5;

    public function __construct(
        FaceRecognitionServiceInterface $faceRecognitionService,
        StudentRepositoryInterface $studentRepository,
        FaceDataRepositoryInterface $faceDataRepository
    ) {
        $this->faceRecognitionService = $faceRecognitionService;
        $this->studentRepository = $studentRepository;
        $this->faceDataRepository = $faceDataRepository;
    }

    public function index()
    {
        $student = Auth::user()->student;

        // Get pending face update request if any
        $pendingRequest = FaceUpdateRequest::where('student_id', $student->id)
            ->where('status', 'pending')
            ->first();

        // Get approved request if any
        $approvedRequest = FaceUpdateRequest::where('student_id', $student->id)
            ->where('status', 'approved')
            ->latest()
            ->first();

        // Get rejected request if any
        $rejectedRequest = FaceUpdateRequest::where('student_id', $student->id)
            ->where('status', 'rejected')
            ->latest()
            ->first();

        return view('student.face.index', compact('student', 'pendingRequest', 'approvedRequest', 'rejectedRequest'));
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

    /**
     * Show the face update form for approved requests
     */
    public function update(Request $request, $updateRequestId)
    {
        $student = Auth::user()->student;
        $this->authorize('update', $student);

        // Find the update request
        $updateRequest = FaceUpdateRequest::findOrFail($updateRequestId);

        // Check if request belongs to the student and is approved
        if ($updateRequest->student_id !== $student->id || !$updateRequest->isApproved()) {
            return redirect()->route('student.face.index')
                ->with('error', 'Invalid or unauthorized face update request.');
        }

        $redirectUrl = route('student.face.index');
        $remainingShots = self::REQUIRED_IMAGES;
        $isUpdate = true;

        return view('student.face.register', compact('redirectUrl', 'remainingShots', 'isUpdate', 'updateRequest'));
    }

    /**
     * Handle face update request submission
     */
    public function storeRequest(Request $request)
    {
        $student = Auth::user()->student;

        // Validate the request
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        // Check if student has face registered
        if (!$student->face_registered) {
            return redirect()->route('student.face.index')
                ->with('error', 'You must register your face first before requesting an update.');
        }

        // Check if there's already a pending request
        $pendingRequest = FaceUpdateRequest::where('student_id', $student->id)
            ->where('status', 'pending')
            ->exists();

        if ($pendingRequest) {
            return redirect()->route('student.face.index')
                ->with('error', 'You already have a pending face update request.');
        }

        // Create the update request
        FaceUpdateRequest::create([
            'student_id' => $student->id,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return redirect()->route('student.face.index')
            ->with('success', 'Your face update request has been submitted and is awaiting approval.');
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
            $result = $this->faceRecognitionService->registerFace(
                $request->file('images'),
                $nim
            );

            if ($result['status'] === 'success') {
                // Update student status
                $this->studentRepository->updateFaceRegistered($student->id, true);

               // If this was an update from an approved request, mark it as completed
                if ($isUpdate && $updateRequestId) {
                    $updateRequest = FaceUpdateRequest::where('id', $updateRequestId)
                        ->where('student_id', $student->id)
                        ->where('status', 'approved')
                        ->first();

                    if ($updateRequest) {
                        // Change this part - add a new 'completed' status instead of keeping it 'approved'
                        $updateRequest->update([
                            'status' => 'completed', // Change from 'approved' to 'completed'
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

            // If we have a specific error code, include it in the response
            $errorCode = $result['code'] ?? null;

            return response()->json([
                'status' => 'error',
                'message' => $result['message'] ?? 'Face registration failed.',
                'code' => $errorCode
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
                'code' => 'SYSTEM_ERROR'
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
                'message' => 'Quality check failed: ' . $e->getMessage(),
                'code' => 'VALIDATION_ERROR'
            ], 500);
        }
    }
}
