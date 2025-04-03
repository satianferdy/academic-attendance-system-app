<?php

namespace App\Policies;

use App\Models\ClassSchedule;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClassSchedulePolicy
{
    use HandlesAuthorization;

    /**
     * Check if the user is the assigned lecturer for this class schedule.
     */
    private function isAssignedLecturer(User $user, ClassSchedule $classSchedule): bool
    {
        return $user->lecturer && $user->lecturer->id === $classSchedule->lecturer_id;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ClassSchedule $classSchedule): bool
    {
        // Admin can view any class schedule
        if ($user->isAdmin()) {
            return true;
        }

        // Lecturer can view their own class schedules
        if ($user->isLecturer()) {
            return $this->isAssignedLecturer($user, $classSchedule);
        }

        // Students can view class schedules they're enrolled in
        if ($user->isStudent() && $user->student) {
            return $user->student->isEnrolledInClass($classSchedule->id);
        }

        return false;
    }

    /**
     * Determine whether the user can manage the model.
     */
    public function manage(User $user, ClassSchedule $classSchedule): bool
    {
        // Admin can manage any class schedule
        if ($user->isAdmin()) {
            return true;
        }

        // Lecturer can manage their own class schedules
        if ($user->isLecturer()) {
            return $this->isAssignedLecturer($user, $classSchedule);
        }

        return false;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admin and lecturers can view class schedules
        return $user->isAdmin() || $user->isLecturer() || $user->isStudent();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admin and lecturers can create class schedules
        return $user->isAdmin() || $user->isLecturer();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ClassSchedule $classSchedule): bool
    {
        // Admin can update any class schedule
        if ($user->isAdmin()) {
            return true;
        }

        // Lecturer can update their own class schedules
        if ($user->isLecturer()) {
            return $this->isAssignedLecturer($user, $classSchedule);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ClassSchedule $classSchedule): bool
    {
        // Admin can delete any class schedule
        if ($user->isAdmin()) {
            return true;
        }

        // Lecturer can delete their own class schedules
        if ($user->isLecturer()) {
            return $this->isAssignedLecturer($user, $classSchedule);
        }

        return false;
    }

    /**
     * Determine whether the user can extend attendance time.
     */
    public function extendTime(User $user, ClassSchedule $classSchedule): bool
    {
        // Admin can extend time for any class schedule
        if ($user->isAdmin()) {
            return true;
        }

        // Lecturer can extend time for their own class schedules
        if ($user->isLecturer()) {
            return $this->isAssignedLecturer($user, $classSchedule);
        }

        return false;
    }

    /**
     * Determine whether the user can generate QR codes for attendance.
     */
    public function generateQR(User $user, ClassSchedule $classSchedule): bool
    {
        // Admin can generate QR for any class schedule
        if ($user->isAdmin()) {
            return true;
        }

        // Lecturer can generate QR for their own class schedules
        if ($user->isLecturer()) {
            return $this->isAssignedLecturer($user, $classSchedule);
        }

        return false;
    }
}
