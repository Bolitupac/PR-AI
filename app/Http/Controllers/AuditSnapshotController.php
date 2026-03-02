<?php

namespace App\Http\Controllers;

use App\Services\Audit\AuditSnapshotWriter;
use App\Services\GitHubApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditSnapshotController extends Controller
{
    public function __construct(
        private readonly AuditSnapshotWriter $snapshotWriter,
        private readonly GitHubApiService $gitHubApiService
    ) {
    }

    // Saves a debug snapshot of current audit input into newfile.txt.
    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'source' => ['required', 'string', 'in:github,upload'],
            'repo' => ['nullable', 'string', 'max:255'],
            'pr_number' => ['nullable', 'integer'],
            'file_name' => ['nullable', 'string', 'max:255'],
            'diff_text' => ['required', 'string', 'max:2000000'],
        ]);

        $source = (string) $payload['source'];
        $repo = (string) ($payload['repo'] ?? '');
        $prNumber = isset($payload['pr_number']) ? (string) $payload['pr_number'] : '';
        $diffText = (string) $payload['diff_text'];

        $issueComments = [];
        $reviewComments = [];

        if ($source === 'github' && $repo !== '' && $prNumber !== '') {
            $user = Auth::user();
            if ($user?->github_access_token) {
                $issueResult = $this->gitHubApiService->getPullIssueComments($user->github_access_token, $repo, $prNumber);
                if ($issueResult['ok']) {
                    $issueComments = $issueResult['data'];
                }

                $reviewResult = $this->gitHubApiService->getPullReviewComments($user->github_access_token, $repo, $prNumber);
                if ($reviewResult['ok']) {
                    $reviewComments = $reviewResult['data'];
                }
            }
        }

        $content = $this->snapshotWriter->buildContent([
            'source' => $source,
            'repo' => $repo !== '' ? $repo : null,
            'pr_number' => $prNumber !== '' ? $prNumber : null,
            'file_name' => $payload['file_name'] ?? null,
            'diff_text' => $diffText,
            'issue_comments' => $issueComments,
            'review_comments' => $reviewComments,
        ]);

        $path = $this->snapshotWriter->write($content);

        return response()->json([
            'message' => 'Snapshot saved.',
            'path' => $path,
        ]);
    }
}

