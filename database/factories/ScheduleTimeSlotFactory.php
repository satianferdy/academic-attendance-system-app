<?php

namespace Database\Factories;

use App\Models\ClassSchedule;
use App\Models\ScheduleTimeSlot;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleTimeSlotFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ScheduleTimeSlot::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate a random start time between 8 AM and 4 PM
        $hour = $this->faker->numberBetween(8, 16);
        $startTime = Carbon::createFromTime($hour, 0, 0)->format('H:i');

        // End time is 2 hours after start time
        $endTime = Carbon::createFromTime($hour + 2, 0, 0)->format('H:i');

        return [
            'class_schedule_id' => ClassSchedule::factory(),
            'start_time' => $startTime,
            'end_time' => $endTime,
        ];
    }
}
