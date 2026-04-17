<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use Illuminate\Support\Facades\DB;

class CourseMillionDirhamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $course = Course::updateOrCreate(
            ['title' => 'Million Dirham Beliefs Program'],
            [
                'description' => 'A comprehensive 24-day program to install a million-dollar realtor identity and mindset.',
                'min_tier' => 'Rainmaker',
            ]
        );

        // Check if modules already exist to preserve manual updates
        if (DB::table('course_modules')->where('course_id', $course->id)->count() > 0) {
            // Modules exist, skip seeding to avoid overwriting updates
            return;
        }

        // Delete existing modules to avoid duplicates (only for fresh seed)
        DB::table('course_modules')->where('course_id', $course->id)->delete();

        $modules = [
            [
                'title' => 'Day 1',
                'lessons' => [
                    ['title' => 'Congratulations & Welcome to Realtor One', 'materials' => [['type' => 'Text & Images', 'count' => 1], ['type' => 'Video', 'count' => 1]]],
                    ['title' => 'Chapter 1: The Blueprint for Success', 'materials' => [['type' => 'Text & Images', 'count' => 1], ['type' => 'Video', 'count' => 1]]],
                    ['title' => 'Download Your Million Dirham Beliefs Practice Workbook', 'materials' => [['type' => 'Text & Images', 'count' => 1], ['type' => 'PDF', 'count' => 1]]],
                ]
            ],
            [
                'title' => 'Day 2',
                'lessons' => [
                    ['title' => 'Chapter 2 : Model of Transformation', 'materials' => [['type' => 'Text & Images', 'count' => 2], ['type' => 'Video', 'count' => 1]]],
                    ['title' => '( Visualization) Million-Dollar Agent Identity & Manifesting Lucrative Deals', 'materials' => [['type' => 'Text & Images', 'count' => 1], ['type' => 'Video', 'count' => 1]]],
                ]
            ],
            [
                'title' => 'Day 3',
                'lessons' => [
                    ['title' => 'Chapter 3: Breaking the Habit of the Old – Letting Go of Limiting Beliefs to Become a Top Realtor', 'materials' => [['type' => 'Text & Images', 'count' => 1], ['type' => 'Video', 'count' => 1]]],
                    ['title' => 'Affirmations for a Millionaire Mindset', 'materials' => [['type' => 'Audio', 'count' => 1]]],
                ]
            ],
            [
                'title' => 'Day 4',
                'lessons' => [
                    ['title' => 'Chapter 4: Creating a New Reality', 'materials' => [['type' => 'Text & Images', 'count' => 1], ['type' => 'Video', 'count' => 1]]],
                    ['title' => 'Powerful Beliefs for Realtor One (PDF)', 'materials' => [['type' => 'PDF', 'count' => 1]]],
                ]
            ],
            [
                'title' => 'Day 5',
                'lessons' => [
                    ['title' => 'Chapter 5 : The Power of Your Inner Dialogue', 'materials' => [['type' => 'Text & Images', 'count' => 1], ['type' => 'Video', 'count' => 1]]],
                    ['title' => 'Million-Dollar Realtor Meditation', 'materials' => [['type' => 'Text & Images', 'count' => 1], ['type' => 'Audio', 'count' => 1]]],
                ]
            ],
            [
                'title' => 'Day 6',
                'lessons' => [
                    ['title' => 'Chapter 6: The Power of Visualization', 'materials' => [['type' => 'Text & Images', 'count' => 1], ['type' => 'Video', 'count' => 1]]],
                    ['title' => 'Guided Visualization Meditation', 'materials' => [['type' => 'Text & Images', 'count' => 1], ['type' => 'Audio', 'count' => 1]]],
                ]
            ],
            [
                'title' => 'Day 7',
                'lessons' => [
                    ['title' => 'Chapter 7: Living in Creation', 'materials' => [['type' => 'Text & Images', 'count' => 1], ['type' => 'Video', 'count' => 1]]],
                    ['title' => 'Guided Meditation', 'materials' => [['type' => 'Text & Images', 'count' => 1], ['type' => 'Audio', 'count' => 1]]],
                ]
            ],
            [
                'title' => 'Day 8',
                'lessons' => [
                    ['title' => 'Chapter 8: Mastering the Blueprint of Realtor One', 'materials' => [['type' => 'Text & Images', 'count' => 1], ['type' => 'Video', 'count' => 1]]],
                    ['title' => 'Mirror Practice', 'materials' => [['type' => 'Text & Images', 'count' => 1]]],
                ]
            ],
            [
                'title' => 'Day 9: Create & Install Your Million-Dollar Realtor Identity',
                'lessons' => [
                    ['title' => 'Top Realtors\' Tone to Close Big Deals', 'materials' => [['type' => 'Video', 'count' => 1]]],
                    ['title' => '4 Types of Clients Realtors Must Know', 'materials' => [['type' => 'Video', 'count' => 1]]],
                ]
            ],
            [
                'title' => 'Extra Bonuses: High-Performance Mindset',
                'lessons' => [
                    ['title' => 'Realtor One Deals and Money Affirmations', 'materials' => [['type' => 'Text & Images', 'count' => 1], ['type' => 'Audio', 'count' => 1]]],
                    ['title' => 'Affirmations for Wealth, Prosperity & Abundance', 'materials' => [['type' => 'Audio', 'count' => 1]]],
                    ['title' => 'Realtor One: The High-Ticket Sales Blueprint (e-Book)', 'materials' => [['type' => 'PDF', 'count' => 1]]],
                ]
            ],
            [
                'title' => 'Day 10 – Visualize & Attract Your Next 3 Clients',
                'lessons' => [
                    ['title' => 'Download Day 10 Workbook (PDF)', 'materials' => [['type' => 'PDF', 'count' => 1]]],
                    ['title' => 'Download Day 3 Workbook (PDF)', 'materials' => [['type' => 'PDF', 'count' => 1]]],
                    ['title' => 'Download Workbook (PDF)', 'materials' => [['type' => 'PDF', 'count' => 1]]],
                    ['title' => 'Download Day 9 Workbook (PDF)', 'materials' => [['type' => 'PDF', 'count' => 1]]],
                    ['title' => 'Download Day 7 Workbook (PDF)', 'materials' => [['type' => 'PDF', 'count' => 1]]],
                ]
            ],
            [
                'title' => 'Day 13 – Identity Alignment Self-Check',
                'lessons' => [
                    ['title' => 'Identity Alignment Check (Workbook Task)', 'materials' => [['type' => 'Text & Images', 'count' => 1]]],
                    ['title' => 'Download Day 13 Workbook (PDF)', 'materials' => [['type' => 'PDF', 'count' => 1]]],
                    ['title' => 'Download your 24-Day Practice Workbook', 'materials' => [['type' => 'Text & Images', 'count' => 1], ['type' => 'PDF', 'count' => 1]]],
                ]
            ],
            [
                'title' => 'Day 14 – Mid-Journey Reflection & Integration',
                'lessons' => [
                    ['title' => 'Reflection & Integration Check (Workbook Task)', 'materials' => [['type' => 'Text & Images', 'count' => 1]]],
                    ['title' => 'Download Day 14 Workbook (PDF)', 'materials' => [['type' => 'PDF', 'count' => 1]]],
                ]
            ],
            [
                'title' => 'Day 15: Visualize 3 Big Wins This Week',
                'lessons' => [
                    ['title' => 'Lock In Your Next 3 Closings (Workbook Task)', 'materials' => [['type' => 'Text & Images', 'count' => 1]]],
                    ['title' => 'Download Day 15 Workbook (PDF)', 'materials' => [['type' => 'PDF', 'count' => 1]]],
                ]
            ],
            [
                'title' => 'Day 16: Practice Real Closing Conversations',
                'lessons' => [
                    ['title' => 'Practicing Real Closings with Confidence', 'materials' => [['type' => 'Text & Images', 'count' => 1]]],
                ]
            ],
            [
                'title' => 'Day 17 – High-Performance Energy Activation',
                'lessons' => [
                    ['title' => 'High-Income Presence (Workbook Task)', 'materials' => [['type' => 'Text & Images', 'count' => 1]]],
                    ['title' => 'Download Day 17 Workbook (PDF)', 'materials' => [['type' => 'PDF', 'count' => 1]]],
                ]
            ],
            [
                'title' => 'Day 18: Activate Your High-Ticket Sales Energy',
                'lessons' => [
                    ['title' => 'High-Ticket Sales Blueprint (Workbook Task)', 'materials' => [['type' => 'Text & Images', 'count' => 1]]],
                    ['title' => 'Download Day 18 Workbook (PDF)', 'materials' => [['type' => 'PDF', 'count' => 1]]],
                ]
            ],
            [
                'title' => 'Day 19: Pre-Align with Affluent, High-Value Clients',
                'lessons' => [
                    ['title' => 'Attract Luxury & High-Value Clients (Workbook Task)', 'materials' => [['type' => 'Text & Images', 'count' => 1]]],
                    ['title' => 'Download Day 19 Workbook (PDF)', 'materials' => [['type' => 'PDF', 'count' => 1]]],
                ]
            ],
            [
                'title' => 'Day 20: Build Trust with Magnetic Content Creation',
                'lessons' => [
                    ['title' => 'Become Magnetic (Workbook Task)', 'materials' => [['type' => 'Text & Images', 'count' => 1]]],
                    ['title' => 'Download Day 20 Workbook (PDF)', 'materials' => [['type' => 'PDF', 'count' => 1]]],
                ]
            ],
            [
                'title' => 'Day 21: Expand Your Money Limits & Take Real Client Action',
                'lessons' => [
                    ['title' => 'Million-Dollar Identity (Workbook Task)', 'materials' => [['type' => 'Text & Images', 'count' => 1]]],
                    ['title' => 'Download Day 21 Workbook (PDF)', 'materials' => [['type' => 'PDF', 'count' => 1]]],
                ]
            ],
            [
                'title' => 'Day 22 – Expand Your Wealth Thermostat',
                'lessons' => [
                    ['title' => 'Wealth Thermostat Reset (Workbook Task)', 'materials' => [['type' => 'Text & Images', 'count' => 1]]],
                    ['title' => 'Download Day 22 Workbook (PDF)', 'materials' => [['type' => 'PDF', 'count' => 1]]],
                ]
            ],
            [
                'title' => 'Day 23: Activate Gratitude & Open New Client Doors',
                'lessons' => [
                    ['title' => 'Activate New Business with Gratitude (Workbook Task)', 'materials' => [['type' => 'Text & Images', 'count' => 1]]],
                    ['title' => 'Download Day 23 Workbook (PDF)', 'materials' => [['type' => 'PDF', 'count' => 1]]],
                ]
            ],
            [
                'title' => 'Day 24: Record Your 90-Day Future Vision',
                'lessons' => [
                    ['title' => '5 Live Masterclass with Aanant Bisht', 'materials' => [['type' => 'Text & Images', 'count' => 1]]],
                    ['title' => 'Download Day 24 Workbook (PDF)', 'materials' => [['type' => 'PDF', 'count' => 1]]],
                ]
            ],
        ];

        foreach ($modules as $mIndex => $mData) {
            $moduleId = DB::table('course_modules')->insertGetId([
                'course_id' => $course->id,
                'title' => $mData['title'],
                'sequence' => $mIndex + 1,
                'is_free' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($mData['lessons'] as $lIndex => $lData) {
                $lessonId = DB::table('course_lessons')->insertGetId([
                    'course_module_id' => $moduleId,
                    'title' => $lData['title'],
                    'sequence' => $lIndex + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($lData['materials'] as $matData) {
                    DB::table('course_materials')->insert([
                        'course_lesson_id' => $lessonId,
                        'type' => $matData['type'],
                        'count' => $matData['count'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
