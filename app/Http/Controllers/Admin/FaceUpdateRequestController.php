<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FaceUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FaceUpdateRequestController extends Controller
{
    /**
     * Display a listing of face update requests.
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');

        $requests = FaceUpdateRequest::with(['student.user', 'student.faceData'])
            ->when($status, function ($query, $status) {
                if ($status !== 'all') {
                    $query->where('status', $status);
                }
            })
            ->latest()
            ->paginate(10);

        return view('admin.face-requests.index', compact('requests', 'status'));
    }

    /**
     * Approve a face update request
     */
    public function approve(Request $request, FaceUpdateRequest $faceRequest)
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $faceRequest->update([
            'status' => 'approved',
            'admin_notes' => $validated['admin_notes'] ?? null,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('admin.face-requests.index')
            ->with('success', 'Face update request has been approved.');
    }

    /**
     * Reject a face update request
     */
    public function reject(Request $request, FaceUpdateRequest $faceRequest)
    {
        $validated = $request->validate([
            'admin_notes' => 'required|string|max:500',
        ]);

        $faceRequest->update([
            'status' => 'rejected',
            'admin_notes' => $validated['admin_notes'],
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('admin.face-requests.index')
            ->with('success', 'Face update request has been rejected.');
    }
}
