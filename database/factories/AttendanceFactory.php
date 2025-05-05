<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\ClassSchedule;
use App\Models\SessionAttendance;
use App\Models\Student;
use App\Models\User;
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
        $status = $this->faker->randomElement($statuses);

        // Get or create a class schedule
        $classSchedule = ClassSchedule::inRandomOrder()->first();
        if (!$classSchedule) {
            $classSchedule = ClassSchedule::factory()->create();
        }

        // Get or create a student for this class
        $student = null;
        if ($classSchedule->classroom_id) {
            $student = Student::where('classroom_id', $classSchedule->classroom_id)
                ->inRandomOrder()
                ->first();
        }

        if (!$student) {
            $student = Student::factory()->create([
                'classroom_id' => $classSchedule->classroom_id
            ]);
        }

        // Get or create a session attendance
        $sessionAttendance = SessionAttendance::where('class_schedule_id', $classSchedule->id)
            ->inRandomOrder()
            ->first();

        if (!$sessionAttendance) {
            $sessionDate = Carbon::now()->subDays(rand(0, 30));
            $startTime = Carbon::parse($sessionDate->format('Y-m-d') . ' 08:00:00');
            $endTime = (clone $startTime)->addHours(rand(1, 3));
            $totalHours = $endTime->diffInHours($startTime);

            $sessionAttendance = SessionAttendance::create([
                'class_schedule_id' => $classSchedule->id,
                'session_date' => $sessionDate,
                'week' => rand(1, 16),
                'meetings' => rand(1, 3),
                'start_time' => $startTime->format('H:i:s'),
                'end_time' => $endTime->format('H:i:s'),
                'total_hours' => $totalHours,
                'tolerance_minutes' => 15,
                'qr_code' => $this->faker->uuid(),
                'is_active' => true
            ]);
        } else {
            $totalHours = $sessionAttendance->total_hours;
        }

        // Set default attendance date to session date if available
        $date = $sessionAttendance ? $sessionAttendance->session_date : Carbon::now()->subDays(rand(0, 30));

        // Calculate hours based on status
        $hoursPresent = 0;
        $hoursAbsent = 0;
        $hoursSick = 0;
        $hoursPermitted = 0;

        switch ($status) {
            case 'present':
                $hoursPresent = $totalHours;
                $attendanceTime = $sessionAttendance ?
                    Carbon::parse($sessionAttendance->session_date->format('Y-m-d') . ' ' .
                    $sessionAttendance->start_time->format('H:i:s'))->addMinutes(rand(0, 15)) :
                    Carbon::now();
                break;
            case 'late':
                $hoursPresent = max(0, $totalHours - 1);
                $hoursAbsent = $totalHours - $hoursPresent;
                $attendanceTime = $sessionAttendance ?
                    Carbon::parse($sessionAttendance->session_date->format('Y-m-d') . ' ' .
                    $sessionAttendance->start_time->format('H:i:s'))->addMinutes(rand(16, 30)) :
                    Carbon::now()->addMinutes(20);
                break;
            case 'absent':
                $hoursAbsent = $totalHours;
                $attendanceTime = null;
                break;
            case 'excused':
                $hoursPermitted = $totalHours;
                $attendanceTime = null;
                break;
            default:
                $attendanceTime = null;
        }

        // Random admin user for editing if needed
        $adminUser = User::where('role', 'admin')->inRandomOrder()->first();
        $lastEditedBy = rand(0, 1) && $adminUser ? $adminUser->id : null;

        return [
            'class_schedule_id' => $classSchedule->id,
            'student_id' => $student->id,
            'date' => $date,
            'status' => $status,
            'remarks' => $status !== 'present' ? $this->faker->sentence() : null,
            'edit_notes' => $lastEditedBy ? $this->faker->sentence() : null,
            'hours_present' => $hoursPresent,
            'hours_absent' => $hoursAbsent,
            'hours_permitted' => $hoursPermitted,
            'hours_sick' => $hoursSick,
            'qr_token' => $status === 'present' ? $this->faker->uuid() : null,
            'attendance_time' => $attendanceTime,
            'last_edited_by' => $lastEditedBy,
            'last_edited_at' => $lastEditedBy ? Carbon::now() : null,
        ];
    }

    /**
     * State for present attendance.
     */
    public function present(): static
    {
        return $this->state(function (array $attributes) {
            // Find the session attendance for this class schedule
            $sessionAttendance = SessionAttendance::where('class_schedule_id', $attributes['class_schedule_id'])
                ->first();

            $totalHours = $sessionAttendance ? $sessionAttendance->total_hours : 2;
            $date = isset($attributes['date']) && $attributes['date'] instanceof Carbon ?
                   $attributes['date'] : Carbon::parse($attributes['date']);

            // Get the start time either from session or default to 8 AM
            $startTimeStr = $sessionAttendance && $sessionAttendance->start_time ?
                           $sessionAttendance->start_time->format('H:i:s') : '08:00:00';

            $attendanceTime = $date->copy()->setTimeFromTimeString($startTimeStr)
                                  ->addMinutes(rand(0, 10));

            return [
                'status' => 'present',
                'hours_present' => $totalHours,
                'hours_absent' => 0,
                'hours_permitted' => 0,
                'hours_sick' => 0,
                'attendance_time' => $attendanceTime,
                'qr_token' => $this->faker->uuid(),
            ];
        });
    }

    /**
     * State for absent attendance.
     */
    public function absent(): static
    {
        return $this->state(function (array $attributes) {
            $sessionAttendance = SessionAttendance::where('class_schedule_id', $attributes['class_schedule_id'])
                ->first();

            $totalHours = $sessionAttendance ? $sessionAttendance->total_hours : 2;

            return [
                'status' => 'absent',
                'hours_present' => 0,
                'hours_absent' => $totalHours,
                'hours_permitted' => 0,
                'hours_sick' => 0,
                'attendance_time' => null,
                'qr_token' => null,
                'remarks' => $this->faker->sentence(),
            ];
        });
    }

    /**
     * State for late attendance.
     */
    public function late(): static
    {
        return $this->state(function (array $attributes) {
            $sessionAttendance = SessionAttendance::where('class_schedule_id', $attributes['class_schedule_id'])
                ->first();

            $totalHours = $sessionAttendance ? $sessionAttendance->total_hours : 2;
            $date = isset($attributes['date']) && $attributes['date'] instanceof Carbon ?
                   $attributes['date'] : Carbon::parse($attributes['date']);

            // Get the start time either from session or default to 8 AM, then add delay
            $startTimeStr = $sessionAttendance && $sessionAttendance->start_time ?
                           $sessionAttendance->start_time->format('H:i:s') : '08:00:00';

            $attendanceTime = $date->copy()->setTimeFromTimeString($startTimeStr)
                                  ->addMinutes(rand(16, 30));

            return [
                'status' => 'late',
                'hours_present' => max(0, $totalHours - 1),
                'hours_absent' => 1,
                'hours_permitted' => 0,
                'hours_sick' => 0,
                'attendance_time' => $attendanceTime,
                'qr_token' => $this->faker->uuid(),
                'remarks' => 'Arrived late: ' . $this->faker->sentence(),
            ];
        });
    }

    /**
     * State for excused attendance.
     */
    public function excused(): static
    {
        return $this->state(function (array $attributes) {
            $sessionAttendance = SessionAttendance::where('class_schedule_id', $attributes['class_schedule_id'])
                ->first();

            $totalHours = $sessionAttendance ? $sessionAttendance->total_hours : 2;

            return [
                'status' => 'excused',
                'hours_present' => 0,
                'hours_absent' => 0,
                'hours_permitted' => $totalHours,
                'hours_sick' => 0,
                'attendance_time' => null,
                'qr_token' => null,
                'remarks' => 'Excused absence: ' . $this->faker->sentence(),
            ];
        });
    }
}
