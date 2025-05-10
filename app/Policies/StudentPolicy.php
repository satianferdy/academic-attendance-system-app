<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StudentPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Student $student)
    {
        return $user->isStudent() && $user->student->id === $student->id;
    }

    public function viewFaceImages(User $user)
    {
        // Only admin users can view face images
        return $user->isAdmin();
    }
}
