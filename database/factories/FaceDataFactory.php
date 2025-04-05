<?php

namespace Database\Factories;

use App\Models\FaceData;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class FaceDataFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FaceData::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'face_embedding' => array_fill(0, 128, $this->faker->randomFloat(-1, 1)), // Array, not JSON string
            'image_path' => ['path' => 'faces/' . $this->faker->uuid() . '.jpg'],     // Array structure
            'is_active' => true,
        ];
    }
}
