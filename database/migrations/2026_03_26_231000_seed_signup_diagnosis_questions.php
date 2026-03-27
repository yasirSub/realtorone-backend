<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('diagnosis_questions')) {
            return;
        }

        $now = now();

        $rows = [
            [
                'question_text' => 'How long have you been in real estate?',
                'display_order' => 1,
                'is_active' => true,
                'options_json' => json_encode([
                    ['text' => '0-6 months', 'blocker_type' => 'confidence', 'score' => 3],
                    ['text' => '6-18 months', 'blocker_type' => 'confidence', 'score' => 2],
                    ['text' => '1.5-3 years', 'blocker_type' => 'confidence', 'score' => 1],
                    ['text' => '3+ years', 'blocker_type' => 'confidence', 'score' => 0],
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'question_text' => 'What is your current monthly income from real estate (commission-based)?',
                'display_order' => 2,
                'is_active' => true,
                'options_json' => json_encode([
                    ['text' => "I haven't earned anything yet", 'blocker_type' => 'closing', 'score' => 4],
                    ['text' => 'Less than AED 5K', 'blocker_type' => 'closing', 'score' => 3],
                    ['text' => 'AED 5K - AED 15K', 'blocker_type' => 'closing', 'score' => 2],
                    ['text' => 'AED 15K - AED 40K', 'blocker_type' => 'closing', 'score' => 1],
                    ['text' => 'AED 40K+', 'blocker_type' => 'closing', 'score' => 0],
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'question_text' => 'How many deals do you close per month?',
                'display_order' => 3,
                'is_active' => true,
                'options_json' => json_encode([
                    ['text' => '0 deals', 'blocker_type' => 'closing', 'score' => 3],
                    ['text' => '1-2 deals', 'blocker_type' => 'closing', 'score' => 2],
                    ['text' => '3-5 deals', 'blocker_type' => 'closing', 'score' => 1],
                    ['text' => '5+ deals', 'blocker_type' => 'closing', 'score' => 0],
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'question_text' => 'Where do most of your clients come from?',
                'display_order' => 4,
                'is_active' => true,
                'options_json' => json_encode([
                    ['text' => "I don't have a fixed source yet", 'blocker_type' => 'leadGeneration', 'score' => 3],
                    ['text' => 'Company-provided leads', 'blocker_type' => 'leadGeneration', 'score' => 2],
                    ['text' => 'My personal network / referrals', 'blocker_type' => 'leadGeneration', 'score' => 1],
                    ['text' => 'My own marketing (ads, content, funnels)', 'blocker_type' => 'leadGeneration', 'score' => 0],
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'question_text' => 'What is your goal in the next 12 months?',
                'display_order' => 5,
                'is_active' => true,
                'options_json' => json_encode([
                    ['text' => 'Learn and close my first few deals', 'blocker_type' => 'discipline', 'score' => 3],
                    ['text' => 'Earn consistent monthly income', 'blocker_type' => 'discipline', 'score' => 2],
                    ['text' => 'Build a strong personal brand', 'blocker_type' => 'discipline', 'score' => 1],
                    ['text' => 'Build a team / scale to ₹1Cr+/AED 500K+', 'blocker_type' => 'discipline', 'score' => 0],
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'question_text' => 'Which market do you focus on?',
                'display_order' => 6,
                'is_active' => true,
                'options_json' => json_encode([
                    ['text' => 'Primary (Developers)', 'blocker_type' => 'confidence', 'score' => 1],
                    ['text' => 'Secondary (Resale)', 'blocker_type' => 'confidence', 'score' => 1],
                    ['text' => 'Rentals', 'blocker_type' => 'confidence', 'score' => 1],
                    ['text' => 'Mixed', 'blocker_type' => 'confidence', 'score' => 0],
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'question_text' => 'What is your biggest struggle right now?',
                'display_order' => 7,
                'is_active' => true,
                'options_json' => json_encode([
                    ['text' => 'Getting leads', 'blocker_type' => 'leadGeneration', 'score' => 3],
                    ['text' => 'Converting clients', 'blocker_type' => 'closing', 'score' => 3],
                    ['text' => 'Building trust', 'blocker_type' => 'confidence', 'score' => 3],
                    ['text' => 'Consistency', 'blocker_type' => 'discipline', 'score' => 3],
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Replace any existing rows so API/app/admin are fully DB-driven and consistent.
        DB::table('diagnosis_questions')->delete();
        DB::table('diagnosis_questions')->insert($rows);
    }

    public function down(): void
    {
        if (!Schema::hasTable('diagnosis_questions')) {
            return;
        }

        DB::table('diagnosis_questions')
            ->whereIn('display_order', [1, 2, 3, 4, 5, 6, 7])
            ->delete();
    }
};
