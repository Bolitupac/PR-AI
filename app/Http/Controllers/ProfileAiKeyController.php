<?php

namespace App\Http\Controllers;

use App\Services\Ai\DeepSeekKeyValidator;
use App\Services\Ai\OpenAiKeyValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileAiKeyController extends Controller
{
    public function __construct(
        private readonly OpenAiKeyValidator $openAiKeyValidator,
        private readonly DeepSeekKeyValidator $deepSeekKeyValidator
    ) {
    }

    // --- OpenAI ---

    public function status(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        return response()->json([
            'mode' => (string) ($user->ai_key_mode ?? 'developer'),
            'has_personal_key' => $user->hasCustomOpenAiKey(),
            'masked_key' => (string) $user->masked_open_ai_key,
        ]);
    }

    public function save(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $payload = $request->validate([
            'api_key' => ['required', 'string', 'max:255'],
        ]);

        $result = $this->openAiKeyValidator->validate((string) $payload['api_key']);
        if (!$result['ok']) {
            return response()->json(['message' => $result['message']], 422);
        }

        $user->custom_openai_api_key = trim((string) $payload['api_key']);
        if (($user->ai_key_mode ?? 'developer') !== 'personal') {
            $user->ai_key_mode = 'personal';
        }
        $user->save();

        return response()->json([
            'message' => 'Personal API key saved.',
            'mode' => (string) $user->ai_key_mode,
            'has_personal_key' => $user->hasCustomOpenAiKey(),
            'masked_key' => (string) $user->masked_open_ai_key,
        ]);
    }

    public function remove(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $user->custom_openai_api_key = null;
        if (($user->ai_key_mode ?? 'developer') === 'personal') {
            $user->ai_key_mode = 'developer';
        }
        $user->save();

        return response()->json([
            'message' => 'Personal API key removed.',
            'mode' => (string) $user->ai_key_mode,
            'has_personal_key' => false,
            'masked_key' => '',
        ]);
    }

    public function setMode(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $payload = $request->validate([
            'mode' => ['required', 'string', 'in:developer,personal'],
        ]);

        $mode = (string) $payload['mode'];
        if ($mode === 'personal' && !$user->hasCustomOpenAiKey()) {
            return response()->json(['message' => 'Add a personal API key before switching to personal mode.'], 422);
        }

        $user->ai_key_mode = $mode;
        $user->save();

        return response()->json([
            'message' => $mode === 'personal' ? 'Switched to personal API key.' : 'Switched to system API key.',
            'mode' => (string) $user->ai_key_mode,
            'has_personal_key' => $user->hasCustomOpenAiKey(),
            'masked_key' => (string) $user->masked_open_ai_key,
        ]);
    }

    // --- DeepSeek ---

    public function deepseekStatus(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        return response()->json([
            'mode' => (string) ($user->ai_key_mode ?? 'developer'),
            'has_personal_key' => $user->hasCustomDeepSeekKey(),
            'masked_key' => (string) $user->masked_deep_seek_key,
        ]);
    }

    public function deepseekSave(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $payload = $request->validate([
            'api_key' => ['required', 'string', 'max:255'],
        ]);

        $result = $this->deepSeekKeyValidator->validate((string) $payload['api_key']);
        if (!$result['ok']) {
            return response()->json(['message' => $result['message']], 422);
        }

        $user->custom_deepseek_api_key = trim((string) $payload['api_key']);
        if (($user->ai_key_mode ?? 'developer') !== 'personal') {
            $user->ai_key_mode = 'personal';
        }
        $user->save();

        return response()->json([
            'message' => 'Personal DeepSeek API key saved.',
            'mode' => (string) $user->ai_key_mode,
            'has_personal_key' => $user->hasCustomDeepSeekKey(),
            'masked_key' => (string) $user->masked_deep_seek_key,
        ]);
    }

    public function deepseekRemove(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $user->custom_deepseek_api_key = null;
        if (($user->ai_key_mode ?? 'developer') === 'personal') {
            $user->ai_key_mode = 'developer';
        }
        $user->save();

        return response()->json([
            'message' => 'Personal DeepSeek API key removed.',
            'mode' => (string) $user->ai_key_mode,
            'has_personal_key' => false,
            'masked_key' => '',
        ]);
    }
}
