<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Student;
use App\Models\ClassRoom;
use App\Models\StudyProgram;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all classrooms
        $classrooms = ClassRoom::all();

        // Get all study programs
        $studyPrograms = StudyProgram::all();

        // Keep track of global sequential number to ensure unique NIMs
        $globalSequential = 1;

        // Create students for each classroom
        foreach ($classrooms as $classroom) {
            // Get the study program for this classroom
            $studyProgram = $classroom->studyProgram;

            // Number of students per classroom (random between 5-10)
            $studentCount = rand(5, 8);

            for ($i = 1; $i <= $studentCount; $i++) {
                // Create a user with student role
                $user = User::create([
                    'name' => fake()->name(),
                    'email' => fake()->unique()->safeEmail(),
                    'password' => Hash::make('password'), // Default password for testing
                    'role' => 'student',
                ]);

                // Assign Spatie role
                $user->assignRole('student');

                // Generate a unique student ID (NIM)
                // Format: YY + Program Code + Sequential Number (e.g., 23TI001)
                $yearPrefix = date('y'); // Current year's last two digits
                $programCode = $studyProgram->code;
                $sequentialNumber = str_pad($globalSequential, 3, '0', STR_PAD_LEFT);
                $nim = $yearPrefix . $programCode . $sequentialNumber;

                // Create a student linked to the user and classroom
                Student::create([
                    'user_id' => $user->id,
                    'nim' => $nim,
                    'study_program_id' => $studyProgram->id,
                    'classroom_id' => $classroom->id,
                    'face_registered' => false, // Default to false
                ]);

                // Increment the global sequential number
                $globalSequential++;
            }
        }

        $this->command->info('Created ' . Student::count() . ' students across ' . $classrooms->count() . ' classrooms.');
    }
}
