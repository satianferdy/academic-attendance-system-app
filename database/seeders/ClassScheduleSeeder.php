<?php

namespace Database\Seeders;

use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\Lecturer;
use App\Models\ClassSchedule;
use App\Models\ScheduleTimeSlot;
use App\Models\Semester;
use App\Models\StudyProgram;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ClassScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check for dependencies and seed if necessary
        $this->checkAndSeedDependencies();

        // Get active semester
        $activeSemester = Semester::where('is_active', true)->first();
        if (!$activeSemester) {
            $this->command->error('No active semester found. Make sure to run SemesterSeeder first.');
            return;
        }

        // Get all classrooms
        $classrooms = ClassRoom::all();

        // Get all lecturers
        $lecturers = Lecturer::all();

        if ($lecturers->isEmpty()) {
            $this->command->error('No lecturers found. Make sure to run LecturerSeeder first.');
            return;
        }

        // Days of the week
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        // Define sequential time slots (2-hour blocks)
        $timeSlotBlocks = [
            [
                ['07:00', '08:00'],
                ['08:00', '09:00']
            ],
            [
                ['09:00', '10:00'],
                ['10:00', '11:00']
            ],
            [
                ['11:00', '12:00'],
                ['12:00', '13:00']
            ],
            [
                ['13:00', '14:00'],
                ['14:00', '15:00']
            ],
            [
                ['15:00', '16:00']
            ]
        ];

        // Rooms
        $rooms = [
            'A101', 'A102', 'A103',
            'B201', 'B202', 'B203',
            'C301', 'C302', 'C303',
            'LAB01', 'LAB02', 'LAB03'
        ];

        $schedulesCreated = 0;
        $timeSlotsCreated = 0;

        // For each classroom, create schedules for ALL courses in the study program
        foreach ($classrooms as $classroom) {
            // Get courses for this classroom's study program
            $courses = Course::where('study_program_id', $classroom->study_program_id)->get();

            if ($courses->isEmpty()) {
                continue; // Skip if no courses for this study program
            }

            // Keep track of assigned days and times to avoid conflicts
            $assignedSlots = [];

            // Create a schedule for EACH course (not randomly selected ones)
            foreach ($courses as $course) {
                // Pick a random lecturer
                $lecturer = $lecturers->random();

                // Try to find a non-conflicting day and time slot
                $slotAssigned = false;
                $maxAttempts = 10; // Limit attempts to prevent infinite loops
                $attempts = 0;

                while (!$slotAssigned && $attempts < $maxAttempts) {
                    // Pick a random day
                    $day = $days[array_rand($days)];

                    // Pick a random time slot block
                    $blockIndex = array_rand($timeSlotBlocks);
                    $timeSlotBlock = $timeSlotBlocks[$blockIndex];

                    // Pick a random room
                    $room = $rooms[array_rand($rooms)];

                    // Check for conflicts (same day and time)
                    $slotKey = $day . '_' . $blockIndex . '_' . $room;

                    if (!in_array($slotKey, $assignedSlots)) {
                        // Mark this slot as assigned
                        $assignedSlots[] = $slotKey;
                        $slotAssigned = true;
                    }

                    $attempts++;
                }

                // If couldn't find a non-conflicting slot after max attempts, continue to next course
                if (!$slotAssigned) {
                    $this->command->warning("Could not find non-conflicting slot for {$course->name} in {$classroom->name} after {$maxAttempts} attempts.");
                    continue;
                }

                // Create class schedule
                $schedule = ClassSchedule::create([
                    'course_id' => $course->id,
                    'lecturer_id' => $lecturer->id,
                    'classroom_id' => $classroom->id,
                    'semester_id' => $activeSemester->id,
                    'study_program_id' => $classroom->study_program_id,
                    'room' => $room,
                    'day' => $day,
                    'semester' => $activeSemester->term, // For backward compatibility
                    'total_weeks' => 16,
                    'meetings_per_week' => 1,
                ]);

                // Create time slots for this schedule
                foreach ($timeSlotBlock as $timeSlot) {
                    ScheduleTimeSlot::create([
                        'class_schedule_id' => $schedule->id,
                        'start_time' => $timeSlot[0],
                        'end_time' => $timeSlot[1]
                    ]);
                    $timeSlotsCreated++;
                }

                $schedulesCreated++;
            }
        }

        $this->command->info("Created {$schedulesCreated} class schedules with {$timeSlotsCreated} time slots.");
    }

    /**
     * Check if dependencies exist and seed them if not
     */
    private function checkAndSeedDependencies()
    {
        // Check for courses
        if (Course::count() === 0) {
            $this->command->info('Seeding courses...');
            $this->call(CourseSeeder::class);
        }

        // Check for classrooms
        if (ClassRoom::count() === 0) {
            $this->command->info('Seeding classrooms...');
            $this->call(ClassroomSeeder::class);
        }

        // Check for lecturers
        if (Lecturer::count() === 0) {
            $this->command->info('Seeding lecturers...');
            $this->call(LecturerSeeder::class);
        }

        // Check for semesters
        if (Semester::count() === 0) {
            $this->command->info('Seeding semesters...');
            $this->call(SemesterSeeder::class);
        }
    }
}
