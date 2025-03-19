<?php

namespace App\Http\Controllers\Lecturer;

use Illuminate\Http\Request;
use App\Models\ClassSchedule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LecturerScheduleController extends Controller
{
    public function index() {

        $user = Auth::user();
        $lecturer = $user->lecturer;  // Get the associated lecturer model

        // get class schedules with course, classroom, and lecturer data
        $schedules = ClassSchedule::where('lecturer_id', $lecturer->id)
            ->with(['course', 'classroom', 'lecturer'])
            ->get();
        // dd($schedules);

        return view('lecturer.schedule.index', compact('schedules'));
    }
}
