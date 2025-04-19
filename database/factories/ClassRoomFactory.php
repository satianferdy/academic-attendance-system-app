<?php

namespace Database\Factories;

use App\Models\ClassRoom;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassRoomFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ClassRoom::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $classLevels = ['1A', '1B', '2A', '2B', '3A', '3B', '4A', '4B'];
        $departments = ['Computer Science', 'Information Systems', 'Data Science', 'Software Engineering'];

        return [
            'name' => $this->faker->randomElement($classLevels) . ' ' . $this->faker->randomElement($departments),
            'department' => $this->faker->randomElement($departments),
            'faculty' => $this->faker->randomElement(['Engineering', 'Science', 'Arts']),
            'capacity' => 30, // Default capacity
        ];
    }
}
