<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\UpdatePasswordRequest;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
    public function index()
    {
        $user = Auth::user();
        $classCount = 0;

        // Get class count for lecturers
        if ($user->role === 'lecturer' && $user->lecturer) {
            $classCount = $user->lecturer->classSchedules()->count();
        }

        return view('profile.index', compact('user', 'classCount'));
    }

    /**
     * Show the form for changing password.
     */
    public function changePassword()
    {
        return view('profile.change-password');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        // Check if current password matches
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Current password is incorrect');
        }

        // Update the password using DB instance
        \Illuminate\Support\Facades\DB::table('users')
            ->where('id', $user->id)
            ->update(['password' => Hash::make($request->password)]);

        return redirect()->route('profile.index')
            ->with('success', 'Password changed successfully');
    }
}
