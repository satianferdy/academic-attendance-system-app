<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\ClassSchedule;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Attendance::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['present', 'absent', 'late', 'excused'];
        $today = Carbon::today();

        // Random time between 8 AM and 4 PM
        $attendanceTime = Carbon::today()->addHours($this->faker->numberBetween(8, 16))->addMinutes($this->faker->numberBetween(0, 59));

        return [
            'class_schedule_id' => ClassSchedule::factory(),
            'student_id' => Student::factory(),
            'date' => $today,
            'status' => $this->faker->randomElement($statuses),
            'remarks' => $this->faker->optional(0.3)->sentence(),
            'qr_token' => $this->faker->optional(0.7)->uuid(),
            'attendance_time' => $attendanceTime,
        ];
    }

    /**
     * State for present attendance.
     */
    public function present(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'present',
        ]);
    }

    /**
     * State for absent attendance.
     */
    public function absent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'absent',
            'attendance_time' => null,
        ]);
    }

    /**
     * State for late attendance.
     */
    public function late(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'late',
        ]);
    }
}
