<?php

namespace Database\Factories;

use App\Models\StudyProgram;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudyProgramFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StudyProgram::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $programs = [
            [
                'name' => 'D-IV Teknik Informatika',
                'code' => 'TI',
                'degree_level' => 'D-IV',
                'faculty' => 'Fakultas Teknik'
            ],
            [
                'name' => 'D-IV Sistem Informasi Bisnis',
                'code' => 'SIB',
                'degree_level' => 'D-IV',
                'faculty' => 'Fakultas Teknik'
            ],
            [
                'name' => 'S1 Teknik Elektro',
                'code' => 'TE',
                'degree_level' => 'S1',
                'faculty' => 'Fakultas Teknik'
            ],
            [
                'name' => 'D-III Sistem Informasi',
                'code' => 'SI',
                'degree_level' => 'D-III',
                'faculty' => 'Fakultas Teknik'
            ],
        ];

        $program = $this->faker->unique()->randomElement($programs);

        return [
            'name' => $program['name'],
            'code' => $program['code'],
            'degree_level' => $program['degree_level'],
            'faculty' => $program['faculty'],
            'description' => $this->faker->paragraph(),
        ];
    }
}
