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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('shift_name');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('break_minutes')->default(0);
            $table->integer('grace_period_minutes')->default(0);
            $table->integer('ot_start_after_minutes')->default(0);
            $table->boolean('night_shift')->default(false);
            $table->decimal('full_day_hours', 5, 2)->default(8.00);
            $table->decimal('half_day_hours', 5, 2)->default(4.00);
            $table->text('remarks')->nullable();
            $table->boolean('status')->default(true);
            $table->unsignedBigInteger('last_updated_by')->nullable();
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
