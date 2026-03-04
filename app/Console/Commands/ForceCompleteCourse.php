<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ForceCompleteCourse extends Command
{
    protected $signature = 'course:force-complete {email} {course_id?}';
    protected $description = 'Mark a course 100% complete for a user. course_id optional (default: Cold Calling course).';

    public function handle(): int
    {
        $email = $this->argument('email');
        $courseId = $this->argument('course_id');

        if (!$courseId) {
            $course = DB::table('courses')->where('title', 'like', '%Realtor Cold Calling%')->first();
            if (!$course) {
                $this->error('Cold Calling course not found. Provide course_id explicitly.');
                return 1;
            }
            $courseId = $course->id;
            $this->info("Using course: {$course->title} (id: {$courseId})");
        } else {
            $course = DB::table('courses')->find($courseId);
            if (!$course) {
                $this->error("Course id {$courseId} not found.");
                return 1;
            }
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("User {$email} not found.");
            return 1;
        }

        $materialIds = DB::table('course_materials')
            ->join('course_lessons', 'course_lessons.id', '=', 'course_materials.course_lesson_id')
            ->join('course_modules', 'course_modules.id', '=', 'course_lessons.course_module_id')
            ->where('course_modules.course_id', $courseId)
            ->pluck('course_materials.id');

        $now = now();
        foreach ($materialIds as $mid) {
            $exists = DB::table('course_material_progress')
                ->where('user_id', $user->id)
                ->where('material_id', $mid)
                ->exists();
            if ($exists) {
                DB::table('course_material_progress')
                    ->where('user_id', $user->id)
                    ->where('material_id', $mid)
                    ->update(['is_completed' => true, 'completed_at' => $now, 'updated_at' => $now]);
            } else {
                DB::table('course_material_progress')->insert([
                    'user_id' => $user->id,
                    'material_id' => $mid,
                    'progress_seconds' => 0,
                    'is_completed' => true,
                    'completed_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $exists = DB::table('course_progress')
            ->where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->exists();
        if ($exists) {
            DB::table('course_progress')
                ->where('user_id', $user->id)
                ->where('course_id', $courseId)
                ->update([
                    'progress_percent' => 100,
                    'is_completed' => true,
                    'completed_at' => $now,
                    'last_accessed_at' => $now,
                    'updated_at' => $now,
                ]);
        } else {
            DB::table('course_progress')->insert([
                'user_id' => $user->id,
                'course_id' => $courseId,
                'progress_percent' => 100,
                'is_completed' => true,
                'completed_at' => $now,
                'last_accessed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->info("Course marked 100% complete for {$email}");
        return 0;
    }
}
