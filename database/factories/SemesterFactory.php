<?php

namespace Database\Factories;

use App\Models\Semester;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class SemesterFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Semester::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $academicYears = ['2023/2024', '2024/2025', '2025/2026'];
        $terms = ['Ganjil', 'Genap'];

        $academicYear = $this->faker->randomElement($academicYears);
        $term = $this->faker->randomElement($terms);

        // Generate logical start and end dates
        $year = substr($academicYear, 0, 4);
        $startMonth = ($term === 'Ganjil') ? 8 : 2; // August for Odd, February for Even
        $endMonth = ($term === 'Ganjil') ? 1 : 7;   // January for Odd, July for Even

        $startDate = Carbon::create($year, $startMonth, 1);
        $endDate = ($term === 'Ganjil')
            ? Carbon::create((int)$year + 1, $endMonth, 31)  // Next year January
            : Carbon::create($year, $endMonth, 31);          // Same year July

        return [
            'name' => $academicYear . ' ' . $term,
            'academic_year' => $academicYear,
            'term' => $term,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => false,
        ];
    }

    /**
     * Indicate that the semester is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }
}
