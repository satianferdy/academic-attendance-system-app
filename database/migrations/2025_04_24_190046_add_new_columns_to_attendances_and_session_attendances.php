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
        Schema::table('attendances', function (Blueprint $table) {
            $table->integer('hours_present')->default(0)->after('attendance_time');
            $table->integer('hours_absent')->default(0)->after('hours_present');
            $table->integer('hours_permitted')->default(0)->after('hours_absent');
            $table->integer('hours_sick')->default(0)->after('hours_permitted');
            $table->text('edit_notes')->nullable()->after('remarks');
            $table->timestamp('last_edited_at')->nullable()->after('edit_notes');
            $table->unsignedBigInteger('last_edited_by')->nullable()->after('last_edited_at');
        });

        Schema::table('session_attendance', function (Blueprint $table) {
            $table->integer('total_hours')->default(4)->after('end_time');
            $table->integer('tolerance_minutes')->default(15)->after('total_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'hours_present',
                'hours_absent',
                'hours_permitted',
                'hours_sick',
                'edit_notes',
                'last_edited_at',
                'last_edited_by'
            ]);
        });

        Schema::table('session_attendance', function (Blueprint $table) {
            $table->dropColumn(['total_hours', 'tolerance_minutes']);
        });
    }
};
