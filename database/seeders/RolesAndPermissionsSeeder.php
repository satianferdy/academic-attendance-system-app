<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        // User management
        Permission::create(['name' => 'manage users']);

        // Attendance permissions
        Permission::create(['name' => 'view attendances']);
        Permission::create(['name' => 'create attendances']);
        Permission::create(['name' => 'edit attendances']);

        // Schedule permissions
        Permission::create(['name' => 'view schedules']);
        Permission::create(['name' => 'create schedules']);
        Permission::create(['name' => 'edit schedules']);
        Permission::create(['name' => 'delete schedules']);

        // Face permissions
        Permission::create(['name' => 'register face']);
        Permission::create(['name' => 'view face data']);
        Permission::create(['name' => 'manage face data']);
        Permission::create(['name' => 'verify face']);

        // Session permissions
        Permission::create(['name' => 'create sessions']);
        Permission::create(['name' => 'extend sessions']);
        Permission::create(['name' => 'view session qr']);

        // Report permissions
        Permission::create(['name' => 'generate reports']);
        Permission::create(['name' => 'export data']);

        // Create roles and assign permissions
        // 1. Admin role - can do everything
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        // 2. Lecturer role
        $lecturer = Role::create(['name' => 'lecturer']);
        $lecturer->givePermissionTo([
            'view attendances',
            'create attendances',
            'edit attendances',
            'view schedules',
            'create sessions',
            'extend sessions',
            'view session qr',
            'generate reports',
            'export data'
        ]);

        // 3. Student role
        $student = Role::create(['name' => 'student']);
        $student->givePermissionTo([
            'view attendances', // Students can view their own attendance
            'register face',     // Students can register their face
            'verify face',      // Students can verify their face
            'view schedules' // Students can view their own schedule
        ]);

        $this->command->info('Permissions and roles created successfully!');
    }
}
