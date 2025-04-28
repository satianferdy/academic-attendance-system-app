<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $user = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $user->assignRole('admin');

        $this->command->info('Admin user created with email: admin@example.com and password: password');
    }
}
