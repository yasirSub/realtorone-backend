<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseColdCallingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $course = \App\Models\Course::updateOrCreate(
            ['title' => 'Realtor Cold Calling Mastery Program'],
            [
                'description' => 'A comprehensive program designed to master cold calling for realtors.',
                'min_tier' => 'Consultant',
            ]
        );

        $modules = [
            [
                'title' => 'Module 1 — Identity & Mindset for Cold Calling',
                'is_free' => true,
                'lessons' => [
                    [
                        'title' => 'Caller Identity Reset — The Mental Operating System for Cold Calling',
                        'materials' => [
                            ['type' => 'Video', 'count' => 1]
                        ]
                    ]
                ]
            ],
            [
                'title' => 'Module 2 — The Authority Call System',
                'is_free' => false,
                'lessons' => [
                    [
                        'title' => 'Part- 1 (Buyer Psychology)',
                        'materials' => [
                            ['type' => 'Video', 'count' => 1],
                            ['type' => 'PDF', 'count' => 1]
                        ]
                    ],
                    [
                        'title' => 'Part- 2 (The Authority Call Framework)',
                        'materials' => [
                            ['type' => 'Video', 'count' => 1],
                            ['type' => 'PDF', 'count' => 1]
                        ]
                    ],
                    [
                        'title' => 'Part- 3 (Strategic Questioning (ISA Method))',
                        'materials' => [
                            ['type' => 'Video', 'count' => 1],
                            ['type' => 'PDF', 'count' => 1]
                        ]
                    ],
                    [
                        'title' => 'Part- 4 (Tonality & Daily Execution)',
                        'materials' => [
                            ['type' => 'Video', 'count' => 1],
                            ['type' => 'PDF', 'count' => 1]
                        ]
                    ],
                    [
                        'title' => 'Part- 5 (Objection Handling)',
                        'materials' => [
                            ['type' => 'Video', 'count' => 1],
                            ['type' => 'PDF', 'count' => 1]
                        ]
                    ]
                ]
            ],
            [
                'title' => 'Module 3 — Tonality & Call Presence',
                'is_free' => false,
                'lessons' => [
                    [
                        'title' => 'Tonality Practice & Real Call Application',
                        'materials' => [
                            ['type' => 'Video', 'count' => 1]
                        ]
                    ]
                ]
            ],
            [
                'title' => 'Module 4 — Weekly Ritual Practice',
                'is_free' => false,
                'lessons' => [
                    [
                        'title' => 'The 10-Minute Weekly Ritual',
                        'materials' => [
                            ['type' => 'PDF', 'count' => 1]
                        ]
                    ]
                ]
            ],
            [
                'title' => 'Module 5 — Cold Calling Digital Programme – Workbook',
                'is_free' => false,
                'lessons' => [
                    [
                        'title' => 'How to recover when calls go wrong',
                        'materials' => [
                            ['type' => 'PDF', 'count' => 1]
                        ]
                    ]
                ]
            ]
        ];

        // Check if modules already exist to preserve manual updates
        if (\DB::table('course_modules')->where('course_id', $course->id)->count() > 0) {
            // Modules exist, skip seeding to avoid overwriting updates
            return;
        }

        // Delete existing modules to avoid duplicates (only for fresh seed)
        \DB::table('course_modules')->where('course_id', $course->id)->delete();

        foreach ($modules as $moduleSequence => $moduleData) {
            $module = \DB::table('course_modules')->insertGetId([
                'course_id' => $course->id,
                'title' => $moduleData['title'],
                'is_free' => $moduleData['is_free'],
                'sequence' => $moduleSequence + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($moduleData['lessons'] as $lessonSequence => $lessonData) {
                $lesson = \DB::table('course_lessons')->insertGetId([
                    'course_module_id' => $module,
                    'title' => $lessonData['title'],
                    'sequence' => $lessonSequence + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($lessonData['materials'] as $materialData) {
                    \DB::table('course_materials')->insert([
                        'course_lesson_id' => $lesson,
                        'type' => $materialData['type'],
                        'count' => $materialData['count'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
