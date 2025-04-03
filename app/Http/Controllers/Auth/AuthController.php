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
            'username' => 'required|string',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $username = $request->username;
        $password = $request->password;
        $user = null;

        // First try to find a student with matching NIM
        $student = Student::where('nim', $username)->first();
        if ($student) {
            $user = User::find($student->user_id);
        }

        // If not found, try to find a lecturer with matching NIP
        if (!$user) {
            $lecturer = Lecturer::where('nip', $username)->first();
            if ($lecturer) {
                $user = User::find($lecturer->user_id);
            }
        }

        // If still not found, try to find a user with matching email (for admins)
        if (!$user) {
            $user = User::where('email', $username)->first();
        }

        // If user is found and password matches, log them in
        if ($user && Hash::check($password, $user->password)) {
            Auth::login($user);
            $request->session()->regenerate();

            // Redirect based on user role
            $role = $user->role;

            if ($role === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($role === 'lecturer') {
                return redirect()->route('lecturer.dashboard');
            } else {
                return redirect()->route('student.dashboard');
            }
        }

        // Authentication failed
        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
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
