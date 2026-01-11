<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CommandController;
use App\Http\Controllers\DictionaryController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SharedDictionaryController;
use App\Http\Controllers\SnippetController;
use App\Http\Controllers\StyleController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TranscriptionController;
use App\Http\Controllers\UsageController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Protected routes
Route::middleware(['auth:api', 'rate.limit.plan'])->group(function () {
    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::put('/user', [AuthController::class, 'updateProfile']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::delete('/account', [AuthController::class, 'deleteAccount']);
        Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
        Route::post('/resend-verification', [AuthController::class, 'resendVerificationEmail']);
    });

    // Transcription (with usage limit check)
    Route::middleware(['check.usage', 'track.usage'])->group(function () {
        Route::post('/transcribe', [TranscriptionController::class, 'transcribe']);
        Route::post('/polish', [TranscriptionController::class, 'polish']);
        Route::post('/transcribe-and-polish', [TranscriptionController::class, 'transcribeAndPolish']);
    });

    // Usage
    Route::get('/usage', [UsageController::class, 'current']);
    Route::get('/usage/stats', [UsageController::class, 'stats']);
    Route::get('/usage/history', [UsageController::class, 'history']);

    // Dictionary
    Route::apiResource('dictionary', DictionaryController::class);
    Route::post('/dictionary/import', [DictionaryController::class, 'import']);
    Route::get('/dictionary/export', [DictionaryController::class, 'export']);

    // Commands
    Route::apiResource('commands', CommandController::class);
    Route::post('/commands/{command}/toggle', [CommandController::class, 'toggle']);

    // Snippets
    Route::apiResource('snippets', SnippetController::class);
    Route::post('/snippets/{snippet}/toggle', [SnippetController::class, 'toggle']);
    Route::post('/snippets/import', [SnippetController::class, 'import']);
    Route::get('/snippets/export', [SnippetController::class, 'export']);

    // Styles
    Route::get('/styles', [StyleController::class, 'index']);
    Route::post('/styles', [StyleController::class, 'store']);
    Route::put('/styles/{app}', [StyleController::class, 'update']);
    Route::delete('/styles/{app}', [StyleController::class, 'destroy']);
    Route::get('/styles/default', [StyleController::class, 'getDefault']);
    Route::post('/styles/default', [StyleController::class, 'setDefault']);

    // History
    Route::get('/history', [HistoryController::class, 'index']);
    Route::get('/history/export', [HistoryController::class, 'export']);
    Route::get('/history/{transcription}', [HistoryController::class, 'show']);
    Route::delete('/history/{transcription}', [HistoryController::class, 'destroy']);
    Route::delete('/history', [HistoryController::class, 'bulkDestroy']);

    // Subscription
    Route::get('/subscription', [SubscriptionController::class, 'show']);
    Route::get('/subscription/plans', [SubscriptionController::class, 'plans']);
    Route::post('/subscription/checkout', [SubscriptionController::class, 'checkout']);
    Route::post('/subscription/portal', [SubscriptionController::class, 'portal']);
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel']);
    Route::post('/subscription/resume', [SubscriptionController::class, 'resume']);

    // Settings
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::post('/settings', [SettingsController::class, 'update']);
    Route::get('/settings/{key}', [SettingsController::class, 'show']);
    Route::put('/settings/{key}', [SettingsController::class, 'updateKey']);

    // Languages
    Route::get('/languages', [LanguageController::class, 'index']);

    // Teams (Business Plan)
    Route::apiResource('teams', TeamController::class);
    Route::post('/teams/{team}/invite', [TeamController::class, 'invite']);
    Route::post('/teams/{team}/switch', [TeamController::class, 'switchTeam']);
    Route::delete('/teams/{team}/members/{user}', [TeamController::class, 'removeMember']);
    Route::put('/teams/{team}/members/{user}', [TeamController::class, 'updateMemberRole']);

    // Shared Dictionary (Team)
    Route::get('/teams/{team}/dictionary', [SharedDictionaryController::class, 'index']);
    Route::post('/teams/{team}/dictionary', [SharedDictionaryController::class, 'store']);
    Route::put('/teams/{team}/dictionary/{word}', [SharedDictionaryController::class, 'update']);
    Route::delete('/teams/{team}/dictionary/{word}', [SharedDictionaryController::class, 'destroy']);
    Route::post('/teams/{team}/dictionary/import', [SharedDictionaryController::class, 'import']);
    Route::get('/teams/{team}/dictionary/export', [SharedDictionaryController::class, 'export']);
});

// Stripe Webhook (no auth, verified by signature)
Route::post('/webhooks/stripe', [WebhookController::class, 'handleWebhook']);
