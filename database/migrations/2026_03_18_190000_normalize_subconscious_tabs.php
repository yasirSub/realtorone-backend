<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tab 1: Mindset & Inner Strength
        $tab1 = [
            'visualization' => 1,
            'affirmations' => 2,
            'belief_exercise' => 3,
            'identity_statement' => 4,
            'gratitude_journaling' => 5,
            'calm_reset' => 6,
            'audio_reprogramming' => 7,
        ];

        foreach ($tab1 as $key => $order) {
            DB::table('activity_types')
                ->where('category', 'subconscious')
                ->where('type_key', $key)
                ->update([
                    'section_title' => 'Mindset & Inner Strength',
                    'section_order' => 1,
                    'item_order' => $order,
                ]);
        }

        // Tab 2: Growth & Daily Performance
        $tab2 = [
            'morning_focus_ritual' => 1,
            'mindset_training' => 2,
        ];

        foreach ($tab2 as $key => $order) {
            DB::table('activity_types')
                ->where('category', 'subconscious')
                ->where('type_key', $key)
                ->update([
                    'section_title' => 'Growth & Daily Performance',
                    'section_order' => 2,
                    'item_order' => $order,
                ]);
        }

        // Anything else subconscious goes to Tab 2 by default (keeps UI simple)
        DB::table('activity_types')
            ->where('category', 'subconscious')
            ->whereNotIn('type_key', array_merge(array_keys($tab1), array_keys($tab2)))
            ->update([
                'section_title' => 'Growth & Daily Performance',
                'section_order' => 2,
                'item_order' => 999,
            ]);
    }

    public function down(): void
    {
        // No-op: keep normalized structure once applied.
    }
};
