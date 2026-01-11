<?php

namespace App\Http\Controllers;

use App\Http\Requests\StyleRequest;
use App\Http\Resources\StylePreferenceResource;
use App\Models\StylePreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StyleController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return StylePreferenceResource::collection(
            $request->user()->stylePreferences()->orderBy('app_name')->get()
        );
    }

    public function store(StyleRequest $request): JsonResponse
    {
        $style = $request->user()->stylePreferences()->updateOrCreate(
            ['app_identifier' => $request->app_identifier],
            [
                'app_name' => $request->app_name,
                'style' => $request->style,
            ]
        );

        return response()->json([
            'message' => 'Style preference saved.',
            'style' => new StylePreferenceResource($style),
        ], 201);
    }

    public function update(StyleRequest $request, string $appIdentifier): JsonResponse
    {
        $style = $request->user()->stylePreferences()
            ->where('app_identifier', $appIdentifier)
            ->firstOrFail();

        $style->update([
            'app_name' => $request->app_name,
            'style' => $request->style,
        ]);

        return response()->json([
            'message' => 'Style preference updated.',
            'style' => new StylePreferenceResource($style),
        ]);
    }

    public function destroy(Request $request, string $appIdentifier): JsonResponse
    {
        $request->user()->stylePreferences()
            ->where('app_identifier', $appIdentifier)
            ->delete();

        return response()->json([
            'message' => 'Style preference removed.',
        ]);
    }

    public function getDefault(Request $request): JsonResponse
    {
        $settings = $request->user()->settings;
        $defaultStyle = $settings?->settings['default_style'] ?? 'casual';

        return response()->json([
            'default_style' => $defaultStyle,
        ]);
    }

    public function setDefault(Request $request): JsonResponse
    {
        $request->validate([
            'style' => ['required', 'string', 'in:formal,casual,extremely_casual'],
        ]);

        $user = $request->user();
        $settings = $user->settings ?? $user->settings()->create(['settings' => []]);

        $currentSettings = $settings->settings;
        $currentSettings['default_style'] = $request->style;
        $settings->update(['settings' => $currentSettings]);

        return response()->json([
            'message' => 'Default style updated.',
            'default_style' => $request->style,
        ]);
    }
}
