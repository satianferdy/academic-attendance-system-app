<?php

namespace Database\Factories;

use App\Models\ClassSchedule;
use App\Models\SessionAttendance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SessionAttendanceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SessionAttendance::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate a date within the current semester (last 3 months to next 3 months)
        $sessionDate = Carbon::today()->addDays($this->faker->numberBetween(-90, 90));

        // Generate random start time between 8 AM and 4 PM
        $hour = $this->faker->numberBetween(8, 16);
        $minute = $this->faker->randomElement([0, 15, 30, 45]); // More realistic class start times
        $startTime = Carbon::createFromTime($hour, $minute, 0);

        // Classes typically last 1-3 hours
        $duration = $this->faker->randomElement([1, 1.5, 2, 3]);
        $endTime = (clone $startTime)->addMinutes($duration * 60);

        // Make sure we have a class schedule
        $classSchedule = ClassSchedule::inRandomOrder()->first();
        if (!$classSchedule) {
            $classSchedule = ClassSchedule::factory()->create();
        }

        // Calculate the appropriate week based on the semester start date
        $semesterStartDate = $classSchedule->semester->start_date ?? Carbon::today()->startOfMonth();
        $weekNumber = max(1, ceil($sessionDate->diffInDays($semesterStartDate) / 7));

        return [
            'class_schedule_id' => $classSchedule->id,
            'session_date' => $sessionDate,
            'week' => min(16, $weekNumber), // Limit to 16 weeks (typical semester length)
            'meetings' => $this->faker->numberBetween(1, 3), // Random number of meetings
            'start_time' => $startTime->format('H:i'),
            'end_time' => $endTime->format('H:i'),
            'total_hours' => $duration, // Add total_hours field
            'tolerance_minutes' => $this->faker->randomElement([10, 15, 20]), // Realistic tolerance times
            'qr_code' => Str::upper(Str::random(8)),
            'is_active' => $this->faker->boolean(70), // 70% chance of being active
        ];
    }

    /**
     * State for active session.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * State for inactive session.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * State for current/today's session.
     */
    public function today(): static
    {
        return $this->state(function (array $attributes) {
            // Create a realistic time for today's class
            $hour = Carbon::now()->hour;
            // If it's early morning, schedule for later today
            if ($hour < 8) {
                $startHour = $this->faker->numberBetween(9, 16);
            }
            // If it's late evening, schedule it as if it happened earlier today
            else if ($hour > 18) {
                $startHour = $this->faker->numberBetween(8, 17);
            }
            // Otherwise schedule it close to current time
            else {
                $startHour = $hour + $this->faker->numberBetween(-1, 2);
                $startHour = max(8, min(17, $startHour)); // Keep within 8 AM - 5 PM
            }

            $startTime = Carbon::today()->setHour($startHour)->setMinute($this->faker->randomElement([0, 15, 30, 45]));
            $duration = $this->faker->randomElement([1, 1.5, 2, 3]);
            $endTime = (clone $startTime)->addMinutes($duration * 60);

            return [
                'session_date' => Carbon::today(),
                'start_time' => $startTime->format('H:i'),
                'end_time' => $endTime->format('H:i'),
                'total_hours' => $duration,
                'is_active' => true,
            ];
        });
    }

    /**
     * State for an upcoming session this week.
     */
    public function upcoming(): static
    {
        return $this->state(function (array $attributes) {
            $daysAhead = $this->faker->numberBetween(1, 7);
            $sessionDate = Carbon::today()->addDays($daysAhead);

            $startTime = Carbon::createFromTime(
                $this->faker->numberBetween(8, 16),
                $this->faker->randomElement([0, 15, 30, 45]),
                0
            );

            $duration = $this->faker->randomElement([1, 1.5, 2, 3]);
            $endTime = (clone $startTime)->addMinutes($duration * 60);

            return [
                'session_date' => $sessionDate,
                'start_time' => $startTime->format('H:i'),
                'end_time' => $endTime->format('H:i'),
                'total_hours' => $duration,
                'is_active' => true,
            ];
        });
    }
}
