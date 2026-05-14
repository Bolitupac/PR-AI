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
        $user = User::create([
            'name' => 'Temporary User',
            'email' => 'temp_' . Str::random(8) . '@example.com',
            'password' => bcrypt(Str::random(16)),
            'github_id' => 'temp_' . Str::random(10),
            'github_username' => 'temp_user_' . Str::random(5),
        ]);

        Auth::login($user);

        return redirect()->route('auditor.index');
    }
}
