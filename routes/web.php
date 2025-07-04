<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\FaceImageController;
use App\Http\Controllers\Admin\ClassScheduleController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AttendanceDataController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\FaceUpdateRequestController;
use App\Http\Controllers\Student\StudentScheduleController;
use App\Http\Controllers\Student\FaceRegistrationController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Lecturer\LecturerScheduleController;
use App\Http\Controllers\Student\StudentAttendanceController;
use App\Http\Controllers\Lecturer\LecturerDashboardController;
use App\Http\Controllers\Lecturer\LecturerAttendanceController;
use App\Http\Controllers\NonDI\DirectFaceRegistrationController;
use App\Http\Controllers\Lecturer\LecturerAttendanceDataController;

Route::get('/', function () {
    return view('auth.login');
});

// Authentication routes
Route::group(['middleware' => 'guest'], function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    // Add these to your routes/web.php
    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])
        ->name('password.email');
    Route::get('/reset-password/{token}', function ($token) {
        return view('auth.reset-password', ['token' => $token]);
    })->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])
        ->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Profile routes - accessible to all authenticated users
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');
    Route::post('/profile/update-password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');

    Route::get('/face-images/{studentId}', [FaceImageController::class, 'show'])
    ->name('face-images.show');
});
// Admin routes
Route::group(['middleware' => ['auth', 'role:admin'], 'prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // schedule check availability
    Route::get('schedules/check-availability', [ClassScheduleController::class, 'checkAvailability'])->name('schedules.check-availability');

    // Class schedules
    Route::resource('schedules', ClassScheduleController::class);

    // User management routes
    Route::resource('users', UserManagementController::class);

    // Attendance management routes
    Route::get('attendance', [AttendanceDataController::class, 'index'])->name('attendance.index');
    Route::get('attendance/session/{session}', [AttendanceDataController::class, 'editSession'])->name('attendance.edit-session');
    Route::post('attendance/update-status', [AttendanceDataController::class, 'updateStatus'])->name('attendance.update-status');

    // Face update request management routes
    Route::get('/face-requests', [FaceUpdateRequestController::class, 'index'])->name('face-requests.index');
    Route::post('/face-requests/{faceRequest}/approve', [FaceUpdateRequestController::class, 'approve'])->name('face-requests.approve');
    Route::post('/face-requests/{faceRequest}/reject', [FaceUpdateRequestController::class, 'reject'])->name('face-requests.reject');
});

// Lecturer routes
Route::group(['middleware' => ['auth', 'role:lecturer'], 'prefix' => 'lecturer', 'as' => 'lecturer.'], function () {
    Route::get('/dashboard', [LecturerDashboardController::class, 'index'])->name('dashboard');

    //schedule
    Route::get('/schedule', [LecturerScheduleController::class, 'index'])->name('schedule.index');

    // Attendance Management
    Route::get('/attendance', [LecturerAttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/create', [LecturerAttendanceController::class, 'create'])->name('attendance.create');

    // QR and extend time routes - MOVE THESE BEFORE THE GENERAL ROUTES
    Route::get('/attendance/view-qr/{classSchedule}/{date}', [LecturerAttendanceController::class, 'viewQR'])
        ->name('attendance.view_qr')
        ->where('date', '\d{4}-\d{2}-\d{2}');
    Route::post('/attendance/extend-time/{classSchedule}/{date}', [LecturerAttendanceController::class, 'extendTime'])
        ->name('attendance.extend_time');
    // Inside lecturer routes group
    Route::get('/attendance/get-used-sessions/{classSchedule}', [LecturerAttendanceController::class, 'getUsedSessions'])
    ->name('attendance.get-used-sessions');

    // General routes - THESE COME AFTER THE SPECIFIC ONES
    Route::get('/attendance/{classSchedule}', [LecturerAttendanceController::class, 'show'])->name('attendance.show');
    Route::get('/attendance/{classSchedule}/edit', [LecturerAttendanceController::class, 'edit'])->name('attendance.edit');
    Route::put('/attendance/update/{attendance}', [LecturerAttendanceController::class, 'update'])->name('attendance.update');

    Route::get('attendance-data', [LecturerAttendanceDataController::class, 'index'])->name('attendance-data.index');
    Route::get('attendance-data/session/{session}', [LecturerAttendanceDataController::class, 'editSession'])->name('attendance-data.edit-session');
    Route::post('attendance-data/update-status', [LecturerAttendanceDataController::class, 'updateStatus'])->name('attendance-data.update-status');
});

// Student routes
Route::group(['middleware' => ['auth', 'role:student'], 'prefix' => 'student', 'as' => 'student.'], function () {
    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');

    // Schedule
    Route::get('/schedule', [StudentScheduleController::class, 'index'])->name('schedule.index');

    // Attendance
    Route::get('/attendance', [StudentAttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/{token}', [StudentAttendanceController::class, 'show'])->name('attendance.show');
    Route::post('/attendance/verify', [StudentAttendanceController::class, 'verify'])->name('attendance.verify');

    // Face management
    Route::get('/face', [FaceRegistrationController::class, 'index'])->name('face.index');
    Route::get('/face/register/{token?}', [FaceRegistrationController::class, 'register'])->name('face.register');
    Route::post('/face/store', [FaceRegistrationController::class, 'store'])->name('face.store');
    Route::post('/face/store-request', [FaceRegistrationController::class, 'storeRequest'])->name('face.store-request');
    Route::get('/face/update/{updateRequestId}', [FaceRegistrationController::class, 'update'])->name('face.update');
    Route::post('/validate-face-quality', [FaceRegistrationController::class, 'validateQuality'])->name('face.validate-quality');
    //non DI face registration
    Route::post('/face/store-non-di', [DirectFaceRegistrationController::class, 'store'])
    ->name('student.face.store-non-di');
});
