<?php

namespace Database\Seeders;

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
        // Reset auto-increment jika diperlukan
        Schema::disableForeignKeyConstraints();
        DB::table('classrooms')->truncate();
        Schema::enableForeignKeyConstraints();

        // Insert dummy classrooms
        DB::table('classrooms')->insert([
            ['name' => 'TI 1A', 'department' => 'Teknik Informatika', 'faculty' => 'Fakultas Teknik'],
            ['name' => 'TI 1B', 'department' => 'Teknik Informatika', 'faculty' => 'Fakultas Teknik'],
            ['name' => 'TI 1C', 'department' => 'Teknik Informatika', 'faculty' => 'Fakultas Teknik'],
            ['name' => 'TI 1D', 'department' => 'Teknik Informatika', 'faculty' => 'Fakultas Teknik'],
            ['name' => 'TI 1E', 'department' => 'Teknik Informatika', 'faculty' => 'Fakultas Teknik'],
            ['name' => 'TI 1F', 'department' => 'Teknik Informatika', 'faculty' => 'Fakultas Teknik'],
            ['name' => 'TI 1G', 'department' => 'Teknik Informatika', 'faculty' => 'Fakultas Teknik'],
            ['name' => 'TI 1H', 'department' => 'Teknik Informatika', 'faculty' => 'Fakultas Teknik'],
        ]);

    }
}
