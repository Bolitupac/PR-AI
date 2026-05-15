<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuditorController;
use App\Http\Controllers\Auth\GitHubOAuthController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\GitHubRepositoryController;
use App\Http\Controllers\GitCommitController;
use App\Http\Controllers\ImportsController;
use App\Http\Controllers\Vcs\VcsConnectionController;
use App\Http\Controllers\Vcs\VcsRepositoryController;
use App\Http\Controllers\Ai\SimpleChatController;
use App\Http\Controllers\Ai\DocGenController;
use App\Http\Controllers\Ai\AuditDiffController;
use App\Http\Controllers\Ai\Voice\TranscriptionController;
use App\Http\Controllers\AuditSnapshotController;
use App\Http\Controllers\ProfileAiKeyController;
use App\Http\Controllers\AiPreferencesController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        if (Auth::check()) {
            return redirect()->route('auditor.index');
        }

        return view('auth.login');
    })->name('login');

    Route::get('/auth/github', [GitHubOAuthController::class, 'redirect'])->name('github.redirect');
    Route::get('/auth/github/callback', [GitHubOAuthController::class, 'callback'])->name('github.callback');
    Route::post('/auth/temp-login', [App\Http\Controllers\Auth\TempLoginController::class, 'login'])->name('temp.login');
});

Route::post('/logout', LogoutController::class)->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/auditor', [AuditorController::class, 'index'])->name('auditor.index');
    Route::get('/imports', [ImportsController::class, 'index'])->name('imports.index');
    Route::get('/api/github/repos', [GitHubRepositoryController::class, 'repos'])->name('github.repos');
    Route::get('/api/github/branches', [GitHubRepositoryController::class, 'branches'])->name('github.branches');
    Route::get('/api/github/metadata', [GitHubRepositoryController::class, 'metadata'])->name('github.metadata');
    Route::get('/api/github/pulls', [GitHubRepositoryController::class, 'pullRequests'])->name('github.pulls');
    Route::get('/api/github/recent-pulls', [GitHubRepositoryController::class, 'recentPullRequests'])->name('github.recent-pulls');
    Route::get('/api/github/recent-commits', [GitHubRepositoryController::class, 'recentCommits'])->name('github.recent-commits');
    Route::get('/api/github/pull-comments', [GitHubRepositoryController::class, 'pullComments'])->name('github.pull-comments');
    Route::get('/api/github/pull-diff', [GitHubRepositoryController::class, 'pullDiff'])->name('github.pull-diff');
    Route::get('/api/github/branch-diff', [GitHubRepositoryController::class, 'branchDiff'])->name('github.branch-diff');
    Route::get('/api/vcs/{provider}/repos', [VcsRepositoryController::class, 'repos'])->name('vcs.repos');
    Route::get('/api/vcs/{provider}/branches', [VcsRepositoryController::class, 'branches'])->name('vcs.branches');
    Route::get('/api/vcs/{provider}/metadata', [VcsRepositoryController::class, 'metadata'])->name('vcs.metadata');
    Route::get('/api/vcs/{provider}/pulls', [VcsRepositoryController::class, 'pullRequests'])->name('vcs.pulls');
    Route::get('/api/vcs/{provider}/recent-pulls', [VcsRepositoryController::class, 'recentPullRequests'])->name('vcs.recent-pulls');
    Route::get('/api/vcs/{provider}/recent-commits', [VcsRepositoryController::class, 'recentCommits'])->name('vcs.recent-commits');
    Route::get('/api/vcs/{provider}/pull-comments', [VcsRepositoryController::class, 'pullComments'])->name('vcs.pull-comments');
    Route::get('/api/vcs/{provider}/pull-diff', [VcsRepositoryController::class, 'pullDiff'])->name('vcs.pull-diff');
    Route::get('/api/vcs/{provider}/branch-diff', [VcsRepositoryController::class, 'branchDiff'])->name('vcs.branch-diff');
    Route::get('/api/git/commit-diff', [GitCommitController::class, 'diff'])
        ->name('git.commit-diff');
    Route::post('/api/ai/chat', [SimpleChatController::class, 'chat'])->name('ai.chat');
    Route::post('/api/ai/chat-stream', [SimpleChatController::class, 'chatStream'])->name('ai.chat.stream');
    Route::post('/api/ai/docgen/chat', [DocGenController::class, 'chat'])->name('ai.docgen.chat');
    Route::post('/api/ai/docgen/chat-stream', [DocGenController::class, 'chatStream'])->name('ai.docgen.chat-stream');
    Route::post('/api/ai/docgen/export', [DocGenController::class, 'export'])->name('ai.docgen.export');
    Route::post('/api/ai/inline-comments', [SimpleChatController::class, 'inlineComments'])->name('ai.inline-comments');
    Route::post('/api/ai/followups', [SimpleChatController::class, 'followUps'])->name('ai.followups');
    Route::post('/api/ai/transcribe', [TranscriptionController::class, 'transcribe'])->name('ai.transcribe');
    Route::post('/api/ai/audit-diff', [AuditDiffController::class, 'audit'])->name('ai.audit-diff');
    Route::post('/api/ai/audit-diff-stream', [AuditDiffController::class, 'auditStream'])->name('ai.audit-diff.stream');
    Route::post('/api/audit/snapshot', [AuditSnapshotController::class, 'store'])->name('audit.snapshot');
    Route::post('/vcs/{provider}/connect', [VcsConnectionController::class, 'store'])->name('vcs.connections.store');
    Route::delete('/vcs/{provider}/connect', [VcsConnectionController::class, 'destroy'])->name('vcs.connections.destroy');

    Route::get('/profile/ai-key/status', [ProfileAiKeyController::class, 'status'])->name('profile.ai-key.status');
    Route::post('/profile/ai-key', [ProfileAiKeyController::class, 'save'])->name('profile.ai-key.save');
    Route::delete('/profile/ai-key', [ProfileAiKeyController::class, 'remove'])->name('profile.ai-key.remove');
    Route::post('/profile/ai-key/mode', [ProfileAiKeyController::class, 'setMode'])->name('profile.ai-key.mode');

    Route::get('/profile/ai-preferences', [AiPreferencesController::class, 'show'])->name('profile.ai-preferences.show');
    Route::post('/profile/ai-preferences', [AiPreferencesController::class, 'save'])->name('profile.ai-preferences.save');
});
