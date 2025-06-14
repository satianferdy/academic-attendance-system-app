<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Lecturer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\RefreshPermissions;

class ProfileTest extends TestCase
{
    use RefreshDatabase, RefreshPermissions;

    protected $user;
    protected $lecturer;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup permissions and roles
        $this->setupPermissions();

        // Create user with lecturer relationship
        $this->user = User::factory()->create([
            'role' => 'lecturer',
            'password' => Hash::make('current_password')
        ]);
        $this->user->assignRole('lecturer');

        $this->lecturer = Lecturer::factory()->create([
            'user_id' => $this->user->id
        ]);
    }

    public function test_user_can_view_profile()
    {
        // Act: User visits profile page
        $response = $this->actingAs($this->user)
            ->get(route('profile.index'));

        // Assert: Profile page loads with user data
        $response->assertStatus(200);
        $response->assertViewIs('profile.index');
        $response->assertViewHas('user', $this->user);
    }

    public function test_lecturer_sees_class_count()
    {
        // Create some class schedules for this lecturer
        for ($i = 0; $i < 3; $i++) {
            \App\Models\ClassSchedule::factory()->create([
                'lecturer_id' => $this->lecturer->id
            ]);
        }

        // Act: Lecturer visits profile page
        $response = $this->actingAs($this->user)
            ->get(route('profile.index'));

        // Assert: Profile page shows class count
        $response->assertStatus(200);
        $response->assertViewHas('classCount', 3);
    }

    public function test_unauthenticated_user_redirected_from_profile()
    {
        // Act: Unauthenticated request to profile page
        $response = $this->get(route('profile.index'));

        // Assert: Redirected to login
        $response->assertRedirect(route('login'));
    }

    public function test_user_can_view_change_password_form()
    {
        // Act: User visits change password page
        $response = $this->actingAs($this->user)
            ->get(route('profile.change-password'));

        // Assert: Change password page loads
        $response->assertStatus(200);
        $response->assertViewIs('profile.change-password');
    }

    public function test_user_can_update_password_with_correct_credentials()
    {
        // Act: User submits password change with correct current password
        $response = $this->actingAs($this->user)
            ->post(route('profile.update-password'), [
                'current_password' => 'current_password',
                'password' => 'new_password12345',
                'password_confirmation' => 'new_password12345'
            ]);

        // Assert: Password updated and redirected to profile
        $response->assertRedirect(route('profile.index'));
        $response->assertSessionHas('success', 'Password changed successfully');

        // Check that the password hash has changed
        $this->user->refresh();
        $this->assertTrue(Hash::check('new_password12345', $this->user->password));
    }

    public function test_user_cannot_update_password_with_incorrect_current_password()
    {
        // Act: User submits password change with incorrect current password
        $response = $this->actingAs($this->user)
            ->post(route('profile.update-password'), [
                'current_password' => 'wrong_password',
                'password' => 'new_password12345',
                'password_confirmation' => 'new_password12345'
            ]);

        // Assert: Error message shown and redirected back
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Current password is incorrect');

        // Check that password hasn't changed
        $this->user->refresh();
        $this->assertFalse(Hash::check('new_password12345', $this->user->password));
        $this->assertTrue(Hash::check('current_password', $this->user->password));
    }

    public function test_password_validation_requires_confirmation()
    {
        // Act: User submits password change with mismatched confirmation
        $response = $this->actingAs($this->user)
            ->post(route('profile.update-password'), [
                'current_password' => 'current_password',
                'password' => 'new_password12345',
                'password_confirmation' => 'different_password'
            ]);

        // Assert: Validation error shown
        $response->assertRedirect();
        $response->assertSessionHasErrors('password');

        // Check that password hasn't changed
        $this->user->refresh();
        $this->assertTrue(Hash::check('current_password', $this->user->password));
    }

    public function test_password_validation_requires_minimum_length()
    {
        // Act: User submits password change with too short new password
        $response = $this->actingAs($this->user)
            ->post(route('profile.update-password'), [
                'current_password' => 'current_password',
                'password' => 'short',
                'password_confirmation' => 'short'
            ]);

        // Assert: Validation error shown
        $response->assertRedirect();
        $response->assertSessionHasErrors('password');

        // Check that password hasn't changed
        $this->user->refresh();
        $this->assertTrue(Hash::check('current_password', $this->user->password));
    }
}
