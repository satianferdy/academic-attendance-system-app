<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('manage users');
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasPermissionTo('manage users') || $user->id === $model->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage users');
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasPermissionTo('manage users') || $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasPermissionTo('manage users') && $user->id !== $model->id;
    }
}
