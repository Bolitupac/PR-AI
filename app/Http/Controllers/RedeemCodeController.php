<?php

namespace App\Http\Controllers;

use App\Models\RedeemCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RedeemCodeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $payload = $request->validate([
            'code' => ['required', 'string', 'max:50'],
        ]);

        $code = strtoupper(trim((string) $payload['code']));

        $redeemCode = RedeemCode::where('code', $code)->first();

        if (! $redeemCode) {
            return response()->json(['message' => 'Invalid redeem code.'], 422);
        }

        if (! $redeemCode->isValid()) {
            return response()->json(['message' => 'This code is expired or no longer valid.'], 422);
        }

        // Check if user already redeemed this code
        $alreadyRedeemed = DB::table('user_redeemed_codes')
            ->where('user_id', $user->id)
            ->where('redeem_code_id', $redeemCode->id)
            ->exists();

        if ($alreadyRedeemed) {
            return response()->json(['message' => 'You have already redeemed this code.'], 422);
        }

        DB::transaction(function () use ($user, $redeemCode) {
            // Record redemption
            DB::table('user_redeemed_codes')->insert([
                'user_id' => $user->id,
                'redeem_code_id' => $redeemCode->id,
                'redeemed_at' => now(),
            ]);

            // Increment usage count
            $redeemCode->increment('times_used');

            // Grant credits
            $user->increment('system_key_credits', $redeemCode->credits);
        });

        $newBalance = (int) $user->fresh()->system_key_credits;

        return response()->json([
            'message' => "Code redeemed! {$redeemCode->credits} credits added. You now have {$newBalance} System Key requests.",
            'credits_added' => $redeemCode->credits,
            'credits_remaining' => $newBalance,
        ]);
    }
}
