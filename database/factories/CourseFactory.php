<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Course::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subjects = [
            'Introduction to Computer Science',
            'Database Systems',
            'Software Engineering',
            'Data Structures and Algorithms',
            'Artificial Intelligence',
            'Web Development',
            'Mobile Application Development',
            'Computer Networks',
            'Operating Systems',
            'Computer Architecture'
        ];

        $courseCode = 'CS' . $this->faker->unique()->numberBetween(100, 999);

        return [
            'code' => $courseCode,
            'name' => $this->faker->randomElement($subjects),
        ];
    }
}
