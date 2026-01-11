<?php

namespace App\Http\Controllers;

use App\Http\Requests\SnippetRequest;
use App\Http\Resources\SnippetResource;
use App\Models\Snippet;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SnippetController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $request->user()->snippets();

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        return SnippetResource::collection(
            $query->orderBy('trigger_phrase')->paginate($request->per_page ?? 50)
        );
    }

    public function store(SnippetRequest $request): JsonResponse
    {
        $snippet = $request->user()->snippets()->create([
            'trigger_phrase' => $request->trigger_phrase,
            'expansion_text' => $request->expansion_text,
            'category' => $request->category ?? 'general',
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Snippet created.',
            'snippet' => new SnippetResource($snippet),
        ], 201);
    }

    public function show(Request $request, Snippet $snippet): JsonResponse
    {
        $this->authorize('view', $snippet);

        return response()->json([
            'snippet' => new SnippetResource($snippet),
        ]);
    }

    public function update(SnippetRequest $request, Snippet $snippet): JsonResponse
    {
        $this->authorize('update', $snippet);

        $snippet->update([
            'trigger_phrase' => $request->trigger_phrase,
            'expansion_text' => $request->expansion_text,
            'category' => $request->category ?? $snippet->category,
        ]);

        return response()->json([
            'message' => 'Snippet updated.',
            'snippet' => new SnippetResource($snippet),
        ]);
    }

    public function destroy(Request $request, Snippet $snippet): JsonResponse
    {
        $this->authorize('delete', $snippet);

        $snippet->delete();

        return response()->json([
            'message' => 'Snippet deleted.',
        ]);
    }

    public function toggle(Request $request, Snippet $snippet): JsonResponse
    {
        $this->authorize('update', $snippet);

        $snippet->update([
            'is_active' => !$snippet->is_active,
        ]);

        return response()->json([
            'message' => $snippet->is_active ? 'Snippet activated.' : 'Snippet deactivated.',
            'snippet' => new SnippetResource($snippet),
        ]);
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'snippets' => ['required', 'array', 'max:200'],
            'snippets.*.trigger_phrase' => ['required', 'string', 'max:100'],
            'snippets.*.expansion_text' => ['required', 'string', 'max:5000'],
            'snippets.*.category' => ['nullable', 'string', 'max:50'],
        ]);

        $user = $request->user();
        $imported = 0;

        foreach ($request->snippets as $snippetData) {
            $user->snippets()->updateOrCreate(
                ['trigger_phrase' => $snippetData['trigger_phrase']],
                [
                    'expansion_text' => $snippetData['expansion_text'],
                    'category' => $snippetData['category'] ?? 'general',
                    'is_active' => true,
                ]
            );
            $imported++;
        }

        return response()->json([
            'message' => "{$imported} snippets imported.",
            'count' => $imported,
        ]);
    }

    public function export(Request $request): JsonResponse
    {
        $snippets = $request->user()->snippets()
            ->orderBy('category')
            ->orderBy('trigger_phrase')
            ->get();

        return response()->json([
            'snippets' => SnippetResource::collection($snippets),
            'count' => $snippets->count(),
        ]);
    }
}
