<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('shift_schedules', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('last_updated_at');
        });
    }

    public function down()
    {
        Schema::table('shift_schedules', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
