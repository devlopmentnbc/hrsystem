<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('recipient_name')->nullable();
            $table->json('to_emails');
            $table->json('cc_emails')->nullable();
            $table->time('scheduled_time');
            $table->boolean('is_active')->default(true);
            $table->boolean('notify_pending_leave')->default(false);
            $table->boolean('notify_missed_punch')->default(true);
            $table->boolean('notify_absent')->default(true);
            $table->boolean('notify_late')->default(true);
            $table->boolean('notify_early_out')->default(true);
            $table->boolean('notify_missing_roster')->default(false);
            $table->boolean('notify_ot_threshold')->default(false);
            $table->boolean('notify_weekend_holiday_punch')->default(false);
            $table->boolean('notify_repeated_half_day')->default(false);
            $table->timestamp('last_sent_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'scheduled_time'], 'attendance_notification_schedule_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_notification_settings');
    }
};