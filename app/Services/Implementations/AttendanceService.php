<?php

namespace App\Services\Implementations;

use App\Models\Attendance;
use App\Models\ClassSchedule;
use App\Models\SessionAttendance;
use App\Models\Student;
use App\Services\Interfaces\AttendanceServiceInterface;
use Illuminate\Support\Facades\DB;
use App\Exceptions\AttendanceException;

class AttendanceService implements AttendanceServiceInterface
{
    // constants for confiquaration
    const SESSION_DURATION_MINUTES = 30; // in minutes

    public function markAttendance(int $classId, int $studentId, string $date): array
    {
        try {
            // Find the active session
            $session = SessionAttendance::where('class_schedule_id', $classId)
                ->where('session_date', $date)
                ->where('is_active', true)
                ->first();

            if (!$session) {
                throw new AttendanceException('No active attendance session found.');
            }

            // Check if the session is still open
            if ($session->end_time->isPast()) {
                throw new AttendanceException('Attendance session has expired.');
            }

            // Find attendance record
            $attendance = Attendance::where('class_schedule_id', $classId)
                ->where('student_id', $studentId)
                ->where('date', $date)
                ->first();

            if (!$attendance) {
                throw new AttendanceException('Attendance record not found.');
            }

            $attendance->update([
                'status' => 'present',
                'attendance_time' => now(),
            ]);

            return [
                'status' => 'success',
                'message' => 'Attendance marked successfully.'
            ];
        } catch (AttendanceException $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to mark attendance: ' . $e->getMessage()
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to mark attendance: ' . $e->getMessage()
            ];
        }
    }

    public function getAttendanceByClass(int $classId, string $date): array
    {
        $attendances = Attendance::with('student.user')
            ->where('class_schedule_id', $classId)
            ->where('date', $date)
            ->get();

        return $attendances->toArray();
    }

    public function generateSessionAttendance(ClassSchedule $classSchedule, string $date): array
    {
        DB::beginTransaction();

        try {
            // Set session duration (30 minutes from now)
            $startTime = now();
            $endTime = now()->addMinutes(self::SESSION_DURATION_MINUTES);

            // Create session if it doesn't exist
            $session = SessionAttendance::firstOrCreate(
                [
                    'class_schedule_id' => $classSchedule->id,
                    'session_date' => $date,
                ],
                [
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'is_active' => true,
                ]
            );

            // Get students enrolled in this class
            $students = $classSchedule->students;

            if ($students->isEmpty()) {
                throw new AttendanceException('No students enrolled in this class.');
            }

            // Create default absent attendances for all students
            foreach ($students as $student) {
                Attendance::firstOrCreate(
                    [
                        'class_schedule_id' => $classSchedule->id,
                        'student_id' => $student->id,
                        'date' => $date,
                    ],
                    [
                        'status' => 'absent',
                    ]
                );
            }

            DB::commit();
            return [
                'status' => 'success',
                'message' => 'Session and attendances generated successfully',
                'session_id' => $session->id,
                'session_expires' => $endTime->format('H:i')
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'status' => 'error',
                'message' => 'Failed to generate attendances: ' . $e->getMessage(),
            ];
        }
    }
}
