<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('activity_types')
            ->where('category', 'subconscious')
            ->where(function ($query) {
                $query->whereNull('section_title')->orWhere('section_title', '');
            })
            ->update([
                'section_title' => 'Identity Conditioning',
                'section_order' => 99,
                'item_order' => 999,
            ]);

        DB::table('activity_types')
            ->where('category', 'conscious')
            ->where(function ($query) {
                $query->whereNull('section_title')->orWhere('section_title', '');
            })
            ->update([
                'section_title' => 'Revenue Actions',
                'section_order' => 99,
                'item_order' => 999,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: keep section data once it exists.
    }
};
