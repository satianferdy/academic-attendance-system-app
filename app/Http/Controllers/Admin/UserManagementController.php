<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ClassRoom;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\StudyProgram;
use App\Services\Interfaces\UserServiceInterface;

class UserManagementController extends Controller
{
    protected $userService;

    public function __construct(
        UserServiceInterface $userService
    )
    {
        $this->userService = $userService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);

        $userData = $this->userService->getAllUsersByRole();

        return view('admin.user.index', $userData);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', User::class);

        $classrooms = ClassRoom::all();
        $studyPrograms = StudyProgram::all();
        return view('admin.user.create', compact('classrooms', 'studyPrograms'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,student,lecturer',

            // Student validation
            'student_nim' => 'required_if:role,student|string|max:20|unique:students,nim',
            'study_program_id' => 'required_if:role,student|exists:study_programs,id',
            'classroom_id' => 'required_if:role,student|exists:classrooms,id',

            // Lecturer validation
            'lecturer_nip' => 'required_if:role,lecturer|string|max:20|unique:lecturers,nip',
        ], [
            'student_nim.required_if' => 'NIM wajib diisi untuk Student',
            'lecturer_nip.required_if' => 'NIP wajib diisi untuk Lecturer',
            'classroom_id.exists' => 'Kelas yang dipilih tidak valid',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $this->userService->createUser($request->all());
            return redirect()->route('admin.users.index')
                ->with('success', 'User berhasil dibuat');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error creating user: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $classrooms = ClassRoom::all();
        $studyPrograms = StudyProgram::all();
        return view('admin.user.edit', compact('user', 'classrooms', 'studyPrograms'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validationRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ];

        // Add password validation if provided
        if ($request->filled('password')) {
            $validationRules['password'] = 'required|string|min:8|confirmed';
        }

        // Add role-specific validation
        if ($user->role === 'student') {
            $validationRules['nim'] = 'required|string|max:20|unique:students,nim,' . $user->student->id;
            $validationRules['study_program_id'] = 'required|exists:study_programs,id';
            $validationRules['classroom_id'] = 'required|exists:classrooms,id';
        } elseif ($user->role === 'lecturer') {
            $validationRules['nip'] = 'required|string|max:20|unique:lecturers,nip,' . $user->lecturer->id;
        }

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $this->userService->updateUser($user, $request->all());
            return redirect()->route('admin.users.index')
                ->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating user: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        try {
            $this->userService->deleteUser($user);
            return redirect()->route('admin.users.index')
                ->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Error deleting user: ' . $e->getMessage());
        }
    }
}
