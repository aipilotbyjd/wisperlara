<?php

namespace App\Http\Controllers;

use App\Http\Resources\DictionaryWordResource;
use App\Models\SharedDictionaryWord;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SharedDictionaryController extends Controller
{
    public function index(Request $request, Team $team): AnonymousResourceCollection
    {
        $this->authorizeTeamAccess($request->user(), $team);

        $query = $team->sharedDictionary();

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

    public function store(Request $request, Team $team): JsonResponse
    {
        $this->authorizeTeamAccess($request->user(), $team);

        $request->validate([
            'word' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:50'],
            'pronunciation' => ['nullable', 'string', 'max:255'],
        ]);

        $word = $team->sharedDictionary()->create([
            'word' => $request->word,
            'category' => $request->category ?? 'general',
            'pronunciation' => $request->pronunciation,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Word added to team dictionary.',
            'word' => new DictionaryWordResource($word),
        ], 201);
    }

    public function update(Request $request, Team $team, SharedDictionaryWord $word): JsonResponse
    {
        $this->authorizeTeamAccess($request->user(), $team);

        if ($word->team_id !== $team->id) {
            abort(404);
        }

        $request->validate([
            'word' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:50'],
            'pronunciation' => ['nullable', 'string', 'max:255'],
        ]);

        $word->update([
            'word' => $request->word,
            'category' => $request->category ?? $word->category,
            'pronunciation' => $request->pronunciation,
        ]);

        return response()->json([
            'message' => 'Word updated.',
            'word' => new DictionaryWordResource($word),
        ]);
    }

    public function destroy(Request $request, Team $team, SharedDictionaryWord $word): JsonResponse
    {
        $this->authorizeTeamAccess($request->user(), $team);

        if ($word->team_id !== $team->id) {
            abort(404);
        }

        $word->delete();

        return response()->json([
            'message' => 'Word deleted from team dictionary.',
        ]);
    }

    public function import(Request $request, Team $team): JsonResponse
    {
        $this->authorizeTeamAdmin($request->user(), $team);

        $request->validate([
            'words' => ['required', 'array', 'max:500'],
            'words.*.word' => ['required', 'string', 'max:255'],
            'words.*.category' => ['nullable', 'string', 'max:50'],
            'words.*.pronunciation' => ['nullable', 'string', 'max:255'],
        ]);

        $imported = 0;

        foreach ($request->words as $wordData) {
            $team->sharedDictionary()->updateOrCreate(
                ['word' => $wordData['word']],
                [
                    'category' => $wordData['category'] ?? 'general',
                    'pronunciation' => $wordData['pronunciation'] ?? null,
                    'created_by' => $request->user()->id,
                ]
            );
            $imported++;
        }

        return response()->json([
            'message' => "{$imported} words imported.",
            'count' => $imported,
        ]);
    }

    public function export(Request $request, Team $team): JsonResponse
    {
        $this->authorizeTeamAccess($request->user(), $team);

        $words = $team->sharedDictionary()
            ->orderBy('category')
            ->orderBy('word')
            ->get();

        return response()->json([
            'words' => DictionaryWordResource::collection($words),
            'count' => $words->count(),
        ]);
    }

    private function authorizeTeamAccess(User $user, Team $team): void
    {
        if (!$team->members()->where('user_id', $user->id)->exists()) {
            abort(403, 'You are not a member of this team.');
        }
    }

    private function authorizeTeamAdmin(User $user, Team $team): void
    {
        $member = $team->members()->where('user_id', $user->id)->first();
        if (!$member || !in_array($member->pivot->role, ['owner', 'admin'])) {
            abort(403, 'Admin access required.');
        }
    }
}
