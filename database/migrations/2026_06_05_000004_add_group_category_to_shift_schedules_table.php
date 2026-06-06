<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_schedules', function (Blueprint $table) {
            $table->string('group_category')->nullable()->after('schedule_name');
            $table->index(['group_category', 'start_date', 'end_date'], 'shift_schedules_category_date_idx');
        });
    }

    public function down(): void
    {
        Schema::table('shift_schedules', function (Blueprint $table) {
            $table->dropIndex('shift_schedules_category_date_idx');
            $table->dropColumn('group_category');
        });
    }
};
