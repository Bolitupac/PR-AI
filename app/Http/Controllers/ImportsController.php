<?php

namespace App\Http\Controllers;

use App\Services\Git\RecentCommitsService;
use Illuminate\View\View;

class ImportsController extends Controller
{
    public function __construct(private readonly RecentCommitsService $recentCommitsService)
    {
    }

    public function index(): View
    {
        $githubConnected = (bool) auth()->user()?->github_access_token;

        return view('imports', [
            'githubConnected' => $githubConnected,
            'recentCommits' => $githubConnected ? $this->recentCommitsService->getRecentCommits(15) : [],
            'recentCommitsUnavailableReason' => $githubConnected
                ? $this->recentCommitsService->getUnavailableReason()
                : 'Log in with GitHub to load your recent commits.',
        ]);
    }
}
