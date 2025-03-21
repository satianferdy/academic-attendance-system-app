<?php

namespace App\Providers;

use App\Services\Implementations\AttendanceService;
use App\Services\Implementations\FaceRecognitionService;
use App\Services\Implementations\QRCodeService;
use App\Services\Implementations\ScheduleService;
use App\Services\Interfaces\AttendanceServiceInterface;
use App\Services\Interfaces\FaceRecognitionServiceInterface;
use App\Services\Interfaces\QRCodeServiceInterface;
use App\Services\Interfaces\ScheduleServiceInterface;
use Illuminate\Support\ServiceProvider;


class FaceRecognitionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(FaceRecognitionServiceInterface::class, FaceRecognitionService::class);
        $this->app->bind(QRCodeServiceInterface::class, QRCodeService::class);
        $this->app->bind(AttendanceServiceInterface::class, AttendanceService::class);
        $this->app->bind(ScheduleServiceInterface::class, ScheduleService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
