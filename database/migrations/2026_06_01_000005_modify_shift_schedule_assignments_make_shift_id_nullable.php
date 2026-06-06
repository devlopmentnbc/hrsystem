<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
            DB::statement('ALTER TABLE shift_schedule_assignments RENAME TO _tmp_shift_schedule_assignments');

            Schema::create('shift_schedule_assignments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('shift_schedule_id');
                $table->unsignedBigInteger('shift_id')->nullable();
                $table->date('scheduled_date');
                $table->unsignedBigInteger('shifts_group_id');
                $table->enum('assignment_type', ['work', 'off']);
                $table->timestamps();

                $table->foreign('shift_schedule_id')->references('id')->on('shift_schedules')->onDelete('cascade');
                $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('cascade');
                $table->foreign('shifts_group_id')->references('id')->on('shifts_groups')->onDelete('cascade');
            });

            DB::statement('INSERT INTO shift_schedule_assignments (id, shift_schedule_id, shift_id, scheduled_date, shifts_group_id, assignment_type, created_at, updated_at) SELECT id, shift_schedule_id, shift_id, scheduled_date, shifts_group_id, assignment_type, created_at, updated_at FROM _tmp_shift_schedule_assignments');
            Schema::dropIfExists('_tmp_shift_schedule_assignments');
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            Schema::table('shift_schedule_assignments', function (Blueprint $table) {
                $table->unsignedBigInteger('shift_id')->nullable()->change();
            });
        }
    }

    public function down()
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
            DB::statement('ALTER TABLE shift_schedule_assignments RENAME TO _tmp_shift_schedule_assignments');

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

            DB::statement('INSERT INTO shift_schedule_assignments (id, shift_schedule_id, shift_id, scheduled_date, shifts_group_id, assignment_type, created_at, updated_at) SELECT id, shift_schedule_id, shift_id, scheduled_date, shifts_group_id, assignment_type, created_at, updated_at FROM _tmp_shift_schedule_assignments');
            Schema::dropIfExists('_tmp_shift_schedule_assignments');
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            Schema::table('shift_schedule_assignments', function (Blueprint $table) {
                $table->unsignedBigInteger('shift_id')->nullable(false)->change();
            });
        }
    }
};
