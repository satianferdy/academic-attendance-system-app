<?php

namespace Tests\Feature\Auth;

use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Tests\Feature\FeatureTestCase;

class AuthTest extends FeatureTestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'lecturer']);
        Role::create(['name' => 'student']);
    }

    public function test_login_form_can_be_displayed()
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    public function test_student_can_login_with_nim()
    {
        // Create user and assign student role
        $user = User::factory()->create(['role' => 'student']);
        $user->assignRole('student');

        // Create student record
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
        // Create user and assign lecturer role
        $user = User::factory()->create(['role' => 'lecturer']);
        $user->assignRole('lecturer');

        // Create lecturer record
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
        // Create user and assign admin role
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'role' => 'admin'
        ]);
        $user->assignRole('admin');

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
}
