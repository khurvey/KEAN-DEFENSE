<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained('users');

            $table->foreignId('faculty_id')
                ->constrained('users');

            $table->foreignId('faculty_subject_id')
                ->constrained('faculty_subjects');

            $table->string('concern');
            $table->text('details');

            $table->date('appointment_date');
            $table->time('appointment_time');

            $table->string('status')->default('Pending');
            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};