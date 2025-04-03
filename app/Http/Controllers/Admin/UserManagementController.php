<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;
use App\Models\Lecturer;
use App\Models\ClassRoom;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get all users with their relationships
        $users = User::with(['student', 'lecturer'])->get();

        // Filter users by role
        $admins = $users->where('role', 'admin');
        $lecturers = $users->where('role', 'lecturer');
        $students = $users->where('role', 'student');

        return view('admin.user.index', compact('users', 'admins', 'lecturers', 'students'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', User::class);

        $classrooms = ClassRoom::all();
        return view('admin.user.create', compact('classrooms'));
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
            'student_department' => 'required_if:role,student|string|max:100',
            'student_faculty' => 'required_if:role,student|string|max:100',
            'classroom_id' => 'required_if:role,student|exists:classrooms,id',

            // Lecturer validation
            'lecturer_nip' => 'required_if:role,lecturer|string|max:20|unique:lecturers,nip',
            'lecturer_department' => 'required_if:role,lecturer|string|max:100',
            'lecturer_faculty' => 'required_if:role,lecturer|string|max:100',
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

        // Proses pembuatan user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        // Proses data sesuai role
        if ($request->role === 'student') {
            Student::create([
                'user_id' => $user->id,
                'nim' => $request->student_nim,
                'department' => $request->student_department,
                'faculty' => $request->student_faculty,
                'classroom_id' => $request->classroom_id,
            ]);
        } elseif ($request->role === 'lecturer') {
            Lecturer::create([
                'user_id' => $user->id,
                'nip' => $request->lecturer_nip,
                'department' => $request->lecturer_department,
                'faculty' => $request->lecturer_faculty,
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dibuat');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $classrooms = ClassRoom::all();
        return view('admin.user.edit', compact('user', 'classrooms'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Update password if provided
        if ($request->filled('password')) {
            $validator = Validator::make($request->all(), [
                'password' => 'required|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        // Update student-specific fields if user is a student
        if ($user->role === 'student' && $user->student) {
            $validator = Validator::make($request->all(), [
                'nim' => 'required|string|max:20|unique:students,nim,' . $user->student->id,
                'department' => 'required|string|max:100',
                'faculty' => 'required|string|max:100',
                'classroom_id' => 'required|exists:classrooms,id',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $user->student->update([
                'nim' => $request->nim,
                'department' => $request->department,
                'faculty' => $request->faculty,
                'classroom_id' => $request->classroom_id,
            ]);
        }

        // Update lecturer-specific fields if user is a lecturer
        if ($user->role === 'lecturer' && $user->lecturer) {
            $validator = Validator::make($request->all(), [
                'nip' => 'required|string|max:20|unique:lecturers,nip,' . $user->lecturer->id,
                'department' => 'required|string|max:100',
                'faculty' => 'required|string|max:100',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $user->lecturer->update([
                'nip' => $request->nip,
                'department' => $request->department,
                'faculty' => $request->faculty,
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
