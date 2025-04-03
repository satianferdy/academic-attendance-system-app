<?php

namespace App\Services\Implementations;

use App\Exceptions\AttendanceException;
use App\Repositories\Interfaces\AttendanceRepositoryInterface;
use App\Repositories\Interfaces\ClassScheduleRepositoryInterface;
use App\Repositories\Interfaces\SessionAttendanceRepositoryInterface;
use App\Repositories\Interfaces\StudentRepositoryInterface;
use App\Services\Interfaces\AttendanceServiceInterface;
use Illuminate\Support\Facades\DB;

class AttendanceService implements AttendanceServiceInterface
{
    // constants for configuration
    const SESSION_DURATION_MINUTES = 30; // in minutes

    protected $attendanceRepository;
    protected $sessionRepository;
    protected $classScheduleRepository;
    protected $studentRepository;

    public function __construct(
        AttendanceRepositoryInterface $attendanceRepository,
        SessionAttendanceRepositoryInterface $sessionRepository,
        ClassScheduleRepositoryInterface $classScheduleRepository,
        StudentRepositoryInterface $studentRepository
    ) {
        $this->attendanceRepository = $attendanceRepository;
        $this->sessionRepository = $sessionRepository;
        $this->classScheduleRepository = $classScheduleRepository;
        $this->studentRepository = $studentRepository;
    }

    public function markAttendance(int $classId, int $studentId, string $date): array
    {
        try {
            // Find the active session
            $session = $this->sessionRepository->findActiveByClassAndDate($classId, $date);

            if (!$session) {
                throw new AttendanceException('No active attendance session found.');
            }

            // Check if the session is still open
            if ($session->end_time->isPast()) {
                throw new AttendanceException('Attendance session has expired.');
            }

            // Find attendance record
            $attendance = $this->attendanceRepository->findByClassStudentAndDate($classId, $studentId, $date);

            if (!$attendance) {
                throw new AttendanceException('Attendance record not found.');
            }

            $this->attendanceRepository->update($attendance, [
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
        $attendances = $this->attendanceRepository->getStudentAttendanceByClass($classId, $date);
        return $attendances->toArray();
    }

    public function isStudentEnrolled(int $studentId, int $classScheduleId): bool
    {
        return $this->studentRepository->isEnrolledInClass($studentId, $classScheduleId);
    }

    public function isAttendanceAlreadyMarked(int $studentId, int $classId, string $date): bool
    {
        $attendance = $this->attendanceRepository->findByClassStudentAndDate($classId, $studentId,  $date);
        return $attendance && $attendance->status === 'present';
    }

    public function isSessionActive(int $classId, string $date): bool
    {
        return $this->sessionRepository->findActiveByClassAndDate($classId, $date) !== null;
    }

    public function getStudentAttendances(int $studentId)
    {
        return $this->attendanceRepository->getStudentAttendances($studentId);
    }

    public function updateAttendanceStatus($attendance, string $status): array
    {
        try {
            $this->attendanceRepository->update($attendance, [
                'status' => $status,
            ]);

            return [
                'status' => 'success',
                'message' => 'Attendance status updated successfully'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to update attendance status: ' . $e->getMessage()
            ];
        }
    }

    public function generateSessionAttendance(int $classScheduleId, string $date): array
    {
        DB::beginTransaction();

        try {
            $classSchedule = $this->classScheduleRepository->find($classScheduleId);

            // Set session duration (30 minutes from now)
            $startTime = now();
            $endTime = now()->addMinutes(self::SESSION_DURATION_MINUTES);

            // Create session if it doesn't exist
            $session = $this->sessionRepository->createOrUpdate(
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
                $this->attendanceRepository->createOrUpdateByClassStudentDate(
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

