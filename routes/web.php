<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GitHubAuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auditor', function () {
    return view('auditor');
});

Route::get('/auth/github', [GitHubAuthController::class, 'redirect'])->name('github.redirect');
Route::get('/auth/github/callback', [GitHubAuthController::class, 'callback'])->name('github.callback');
Route::get('/api/github/repos', [GitHubAuthController::class, 'repos'])
    ->middleware('auth')
    ->name('github.repos');
