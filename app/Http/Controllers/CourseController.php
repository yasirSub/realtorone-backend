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
        return response()->json(['success' => true, 'data' => \App\Models\Course::all()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'min_tier' => 'required|in:Consultant,Rainmaker,Titan',
            'url' => 'nullable|url',
            'description' => 'nullable|string',
            'module_number' => 'nullable|integer|min:1|max:3',
            'sequence' => 'nullable|integer|min:0'
        ]);

        $course = \App\Models\Course::create($validated);
        return response()->json(['success' => true, 'data' => $course]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
}
