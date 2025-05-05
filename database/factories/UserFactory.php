<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'), // Default password for testing
            'remember_token' => null,
            'role' => $this->faker->randomElement(['admin', 'lecturer', 'student']),
        ];
    }

    /**
     * Configure the model factory for an admin user.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    /**
     * Configure the model factory for a lecturer user.
     */
    public function lecturer(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'lecturer',
        ]);
    }

    /**
     * Configure the model factory for a student user.
     */
    public function student(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'student',
        ]);
    }
}
