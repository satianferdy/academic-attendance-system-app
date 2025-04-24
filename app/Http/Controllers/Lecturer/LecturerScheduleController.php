<?php

namespace App\Http\Controllers\Lecturer;

use Illuminate\Http\Request;
use App\Models\ClassSchedule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Interfaces\ClassScheduleRepositoryInterface;

class LecturerScheduleController extends Controller
{
    protected $classScheduleRepository;

    public function __construct(ClassScheduleRepositoryInterface $classScheduleRepository)
    {
        $this->classScheduleRepository = $classScheduleRepository;
    }

    public function index()
    {
        $this->authorize('viewAny', ClassSchedule::class);
        // Get the associated lecturer model
        $lecturer = Auth::user()->lecturer;

         $schedules = $this->classScheduleRepository->getSchedulesByLecturerId($lecturer->id);

        return view('lecturer.schedule.index', compact('schedules'));
    }
}
