<?php

namespace App\Http\Controllers\Student;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\ClassSchedule;

class StudentScheduleController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', ClassSchedule::class);

        $user = Auth::user();

        // Pastikan user memiliki student
        if (!$user->student) {
            return redirect()->back()->with('error', 'Student data not found.');
        }

        $student = $user->student;

        // Pastikan student memiliki classroom
        if (!$student->classroom) {
            return redirect()->back()->with('error', 'Classroom not assigned to student.');
        }

        $classroom = $student->classroom;

        // Ambil jadwal berdasarkan classroom_id
        $schedules = ClassSchedule::where('classroom_id', $classroom->id)
            ->with(['course', 'lecturer'])
            ->get();

        return view('student.schedule.index', compact('schedules', 'classroom'));
    }
}
