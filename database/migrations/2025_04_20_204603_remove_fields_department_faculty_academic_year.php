<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove fields from students table
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['department', 'faculty']);
        });

        // Remove fields from lecturers table
        Schema::table('lecturers', function (Blueprint $table) {
            $table->dropColumn(['department', 'faculty']);
        });

        // Remove fields from classrooms table
        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropColumn(['department', 'faculty', 'academic_year']);
        });

        // Remove academic_year from class_schedules table
        Schema::table('class_schedules', function (Blueprint $table) {
            $table->dropColumn('academic_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add fields back to students table
        Schema::table('students', function (Blueprint $table) {
            $table->string('department')->nullable();
            $table->string('faculty')->nullable();
        });

        // Add fields back to lecturers table
        Schema::table('lecturers', function (Blueprint $table) {
            $table->string('department')->nullable();
            $table->string('faculty')->nullable();
        });

        // Add fields back to classrooms table
        Schema::table('classrooms', function (Blueprint $table) {
            $table->string('department')->nullable();
            $table->string('faculty')->nullable();
            $table->string('academic_year')->nullable();
        });

        // Add academic_year back to class_schedules table
        Schema::table('class_schedules', function (Blueprint $table) {
            $table->string('academic_year')->nullable();
        });
    }
};
