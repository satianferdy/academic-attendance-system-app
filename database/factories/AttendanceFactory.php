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
        $statuses = ['present', 'absent', 'sick', 'permitted'];
        $status = $this->faker->randomElement($statuses);
        $sessionAttendance = SessionAttendance::inRandomOrder()->first();

        // If no session attendance exists, create one
        if (!$sessionAttendance) {
            $classSchedule = ClassSchedule::inRandomOrder()->first();
            if (!$classSchedule) {
                $classSchedule = ClassSchedule::factory()->create();
            }

            $sessionDate = Carbon::now()->subDays(rand(0, 30));
            $startTime = Carbon::parse($sessionDate->format('Y-m-d') . ' 08:00:00');
            $endTime = (clone $startTime)->addHours(rand(1, 3));

            $sessionAttendance = SessionAttendance::create([
                'class_schedule_id' => $classSchedule->id,
                'session_date' => $sessionDate,
                'week' => rand(1, 16),
                'meetings' => rand(1, 3),
                'start_time' => $startTime,
                'end_time' => $endTime,
                'total_hours' => $endTime->diffInHours($startTime),
                'tolerance_minutes' => 15,
                'qr_code' => $this->faker->uuid(),
                'is_active' => true
            ]);
        }

        $classSchedule = $sessionAttendance->classSchedule;

        // Get students who might be enrolled in this class
        $studyProgramId = $classSchedule->study_program_id;
        $student = Student::where('study_program_id', $studyProgramId)
            ->inRandomOrder()
            ->first();

        // If no matching student, create one
        if (!$student) {
            $student = Student::factory()->create([
                'study_program_id' => $studyProgramId,
                'classroom_id' => $classSchedule->classroom_id
            ]);
        }

        // Calculate hours based on status
        $hoursPresent = 0;
        $hoursAbsent = 0;
        $hoursSick = 0;
        $hoursPermitted = 0;
        $totalHours = $sessionAttendance->total_hours;

        switch ($status) {
            case 'present':
                $hoursPresent = $totalHours;
                $attendanceTime = Carbon::parse($sessionAttendance->session_date->format('Y-m-d') . ' ' .
                    $sessionAttendance->start_time->format('H:i:s'))->addMinutes(rand(0, 15));
                break;
            case 'absent':
                $hoursAbsent = $totalHours;
                $attendanceTime = null;
                break;
            case 'sick':
                $hoursSick = $totalHours;
                $attendanceTime = null;
                break;
            case 'permitted':
                $hoursPermitted = $totalHours;
                $attendanceTime = null;
                break;
            default:
                $attendanceTime = null;
        }

        // Random admin user for editing if needed
        $adminUser = User::whereHas('roles', function($query) {
            $query->where('name', 'admin');
        })->inRandomOrder()->first();

        $lastEditedBy = rand(0, 1) ? ($adminUser ? $adminUser->id : null) : null;
        $remarks = $status !== 'present' ? $this->faker->sentence() : null;

        return [
            'class_schedule_id' => $classSchedule->id,
            'student_id' => $student->id,
            'date' => $sessionAttendance->session_date,
            'status' => $status,
            'remarks' => $remarks,
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
            $sessionAttendance = SessionAttendance::find($attributes['class_schedule_id']);
            $totalHours = $sessionAttendance ? $sessionAttendance->total_hours : 2;

            return [
                'status' => 'present',
                'hours_present' => $totalHours,
                'hours_absent' => 0,
                'hours_permitted' => 0,
                'hours_sick' => 0,
                'attendance_time' => Carbon::parse($attributes['date'])->setTimeFromTimeString(
                    $sessionAttendance ? $sessionAttendance->start_time->format('H:i:s') : '08:00:00'
                )->addMinutes(rand(0, 10)),
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
            $sessionAttendance = SessionAttendance::find($attributes['class_schedule_id']);
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
     * State for sick attendance.
     */
    public function sick(): static
    {
        return $this->state(function (array $attributes) {
            $sessionAttendance = SessionAttendance::find($attributes['class_schedule_id']);
            $totalHours = $sessionAttendance ? $sessionAttendance->total_hours : 2;

            return [
                'status' => 'sick',
                'hours_present' => 0,
                'hours_absent' => 0,
                'hours_permitted' => 0,
                'hours_sick' => $totalHours,
                'attendance_time' => null,
                'qr_token' => null,
                'remarks' => 'Student was sick: ' . $this->faker->sentence(),
            ];
        });
    }

    /**
     * State for permitted attendance.
     */
    public function permitted(): static
    {
        return $this->state(function (array $attributes) {
            $sessionAttendance = SessionAttendance::find($attributes['class_schedule_id']);
            $totalHours = $sessionAttendance ? $sessionAttendance->total_hours : 2;

            return [
                'status' => 'permitted',
                'hours_present' => 0,
                'hours_absent' => 0,
                'hours_permitted' => $totalHours,
                'hours_sick' => 0,
                'attendance_time' => null,
                'qr_token' => null,
                'remarks' => 'Permission granted: ' . $this->faker->sentence(),
            ];
        });
    }
}
