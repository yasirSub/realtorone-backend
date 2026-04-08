<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class CourseController extends Controller
{
    private function extractAssetFilename(?string $url): ?string
    {
        if (!$url) {
            return null;
        }
        $path = (string) parse_url($url, PHP_URL_PATH);
        if ($path === '') {
            return null;
        }
        $basename = basename($path);
        if ($basename === '' || $basename === '/' || $basename === '.') {
            return null;
        }
        return rawurldecode($basename);
    }

    private function deleteDirectorySafe(string $dir): void
    {
        if (is_dir($dir)) {
            File::deleteDirectory($dir);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'success' => true, 
            'data' => \App\Models\Course::where('is_published', true)
                ->orderBy('module_number')
                ->orderBy('sequence')
                ->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'min_tier' => 'required|in:Consultant,Rainmaker,Titan',
            'url' => 'nullable|string',
            'description' => 'nullable|string',
            'thumbnail_url' => 'nullable|string',
            'module_number' => 'nullable|integer|min:1',
            'sequence' => 'nullable|integer|min:0',
            'is_published' => 'nullable|boolean'
        ]);

        $course = \App\Models\Course::create($validated);
        return response()->json(['success' => true, 'data' => $course]);
    }

    /**
     * Display the specified resource (admin curriculum editor).
     * Loads all modules and lessons regardless of is_published so admins can see and edit everything.
     */
    public function show(string $id)
    {
        $course = \App\Models\Course::with([
            'modules' => function ($query) {
                $query->orderBy('sequence');
            },
            'modules.lessons' => function ($query) {
                $query->orderBy('sequence');
            },
            'modules.lessons.materials' => function ($query) {
                $query
                    ->orderByRaw("CASE WHEN LOWER(type) = 'video' THEN 0 WHEN LOWER(type) = 'pdf' THEN 1 ELSE 2 END")
                    ->orderBy('id');
            }
        ])->findOrFail($id);

        return response()->json(['success' => true, 'data' => $course]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $course = \App\Models\Course::findOrFail($id);
        $course->update($request->all());
        return response()->json(['success' => true, 'data' => $course]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        \App\Models\Course::destroy($id);
        return response()->json(['success' => true]);
    }

    // Module Management
    public function storeModule(Request $request, $courseId)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'sequence' => 'nullable|integer',
            'is_free' => 'nullable|boolean'
        ]);
        $validated['course_id'] = $courseId;
        $module = \App\Models\CourseModule::create($validated);
        return response()->json(['success' => true, 'data' => $module]);
    }

    public function updateModule(Request $request, $id)
    {
        $module = \App\Models\CourseModule::findOrFail($id);
        $module->update($request->all());
        return response()->json(['success' => true, 'data' => $module]);
    }

    public function destroyModule($id)
    {
        \App\Models\CourseModule::destroy($id);
        return response()->json(['success' => true]);
    }

    // Lesson Management
    public function storeLesson(Request $request, $moduleId)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'sequence' => 'nullable|integer',
            'is_published' => 'nullable|boolean',
            'allow_comments' => 'nullable|boolean',
            'is_preview' => 'nullable|boolean',
            'allow_video_download' => 'nullable|boolean',
            'allow_pdf_download' => 'nullable|boolean',
        ]);
        $validated['course_module_id'] = $moduleId;
        $lesson = \App\Models\CourseLesson::create($validated);
        return response()->json(['success' => true, 'data' => $lesson]);
    }

    public function updateLesson(Request $request, $id)
    {
        $lesson = \App\Models\CourseLesson::findOrFail($id);
        $lesson->update($request->all());
        return response()->json(['success' => true, 'data' => $lesson]);
    }

    public function destroyLesson($id)
    {
        \App\Models\CourseLesson::destroy($id);
        return response()->json(['success' => true]);
    }

    // Material Management
    public function storeMaterial(Request $request, $lessonId)
    {
        $validated = $request->validate([
            'title' => 'nullable|string',
            'type' => 'required|string',
            'url' => 'nullable|string',
            'thumbnail_url' => 'nullable|string',
            'show_download_link' => 'nullable|boolean',
            'subtitle_source' => 'nullable|string',
            'subtitles_url' => 'nullable|string',
            'audio_language' => 'nullable|string',
            'settings' => 'nullable|array',
            'count' => 'nullable|integer'
        ]);
        $validated['course_lesson_id'] = $lessonId;
        $material = \App\Models\CourseMaterial::create($validated);
        return response()->json(['success' => true, 'data' => $material]);
    }

    public function updateMaterial(Request $request, $id)
    {
        $material = \App\Models\CourseMaterial::findOrFail($id);
        $material->update($request->all());
        return response()->json(['success' => true, 'data' => $material]);
    }

    public function destroyMaterial($id)
    {
        \App\Models\CourseMaterial::destroy($id);
        return response()->json(['success' => true]);
    }

    // File Upload (Stores in public storage)
    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:52428800', // 50MB for audio/video
            'type' => 'required|string'
        ]);

        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('course-assets', $filename, 'public');
        $url = '/api/stream/' . rawurlencode($filename);

        return response()->json([
            'success' => true,
            'url' => $url,
            'path' => $path,
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType()
        ]);
    }

    public function downloadCourseBackup($id)
    {
        $course = \App\Models\Course::with(['modules.lessons.materials'])->findOrFail($id);
        $tempDir = storage_path('app/temp_course_backup_' . $course->id . '_' . time());
        $filesDir = $tempDir . DIRECTORY_SEPARATOR . 'files';
        $zipName = 'course_' . $course->id . '_backup_' . date('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path('app/' . $zipName);

        File::ensureDirectoryExists($filesDir);

        try {
            $modules = [];
            foreach ($course->modules as $module) {
                $lessons = [];
                foreach ($module->lessons as $lesson) {
                    $materials = [];
                    foreach ($lesson->materials as $material) {
                        $item = [
                            'title' => $material->title,
                            'type' => $material->type,
                            'url' => $material->url,
                            'thumbnail_url' => $material->thumbnail_url,
                            'show_download_link' => $material->show_download_link,
                            'subtitle_source' => $material->subtitle_source,
                            'subtitles_url' => $material->subtitles_url,
                            'audio_language' => $material->audio_language,
                            'settings' => $material->settings,
                            'count' => $material->count,
                        ];

                        $urlFile = $this->extractAssetFilename($material->url);
                        if ($urlFile) {
                            $source = storage_path('app/public/course-assets/' . $urlFile);
                            if (file_exists($source)) {
                                copy($source, $filesDir . DIRECTORY_SEPARATOR . $urlFile);
                                $item['url_file'] = $urlFile;
                            }
                        }

                        $thumbFile = $this->extractAssetFilename($material->thumbnail_url);
                        if ($thumbFile) {
                            $source = storage_path('app/public/course-assets/' . $thumbFile);
                            if (file_exists($source)) {
                                copy($source, $filesDir . DIRECTORY_SEPARATOR . $thumbFile);
                                $item['thumbnail_file'] = $thumbFile;
                            }
                        }
                        $materials[] = $item;
                    }
                    $lessons[] = [
                        'title' => $lesson->title,
                        'description' => $lesson->description,
                        'sequence' => $lesson->sequence,
                        'is_published' => (bool) $lesson->is_published,
                        'allow_comments' => (bool) $lesson->allow_comments,
                        'is_preview' => (bool) $lesson->is_preview,
                        'allow_video_download' => (bool) $lesson->allow_video_download,
                        'allow_pdf_download' => (bool) $lesson->allow_pdf_download,
                        'materials' => $materials,
                    ];
                }
                $modules[] = [
                    'title' => $module->title,
                    'description' => $module->description,
                    'sequence' => $module->sequence,
                    'is_free' => (bool) $module->is_free,
                    'is_published' => (bool) $module->is_published,
                    'lessons' => $lessons,
                ];
            }

            $payload = [
                'course' => [
                    'title' => $course->title,
                    'description' => $course->description,
                    'thumbnail_url' => $course->thumbnail_url,
                    'url' => $course->url,
                    'min_tier' => $course->min_tier,
                    'module_number' => $course->module_number,
                    'sequence' => $course->sequence,
                    'is_published' => (bool) $course->is_published,
                ],
                'modules' => $modules,
                'meta' => [
                    'exported_at' => now()->toIso8601String(),
                ],
            ];

            file_put_contents(
                $tempDir . DIRECTORY_SEPARATOR . 'course_backup.json',
                json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );

            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('Could not create backup zip.');
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($tempDir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                $filePath = $file->getPathname();
                $localPath = ltrim(str_replace($tempDir, '', $filePath), DIRECTORY_SEPARATOR);
                $zip->addFile($filePath, str_replace('\\', '/', $localPath));
            }
            $zip->close();

            return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
        } finally {
            $this->deleteDirectorySafe($tempDir);
        }
    }

    public function restoreCourseBackup(Request $request, $id)
    {
        $course = \App\Models\Course::findOrFail($id);
        $request->validate([
            'backup_file' => 'required|file|mimes:zip|max:512000', // 500MB
        ]);

        $tempDir = storage_path('app/temp_course_restore_' . $course->id . '_' . time());
        File::ensureDirectoryExists($tempDir);

        try {
            $zip = new \ZipArchive();
            if ($zip->open($request->file('backup_file')->getRealPath()) !== true) {
                throw new \RuntimeException('Could not open backup zip.');
            }
            $zip->extractTo($tempDir);
            $zip->close();

            $jsonPath = $tempDir . DIRECTORY_SEPARATOR . 'course_backup.json';
            if (!file_exists($jsonPath)) {
                throw new \RuntimeException('Backup metadata missing.');
            }
            $data = json_decode((string) file_get_contents($jsonPath), true);
            if (!is_array($data) || !isset($data['course']) || !isset($data['modules'])) {
                throw new \RuntimeException('Invalid backup format.');
            }

            $courseData = $data['course'];
            $course->update([
                'title' => $courseData['title'] ?? $course->title,
                'description' => $courseData['description'] ?? $course->description,
                'thumbnail_url' => $courseData['thumbnail_url'] ?? $course->thumbnail_url,
                'url' => $courseData['url'] ?? $course->url,
                'min_tier' => $courseData['min_tier'] ?? $course->min_tier,
                'module_number' => $courseData['module_number'] ?? $course->module_number,
                'sequence' => $courseData['sequence'] ?? $course->sequence,
                'is_published' => (bool) ($courseData['is_published'] ?? false),
            ]);

            // Clear old data safely? User might want to append or replace.
            // For courses, usually we want to "Restore" which means replace structure.
            foreach ($course->modules as $mod) {
                foreach ($mod->lessons as $les) {
                    \App\Models\CourseMaterial::where('course_lesson_id', $les->id)->delete();
                    $les->delete();
                }
                $mod->delete();
            }

            $filesDir = $tempDir . DIRECTORY_SEPARATOR . 'files';
            $assetTargetDir = storage_path('app/public/course-assets');
            File::ensureDirectoryExists($assetTargetDir);

            foreach ((array) $data['modules'] as $moduleData) {
                $module = \App\Models\CourseModule::create([
                    'course_id' => $course->id,
                    'title' => $moduleData['title'] ?? 'Untitled',
                    'description' => $moduleData['description'] ?? null,
                    'sequence' => $moduleData['sequence'] ?? 0,
                    'is_free' => (bool) ($moduleData['is_free'] ?? false),
                    'is_published' => (bool) ($moduleData['is_published'] ?? false),
                ]);

                foreach ((array) ($moduleData['lessons'] ?? []) as $lessonData) {
                    $lesson = \App\Models\CourseLesson::create([
                        'course_module_id' => $module->id,
                        'title' => $lessonData['title'] ?? 'Untitled',
                        'description' => $lessonData['description'] ?? null,
                        'sequence' => $lessonData['sequence'] ?? 0,
                        'is_published' => (bool) ($lessonData['is_published'] ?? false),
                        'allow_comments' => (bool) ($lessonData['allow_comments'] ?? false),
                        'is_preview' => (bool) ($lessonData['is_preview'] ?? false),
                        'allow_video_download' => (bool) ($lessonData['allow_video_download'] ?? false),
                        'allow_pdf_download' => (bool) ($lessonData['allow_pdf_download'] ?? false),
                    ]);

                    foreach ((array) ($lessonData['materials'] ?? []) as $materialData) {
                        $newUrl = $materialData['url'] ?? null;
                        $newThumb = $materialData['thumbnail_url'] ?? null;

                        if (!empty($materialData['url_file'])) {
                            $src = $filesDir . DIRECTORY_SEPARATOR . basename((string) $materialData['url_file']);
                            if (file_exists($src)) {
                                $newName = time() . '_' . \Illuminate\Support\Str::random(6) . '_' . basename((string) $materialData['url_file']);
                                copy($src, $assetTargetDir . DIRECTORY_SEPARATOR . $newName);
                                $newUrl = '/api/stream/' . rawurlencode($newName);
                            }
                        }

                        if (!empty($materialData['thumbnail_file'])) {
                            $src = $filesDir . DIRECTORY_SEPARATOR . basename((string) $materialData['thumbnail_file']);
                            if (file_exists($src)) {
                                $newName = time() . '_' . \Illuminate\Support\Str::random(6) . '_' . basename((string) $materialData['thumbnail_file']);
                                copy($src, $assetTargetDir . DIRECTORY_SEPARATOR . $newName);
                                $newThumb = '/api/stream/' . rawurlencode($newName);
                            }
                        }

                        \App\Models\CourseMaterial::create([
                            'course_lesson_id' => $lesson->id,
                            'title' => $materialData['title'] ?? null,
                            'type' => $materialData['type'] ?? 'PDF',
                            'url' => $newUrl,
                            'thumbnail_url' => $newThumb,
                            'show_download_link' => (bool) ($materialData['show_download_link'] ?? false),
                            'subtitle_source' => $materialData['subtitle_source'] ?? null,
                            'subtitles_url' => $materialData['subtitles_url'] ?? null,
                            'audio_language' => $materialData['audio_language'] ?? null,
                            'settings' => $materialData['settings'] ?? null,
                            'count' => (int) ($materialData['count'] ?? 1),
                        ]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Course restored successfully.',
            ]);
        } finally {
            $this->deleteDirectorySafe($tempDir);
        }
    }

    public function downloadLessonBackup($id)
    {
        $lesson = \App\Models\CourseLesson::with(['materials', 'module.course'])->findOrFail($id);
        $tempDir = storage_path('app/temp_lesson_backup_' . $lesson->id . '_' . time());
        $filesDir = $tempDir . DIRECTORY_SEPARATOR . 'files';
        $zipName = 'lesson_' . $lesson->id . '_backup_' . date('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path('app/' . $zipName);

        File::ensureDirectoryExists($filesDir);

        try {
            $materials = [];
            foreach ($lesson->materials as $material) {
                $item = [
                    'title' => $material->title,
                    'type' => $material->type,
                    'url' => $material->url,
                    'thumbnail_url' => $material->thumbnail_url,
                    'show_download_link' => $material->show_download_link,
                    'subtitle_source' => $material->subtitle_source,
                    'subtitles_url' => $material->subtitles_url,
                    'audio_language' => $material->audio_language,
                    'settings' => $material->settings,
                    'count' => $material->count,
                ];

                $urlFile = $this->extractAssetFilename($material->url);
                if ($urlFile) {
                    $source = storage_path('app/public/course-assets/' . $urlFile);
                    if (file_exists($source)) {
                        copy($source, $filesDir . DIRECTORY_SEPARATOR . $urlFile);
                        $item['url_file'] = $urlFile;
                    }
                }

                $thumbFile = $this->extractAssetFilename($material->thumbnail_url);
                if ($thumbFile) {
                    $source = storage_path('app/public/course-assets/' . $thumbFile);
                    if (file_exists($source)) {
                        copy($source, $filesDir . DIRECTORY_SEPARATOR . $thumbFile);
                        $item['thumbnail_file'] = $thumbFile;
                    }
                }

                $materials[] = $item;
            }

            $payload = [
                'lesson' => [
                    'title' => $lesson->title,
                    'description' => $lesson->description,
                    'sequence' => $lesson->sequence,
                    'is_published' => (bool) $lesson->is_published,
                    'allow_comments' => (bool) $lesson->allow_comments,
                    'is_preview' => (bool) $lesson->is_preview,
                    'allow_video_download' => (bool) $lesson->allow_video_download,
                    'allow_pdf_download' => (bool) $lesson->allow_pdf_download,
                ],
                'meta' => [
                    'course' => $lesson->module?->course?->title,
                    'module' => $lesson->module?->title,
                    'exported_at' => now()->toIso8601String(),
                ],
                'materials' => $materials,
            ];

            file_put_contents(
                $tempDir . DIRECTORY_SEPARATOR . 'lesson_backup.json',
                json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('Could not create backup zip.');
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($tempDir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                $filePath = $file->getPathname();
                $localPath = ltrim(str_replace($tempDir, '', $filePath), DIRECTORY_SEPARATOR);
                $zip->addFile($filePath, str_replace('\\', '/', $localPath));
            }
            $zip->close();

            return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
        } finally {
            $this->deleteDirectorySafe($tempDir);
        }
    }

    public function restoreLessonBackup(Request $request, $id)
    {
        $lesson = \App\Models\CourseLesson::with('materials')->findOrFail($id);
        $request->validate([
            'backup_file' => 'required|file|mimes:zip|max:102400',
        ]);

        $tempDir = storage_path('app/temp_lesson_restore_' . $lesson->id . '_' . time());
        File::ensureDirectoryExists($tempDir);

        try {
            $zip = new ZipArchive();
            if ($zip->open($request->file('backup_file')->getRealPath()) !== true) {
                throw new \RuntimeException('Could not open backup zip.');
            }
            $zip->extractTo($tempDir);
            $zip->close();

            $jsonPath = $tempDir . DIRECTORY_SEPARATOR . 'lesson_backup.json';
            if (!file_exists($jsonPath)) {
                throw new \RuntimeException('Backup metadata missing.');
            }
            $data = json_decode((string) file_get_contents($jsonPath), true);
            if (!is_array($data) || !isset($data['lesson']) || !isset($data['materials'])) {
                throw new \RuntimeException('Invalid backup format.');
            }

            $lessonData = $data['lesson'];
            $lesson->update([
                'title' => $lessonData['title'] ?? $lesson->title,
                'description' => $lessonData['description'] ?? null,
                'sequence' => $lessonData['sequence'] ?? $lesson->sequence,
                'is_published' => (bool) ($lessonData['is_published'] ?? false),
                'allow_comments' => (bool) ($lessonData['allow_comments'] ?? false),
                'is_preview' => (bool) ($lessonData['is_preview'] ?? false),
                'allow_video_download' => (bool) ($lessonData['allow_video_download'] ?? false),
                'allow_pdf_download' => (bool) ($lessonData['allow_pdf_download'] ?? false),
            ]);

            \App\Models\CourseMaterial::where('course_lesson_id', $lesson->id)->delete();

            $filesDir = $tempDir . DIRECTORY_SEPARATOR . 'files';
            $assetTargetDir = storage_path('app/public/course-assets');
            File::ensureDirectoryExists($assetTargetDir);

            $createdCount = 0;
            foreach ((array) $data['materials'] as $material) {
                $newUrl = $material['url'] ?? null;
                $newThumb = $material['thumbnail_url'] ?? null;

                if (!empty($material['url_file'])) {
                    $src = $filesDir . DIRECTORY_SEPARATOR . basename((string) $material['url_file']);
                    if (file_exists($src)) {
                        $newName = time() . '_' . Str::random(6) . '_' . basename((string) $material['url_file']);
                        copy($src, $assetTargetDir . DIRECTORY_SEPARATOR . $newName);
                        $newUrl = '/api/stream/' . rawurlencode($newName);
                    }
                }

                if (!empty($material['thumbnail_file'])) {
                    $src = $filesDir . DIRECTORY_SEPARATOR . basename((string) $material['thumbnail_file']);
                    if (file_exists($src)) {
                        $newName = time() . '_' . Str::random(6) . '_' . basename((string) $material['thumbnail_file']);
                        copy($src, $assetTargetDir . DIRECTORY_SEPARATOR . $newName);
                        $newThumb = '/api/stream/' . rawurlencode($newName);
                    }
                }

                \App\Models\CourseMaterial::create([
                    'course_lesson_id' => $lesson->id,
                    'title' => $material['title'] ?? null,
                    'type' => $material['type'] ?? 'PDF',
                    'url' => $newUrl,
                    'thumbnail_url' => $newThumb,
                    'show_download_link' => (bool) ($material['show_download_link'] ?? false),
                    'subtitle_source' => $material['subtitle_source'] ?? null,
                    'subtitles_url' => $material['subtitles_url'] ?? null,
                    'audio_language' => $material['audio_language'] ?? null,
                    'settings' => $material['settings'] ?? null,
                    'count' => (int) ($material['count'] ?? 1),
                ]);
                $createdCount++;
            }

            return response()->json([
                'success' => true,
                'message' => 'Lesson restored successfully.',
                'data' => ['materials_restored' => $createdCount],
            ]);
        } finally {
            $this->deleteDirectorySafe($tempDir);
        }
    }
}
