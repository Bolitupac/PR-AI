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
        $chatHistory = [
            ['title' => 'Audit for billing service PR', 'time' => '2 min ago', 'preview' => 'Risk: medium, 3 change requests.'],
            ['title' => 'Review edge API branch', 'time' => '11 min ago', 'preview' => 'Null checks missing in 2 files.'],
            ['title' => 'Frontend lint cleanup', 'time' => '45 min ago', 'preview' => 'Low-risk formatting updates only.'],
            ['title' => 'Repository sync checklist', 'time' => '1 hr ago', 'preview' => 'Branch protections and labels reviewed.'],
            ['title' => 'Testing out the ai system for the user', 'time' => '59 min ago', 'preview' => 'Risk: medium, 3 change requests.'],
        ];

        return view('imports', [
            'chatHistory' => $chatHistory,
            'recentCommits' => $this->recentCommitsService->getRecentCommits(15),
        ]);
    }
}

