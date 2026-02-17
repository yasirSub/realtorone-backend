<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update activities table
        Schema::table('activities', function (Blueprint $table) {
            if (!Schema::hasColumn('activities', 'points')) {
                $table->integer('points')->default(0)->after('category');
            }
            if (!Schema::hasColumn('activities', 'quantity')) {
                $table->integer('quantity')->nullable()->after('points');
            }
            if (!Schema::hasColumn('activities', 'value')) {
                $table->decimal('value', 15, 2)->nullable()->after('quantity');
            }
            if (!Schema::hasColumn('activities', 'min_tier')) {
                $table->string('min_tier')->default('Consultant')->after('value');
            }
        });

        // Create performance_metrics table
        Schema::create('performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->integer('subconscious_score')->default(0);
            $table->integer('conscious_score')->default(0);
            $table->integer('results_score')->default(0);
            $table->integer('total_momentum_score')->default(0);
            $table->integer('leads_generated')->default(0);
            $table->integer('deals_closed')->default(0);
            $table->decimal('commission_earned', 15, 2)->default(0);
            $table->integer('streak_count')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_metrics');
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn(['points', 'quantity', 'value', 'min_tier']);
        });
    }
};
