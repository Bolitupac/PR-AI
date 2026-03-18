<?php

namespace App\Http\Controllers;

use App\Services\Git\CommitDiffService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class GitCommitController extends Controller
{
    public function __construct(private readonly CommitDiffService $commitDiffService)
    {
    }

    public function diff(): Response|JsonResponse
    {
        $commit = (string) request()->query('commit', '');

        if (!preg_match('/^[0-9a-f]{7,40}$/i', $commit)) {
            return response()->json(['message' => 'Invalid commit hash'], 422);
        }

        $result = $this->commitDiffService->getCommitDiff($commit);
        if (!$result['ok']) {
            return response()->json(['message' => 'Failed to load commit diff'], $result['status']);
        }

        return response($result['data'], 200, ['Content-Type' => 'text/plain; charset=utf-8']);
    }
}
