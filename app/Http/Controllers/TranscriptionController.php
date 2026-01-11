<?php

namespace App\Http\Controllers;

use App\Http\Requests\PolishRequest;
use App\Http\Requests\TranscribeAndPolishRequest;
use App\Http\Requests\TranscribeRequest;
use App\Http\Resources\TranscriptionResource;
use App\Models\Transcription;
use App\Services\Polishing\PolishingService;
use App\Services\Transcription\TranscriptionService;
use Illuminate\Http\JsonResponse;

class TranscriptionController extends Controller
{
    public function __construct(
        private TranscriptionService $transcriptionService,
        private PolishingService $polishingService,
    ) {}

    public function transcribe(TranscribeRequest $request): JsonResponse
    {
        $user = $request->user();

        $result = $this->transcriptionService->transcribe(
            $request->file('file'),
            $user,
            $request->provider,
            $request->language,
        );

        return response()->json([
            'text' => $result['text'],
            'duration' => $result['duration'],
            'language' => $result['language'],
            'provider' => $result['provider'],
        ]);
    }

    public function polish(PolishRequest $request): JsonResponse
    {
        $user = $request->user();

        $result = $this->polishingService->polish(
            $request->text,
            $user,
            $request->style ?? 'casual',
            $request->context,
            $request->provider,
        );

        return response()->json([
            'text' => $result['text'],
            'style' => $result['style'],
            'provider' => $result['provider'],
        ]);
    }

    public function transcribeAndPolish(TranscribeAndPolishRequest $request): JsonResponse
    {
        $user = $request->user();

        // Transcribe
        $transcription = $this->transcriptionService->transcribe(
            $request->file('file'),
            $user,
            $request->transcription_provider,
            $request->language,
        );

        // Polish
        $polished = $this->polishingService->polish(
            $transcription['text'],
            $user,
            $request->style ?? 'casual',
            $request->context,
            $request->polishing_provider,
        );

        // Save to history
        $history = Transcription::create([
            'user_id' => $user->id,
            'original_text' => $transcription['text'],
            'polished_text' => $polished['text'],
            'app_context' => $request->context,
            'style' => $polished['style'],
            'language' => $transcription['language'],
            'duration_seconds' => (int) $transcription['duration'],
            'word_count' => str_word_count($polished['text']),
            'transcription_provider' => $transcription['provider'],
            'polishing_provider' => $polished['provider'],
        ]);

        return response()->json([
            'original' => $transcription['text'],
            'polished' => $polished['text'],
            'duration' => $transcription['duration'],
            'language' => $transcription['language'],
            'style' => $polished['style'],
            'history' => new TranscriptionResource($history),
        ]);
    }
}
