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
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('package_id')->constrained('subscription_packages')->onDelete('cascade');
            $table->timestamp('started_at');
            $table->timestamp('expires_at')->nullable();
            $table->string('status')->default('active'); // active, cancelled, expired
            $table->string('payment_method')->default('paypal');
            $table->string('payment_id')->nullable(); // PayPal transaction ID
            $table->decimal('amount_paid', 10, 2);
            $table->foreignId('coupon_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
