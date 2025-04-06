<?php

namespace Database\Factories;

use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Student::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $departments = ['Computer Science', 'Information Systems', 'Data Science', 'Software Engineering'];
        $faculties = ['Engineering', 'Science', 'Arts'];

        return [
            'user_id' => User::factory()->student(),
            'classroom_id' => ClassRoom::factory(),
            'nim' => $this->faker->unique()->numerify('############'),
            'department' => $this->faker->randomElement($departments),
            'faculty' => $this->faker->randomElement($faculties),
            'face_registered' => $this->faker->boolean(30), // 30% chance of being true
        ];
    }

    /**
     * State for students with registered face data.
     */
    public function withRegisteredFace(): static
    {
        return $this->state(fn (array $attributes) => [
            'face_registered' => true,
        ]);
    }
}
