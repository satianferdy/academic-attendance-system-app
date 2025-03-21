<?php

namespace App\Providers;

use App\Models\Attendance;
use App\Models\ClassSchedule;
use App\Models\User;
use App\Models\Student;
use App\Policies\ClassSchedulePolicy;
use App\Policies\AttendancePolicy;
use App\Policies\UserPolicy;
use App\Policies\StudentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        ClassSchedule::class => ClassSchedulePolicy::class,
        Attendance::class => AttendancePolicy::class,
        User::class => UserPolicy::class,
        Student::class => StudentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
