<?php

namespace Database\Seeders;

use App\Models\Semester;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SemesterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $semesters = [
            [
                'name' => '2023/2024 Ganjil',
                'academic_year' => '2023/2024',
                'term' => 'Ganjil',
                'start_date' => Carbon::create(2023, 8, 1),
                'end_date' => Carbon::create(2024, 1, 31),
                'is_active' => false,
            ],
            [
                'name' => '2023/2024 Genap',
                'academic_year' => '2023/2024',
                'term' => 'Genap',
                'start_date' => Carbon::create(2024, 2, 1),
                'end_date' => Carbon::create(2024, 7, 31),
                'is_active' => false,
            ],
            [
                'name' => '2024/2025 Ganjil',
                'academic_year' => '2024/2025',
                'term' => 'Ganjil',
                'start_date' => Carbon::create(2024, 8, 1),
                'end_date' => Carbon::create(2025, 1, 31),
                'is_active' => false, // Current active semester
            ],
            [
                'name' => '2024/2025 Genap',
                'academic_year' => '2024/2025',
                'term' => 'Genap',
                'start_date' => Carbon::create(2025, 2, 1),
                'end_date' => Carbon::create(2025, 7, 31),
                'is_active' => true,
            ],
        ];

        foreach ($semesters as $semester) {
            Semester::create($semester);
        }
    }
}
