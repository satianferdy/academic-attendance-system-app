<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\ClassScheduleController;
use App\Http\Controllers\Lecturer\LecturerAttendanceController;
use App\Http\Controllers\Student\StudentAttendanceController;
use App\Http\Controllers\Student\FaceRegistrationController;
use App\Http\Controllers\Admin\AttendanceListController;
use App\Http\Controllers\Lecturer\LecturerScheduleController;
use App\Http\Controllers\Student\StudentScheduleController;

Route::get('/', function () {
    return view('auth.login');
});

// Authentication routes
Route::group(['middleware' => 'guest'], function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Admin routes
Route::group(['middleware' => ['auth', 'role:admin'], 'prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // schedule check availability
    Route::get('schedules/check-availability', [ClassScheduleController::class, 'checkAvailability'])->name('schedules.check-availability');

    // Class schedules
    Route::resource('schedules', ClassScheduleController::class);

    // User management routes
    Route::resource('users', UserManagementController::class);

    // Attendance list
    Route::get('attendance', [AttendanceListController::class, 'index'])->name('attendance.index');
    Route::post('attendance/update-status', [AttendanceListController::class, 'updateStatus'])->name('attendance.update-status');
});

// Lecturer routes
Route::group(['middleware' => ['auth', 'role:lecturer'], 'prefix' => 'lecturer', 'as' => 'lecturer.'], function () {
    Route::get('/dashboard', function () {
        return view('lecturer.dashboard');
    })->name('dashboard');

    //schedule
    Route::get('/schedule', [LecturerScheduleController::class, 'index'])->name('schedule.index');

    // Attendance Management
    Route::get('/attendance', [LecturerAttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/create', [LecturerAttendanceController::class, 'create'])->name('attendance.create');
    Route::get('/attendance/{id}', [LecturerAttendanceController::class, 'show'])->name('attendance.show');
    Route::get('/attendance/{id}/edit', [LecturerAttendanceController::class, 'edit'])->name('attendance.edit');
    Route::put('/attendance/{id}', [LecturerAttendanceController::class, 'update'])->name('attendance.update');

    // Ganti rute view_qr dan extend_time dengan:
    Route::get('/attendance/view-qr/{classSchedule}/{date}', [LecturerAttendanceController::class, 'viewQR'])
    ->name('attendance.view_qr')
    ->where('date', '\d{4}-\d{2}-\d{2}'); // Validasi format tanggal YYYY-MM-DD
    Route::post('/attendance/extend-time/{classSchedule}/{date}', [LecturerAttendanceController::class, 'extendTime'])
    ->name('attendance.extend_time');

});

// Student routes
Route::group(['middleware' => ['auth', 'role:student'], 'prefix' => 'student', 'as' => 'student.'], function () {
    Route::get('/dashboard', function () {
        return view('student.dashboard');
    })->name('dashboard');

    // Schedule
    Route::get('/schedule', [StudentScheduleController::class, 'index'])->name('schedule.index');

    // Attendance
    Route::get('/attendance', [StudentAttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/{token}', [StudentAttendanceController::class, 'show'])->name('attendance.show');
    Route::post('/attendance/verify', [StudentAttendanceController::class, 'verify'])->name('attendance.verify');

    // Face registration
    Route::get('/face', [FaceRegistrationController::class, 'index'])->name('face.index');
    Route::get('/face/register/{token?}', [FaceRegistrationController::class, 'register'])->name('face.register');
    Route::post('/face/store', [FaceRegistrationController::class, 'store'])->name('face.store');

    Route::post('/validate-face-quality', [FaceRegistrationController::class, 'validateQuality'])->name('face.validate-quality');
});
