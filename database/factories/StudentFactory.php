<?php

namespace Database\Factories;

use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\StudyProgram;
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
        // Get or create a study program
        $studyProgram = StudyProgram::inRandomOrder()->first()
            ?? StudyProgram::factory()->create();

        return [
            'user_id' => User::factory()->student(),
            'classroom_id' => ClassRoom::factory(),
            'study_program_id' => $studyProgram->id,
            'nim' => $this->faker->unique()->numerify('############'),
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

    /**
     * Configure the student with a specific study program.
     */
    public function forProgram(StudyProgram $program): static
    {
        return $this->state(fn (array $attributes) => [
            'study_program_id' => $program->id,
        ]);
    }
}
