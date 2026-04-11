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
        Schema::create('notification_workflow_triggers', function (Blueprint $table) {
            $table->id();
            $table->string('event_key')->unique(); // e.g., deal_room.client_added
            $table->string('display_name');
            $table->string('title_template');
            $table->text('body_template');
            $table->boolean('is_enabled')->default(true);
            $table->integer('delay_minutes')->default(0);
            $table->string('category')->default('general'); // momentum, deal_room, etc.
            $table->json('metadata')->nullable(); // for extra config
            $table->timestamps();
        });

        // Seed with some default triggers
        DB::table('notification_workflow_triggers')->insert([
            [
                'event_key' => 'deal_room.client_added',
                'display_name' => 'New Client in Deal Room',
                'title_template' => 'Welcome to your Deal Room!',
                'body_template' => 'A new deal has been initiated. Check your Deal Room to track progress.',
                'category' => 'deal_room',
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'event_key' => 'deal_room.status_updated',
                'display_name' => 'Deal Status Update',
                'title_template' => 'Deal Progress Update',
                'body_template' => 'Your deal has moved to the next stage. Check the app for details.',
                'category' => 'deal_room',
                'created_at' => now(), 'updated_at' => now()
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_workflow_triggers');
    }
};
