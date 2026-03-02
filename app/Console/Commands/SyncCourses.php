<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\CourseLesson;
use App\Models\CourseMaterial;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SyncCourses extends Command
{
    protected $signature = 'courses:sync';
    protected $description = 'Sync courses from the research directory to the database';

    public function handle()
    {
        $researchPath = 'F:\\xcode\\office wrok\\realtorone\\realtorone-research\\courses';
        
        if (!File::exists($researchPath)) {
            $this->error("Research path not found: $researchPath");
            return;
        }

        $this->info("Scanning courses in $researchPath...");

        // 1. Cold Calling Program (Directory based)
        $this->syncColdCalling($researchPath . DIRECTORY_SEPARATOR . 'coldcallingmaster_program');

        // 2. Million Dirham Beliefs (Markdown based)
        $this->syncMillionDirham($researchPath . DIRECTORY_SEPARATOR . 'Million Dirham Beliefs Program');

        $this->info("Sync complete!");
    }

    private function syncColdCalling($path)
    {
        if (!File::isDirectory($path)) return;

        $this->info("Syncing Cold Calling Mastery Program...");
        
        $course = Course::updateOrCreate(
            ['title' => 'Realtor Cold Calling Mastery Program'],
            [
                'description' => 'A comprehensive program designed to master cold calling for realtors.',
                'min_tier' => 'Consultant',
            ]
        );

        $moduleDirs = File::directories($path);
        sort($moduleDirs);

        foreach ($moduleDirs as $index => $modulePath) {
            $moduleName = basename($modulePath);
            $module = CourseModule::updateOrCreate(
                ['course_id' => $course->id, 'title' => $moduleName],
                ['sequence' => $index + 1]
            );

            $lessonDirs = File::directories($modulePath);
            sort($lessonDirs);

            foreach ($lessonDirs as $lessonIndex => $lessonPath) {
                $lessonName = basename($lessonPath);
                $lesson = CourseLesson::updateOrCreate(
                    ['course_module_id' => $module->id, 'title' => $lessonName],
                    ['sequence' => $lessonIndex + 1, 'is_published' => true]
                );

                // Look for files
                $files = File::files($lessonPath);
                foreach ($files as $file) {
                    $ext = strtolower($file->getExtension());
                    $type = null;
                    if (in_array($ext, ['mp4', 'mov', 'avi'])) $type = 'Video';
                    elseif ($ext === 'pdf') $type = 'PDF';
                    elseif (in_array($ext, ['mp3', 'wav'])) $type = 'Audio';

                    if ($type) {
                        CourseMaterial::updateOrCreate(
                            ['course_lesson_id' => $lesson->id, 'type' => $type],
                            [
                                'title' => $file->getFilename(),
                                'url' => $file->getRealPath(), // Note: This is an absolute local path!
                                'count' => 1
                            ]
                        );
                    }
                }
            }
        }
    }

    private function syncMillionDirham($markdownPath)
    {
        if (!File::exists($markdownPath)) return;

        $this->info("Parsing Million Dirham Beliefs Program curriculum...");
        
        $course = Course::updateOrCreate(
            ['title' => 'Million Dirham Beliefs Program'],
            [
                'description' => 'Master your mindset and beliefs to attract million-dollar deals.',
                'min_tier' => 'Consultant',
            ]
        );

        $content = File::get($markdownPath);
        $lines = explode("\n", $content);

        $currentModule = null;
        $moduleCount = 0;
        $lessonCount = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            
            // Module level (### Day X)
            if (strpos($line, '###') === 0) {
                $title = trim(substr($line, 4));
                $currentModule = CourseModule::updateOrCreate(
                    ['course_id' => $course->id, 'title' => $title],
                    ['sequence' => ++$moduleCount]
                );
                $lessonCount = 0;
            } 
            // Lesson level (* **Title**)
            elseif ($currentModule && strpos($line, '* **') === 0 && strpos($line, '**') !== false) {
                preg_match('/\*\*([^*]+)\*\*/', $line, $matches);
                if (isset($matches[1])) {
                    $title = $matches[1];
                    $lesson = CourseLesson::updateOrCreate(
                        ['course_module_id' => $currentModule->id, 'title' => $title],
                        ['sequence' => ++$lessonCount, 'is_published' => true]
                    );

                    // Simplistic material detection based on the markdown description
                    if (stripos($line, 'Video') !== false) {
                        CourseMaterial::updateOrCreate(['course_lesson_id' => $lesson->id, 'type' => 'Video'], ['title' => 'Lesson Video', 'count' => 1]);
                    }
                    if (stripos($line, 'PDF') !== false) {
                        CourseMaterial::updateOrCreate(['course_lesson_id' => $lesson->id, 'type' => 'PDF'], ['title' => 'Workbook PDF', 'count' => 1]);
                    }
                    if (stripos($line, 'Audio') !== false) {
                        CourseMaterial::updateOrCreate(['course_lesson_id' => $lesson->id, 'type' => 'Audio'], ['title' => 'Audio Exercise', 'count' => 1]);
                    }
                }
            }
        }
    }
}
