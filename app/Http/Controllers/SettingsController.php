<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $settings = $user->settings?->settings ?? [];

        return response()->json([
            'settings' => array_merge([
                'default_style' => 'casual',
                'auto_detect_language' => true,
                'preferred_language' => 'en',
                'save_history' => true,
                'sound_enabled' => true,
                'shortcut' => 'cmd+shift+space',
            ], $settings),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'settings' => ['required', 'array'],
        ]);

        $user = $request->user();
        $userSettings = $user->settings ?? $user->settings()->create(['settings' => []]);

        $currentSettings = $userSettings->settings ?? [];
        $newSettings = array_merge($currentSettings, $request->settings);

        $userSettings->update(['settings' => $newSettings]);

        // Sync language preferences to user table
        if (isset($request->settings['preferred_language'])) {
            $user->update(['preferred_language' => $request->settings['preferred_language']]);
        }
        if (isset($request->settings['auto_detect_language'])) {
            $user->update(['auto_detect_language' => $request->settings['auto_detect_language']]);
        }

        return response()->json([
            'message' => 'Settings updated.',
            'settings' => $newSettings,
        ]);
    }

    public function show(Request $request, string $key): JsonResponse
    {
        $user = $request->user();
        $settings = $user->settings?->settings ?? [];

        if (!array_key_exists($key, $settings)) {
            return response()->json([
                'error' => 'setting_not_found',
                'message' => "Setting '{$key}' not found.",
            ], 404);
        }

        return response()->json([
            'key' => $key,
            'value' => $settings[$key],
        ]);
    }

    public function updateKey(Request $request, string $key): JsonResponse
    {
        $request->validate([
            'value' => ['required'],
        ]);

        $user = $request->user();
        $userSettings = $user->settings ?? $user->settings()->create(['settings' => []]);

        $settings = $userSettings->settings ?? [];
        $settings[$key] = $request->value;

        $userSettings->update(['settings' => $settings]);

        return response()->json([
            'message' => 'Setting updated.',
            'key' => $key,
            'value' => $request->value,
        ]);
    }
}
