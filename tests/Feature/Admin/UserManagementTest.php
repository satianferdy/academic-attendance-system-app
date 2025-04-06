<?php

namespace Tests\Feature\Admin;

use App\Models\ClassRoom;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\FeatureTestCase;
use Tests\TestCase;

class UserManagementTest extends FeatureTestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user for authorization
        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_view_user_list()
    {
        // Create users of different roles for testing
        $student = User::factory()->create(['role' => 'student']);
        Student::factory()->create(['user_id' => $student->id]);

        $lecturer = User::factory()->create(['role' => 'lecturer']);
        Lecturer::factory()->create(['user_id' => $lecturer->id]);

        // Act as admin and access the index page
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index'));

        // Assert the response
        $response->assertStatus(200);
        $response->assertViewIs('admin.user.index');
        $response->assertViewHas(['users', 'admins', 'lecturers', 'students']);
        $response->assertSee($this->admin->name);
        $response->assertSee($student->name);
        $response->assertSee($lecturer->name);
    }

    public function test_non_admin_cannot_view_user_list()
    {
        // Create a regular user
        /** @var \App\Models\User $user */
        $user = User::factory()->create(['role' => 'student']);

        // Act as non-admin user and try to access the index page
        $response = $this->actingAs($user)
            ->get(route('admin.users.index'));

        // Should be forbidden
        $response->assertForbidden();
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
    }

    public function test_admin_can_create_student_user()
    {
        // Create a classroom for the student
        $classroom = ClassRoom::factory()->create();

        // Prepare data for a student user
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'student',
            'student_nim' => $this->faker->unique()->numerify('############'),
            'student_department' => 'Computer Science',
            'student_faculty' => 'Engineering',
            'classroom_id' => $classroom->id,
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
            'department' => $userData['student_department'],
            'faculty' => $userData['student_faculty'],
            'classroom_id' => $classroom->id,
        ]);
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
            'lecturer_department' => 'Computer Science',
            'lecturer_faculty' => 'Engineering',
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
            'department' => $userData['lecturer_department'],
            'faculty' => $userData['lecturer_faculty'],
        ]);
    }

    public function test_admin_can_access_edit_user_form()
    {
        // Create a user to edit
        $user = User::factory()->create();

        // Act as admin and access the edit form
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.edit', $user));

        // Assert the response
        $response->assertStatus(200);
        $response->assertViewIs('admin.user.edit');
        $response->assertViewHas(['user', 'classrooms']);
        $response->assertSee($user->name);
    }

    public function test_admin_can_update_user_details()
    {
        // Create a user to update
        $user = User::factory()->create();

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
        $user = User::factory()->create();
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
        // Create a student user
        $user = User::factory()->create(['role' => 'student']);
        $student = Student::factory()->create(['user_id' => $user->id]);
        $classroom = ClassRoom::factory()->create();

        // Prepare update data for student
        $updateData = [
            'name' => 'Updated Student',
            'email' => 'updated.student@example.com',
            'nim' => '9876543210',
            'department' => 'Updated Department',
            'faculty' => 'Updated Faculty',
            'classroom_id' => $classroom->id,
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
            'department' => $updateData['department'],
            'faculty' => $updateData['faculty'],
            'classroom_id' => $classroom->id,
        ]);
    }

    public function test_admin_can_update_lecturer_details()
    {
        // Create a lecturer user
        $user = User::factory()->create(['role' => 'lecturer']);
        $lecturer = Lecturer::factory()->create(['user_id' => $user->id]);

        // Prepare update data for lecturer
        $updateData = [
            'name' => 'Updated Lecturer',
            'email' => 'updated.lecturer@example.com',
            'nip' => '9876543210',
            'department' => 'Updated Department',
            'faculty' => 'Updated Faculty',
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
            'department' => $updateData['department'],
            'faculty' => $updateData['faculty'],
        ]);
    }

    public function test_admin_can_delete_user()
    {
        // Create a user to delete
        $user = User::factory()->create();

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
        $student = Student::factory()->create(['user_id' => $user->id]);

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

    public function test_non_admin_cannot_create_users()
    {
        // Create a regular user
        /** @var \App\Models\User $user */
        $user = User::factory()->create(['role' => 'student']);

        // Prepare data for a new user
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'student',
        ];

        // Act as non-admin and try to create a user
        $response = $this->actingAs($user)
            ->post(route('admin.users.store'), $userData);

        // Should be forbidden
        $response->assertForbidden();
    }

    public function test_non_admin_cannot_edit_users()
    {
        // Create users
        /** @var \App\Models\User $regularUser */
        $regularUser = User::factory()->create(['role' => 'student']);
        $targetUser = User::factory()->create();

        // Act as non-admin and try to edit a user
        $response = $this->actingAs($regularUser)
            ->get(route('admin.users.edit', $targetUser));

        // Should be forbidden
        $response->assertForbidden();
    }

    public function test_non_admin_cannot_update_users()
    {
        // Create users
        /** @var \App\Models\User $regularUser */
        $regularUser = User::factory()->create(['role' => 'student']);
        $targetUser = User::factory()->create();

        // Prepare update data
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated.email@example.com',
        ];

        // Act as non-admin and try to update a user
        $response = $this->actingAs($regularUser)
            ->put(route('admin.users.update', $targetUser), $updateData);

        // Should be forbidden
        $response->assertForbidden();
    }

    public function test_non_admin_cannot_delete_users()
    {
        // Create users
        /** @var \App\Models\User $regularUser */
        $regularUser = User::factory()->create(['role' => 'student']);
        $targetUser = User::factory()->create();

        // Act as non-admin and try to delete a user
        $response = $this->actingAs($regularUser)
            ->delete(route('admin.users.destroy', $targetUser));

        // Should be forbidden
        $response->assertForbidden();
    }
}
