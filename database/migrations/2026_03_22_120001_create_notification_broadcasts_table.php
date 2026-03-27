<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('notification_broadcasts')) {
            Schema::create('notification_broadcasts', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('body');
                $table->string('display_style', 32)->default('standard');
                $table->string('audience', 32);
                $table->string('tier', 64)->nullable();
                $table->json('target_user_ids')->nullable();
                $table->dateTime('scheduled_at')->nullable();
                $table->string('recurrence_type', 16)->default('none');
                $table->string('recurrence_time', 8)->nullable();
                $table->unsignedTinyInteger('recurrence_day_of_week')->nullable();
                $table->string('timezone', 64)->default('UTC');
                $table->string('status', 32)->default('draft');
                $table->dateTime('next_run_at')->nullable();
                $table->dateTime('last_run_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('deep_link', 512)->nullable();
                $table->json('extra_data')->nullable();
                $table->unsignedInteger('last_sent_count')->default(0);
                $table->text('last_error')->nullable();
                $table->timestamps();

                $table->index(['status', 'next_run_at']);
            });
        }

        if (! Schema::hasTable('notification_automation_dedupes')) {
            Schema::create('notification_automation_dedupes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('rule_key', 64);
                $table->date('dedupe_date');
                $table->timestamps();

                $table->unique(['user_id', 'rule_key', 'dedupe_date'], 'notif_auto_dedupe_uidx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_automation_dedupes');
        Schema::dropIfExists('notification_broadcasts');
    }
};
