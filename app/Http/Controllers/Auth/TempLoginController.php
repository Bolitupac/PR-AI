<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TempLoginController extends Controller
{
    public function login()
    {
        $user = User::firstOrCreate(
            ['email' => 'temporary_debug_user@example.com'],
            [
                'name' => 'Temporary Debug User',
                'password' => bcrypt(Str::random(16)),
                'github_id' => 'temp_debug_123',
                'github_username' => 'temp_debug',
            ]
        );

        Auth::login($user);

        return redirect()->route('auditor.index');
    }
}
