<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatConversationController extends Controller
{
    public function index(): JsonResponse
    {
        $conversations = Auth::user()
            ->conversations()
            ->select(['id', 'title', 'provider', 'model', 'created_at', 'updated_at'])
            ->latest()
            ->get();

        return response()->json([
            'conversations' => $conversations,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'provider' => ['nullable', 'string', 'max:50'],
            'model' => ['nullable', 'string', 'max:100'],
            'active_audit_context' => ['nullable', 'string'],
        ]);

        $title = trim($payload['title'] ?? '') ?: 'New Chat';

        $context = $payload['active_audit_context'] ?? $request->session()->get('active_audit_context');

        $conversation = Auth::user()->conversations()->create([
            'title' => $title,
            'provider' => $payload['provider'] ?? 'openai',
            'model' => $payload['model'] ?? null,
            'active_audit_context' => $context,
        ]);

        return response()->json([
            'conversation' => $conversation,
        ], 210);
    }

    public function show(ChatConversation $conversation, Request $request): JsonResponse
    {
        if ($conversation->user_id !== Auth::id()) {
            abort(403);
        }

        if ($conversation->active_audit_context) {
            $request->session()->put('active_audit_context', $conversation->active_audit_context);
        } else {
            $request->session()->forget('active_audit_context');
        }

        if ($conversation->diff_text) {
            $request->session()->put('active_diff_text', $conversation->diff_text);
        } else {
            $request->session()->forget('active_diff_text');
        }

        return response()->json([
            'conversation' => $conversation,
            'messages' => $conversation->messages,
        ]);
    }

    public function update(ChatConversation $conversation, Request $request): JsonResponse
    {
        if ($conversation->user_id !== Auth::id()) {
            abort(403);
        }

        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $conversation->update([
            'title' => trim($payload['title']),
        ]);

        return response()->json([
            'conversation' => $conversation,
        ]);
    }

    public function destroy(ChatConversation $conversation): JsonResponse
    {
        if ($conversation->user_id !== Auth::id()) {
            abort(403);
        }

        $conversation->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
