<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DeleteAccountController extends Controller
{
    /**
     * Permanently delete the authenticated user's account and all associated data.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        try {
            // Delete related chat data
            $user->conversations()->each(function ($conversation) {
                $conversation->messages()->delete();
                $conversation->delete();
            });

            // Delete the user record
            $user->delete();

            // Invalidate session and logout
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'message' => 'Your account has been permanently deleted.',
                'redirect' => route('login'),
            ]);
        } catch (\Throwable $e) {
            Log::error('Account deletion failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
            ]);

            return response()->json([
                'message' => 'Something went wrong while deleting your account. Please try again.',
            ], 500);
        }
    }
}
