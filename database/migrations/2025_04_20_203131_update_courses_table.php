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
        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('study_program_id')->nullable()->after('code')->constrained();
            $table->integer('credits')->default(3)->after('study_program_id'); // Add credits (SKS)
            $table->text('description')->nullable()->after('credits');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['study_program_id']);
            $table->dropColumn(['study_program_id', 'credits', 'description']);
        });
    }
};
