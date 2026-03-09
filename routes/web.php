<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GitHubOAuthController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\GitHubRepositoryController;
use App\Http\Controllers\Ai\SimpleChatController;
use App\Http\Controllers\Ai\AuditDiffController;
use App\Http\Controllers\Ai\Voice\TranscriptionController;
use App\Http\Controllers\AuditSnapshotController;
use App\Http\Controllers\ProfileAiKeyController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auditor', function () {
    request()->session()->forget('active_audit_context');
    return view('auditor');
});

Route::get('/imports', function () {
    return view('imports');
})->name('imports.index');

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
Route::post('/api/ai/chat-stream', [SimpleChatController::class, 'chatStream'])->name('ai.chat.stream');
Route::post('/api/ai/transcribe', [TranscriptionController::class, 'transcribe'])->name('ai.transcribe');
Route::post('/api/ai/audit-diff', [AuditDiffController::class, 'audit'])->name('ai.audit-diff');
Route::post('/api/ai/audit-diff-stream', [AuditDiffController::class, 'auditStream'])->name('ai.audit-diff.stream');
Route::post('/api/audit/snapshot', [AuditSnapshotController::class, 'store'])->name('audit.snapshot');

Route::post('/logout', LogoutController::class)->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/profile/ai-key/status', [ProfileAiKeyController::class, 'status'])->name('profile.ai-key.status');
    Route::post('/profile/ai-key', [ProfileAiKeyController::class, 'save'])->name('profile.ai-key.save');
    Route::delete('/profile/ai-key', [ProfileAiKeyController::class, 'remove'])->name('profile.ai-key.remove');
    Route::post('/profile/ai-key/mode', [ProfileAiKeyController::class, 'setMode'])->name('profile.ai-key.mode');
});
