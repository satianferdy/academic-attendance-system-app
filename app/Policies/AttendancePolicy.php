<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendancePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the attendance record.
     */
    public function view(User $user, Attendance $attendance): bool
    {
        // Admin can view any attendance
        if ($user->isAdmin()) {
            return true;
        }

        // Lecturer can view attendance for their classes
        if ($user->isLecturer() && $user->lecturer) {
            return $user->lecturer->id === $attendance->classSchedule->lecturer_id;
        }

        // Student can view their own attendance
        if ($user->isStudent() && $user->student) {
            return $user->student->id === $attendance->student_id;
        }

        return false;
    }

    /**
     * Determine whether the user can view any attendance records.
     */
    public function viewAny(User $user): bool
    {
        // Admin can view any attendance
        return $user->isAdmin() || $user->isLecturer() || $user->isStudent();

        return false;
    }

    /**
     * Determine whether the user can update the attendance record.
     */
    public function update(User $user, Attendance $attendance): bool
    {
        // Admin can update any attendance
        if ($user->isAdmin()) {
            return true;
        }

        // Lecturer can update attendance for their classes
        if ($user->isLecturer() && $user->lecturer) {
            return $user->lecturer->id === $attendance->classSchedule->lecturer_id;
        }

        return false;
    }

    /**
     * Determine if the user can manage attendance for a specific student
     */
    public function manageStudentAttendance(User $user, Attendance $attendance): bool
    {
        // Admin can manage any student attendance
        if ($user->isAdmin()) {
            return true;
        }

        // Lecturer can manage attendance for students in their classes
        if ($user->isLecturer() && $user->lecturer) {
            return $user->lecturer->id === $attendance->classSchedule->lecturer_id;
        }

        return false;
    }
}
