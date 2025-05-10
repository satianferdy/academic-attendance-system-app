<?php

namespace App\Providers;

use App\Models\FaceData;
use App\Repositories\Implementations\AttendanceRepository;
use App\Repositories\Implementations\ClassScheduleRepository;
use App\Repositories\Implementations\SessionAttendanceRepository;
use App\Repositories\Interfaces\AttendanceRepositoryInterface;
use App\Repositories\Interfaces\ClassScheduleRepositoryInterface;
use App\Repositories\Interfaces\SessionAttendanceRepositoryInterface;
use App\Services\Implementations\AttendanceService;
use App\Services\Implementations\FaceRecognitionService;
use App\Services\Implementations\QRCodeService;
use App\Services\Implementations\ScheduleService;
use App\Services\Interfaces\AttendanceServiceInterface;
use App\Services\Interfaces\FaceRecognitionServiceInterface;
use App\Services\Interfaces\QRCodeServiceInterface;
use App\Services\Interfaces\ScheduleServiceInterface;
use App\Repositories\Implementations\StudentRepository;
use App\Repositories\Implementations\FaceDataRepository;
use App\Repositories\Interfaces\StudentRepositoryInterface;
use App\Repositories\Interfaces\FaceDataRepositoryInterface;
use App\Repositories\Implementations\UserRepository;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Implementations\UserService;
use App\Services\Interfaces\UserServiceInterface;
use Illuminate\Support\ServiceProvider;


class FaceRecognitionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind repositories
        $this->app->bind(AttendanceRepositoryInterface::class, AttendanceRepository::class);
        $this->app->bind(SessionAttendanceRepositoryInterface::class, SessionAttendanceRepository::class);
        $this->app->bind(ClassScheduleRepositoryInterface::class, ClassScheduleRepository::class);
        $this->app->bind(StudentRepositoryInterface::class, StudentRepository::class);
        $this->app->bind(FaceDataRepositoryInterface::class, FaceDataRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);

        // Bind services
        $this->app->bind(FaceRecognitionServiceInterface::class, FaceRecognitionService::class);
        $this->app->bind(QRCodeServiceInterface::class, QRCodeService::class);
        $this->app->bind(AttendanceServiceInterface::class, AttendanceService::class);
        $this->app->bind(ScheduleServiceInterface::class, ScheduleService::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
