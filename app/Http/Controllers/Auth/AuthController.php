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
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        if (RateLimiter::tooManyAttempts('login-attempts:'.$request->ip(), 5)) {
            $seconds = RateLimiter::availableIn('login-attempts:'.$request->ip());

            return redirect()->back()
                ->withInput($request->only('username'))
                ->withErrors(['username' => "Too many login attempts. Please try again in {$seconds} seconds."]);
        }

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

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
                    ? back()->with(['status' => __($status)])
                    : back()->withErrors(['email' => __($status)]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withErrors(['email' => [__($status)]]);
    }
}
