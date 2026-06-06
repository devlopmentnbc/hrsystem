<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('correction_date');
            $table->foreignId('shift_schedule_assignment_id')->nullable()->constrained('shift_schedule_assignments')->nullOnDelete();
            $table->dateTime('original_check_in')->nullable();
            $table->dateTime('original_check_out')->nullable();
            $table->dateTime('corrected_check_in')->nullable();
            $table->dateTime('corrected_check_out')->nullable();
            $table->text('reason');
            $table->boolean('status')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['employee_id', 'correction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_corrections');
    }
};
