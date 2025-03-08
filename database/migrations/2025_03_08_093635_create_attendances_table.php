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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_schedule_id')->constrained();
            $table->foreignId('student_id')->constrained();
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('absent');
            $table->text('remarks')->nullable();
            $table->string('qr_token')->nullable();
            $table->timestamp('attendance_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
