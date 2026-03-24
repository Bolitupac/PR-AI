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
        return view('imports', [
            'recentCommits' => $this->recentCommitsService->getRecentCommits(15),
            'recentCommitsUnavailableReason' => $this->recentCommitsService->getUnavailableReason(),
        ]);
    }
}
