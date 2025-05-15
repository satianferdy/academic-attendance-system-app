<?php

namespace Tests\Feature\Admin;

use App\Models\ClassRoom;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\StudyProgram;
use App\Models\User;
use App\Services\Interfaces\UserServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Feature\FeatureTestCase;

class UserManagementTest extends FeatureTestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $studyProgram;
    protected $classroom;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        Permission::create(['name' => 'manage users']);
        Permission::create(['name' => 'view attendances']);
        Permission::create(['name' => 'create attendances']);
        Permission::create(['name' => 'edit attendances']);
        Permission::create(['name' => 'view schedules']);
        Permission::create(['name' => 'create schedules']);
        Permission::create(['name' => 'edit schedules']);
        Permission::create(['name' => 'delete schedules']);

        // Create roles and assign permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $lecturerRole = Role::create(['name' => 'lecturer']);
        $lecturerRole->givePermissionTo([
            'view attendances',
            'create attendances',
            'edit attendances',
            'view schedules'
        ]);

        $studentRole = Role::create(['name' => 'student']);
        $studentRole->givePermissionTo([
            'view attendances',
            'view schedules'
        ]);

        // Create an admin user for authorization
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->admin->assignRole('admin');

        // Create study program and classroom for student assignments
        $this->studyProgram = StudyProgram::factory()->create();
        $this->classroom = ClassRoom::factory()->create([
            'study_program_id' => $this->studyProgram->id
        ]);
    }

    public function test_admin_can_view_user_list()
    {
        // Create users of different roles for testing
        $student = User::factory()->create(['role' => 'student']);
        Student::factory()->create([
            'user_id' => $student->id,
            'study_program_id' => $this->studyProgram->id,
            'classroom_id' => $this->classroom->id
        ]);
        $student->assignRole('student');

        $lecturer = User::factory()->create(['role' => 'lecturer']);
        Lecturer::factory()->create(['user_id' => $lecturer->id]);
        $lecturer->assignRole('lecturer');

        // Act as admin and access the index page
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index'));

        // Assert the response
        $response->assertStatus(200);
        $response->assertViewIs('admin.user.index');
        $response->assertSee($this->admin->name);
        $response->assertSee($student->name);
        $response->assertSee($lecturer->name);
    }

    public function test_admin_can_access_create_user_form()
    {
        // Act as admin and access the create form
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.create'));

        // Assert the response
        $response->assertStatus(200);
        $response->assertViewIs('admin.user.create');
        $response->assertViewHas('classrooms');
        $response->assertViewHas('studyPrograms');
    }

    public function test_admin_can_create_admin_user()
    {
        // Prepare data for an admin user
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ];

        // Act as admin and submit the form
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), $userData);

        // Assert the response and database state
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
            'role' => 'admin',
        ]);

        // Verify role assignment
        $user = User::where('email', $userData['email'])->first();
        $this->assertTrue($user->hasRole('admin'));
    }

    public function test_admin_can_create_student_user()
    {
        // Prepare data for a student user
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'student',
            'student_nim' => $this->faker->unique()->numerify('############'),
            'study_program_id' => $this->studyProgram->id,
            'classroom_id' => $this->classroom->id,
        ];

        // Act as admin and submit the form
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), $userData);

        // Assert the response and database state
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
            'role' => 'student',
        ]);

        $user = User::where('email', $userData['email'])->first();
        $this->assertDatabaseHas('students', [
            'user_id' => $user->id,
            'nim' => $userData['student_nim'],
            'study_program_id' => $userData['study_program_id'],
            'classroom_id' => $userData['classroom_id'],
        ]);

        // Verify role assignment
        $this->assertTrue($user->hasRole('student'));
    }

    public function test_admin_can_create_lecturer_user()
    {
        // Prepare data for a lecturer user
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'lecturer',
            'lecturer_nip' => $this->faker->unique()->numerify('############'),
        ];

        // Act as admin and submit the form
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), $userData);

        // Assert the response and database state
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
            'role' => 'lecturer',
        ]);

        $user = User::where('email', $userData['email'])->first();
        $this->assertDatabaseHas('lecturers', [
            'user_id' => $user->id,
            'nip' => $userData['lecturer_nip'],
        ]);

        // Verify role assignment
        $this->assertTrue($user->hasRole('lecturer'));
    }

    public function test_admin_can_edit_user()
    {
        // Create a user to edit
        $user = User::factory()->create(['role' => 'admin']);
        $user->assignRole('admin');

        // Act as admin and access the edit form
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.edit', $user));

        // Assert the response
        $response->assertStatus(200);
        $response->assertViewIs('admin.user.edit');
        $response->assertViewHas(['user', 'classrooms', 'studyPrograms']);
        $response->assertSee($user->name);
    }

    public function test_admin_can_update_user_details()
    {
        // Create a user to update
        $user = User::factory()->create(['role' => 'admin']);
        $user->assignRole('admin');

        // Prepare update data
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated.email@example.com',
        ];

        // Act as admin and submit the update
        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $user), $updateData);

        // Assert the response and database state
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $updateData['name'],
            'email' => $updateData['email'],
        ]);
    }

    public function test_admin_can_update_user_password()
    {
        // Create a user to update
        $user = User::factory()->create(['role' => 'admin']);
        $user->assignRole('admin');
        $oldPassword = $user->password;

        // Prepare update data with password
        $updateData = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ];

        // Act as admin and submit the update
        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $user), $updateData);

        // Assert the response
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        // Refresh the user from database
        $user->refresh();

        // Password should have changed
        $this->assertNotEquals($oldPassword, $user->password);
    }

    public function test_admin_can_update_student_details()
    {
        // Create student user
        $user = User::factory()->create(['role' => 'student']);
        $user->assignRole('student');

        $student = Student::factory()->create([
            'user_id' => $user->id,
            'study_program_id' => $this->studyProgram->id,
        ]);

        // New classroom for update
        $newClassroom = ClassRoom::factory()->create();

        // Prepare update data
        $updateData = [
            'name' => 'Updated Student',
            'email' => 'updated.student@example.com',
            'nim' => '9876543210',
            'study_program_id' => $this->studyProgram->id,
            'classroom_id' => $newClassroom->id,
        ];

        // Act as admin and submit the update
        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $user), $updateData);

        // Assert the response and database state
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $updateData['name'],
            'email' => $updateData['email'],
        ]);

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'nim' => $updateData['nim'],
            'classroom_id' => $newClassroom->id,
        ]);
    }

    public function test_admin_can_update_lecturer_details()
    {
        // Create lecturer user
        $user = User::factory()->create(['role' => 'lecturer']);
        $user->assignRole('lecturer');

        $lecturer = Lecturer::factory()->create([
            'user_id' => $user->id,
        ]);

        // Prepare update data
        $updateData = [
            'name' => 'Updated Lecturer',
            'email' => 'updated.lecturer@example.com',
            'nip' => '9876543210',
        ];

        // Act as admin and submit the update
        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $user), $updateData);

        // Assert the response and database state
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $updateData['name'],
            'email' => $updateData['email'],
        ]);

        $this->assertDatabaseHas('lecturers', [
            'id' => $lecturer->id,
            'nip' => $updateData['nip'],
        ]);
    }

    public function test_admin_can_delete_user()
    {
        // Create a user to delete
        $user = User::factory()->create();
        $user->assignRole('admin');

        // Act as admin and delete the user
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $user));

        // Assert the response and database state
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_admin_can_delete_student_with_related_data()
    {
        // Create a student user with related data
        $user = User::factory()->create(['role' => 'student']);
        $user->assignRole('student');

        $student = Student::factory()->create([
            'user_id' => $user->id,
            'study_program_id' => $this->studyProgram->id,
            'classroom_id' => $this->classroom->id,
        ]);

        // Act as admin and delete the user
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $user));

        // Assert the response and database state
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);

        $this->assertDatabaseMissing('students', [
            'id' => $student->id,
        ]);
    }

    public function test_validation_fails_with_invalid_data()
    {
        // Prepare invalid data (missing required fields)
        $invalidData = [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'different',
            'role' => 'invalid-role',
        ];

        // Act as admin and submit the invalid data
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), $invalidData);

        // Assert validation fails
        $response->assertSessionHasErrors(['name', 'email', 'password', 'role']);
        $response->assertRedirect();
    }

    public function test_non_admin_cannot_access_user_management()
    {
        // Create a regular user
        /** @var \App\Models\User $user */
        $user = User::factory()->create(['role' => 'student']);
        $user->assignRole('student');

        // Try to access the user management pages
        $indexResponse = $this->actingAs($user)->get(route('admin.users.index'));
        $createResponse = $this->actingAs($user)->get(route('admin.users.create'));

        // Assert both are forbidden
        $indexResponse->assertForbidden();
        $createResponse->assertForbidden();
    }

    public function test_store_method_catches_exception()
    {
        // Mock UserService to throw an exception
        $this->mock(UserServiceInterface::class, function ($mock) {
            $mock->shouldReceive('createUser')
                ->once()
                ->andThrow(new \Exception('Test error message'));
        });

        // Create form data
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'student',
            'student_nim' => '12345678',
            'study_program_id' => $this->studyProgram->id,
            'classroom_id' => $this->classroom->id,
        ];

        // Submit form and test exception handling
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), $userData);

        // Assert: Should redirect back with error and input
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Error creating user: Test error message');
        $response->assertSessionHasInput('name', 'Test User');
    }

    public function test_update_method_validator_fails()
    {
        // Create a user to update
        $user = User::factory()->create(['role' => 'student']);
        $user->assignRole('student');
        $student = Student::factory()->create([
            'user_id' => $user->id,
            'nim' => 'ORIGINAL_NIM',
            'study_program_id' => $this->studyProgram->id,
            'classroom_id' => $this->classroom->id
        ]);

        // Create invalid form data (missing required fields)
        $userData = [
            'name' => '',  // Empty name should fail validation
            'email' => 'invalid-email',  // Invalid email format
            'password' => 'pass',  // Too short
            'password_confirmation' => 'different-pass',  // Doesn't match
        ];

        // Submit form and test validation failure
        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', ['user' => $user->id]), $userData);

        // Assert: Should redirect back with validator errors
        $response->assertRedirect();
        $response->assertSessionHasErrors(['name', 'email']);
    }

    public function test_update_method_catches_exception()
    {
        // Create a user to update
        $user = User::factory()->create(['role' => 'student']);
        $user->assignRole('student');
        $student = Student::factory()->create([
            'user_id' => $user->id,
            'nim' => 'ORIGINAL_NIM',
            'study_program_id' => $this->studyProgram->id,
            'classroom_id' => $this->classroom->id
        ]);

        // Mock UserService to throw an exception
        $this->mock(UserServiceInterface::class, function ($mock) {
            $mock->shouldReceive('updateUser')
                ->once()
                ->andThrow(new \Exception('Update error message'));
        });

        // Create form data
        $userData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'nim' => 'UPDATED_NIM',
            'study_program_id' => $this->studyProgram->id,
            'classroom_id' => $this->classroom->id
        ];

        // Submit form and test exception handling
        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', ['user' => $user->id]), $userData);

        // Assert: Should redirect back with error and input
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Error updating user: Update error message');
        $response->assertSessionHasInput('name', 'Updated Name');
    }

    public function test_destroy_method_catches_exception()
    {
        // Create a user to delete
        $user = User::factory()->create(['role' => 'student']);
        $user->assignRole('student');

        // Mock UserService to throw an exception
        $this->mock(UserServiceInterface::class, function ($mock) {
            $mock->shouldReceive('deleteUser')
                ->once()
                ->andThrow(new \Exception('Deletion error message'));
        });

        // Submit delete request and test exception handling
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', ['user' => $user->id]));

        // Assert: Should redirect to index with error
        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error', 'Error deleting user: Deletion error message');
    }
}
