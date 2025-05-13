<?php

namespace Database\Factories;

use App\Models\ClassRoom;
use App\Models\ClassSchedule;
use App\Models\Course;
use App\Models\Lecturer;
use App\Models\Semester;
use App\Models\StudyProgram;
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
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        // For backward compatibility, keep the old semester field
        $semesters = ['1', '2', '3', '4', '5', '6'];

        // Get or create an active semester
        $semester = Semester::where('is_active', true)->first()
            ?? Semester::factory()->active()->create();

        // Get or create a study program
        $studyProgram = StudyProgram::inRandomOrder()->first()
            ?? StudyProgram::factory()->create();

        // Get or create a course for this study program
        $course = Course::where('study_program_id', $studyProgram->id)->inRandomOrder()->first()
            ?? Course::factory()->create(['study_program_id' => $studyProgram->id]);

        return [
            'course_id' => $course->id,
            'lecturer_id' => Lecturer::factory(),
            'classroom_id' => ClassRoom::factory(),
            'semester_id' => $semester->id,
            'study_program_id' => $studyProgram->id,
            'room' => $this->faker->randomElement(['RT01', 'RT02', 'RT03', 'RT04', 'LPR01', 'LPR02', 'LT01']),
            'day' => $this->faker->randomElement($days),
            'semester' => $this->faker->randomElement($semesters), // For backward compatibility
            'total_weeks' => $this->faker->numberBetween(12, 16),
            'meetings_per_week' => $this->faker->numberBetween(1, 3),
        ];
    }

    /**
     * Configure the schedule for a specific semester.
     */
    public function forSemester(Semester $semester): static
    {
        return $this->state(fn (array $attributes) => [
            'semester_id' => $semester->id,
        ]);
    }

    /**
     * Configure the schedule for a specific study program.
     */
    public function forProgram(StudyProgram $program): static
    {
        return $this->state(fn (array $attributes) => [
            'study_program_id' => $program->id,
        ]);
    }
}
