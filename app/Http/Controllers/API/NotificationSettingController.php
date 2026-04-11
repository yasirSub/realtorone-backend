<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\NotificationSetting;
use Illuminate\Http\Request;

class NotificationSettingController extends Controller
{
    public function index()
    {
        $settings = NotificationSetting::all();
        return response()->json($settings);
    }

    public function show($key)
    {
        $setting = NotificationSetting::where('key', $key)->firstOrFail();
        return response()->json($setting);
    }

    public function update(Request $request, $id)
    {
        $setting = NotificationSetting::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'is_enabled' => 'sometimes|boolean',
            'default_title' => 'nullable|string|max:255',
            'default_body' => 'nullable|string',
            'trigger_settings' => 'nullable|array',
        ]);

        $setting->update($validated);

        return response()->json([
            'message' => 'Notification setting updated successfully',
            'setting' => $setting
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|unique:notification_settings,key',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_enabled' => 'boolean',
            'default_title' => 'nullable|string|max:255',
            'default_body' => 'nullable|string',
            'trigger_settings' => 'nullable|array',
        ]);

        $setting = NotificationSetting::create($validated);

        return response()->json([
            'message' => 'Notification setting created successfully',
            'setting' => $setting
        ], 201);
    }
}
