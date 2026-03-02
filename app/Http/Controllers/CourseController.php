<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CourseController extends Controller
{
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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $course = \App\Models\Course::with([
            'modules' => function($query) {
                $query->where('is_published', true)->orderBy('sequence');
            },
            'modules.lessons' => function($query) {
                $query->where('is_published', true)->orderBy('sequence');
            },
            'modules.lessons.materials'
        ])
        ->where('is_published', true)
        ->findOrFail($id);
            
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
            'file' => 'required|file|max:1048576', // max 1GB for videos
            'type' => 'required|string'
        ]);

        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('course-assets', $filename, 'public');
        $url = asset('storage/' . $path);

        return response()->json([
            'success' => true,
            'url' => $url,
            'path' => $path,
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType()
        ]);
    }
}
