<?php

namespace App\Policies;

use App\Models\SessionAttendance;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SessionAttendancePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the session.
     */
    public function view(User $user, SessionAttendance $sessionAttendance): bool
    {
        // Admin can view any session
        if ($user->isAdmin()) {
            return true;
        }

        // Lecturer can view sessions for their classes
        if ($user->isLecturer() && $user->lecturer) {
            return $user->lecturer->id === $sessionAttendance->classSchedule->lecturer_id;
        }

        // Students can view sessions for classes they're enrolled in
        if ($user->isStudent() && $user->student) {
            return $user->student->isEnrolledInClass($sessionAttendance->class_schedule_id);
        }

        return false;
    }

    /**
     * Determine whether the user can create a session.
     */
    public function create(User $user, $classScheduleId): bool
    {
        // Admin can create any session
        if ($user->isAdmin()) {
            return true;
        }

        $classSchedule = \App\Models\ClassSchedule::find($classScheduleId);

        if (!$classSchedule) {
            return false;
        }

        // Lecturer can create sessions for their classes
        if ($user->isLecturer() && $user->lecturer) {
            return $user->lecturer->id === $classSchedule->lecturer_id;
        }

        return false;
    }

    /**
     * Determine whether the user can extend a session.
     */
    public function extend(User $user, SessionAttendance $sessionAttendance): bool
    {
        // Only allow if session hasn't ended yet
        if ($sessionAttendance->end_time->isPast()) {
            return false;
        }

        // Admin can extend any active session
        if ($user->isAdmin()) {
            return true;
        }

        // Lecturer can extend sessions for their classes
        if ($user->isLecturer() && $user->lecturer) {
            return $user->lecturer->id === $sessionAttendance->classSchedule->lecturer_id;
        }

        return false;
    }

    /**
     * Determine whether the user can close a session.
     */
    public function close(User $user, SessionAttendance $sessionAttendance): bool
    {
        // Admin can close any session
        if ($user->isAdmin()) {
            return true;
        }

        // Lecturer can close sessions for their classes
        if ($user->isLecturer() && $user->lecturer) {
            return $user->lecturer->id === $sessionAttendance->classSchedule->lecturer_id;
        }

        return false;
    }
}
