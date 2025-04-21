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
            [
                'name' => 'S1 Teknik Elektro',
                'code' => 'TE',
                'degree_level' => 'S1',
                'faculty' => 'Fakultas Teknik',
                'description' => 'Program sarjana empat tahun yang mempelajari tentang sistem kelistrikan dan elektronika.',
            ],
            [
                'name' => 'D-III Sistem Informasi',
                'code' => 'SI',
                'degree_level' => 'D-III',
                'faculty' => 'Fakultas Teknik',
                'description' => 'Program studi diploma tiga tahun yang berfokus pada sistem informasi dan teknologi database.',
            ],
        ];

        foreach ($studyPrograms as $program) {
            StudyProgram::create($program);
        }
    }
}
