<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\ClassScheduleController;

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
});

// Lecturer routes
Route::group(['middleware' => ['auth', 'role:lecturer'], 'prefix' => 'lecturer', 'as' => 'lecturer.'], function () {
    Route::get('/dashboard', function () {
        return view('lecturer.dashboard');
    })->name('dashboard');
});

// Student routes
Route::group(['middleware' => ['auth', 'role:student'], 'prefix' => 'student', 'as' => 'student.'], function () {
    Route::get('/dashboard', function () {
        return view('student.dashboard');
    })->name('dashboard');
});
