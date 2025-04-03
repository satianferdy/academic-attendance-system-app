<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    public function run()
    {
        $courses = [
            ['name' => 'Algoritma dan Struktur Data', 'code' => 'CS101'],
            ['name' => 'Basis Data', 'code' => 'CS102'],
            ['name' => 'Pemrograman Web', 'code' => 'CS103'],
            ['name' => 'Jaringan Komputer', 'code' => 'CS104'],
            ['name' => 'Kecerdasan Buatan', 'code' => 'CS105'],
            ['name' => 'Sistem Operasi', 'code' => 'CS106'],
            ['name' => 'Keamanan Informasi', 'code' => 'CS107'],
            ['name' => 'Pengolahan Citra Digital', 'code' => 'CS108'],
            ['name' => 'Manajemen Proyek TI', 'code' => 'CS109'],
            ['name' => 'Pemrograman Mobile', 'code' => 'CS110'],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }
    }
}
