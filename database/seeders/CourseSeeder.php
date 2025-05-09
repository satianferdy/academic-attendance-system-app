<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\StudyProgram;

class CourseSeeder extends Seeder
{
    public function run()
    {
        // Get study programs
        $studyPrograms = StudyProgram::all();

        // Assign courses to study programs
        $tiProgram = $studyPrograms->where('code', 'TI')->first();
        $sibProgram = $studyPrograms->where('code', 'SIB')->first();

        // TI Program Courses
        $tiCourses = [
            ['name' => 'Algoritma dan Struktur Data', 'code' => 'TI101', 'credits' => 3],
            ['name' => 'Basis Data Lanjut', 'code' => 'TI102', 'credits' => 3],
            ['name' => 'Pemrograman Web', 'code' => 'TI103', 'credits' => 4],
            ['name' => 'Jaringan Komputer', 'code' => 'TI104', 'credits' => 3],
            ['name' => 'Kecerdasan Buatan', 'code' => 'TI107', 'credits' => 3],
        ];

        // SIB Program Courses
        $sibCourses = [
            ['name' => 'Sistem Informasi Bisnis', 'code' => 'SIB101', 'credits' => 3],
            ['name' => 'Analisis Proses Bisnis', 'code' => 'SIB102', 'credits' => 3],
            ['name' => 'Manajemen Basis Data', 'code' => 'SIB103', 'credits' => 3],
            ['name' => 'Pengembangan Sistem Informasi', 'code' => 'SIB104', 'credits' => 4],
            ['name' => 'Keamanan Sistem Informasi', 'code' => 'SIB105', 'credits' => 3],
        ];

        // Insert courses for TI Program
        $this->insertCourses($tiCourses, $tiProgram->id);

        // Insert courses for SIB Program
        $this->insertCourses($sibCourses, $sibProgram->id);
    }

    private function insertCourses($courses, $programId)
    {
        foreach ($courses as $course) {
            Course::create([
                'name' => $course['name'],
                'code' => $course['code'],
                'study_program_id' => $programId,
                'credits' => $course['credits'],
                'description' => 'Course description for ' . $course['name'],
            ]);
        }
    }
}
