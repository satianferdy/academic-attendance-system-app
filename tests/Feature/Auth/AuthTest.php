<?php

namespace Tests\Feature\Auth;

use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
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

    // Tests for the rate limiting functionality
    public function test_too_many_login_attempts_triggers_rate_limiter()
    {
        $ip = '127.0.0.1';

        // Mock RateLimiter to return true for tooManyAttempts
        RateLimiter::shouldReceive('tooManyAttempts')
            ->with('login-attempts:' . $ip, 5)
            ->once()
            ->andReturn(true);

        // Mock availableIn to return seconds
        RateLimiter::shouldReceive('availableIn')
            ->with('login-attempts:' . $ip)
            ->once()
            ->andReturn(60);

        $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->post(route('login'), [
                'username' => 'test@example.com',
                'password' => 'password',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('username')
            ->assertSessionHasErrors([
                'username' => 'Too many login attempts. Please try again in 60 seconds.'
            ]);
    }

    public function test_rate_limiter_allows_login_within_limits()
    {
        $ip = '127.0.0.1';

        // Mock RateLimiter to return false for tooManyAttempts
        RateLimiter::shouldReceive('tooManyAttempts')
            ->with('login-attempts:' . $ip, 5)
            ->once()
            ->andReturn(false);

        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'role' => 'admin'
        ]);
        $user->assignRole('admin');

        $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->post(route('login'), [
                'username' => 'test@example.com',
                'password' => 'password',
            ])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    // Tests for password reset functionality
    public function test_password_reset_link_can_be_sent()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        Password::shouldReceive('sendResetLink')
            ->once()
            ->with(['email' => 'test@example.com'])
            ->andReturn(Password::RESET_LINK_SENT);

        $response = $this->post(route('password.email'), [
            'email' => 'test@example.com'
        ]);

        $response->assertRedirect()
            ->assertSessionHas('status');
    }

    public function test_password_reset_link_request_validates_email()
    {
        $response = $this->post(route('password.email'), [
            'email' => 'not-an-email'
        ]);

        $response->assertRedirect()
            ->assertSessionHasErrors('email');
    }

    public function test_password_reset_link_fails_for_nonexistent_email()
    {
        Password::shouldReceive('sendResetLink')
            ->once()
            ->with(['email' => 'nonexistent@example.com'])
            ->andReturn(Password::INVALID_USER);

        $response = $this->post(route('password.email'), [
            'email' => 'nonexistent@example.com'
        ]);

        $response->assertRedirect()
            ->assertSessionHasErrors('email');
    }

    public function test_password_can_be_reset_with_valid_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        Event::fake();

        $token = 'valid-token';

        Password::shouldReceive('reset')
            ->once()
            ->withArgs(function($credentials, $callback) use ($user) {
                // Test the callback works correctly
                $callback($user, 'new-password');

                // Verify the user's password was updated
                $this->assertTrue(Hash::check('new-password', $user->password));

                // Ensure remember token was set
                $this->assertNotNull($user->remember_token);

                // Check credentials
                return $credentials['email'] === 'test@example.com' &&
                       $credentials['password'] === 'new-password' &&
                       $credentials['token'] === 'valid-token';
            })
            ->andReturn(Password::PASSWORD_RESET);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password'
        ]);

        $response->assertRedirect(route('login'))
            ->assertSessionHas('status');

        Event::assertDispatched(PasswordReset::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });
    }

    public function test_password_reset_validates_inputs()
    {
        $response = $this->post(route('password.update'), [
            'token' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'different'
        ]);

        $response->assertRedirect()
            ->assertSessionHasErrors(['token', 'email', 'password']);
    }

    public function test_password_reset_fails_with_invalid_token()
    {
        Password::shouldReceive('reset')
            ->once()
            ->andReturn(Password::INVALID_TOKEN);

        $response = $this->post(route('password.update'), [
            'token' => 'invalid-token',
            'email' => 'test@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password'
        ]);

        $response->assertRedirect()
            ->assertSessionHasErrors('email');
    }
}
