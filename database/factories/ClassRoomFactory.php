<?php

namespace Database\Factories;

use App\Models\ClassRoom;
use App\Models\Semester;
use App\Models\StudyProgram;
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

        // Get a random study program or create one
        $studyProgram = StudyProgram::inRandomOrder()->first()
            ?? StudyProgram::factory()->create();

        // Get the active semester or create one
        $semester = Semester::where('is_active', true)->first()
            ?? Semester::factory()->active()->create();

        return [
            'name' => $this->faker->randomElement($classLevels) . ' ' . $studyProgram->code,
            'study_program_id' => $studyProgram->id,
            'semester_id' => $semester->id,
            'capacity' => $this->faker->numberBetween(20, 40),
        ];
    }

    /**
     * Configure the classroom for a specific study program.
     */
    public function forProgram(StudyProgram $program): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement(['1A', '1B', '2A', '2B', '3A', '3B', '4A', '4B']) . ' ' . $program->code,
            'study_program_id' => $program->id,
        ]);
    }

    /**
     * Configure the classroom for a specific semester.
     */
    public function forSemester(Semester $semester): static
    {
        return $this->state(fn (array $attributes) => [
            'semester_id' => $semester->id,
        ]);
    }
}
