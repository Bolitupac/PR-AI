<?php

namespace App\Http\Controllers;

use App\Services\Audit\AuditSnapshotWriter;
use App\Services\Vcs\VcsProviderManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditSnapshotController extends Controller
{
    public function __construct(
        private readonly AuditSnapshotWriter $snapshotWriter,
        private readonly VcsProviderManager $vcsProviderManager
    ) {
    }

    // Saves a debug snapshot of current audit input into newfile.txt.
    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'source' => ['required', 'string', 'in:github,gitlab,bitbucket,azure,upload'],
            'repo' => ['nullable', 'string', 'max:255'],
            'repo_id' => ['nullable', 'string', 'max:255'],
            'project' => ['nullable', 'string', 'max:255'],
            'workspace' => ['nullable', 'string', 'max:255'],
            'organization' => ['nullable', 'string', 'max:255'],
            'repo_slug' => ['nullable', 'string', 'max:255'],
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

        if (in_array($source, ['github', 'gitlab', 'bitbucket', 'azure'], true) && $repo !== '' && $prNumber !== '') {
            $connection = $this->vcsProviderManager->resolveConnection($source, $request);
            if ($connection) {
                $provider = $this->vcsProviderManager->provider($source);
                $repoPayload = [
                    'repo' => $repo,
                    'repo_id' => $payload['repo_id'] ?? null,
                    'project' => $payload['project'] ?? null,
                    'workspace' => $payload['workspace'] ?? null,
                    'organization' => $payload['organization'] ?? null,
                    'repo_slug' => $payload['repo_slug'] ?? null,
                ];

                $issueResult = $provider->getPullIssueComments($connection, $repoPayload, $prNumber);
                if ($issueResult['ok']) {
                    $issueComments = $issueResult['data'];
                }

                $reviewResult = $provider->getPullReviewComments($connection, $repoPayload, $prNumber);
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
