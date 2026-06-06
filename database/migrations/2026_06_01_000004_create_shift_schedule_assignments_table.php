<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shift_schedule_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shift_schedule_id');
            $table->unsignedBigInteger('shift_id');
            $table->date('scheduled_date');
            $table->unsignedBigInteger('shifts_group_id');
            $table->enum('assignment_type', ['work', 'off']);
            $table->timestamps();

            $table->foreign('shift_schedule_id')->references('id')->on('shift_schedules')->onDelete('cascade');
            $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('cascade');
            $table->foreign('shifts_group_id')->references('id')->on('shifts_groups')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('shift_schedule_assignments');
    }
};
