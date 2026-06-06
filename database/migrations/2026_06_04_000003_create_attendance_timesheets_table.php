<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attendance_timesheets', function (Blueprint $table) {
            $table->id();
            $table->string('person_id')->nullable();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('department')->nullable();
            $table->dateTime('recorded_at')->nullable();
            $table->string('attendance_status')->nullable();
            $table->string('attendance_checkpoint')->nullable();
            $table->string('custom_name')->nullable();
            $table->string('data_source')->nullable();
            $table->string('handling_type')->nullable();
            $table->string('temperature')->nullable();
            $table->string('abnormal')->nullable();
            $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_timesheets');
    }
};
