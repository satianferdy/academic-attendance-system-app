<?php

namespace Tests\Feature\Auth;

use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\FeatureTestCase;

class AuthTest extends FeatureTestCase
{
    use RefreshDatabase, WithFaker;

    public function test_login_form_can_be_displayed()
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    public function test_registration_form_can_be_displayed()
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
    }

    public function test_student_can_login_with_nim()
    {
        $user = User::factory()->student()->create();
        $student = Student::factory()->create([
            'user_id' => $user->id,
            'nim' => '12345678910'
        ]);

        $response = $this->post(route('login'), [
            'username' => $student->nim,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('student.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_lecturer_can_login_with_nip()
    {
        $user = User::factory()->lecturer()->create();
        $lecturer = Lecturer::factory()->create([
            'user_id' => $user->id,
            'nip' => '98765432100'
        ]);

        $response = $this->post(route('login'), [
            'username' => $lecturer->nip,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('lecturer.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_login_with_email()
    {
        $user = User::factory()->admin()->create([
            'email' => 'admin@example.com'
        ]);

        $response = $this->post(route('login'), [
            'username' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_incorrect_password()
    {
        $user = User::factory()->create();

        $response = $this->post(route('login'), [
            'username' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_user_cannot_login_with_nonexistent_credentials()
    {
        $response = $this->post(route('login'), [
            'username' => 'nonexistent@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_login_requires_username_and_password()
    {
        $response = $this->post(route('login'), [
            'username' => '',
            'password' => '',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['username', 'password']);
    }

    public function test_authenticated_user_can_logout()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_user_can_register_as_student()
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'student',
            'nim' => '12345678901',
            'student_department' => 'Computer Science',
            'student_faculty' => 'Engineering',
        ];

        $response = $this->post(route('register'), $userData);

        $response->assertRedirect(route('student.dashboard'));
        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
            'role' => 'student',
        ]);
        $this->assertDatabaseHas('students', [
            'nim' => $userData['nim'],
            'department' => $userData['student_department'],
            'faculty' => $userData['student_faculty'],
        ]);

        $user = User::where('email', $userData['email'])->first();
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_register_as_lecturer()
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'lecturer',
            'nip' => '12345678901',
            'lecturer_department' => 'Computer Science',
            'lecturer_faculty' => 'Engineering',
        ];

        $response = $this->post(route('register'), $userData);

        $response->assertRedirect(route('lecturer.dashboard'));
        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
            'role' => 'lecturer',
        ]);
        $this->assertDatabaseHas('lecturers', [
            'nip' => $userData['nip'],
            'department' => $userData['lecturer_department'],
            'faculty' => $userData['lecturer_faculty'],
        ]);

        $user = User::where('email', $userData['email'])->first();
        $this->assertAuthenticatedAs($user);
    }

    public function test_register_requires_valid_email()
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => 'not-an-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'student',
            'nim' => '12345678901',
            'student_department' => 'Computer Science',
            'student_faculty' => 'Engineering',
        ];

        $response = $this->post(route('register'), $userData);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('users', [
            'name' => $userData['name'],
        ]);
    }

    public function test_register_requires_matching_password_confirmation()
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'password' => 'password123',
            'password_confirmation' => 'different-password',
            'role' => 'student',
            'nim' => '12345678901',
            'student_department' => 'Computer Science',
            'student_faculty' => 'Engineering',
        ];

        $response = $this->post(route('register'), $userData);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', [
            'email' => $userData['email'],
        ]);
    }

    public function test_register_validates_unique_student_nim()
    {
        $existingUser = User::factory()->student()->create();
        $existingStudent = Student::factory()->create([
            'user_id' => $existingUser->id,
            'nim' => '12345678901'
        ]);

        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'student',
            'nim' => '12345678901',
            'student_department' => 'Computer Science',
            'student_faculty' => 'Engineering',
        ];

        $response = $this->post(route('register'), $userData);

        $response->assertSessionHasErrors('nim');
        $this->assertDatabaseMissing('users', [
            'email' => $userData['email'],
        ]);
    }

    public function test_register_validates_unique_lecturer_nip()
    {
        $existingUser = User::factory()->lecturer()->create();
        $existingLecturer = Lecturer::factory()->create([
            'user_id' => $existingUser->id,
            'nip' => '98765432100'
        ]);

        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'lecturer',
            'nip' => '98765432100',
            'lecturer_department' => 'Computer Science',
            'lecturer_faculty' => 'Engineering',
        ];

        $response = $this->post(route('register'), $userData);

        $response->assertSessionHasErrors('nip');
        $this->assertDatabaseMissing('users', [
            'email' => $userData['email'],
        ]);
    }

    public function test_register_validates_unique_email()
    {
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com'
        ]);

        $userData = [
            'name' => $this->faker->name,
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'student',
            'nim' => '12345678901',
            'student_department' => 'Computer Science',
            'student_faculty' => 'Engineering',
        ];

        $response = $this->post(route('register'), $userData);

        $response->assertSessionHasErrors('email');
    }
}
