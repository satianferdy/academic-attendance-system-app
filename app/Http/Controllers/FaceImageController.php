<?php

namespace App\Http\Controllers;

use App\Models\FaceData;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class FaceImageController extends Controller
{
    /**
     * Display the stored face image
     *
     * @param Request $request
     * @param string $studentId
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $studentId)
    {
        // Authorize the request - only admin and authorized users should access
        $this->authorize('viewFaceImages', Student::class);

        // Find the student's face data
        $student = Student::findOrFail($studentId);
        $faceData = $student->faceData;

        if (!$faceData || !$faceData->image_path) {
            abort(404, 'Face image not found');
        }

        // The image path is stored as JSON string in the database, decode it
        $imagePath = json_decode($faceData->image_path);

        // Check if the image exists
        if (!Storage::exists($imagePath)) {
            abort(404, 'Face image file not found');
        }

        // Get the file contents
        $file = Storage::get($imagePath);
        $type = Storage::mimeType($imagePath);

        // Return the image with proper headers
        return Response::make($file, 200, [
            'Content-Type' => $type,
            'Content-Disposition' => 'inline; filename="face-image.jpg"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }
}
