<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Lecturer;
use App\Models\StudyProgram;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LecturerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all study programs
        $studyPrograms = StudyProgram::all();

        // If no study programs exist, call the StudyProgramSeeder first
        if ($studyPrograms->isEmpty()) {
            $this->call(StudyProgramSeeder::class);
            $studyPrograms = StudyProgram::all();
        }

        // Create lecturers for each study program
        $totalLecturers = 0;

        foreach ($studyPrograms as $program) {
            // Number of lecturers per study program (random between 3-7)
            $lecturerCount = rand(3, 5);

            for ($i = 1; $i <= $lecturerCount; $i++) {
                // Create a user with lecturer role
                $user = User::create([
                    'name' => fake()->name(),
                    'email' => fake()->unique()->safeEmail(),
                    'password' => Hash::make('password'), // Default password for testing
                    'role' => 'lecturer',
                ]);

                // Generate a unique lecturer ID (NIP)
                // Format: Program Code + Year + Sequential Number (e.g., TI2301)
                $programCode = $program->code;
                $yearPrefix = date('y'); // Current year's last two digits
                $sequentialNumber = str_pad($i, 2, '0', STR_PAD_LEFT);
                $nip = $programCode . $yearPrefix . $sequentialNumber;

                // Create a lecturer linked to the user
                Lecturer::create([
                    'user_id' => $user->id,
                    'nip' => $nip,
                ]);

                $totalLecturers++;
            }
        }

        $this->command->info("Created {$totalLecturers} lecturers across {$studyPrograms->count()} study programs.");
    }
}
