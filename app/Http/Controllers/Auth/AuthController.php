<?php
// app/Http/Controllers/Auth/AuthController.php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Redirect based on user role
            $user = Auth::user();
            $role = $user->role;

            if ($role === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($role === 'lecturer') {
                return redirect()->route('lecturer.dashboard');
            } else {
                return redirect()->route('student.dashboard');
            }

            // if ($user->isAdmin()) {
            //     return redirect()->route('admin.dashboard');
            // } elseif ($user->isLecturer()) {
            //     return redirect()->route('lecturer.dashboard');
            // } else {
            //     return redirect()->route('student.dashboard');
            // }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:student,lecturer',
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
                'student_department' => 'required|string|max:100',
                'student_faculty' => 'required|string|max:100',
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
                'department' => $request->student_department,
                'faculty' => $request->student_faculty,
                'face_registered' => false,
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'nip' => 'required|string|max:20|unique:lecturers',
                'lecturer_department' => 'required|string|max:100',
                'lecturer_faculty' => 'required|string|max:100',
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
                'department' => $request->lecturer_department,
                'faculty' => $request->lecturer_faculty,
            ]);
        }

        // Log in the user
        Auth::login($user);

        return redirect()->route($user->role . '.dashboard');
    }
}
