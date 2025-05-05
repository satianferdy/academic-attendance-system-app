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
        Schema::table('classrooms', function (Blueprint $table) {
            $table->foreignId('study_program_id')->nullable()->after('faculty')->constrained();
            $table->foreignId('semester_id')->nullable()->after('study_program_id')->constrained();
            $table->string('academic_year')->nullable()->after('semester_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropForeign(['study_program_id']);
            $table->dropColumn('study_program_id');
            $table->dropForeign(['semester_id']);
            $table->dropColumn('semester_id');
            $table->dropColumn('academic_year');
        });
    }
};
