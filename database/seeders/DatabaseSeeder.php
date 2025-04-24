<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SemesterSeeder::class,
            StudyProgramSeeder::class,
            CourseSeeder::class,
            AdminSeeder::class,
            ClassroomSeeder::class,
            LecturerSeeder::class,   // Added lecturer seeder
            StudentSeeder::class,    // Added student seeder
            ClassScheduleSeeder::class, // Added class schedule seeder
        ]);
    }
}
