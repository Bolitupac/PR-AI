<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiPreferencesController extends Controller
{
    /**
     * Return the authenticated user's saved AI preferences.
     */
    public function show(): JsonResponse
    {
        $user = Auth::user();

        $defaults = [
            'personality' => 'balanced',
            'verbosity'   => 'medium',
            'tone'        => 'supportive',
            'custom_prompt' => '',
        ];

        $prefs = is_array($user->ai_preferences) && count($user->ai_preferences) > 0
            ? array_merge($defaults, $user->ai_preferences)
            : $defaults;

        return response()->json(['preferences' => $prefs]);
    }

    /**
     * Persist the AI preferences to the authenticated user's record.
     */
    public function save(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'personality'   => ['nullable', 'string', 'in:balanced,strict,mentor,architect'],
            'verbosity'     => ['nullable', 'string', 'in:short,medium,detailed'],
            'tone'          => ['nullable', 'string', 'in:supportive,neutral,direct'],
            'custom_prompt' => ['nullable', 'string', 'max:2000'],
        ]);

        $prefs = [
            'personality'   => $payload['personality']   ?? 'balanced',
            'verbosity'     => $payload['verbosity']      ?? 'medium',
            'tone'          => $payload['tone']           ?? 'supportive',
            'custom_prompt' => trim($payload['custom_prompt'] ?? ''),
        ];

        $user = Auth::user();
        $user->update(['ai_preferences' => $prefs]);

        return response()->json(['ok' => true, 'preferences' => $prefs]);
    }
}
