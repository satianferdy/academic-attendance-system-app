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
            $table->integer('week')->after('session_date')->nullable();
            $table->integer('meetings')->after('week')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('session_attendance', function (Blueprint $table) {
            $table->dropColumn('week');
            $table->dropColumn('meetings');
        });
    }
};
