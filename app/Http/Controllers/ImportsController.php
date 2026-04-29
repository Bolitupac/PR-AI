<?php

namespace App\Http\Controllers;

use App\Services\Git\RecentCommitsService;
use App\Services\Vcs\VcsProviderManager;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ImportsController extends Controller
{
    public function __construct(
        private readonly RecentCommitsService $recentCommitsService,
        private readonly VcsProviderManager $vcsProviderManager,
    )
    {
    }

    public function index(Request $request): View
    {
        $hasConnectedProvider = collect($this->vcsProviderManager->providerSummaries($request))
            ->contains(fn (array $provider) => !empty($provider['connected']));

        return view('imports', [
            'recentCommits' => $hasConnectedProvider ? $this->recentCommitsService->getRecentCommits(15) : [],
            'recentCommitsUnavailableReason' => $hasConnectedProvider
                ? $this->recentCommitsService->getUnavailableReason()
                : 'Connect a VCS provider to load your recent commits.',
        ]);
    }
}
