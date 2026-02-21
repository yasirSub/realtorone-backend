<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            return;
        }

        // SQLite: recreate results table with type allowing 'revenue_action'
        DB::statement('PRAGMA foreign_keys=off');
        DB::statement('BEGIN TRANSACTION');

        Schema::create('results_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->string('type'); // varchar - no restrictive CHECK
            $table->string('client_name')->nullable();
            $table->string('property_name')->nullable();
            $table->string('source')->nullable();
            $table->decimal('value', 15, 2)->default(0);
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'date']);
            $table->index(['user_id', 'type']);
        });

        DB::statement('INSERT INTO results_new (id, user_id, date, type, client_name, property_name, source, value, status, notes, created_at, updated_at) SELECT id, user_id, date, type, client_name, property_name, source, value, status, notes, created_at, updated_at FROM results');

        Schema::drop('results');
        Schema::rename('results_new', 'results');

        DB::statement('COMMIT');
        DB::statement('PRAGMA foreign_keys=on');
    }

    public function down(): void
    {
        // Not reversible without data loss for revenue_action rows
    }
};
