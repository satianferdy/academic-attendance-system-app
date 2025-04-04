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
        $today = Carbon::today();

        // Generate random start time between 8 AM and 4 PM
        $hour = $this->faker->numberBetween(8, 16);
        $startTime = Carbon::createFromTime($hour, 0, 0)->format('H:i');

        // End time is 2 hours after start time
        $endTime = Carbon::createFromTime($hour + 2, 0, 0)->format('H:i');

        return [
            'class_schedule_id' => ClassSchedule::factory(),
            'session_date' => $today,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'qr_code' => Str::random(8),
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
}
