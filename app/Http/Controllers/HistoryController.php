<?php

namespace App\Http\Controllers;

use App\Http\Resources\TranscriptionResource;
use App\Models\Transcription;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HistoryController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $request->user()->transcriptions()->latest();

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->has('app')) {
            $query->where('app_context', $request->app);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('original_text', 'like', "%{$search}%")
                    ->orWhere('polished_text', 'like', "%{$search}%");
            });
        }

        return TranscriptionResource::collection(
            $query->paginate($request->per_page ?? 20)
        );
    }

    public function show(Request $request, Transcription $transcription): JsonResponse
    {
        $this->authorize('view', $transcription);

        return response()->json([
            'transcription' => new TranscriptionResource($transcription),
        ]);
    }

    public function destroy(Request $request, Transcription $transcription): JsonResponse
    {
        $this->authorize('delete', $transcription);

        $transcription->delete();

        return response()->json([
            'message' => 'Transcription deleted.',
        ]);
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array', 'max:100'],
            'ids.*' => ['required', 'integer'],
        ]);

        $deleted = $request->user()->transcriptions()
            ->whereIn('id', $request->ids)
            ->delete();

        return response()->json([
            'message' => "{$deleted} transcriptions deleted.",
            'count' => $deleted,
        ]);
    }

    public function export(Request $request): JsonResponse
    {
        $query = $request->user()->transcriptions()->latest();

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transcriptions = $query->get();

        if ($request->format === 'csv') {
            $csv = "ID,Original Text,Polished Text,App,Style,Language,Duration,Created At\n";
            foreach ($transcriptions as $t) {
                $csv .= sprintf(
                    "%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",%d,\"%s\"\n",
                    $t->id,
                    str_replace('"', '""', $t->original_text),
                    str_replace('"', '""', $t->polished_text ?? ''),
                    $t->app_context ?? '',
                    $t->style ?? '',
                    $t->language,
                    $t->duration_seconds,
                    $t->created_at->toIso8601String()
                );
            }

            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="transcriptions.csv"',
            ]);
        }

        return response()->json([
            'transcriptions' => TranscriptionResource::collection($transcriptions),
            'count' => $transcriptions->count(),
        ]);
    }
}
