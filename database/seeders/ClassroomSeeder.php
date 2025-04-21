<?php

namespace Database\Seeders;

use App\Models\Semester;
use App\Models\StudyProgram;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClassroomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset auto-increment if needed
        Schema::disableForeignKeyConstraints();
        DB::table('classrooms')->truncate();
        Schema::enableForeignKeyConstraints();

        // Get study programs
        $studyPrograms = StudyProgram::all();

        if ($studyPrograms->isEmpty()) {
            // Create study programs if none exist
            $this->call(StudyProgramSeeder::class);
            $studyPrograms = StudyProgram::all();
        }

        // Get the active semester
        $activeSemester = Semester::where('is_active', true)->first();

        if (!$activeSemester) {
            // Create semesters if none exist
            $this->call(SemesterSeeder::class);
            $activeSemester = Semester::where('is_active', true)->first();
        }

        // Create classrooms for each study program
        foreach ($studyPrograms as $program) {
            $classLevels = ['1A', '1B', '2A', '2B', '3A', '3B', '4A', '4B'];

            foreach ($classLevels as $level) {
                DB::table('classrooms')->insert([
                    'name' => $level,
                    'study_program_id' => $program->id,
                    'semester_id' => $activeSemester->id,
                    'capacity' => rand(25, 40),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
