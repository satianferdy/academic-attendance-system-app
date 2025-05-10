<?php

namespace App\Services\Implementations;

use App\Exceptions\AttendanceException;
use App\Repositories\Interfaces\AttendanceRepositoryInterface;
use App\Repositories\Interfaces\ClassScheduleRepositoryInterface;
use App\Repositories\Interfaces\SessionAttendanceRepositoryInterface;
use App\Repositories\Interfaces\StudentRepositoryInterface;
use App\Services\Interfaces\AttendanceServiceInterface;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceService implements AttendanceServiceInterface
{
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

     /**
     * Mark attendance with hourly tracking
     */
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

            // Calculate hourly attendance
            $attendanceHours = $this->calculateHourlyAttendance(
                $session->start_time,
                now(),
                $session->total_hours,
                $session->tolerance_minutes
            );

            // Update attendance record
            $this->attendanceRepository->update($attendance, [
                'status' => ($attendanceHours['hours_present'] > 0) ? 'present' : 'absent',
                'attendance_time' => now(),
                'hours_present' => $attendanceHours['hours_present'],
                'hours_absent' => $attendanceHours['hours_absent'],
                'hours_permitted' => $attendanceHours['hours_permitted'],
                'hours_sick' => $attendanceHours['hours_sick']
            ]);

            return [
                'status' => 'success',
                'message' => 'Attendance marked successfully.',
                'attendance_hours' => $attendanceHours
            ];
        } catch (AttendanceException $e) {
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
        $session = $this->sessionRepository->findActiveByClassAndDate($classId, $date);

        if(!$session) {
            return false;
        }

        // Add check for past session date
        $currentTime = now()->setTimezone(config('app.timezone'));
        $sessionDate = Carbon::parse($date)->setTimezone(config('app.timezone'));

        if ($currentTime->copy()->startOfDay()->isAfter($sessionDate->copy()->startOfDay())) {
            return false; // Session date is in the past
        }

        // Check if current time is after session end time
        return $currentTime->lessThanOrEqualTo($session->end_time);
    }

    public function getStudentAttendances(int $studentId)
    {
        return $this->attendanceRepository->getStudentAttendances($studentId);
    }

     /**
     * Update attendance status with hourly breakdown
     */
    public function updateAttendanceStatus($attendance, array $data): array
    {
        try {
            $totalHours = $attendance->classSchedule->timeSlots->count() ?: 4;

            $updateData = [
                'status' => $data['status'] ?? $attendance->status,
                'remarks' => $data['remarks'] ?? $attendance->remarks,
                'last_edited_at' => now(),
                'last_edited_by' => Auth::check() ? Auth::id() : null,
                'edit_notes' => $data['edit_notes'] ?? null
            ];

            // Update hourly breakdown
            if (isset($data['hours_present'])) $updateData['hours_present'] = $data['hours_present'];
            if (isset($data['hours_absent'])) $updateData['hours_absent'] = $data['hours_absent'];
            if (isset($data['hours_permitted'])) $updateData['hours_permitted'] = $data['hours_permitted'];
            if (isset($data['hours_sick'])) $updateData['hours_sick'] = $data['hours_sick'];

            $this->attendanceRepository->update($attendance, $updateData);

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

    public function generateSessionAttendance(int $classScheduleId, string $date, int $week, int $meetings, int $totalHours = 4, int $toleranceMinutes = 15 ): array
    {
        DB::beginTransaction();

        try {
            $classSchedule = $this->classScheduleRepository->find($classScheduleId);

            // Check if a session already exists for this date
            if ($this->sessionRepository->sessionExistsForDate($classScheduleId, $date)) {
                throw new AttendanceException('An attendance session already exists for this date.');
            }

            // Check if this week/meeting combo already exists
            $existingSession = $this->sessionRepository->findByClassWeekAndMeeting(
                $classScheduleId,
                $week,
                $meetings,
            );

            if ($existingSession) {
                throw new AttendanceException('Attendance session for this week and meeting already exists.');
            }

            // CRITICAL FIX: Calculate end time properly
            $startTime = now()->setTimezone(config('app.timezone'));

            // For sessions created late in the day, ensure end time is set properly
            // If adding total hours would make it go to the next day, it's better to
            // set a reasonable end time (e.g., 11:59 PM of the same day)
            $maxEndTime = Carbon::parse($date)->setTimezone(config('app.timezone'))->endOfDay();
            $calculatedEndTime = $startTime->copy()->addHours($totalHours);

            // Use the earlier of calculated end time or end of day
            $endTime = ($calculatedEndTime > $maxEndTime) ? $maxEndTime : $calculatedEndTime;

            // Create session if it doesn't exist
            $session = $this->sessionRepository->createOrUpdate(
                [
                    'class_schedule_id' => $classSchedule->id,
                    'session_date' => $date,
                    'week' => $week,
                    'meetings' => $meetings,
                ],
                [
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'is_active' => true,
                    'total_hours' => $totalHours,
                    'tolerance_minutes' => $toleranceMinutes,
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
                        'hours_absent' => $totalHours,
                        'hours_present' => 0,
                        'hours_permitted' => 0,
                        'hours_sick' => 0,
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

    /**
     * Calculate hourly attendance based on arrival time
     */
    public function calculateHourlyAttendance($startTime, $arrivalTime, $totalHours, $toleranceMinutes)
    {
        $hoursPresent = 0;
        $hoursAbsent = 0;

        $startTime = Carbon::parse($startTime)->setTimezone(config('app.timezone'));
        $arrivalTime = Carbon::parse($arrivalTime)->setTimezone(config('app.timezone'));

        for ($hour = 0; $hour < $totalHours; $hour++) {
            $hourStart = $startTime->copy()->addHours($hour);
            $hourEnd = $hourStart->copy()->addHours(1);

            // Calculate the cutoff time (hour start + tolerance)
            $cutoffTime = $hourStart->copy()->addMinutes($toleranceMinutes);

            // If arrival time is before cutoff time for this hour
            if ($arrivalTime <= $cutoffTime) {
                $hoursPresent++;
            } else {
                // If arrival is after cutoff for this hour but before next hour
                if ($arrivalTime < $hourEnd) {
                    $hoursAbsent++;
                    // Update arrival time to next hour for subsequent calculations
                    $arrivalTime = $hourEnd;
                } else {
                    $hoursAbsent++;
                }
            }
        }

        return [
            'hours_present' => $hoursPresent,
            'hours_absent' => $hoursAbsent,
            'hours_permitted' => 0, // Initially 0, can be updated by lecturer
            'hours_sick' => 0       // Initially 0, can be updated by lecturer
        ];
    }

    /**
     * Get cumulative attendance for a class
     */
    public function getCumulativeAttendance(int $classId, int $studentId = null)
    {
        $attendances = $this->attendanceRepository->getAttendancesByClassAndStudent($classId, $studentId);

        $summary = [
            'total_present' => $attendances->sum('hours_present'),
            'total_absent' => $attendances->sum('hours_absent'),
            'total_permitted' => $attendances->sum('hours_permitted'),
            'total_sick' => $attendances->sum('hours_sick'),
        ];

        return $summary;
    }
}

