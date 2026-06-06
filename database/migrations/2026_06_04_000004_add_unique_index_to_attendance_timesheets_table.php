<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('attendance_timesheets', function (Blueprint $table) {
            $table->unique(['person_id', 'recorded_at'], 'attendance_timesheets_person_recorded_unique');
        });
    }

    public function down()
    {
        Schema::table('attendance_timesheets', function (Blueprint $table) {
            $table->dropUnique('attendance_timesheets_person_recorded_unique');
        });
    }
};
