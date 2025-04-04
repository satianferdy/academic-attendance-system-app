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
            'student_id' => Student::factory()->withRegisteredFace(),
            'face_encoding' => json_encode(array_fill(0, 128, $this->faker->randomFloat(-1, 1))), // Simulated 128-dimension face encoding
            'face_image_path' => 'faces/' . $this->faker->uuid() . '.jpg',
            'registration_date' => $this->faker->dateTimeThisYear(),
        ];
    }
}
