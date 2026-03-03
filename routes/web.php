<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\GitHubOAuthController;
use App\Http\Controllers\GitHubRepositoryController;
use App\Http\Controllers\Ai\SimpleChatController;
use App\Http\Controllers\Ai\AuditDiffController;
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
Route::post('/api/ai/audit-diff', [AuditDiffController::class, 'audit'])->name('ai.audit-diff');
Route::post('/api/audit/snapshot', [AuditSnapshotController::class, 'store'])->name('audit.snapshot');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
})->middleware('auth')->name('logout');
