<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
use App\Models\User;

Schedule::call(function () {
    User::where('email', 'like', 'temp_%@example.com')
        ->where('created_at', '<', now()->subHours(24))
        ->delete();
})->hourly();
