<?php

namespace App\Http\Controllers;

use App\Http\Requests\DictionaryRequest;
use App\Http\Resources\DictionaryWordResource;
use App\Models\DictionaryWord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DictionaryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $request->user()->dictionaryWords();

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('search')) {
            $query->where('word', 'like', '%' . $request->search . '%');
        }

        return DictionaryWordResource::collection(
            $query->orderBy('word')->paginate($request->per_page ?? 50)
        );
    }

    public function store(DictionaryRequest $request): JsonResponse
    {
        $word = $request->user()->dictionaryWords()->create([
            'word' => $request->word,
            'category' => $request->category ?? 'general',
            'pronunciation' => $request->pronunciation,
        ]);

        return response()->json([
            'message' => 'Word added to dictionary.',
            'word' => new DictionaryWordResource($word),
        ], 201);
    }

    public function show(Request $request, DictionaryWord $dictionary): JsonResponse
    {
        $this->authorize('view', $dictionary);

        return response()->json([
            'word' => new DictionaryWordResource($dictionary),
        ]);
    }

    public function update(DictionaryRequest $request, DictionaryWord $dictionary): JsonResponse
    {
        $this->authorize('update', $dictionary);

        $dictionary->update([
            'word' => $request->word,
            'category' => $request->category ?? $dictionary->category,
            'pronunciation' => $request->pronunciation,
        ]);

        return response()->json([
            'message' => 'Word updated.',
            'word' => new DictionaryWordResource($dictionary),
        ]);
    }

    public function destroy(Request $request, DictionaryWord $dictionary): JsonResponse
    {
        $this->authorize('delete', $dictionary);

        $dictionary->delete();

        return response()->json([
            'message' => 'Word deleted.',
        ]);
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'words' => ['required', 'array', 'max:500'],
            'words.*.word' => ['required', 'string', 'max:255'],
            'words.*.category' => ['nullable', 'string', 'max:50'],
            'words.*.pronunciation' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $imported = 0;

        foreach ($request->words as $wordData) {
            $user->dictionaryWords()->updateOrCreate(
                ['word' => $wordData['word']],
                [
                    'category' => $wordData['category'] ?? 'general',
                    'pronunciation' => $wordData['pronunciation'] ?? null,
                ]
            );
            $imported++;
        }

        return response()->json([
            'message' => "{$imported} words imported.",
            'count' => $imported,
        ]);
    }

    public function export(Request $request): JsonResponse
    {
        $words = $request->user()->dictionaryWords()
            ->orderBy('category')
            ->orderBy('word')
            ->get();

        return response()->json([
            'words' => DictionaryWordResource::collection($words),
            'count' => $words->count(),
        ]);
    }
}
