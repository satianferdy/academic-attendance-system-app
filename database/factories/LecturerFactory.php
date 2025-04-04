<?php

namespace Database\Factories;

use App\Models\Lecturer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LecturerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Lecturer::class;

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
            'user_id' => User::factory(),
            'nip' => $this->faker->unique()->numerify('############'),
            'department' => $this->faker->randomElement($departments),
            'faculty' => $this->faker->randomElement($faculties),
        ];
    }
}
