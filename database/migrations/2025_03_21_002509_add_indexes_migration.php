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
        Schema::table('session_attendance', function (Blueprint $table) {
            $table->index(['class_schedule_id', 'session_date']);
            $table->index(['is_active']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->index(['class_schedule_id', 'student_id', 'date']);
            $table->index(['class_schedule_id', 'date']);
        });

        Schema::table('class_schedules', function (Blueprint $table) {
            $table->index(['lecturer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('session_attendance', function (Blueprint $table) {
            $table->dropIndex(['class_schedule_id', 'session_date']);
            $table->dropIndex(['is_active']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['class_schedule_id', 'student_id', 'date']);
            $table->dropIndex(['class_schedule_id', 'date']);
        });

        Schema::table('class_schedules', function (Blueprint $table) {
            $table->dropIndex(['lecturer_id']);
        });
    }
};
