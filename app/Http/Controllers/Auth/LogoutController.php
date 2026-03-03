<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class LogoutController extends Controller
{
    // Logs out current user and clears session state.
    public function __invoke(Request $request): RedirectResponse
    {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        if ($currentUser) {
            // Hard-disable remember-me restoration for this user.
            $currentUser->setRememberToken(null);
            $currentUser->save();
        }

        Auth::logout();
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->regenerate();

        $response = redirect('/auditor');
        $response->withCookie(Cookie::forget(config('session.cookie')));
        $response->withCookie(Cookie::forget('XSRF-TOKEN'));

        $guard = Auth::guard();
        if (method_exists($guard, 'getRecallerName')) {
            /** @var string $recaller */
            $recaller = $guard->getRecallerName();
            $response->withCookie(Cookie::forget($recaller));
        }

        return $response;
    }
}
