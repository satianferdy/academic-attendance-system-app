<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;
use App\Models\Lecturer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = User::paginate(10);
        return view('admin.user.index', compact('user'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.user.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,student,lecturer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        // Create role-specific profile
        if ($request->role === 'student') {
            $validator = Validator::make($request->all(), [
                'nim' => 'required|string|max:20|unique:students',
                'department' => 'required|string|max:100',
                'faculty' => 'required|string|max:100',
            ]);

            if ($validator->fails()) {
                $user->delete();
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            Student::create([
                'user_id' => $user->id,
                'nim' => $request->nim,
                'department' => $request->department,
                'faculty' => $request->faculty,
                'face_registered' => false,
            ]);
        } elseif ($request->role === 'lecturer') {
            $validator = Validator::make($request->all(), [
                'nip' => 'required|string|max:20|unique:lecturers',
                'department' => 'required|string|max:100',
                'faculty' => 'required|string|max:100',
            ]);

            if ($validator->fails()) {
                $user->delete();
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            Lecturer::create([
                'user_id' => $user->id,
                'nip' => $request->nip,
                'department' => $request->department,
                'faculty' => $request->faculty,
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');


    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('admin.user.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
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

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
