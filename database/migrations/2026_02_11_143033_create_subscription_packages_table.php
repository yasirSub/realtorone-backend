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
        Schema::create('subscription_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Free, Silver, Gold, Platinum
            $table->integer('tier_level')->default(0); // 0, 1, 2, 3
            $table->decimal('price_monthly', 10, 2)->default(0.00);
            $table->text('description')->nullable();
            $table->json('features')->nullable(); // list of what's included
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_packages');
    }
};
