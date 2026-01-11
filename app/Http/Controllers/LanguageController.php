<?php

namespace App\Http\Controllers;

use App\Models\SupportedLanguage;
use Illuminate\Http\JsonResponse;

class LanguageController extends Controller
{
    public function index(): JsonResponse
    {
        $languages = SupportedLanguage::where('is_active', true)
            ->orderBy('name')
            ->get(['code', 'name', 'native_name']);

        return response()->json([
            'languages' => $languages,
        ]);
    }
}
