<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GitHubOAuthController;
use App\Http\Controllers\GitHubRepositoryController;
use App\Http\Controllers\Ai\SimpleChatController;
use App\Http\Controllers\AuditSnapshotController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auditor', function () {
    return view('auditor');
});

Route::get('/auth/github', [GitHubOAuthController::class, 'redirect'])->name('github.redirect');
Route::get('/auth/github/callback', [GitHubOAuthController::class, 'callback'])->name('github.callback');
Route::get('/api/github/repos', [GitHubRepositoryController::class, 'repos'])
    ->middleware('auth')
    ->name('github.repos');
Route::get('/api/github/pulls', [GitHubRepositoryController::class, 'pullRequests'])
    ->middleware('auth')
    ->name('github.pulls');
Route::get('/api/github/pull-diff', [GitHubRepositoryController::class, 'pullDiff'])
    ->middleware('auth')
    ->name('github.pull-diff');
Route::post('/api/ai/chat', [SimpleChatController::class, 'chat'])->name('ai.chat');
Route::post('/api/audit/snapshot', [AuditSnapshotController::class, 'store'])->name('audit.snapshot');
