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
            'face_embedding' => json_encode(array_fill(0, 128, 0.1)),
            'image_path' => json_encode(['path' => 'faces/test.jpg']),    // Array structure
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
