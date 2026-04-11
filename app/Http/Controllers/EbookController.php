<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EbookController extends Controller
{
    /**
     * Display a listing of the resource (Admin).
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => \App\Models\Ebook::latest()->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'thumbnail_url' => 'nullable|string',
            'file_url' => 'nullable|string',
            'min_tier' => 'required|in:Consultant,Rainmaker,Titan',
            'is_published' => 'boolean',
        ]);

        $ebook = \App\Models\Ebook::create($validated);

        return response()->json([
            'success' => true,
            'data' => $ebook
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $ebook = \App\Models\Ebook::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $ebook
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $ebook = \App\Models\Ebook::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'thumbnail_url' => 'nullable|string',
            'file_url' => 'nullable|string',
            'min_tier' => 'sometimes|required|in:Consultant,Rainmaker,Titan',
            'is_published' => 'boolean',
        ]);

        $ebook->update($validated);

        return response()->json([
            'success' => true,
            'data' => $ebook
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $ebook = \App\Models\Ebook::findOrFail($id);
        $ebook->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ebook deleted successfully'
        ]);
    }

    /**
     * Get ebooks for mobile app (Public).
     */
    public function getPublicEbooks(Request $request)
    {
        $user = $request->user();
        // Fallback to Consultant if user not authenticated or doesn't have a tier
        $userTier = $user->tier ?? 'Consultant';
        
        $ebooks = \App\Models\Ebook::where('is_published', true)->get();

        return response()->json([
            'success' => true,
            'data' => $ebooks,
            'user_tier' => $userTier
        ]);
    }
}
