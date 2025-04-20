<?php

namespace Database\Factories;

use App\Models\ClassRoom;
use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\Lecturer;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassScheduleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ClassSchedule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $semesters = ['1', '2', '3', '4', '5', '6'];

        return [
            'course_id' => Course::factory(),
            'lecturer_id' => Lecturer::factory(),
            'classroom_id' => ClassRoom::factory(),
            'room' => $this->faker->randomElement(['Room A101', 'Room B202', 'Room C303', 'Lab D404', 'Hall E505']),
            'day' => $this->faker->randomElement($days),
            'semester' => $this->faker->randomElement($semesters),
            'academic_year' => $this->faker->randomElement(['Ganjil 2024/2025', 'Genap 2025/2026']),
            'total_weeks' => $this->faker->numberBetween(12, 16),
            'meetings_per_week' => $this->faker->numberBetween(1, 3),
        ];
    }
}
