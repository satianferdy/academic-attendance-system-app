<?php

namespace App\Services\Implementations;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Interfaces\UserServiceInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserService implements UserServiceInterface
{
    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * UserService constructor.
     *
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getAllUsersByRole(): array
    {
        $users = $this->userRepository->getAllUsers();

        return [
            'users' => $users,
            'admins' => $users->where('role', 'admin'),
            'lecturers' => $users->where('role', 'lecturer'),
            'students' => $users->where('role', 'student'),
        ];
    }

    public function createUser(array $data): User
    {
        try {
            DB::beginTransaction();

            // Create base user
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
            ];

            $user = $this->userRepository->createUser($userData);
            $user->assignRole($data['role']);

            // Create role-specific records
            if ($data['role'] === 'student') {
                $this->userRepository->createStudent([
                    'user_id' => $user->id,
                    'nim' => $data['student_nim'],
                    'study_program_id' => $data['study_program_id'],
                    'classroom_id' => $data['classroom_id'],
                ]);
            } elseif ($data['role'] === 'lecturer') {
                $this->userRepository->createLecturer([
                    'user_id' => $user->id,
                    'nip' => $data['lecturer_nip'],
                ]);
            }

            DB::commit();
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateUser(User $user, array $data): User
    {
        try {
            DB::beginTransaction();

            // Update base user
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
            ];

            // Update password if provided
            if (!empty($data['password'])) {
                $userData['password'] = Hash::make($data['password']);
            }

            $this->userRepository->updateUser($user, $userData);

            // Update role-specific records
            if ($user->role === 'student' && $user->student) {
                $this->userRepository->updateStudent($user->student, [
                    'nim' => $data['nim'],
                    'study_program_id' => $data['study_program_id'],
                    'classroom_id' => $data['classroom_id'],
                ]);
            } elseif ($user->role === 'lecturer' && $user->lecturer) {
                $this->userRepository->updateLecturer($user->lecturer, [
                    'nip' => $data['nip'],
                ]);
            }

            DB::commit();
            return $user->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteUser(User $user): ?bool
    {
        return $this->userRepository->deleteUser($user);
    }
}
