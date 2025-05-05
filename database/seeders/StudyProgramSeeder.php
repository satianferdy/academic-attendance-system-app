<?php

namespace Database\Seeders;

use App\Models\StudyProgram;
use Illuminate\Database\Seeder;

class StudyProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $studyPrograms = [
            [
                'name' => 'D-IV Teknik Informatika',
                'code' => 'TI',
                'degree_level' => 'D-IV',
                'faculty' => 'Fakultas Teknik',
                'description' => 'Program studi diploma empat tahun yang berfokus pada pengembangan aplikasi dan sistem informasi.',
            ],
            [
                'name' => 'D-IV Sistem Informasi Bisnis',
                'code' => 'SIB',
                'degree_level' => 'D-IV',
                'faculty' => 'Fakultas Teknik',
                'description' => 'Program studi diploma empat tahun yang berfokus pada penerapan sistem informasi dalam konteks bisnis.',
            ],
        ];

        foreach ($studyPrograms as $program) {
            StudyProgram::create($program);
        }
    }
}
