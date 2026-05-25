# PR-AI — Application Guide

PR-AI (branded in the UI as **PR ai** / **Git PULL Assistant**) is an AI-assisted code review workspace. Developers import pull requests, branches, or commits from Git hosting providers, inspect unified diffs, and run structured audits with OpenAI-backed chat, DocGen, voice input, and inline comment suggestions.

---

## Architecture

| Layer | Technology |
|--------|------------|
| Backend | PHP 8.2+, Laravel 12 |
| Frontend | Vite 7, vanilla JavaScript modules, Tailwind CSS 4 |
| Diff UI | diff2html |
| Diagrams | Mermaid (npm bundle) |
| Auth | Laravel Socialite (GitHub, GitLab OAuth) |
| AI | OpenAI API (configurable model + optional per-user API key) |
| Deploy | Docker (nginx + PHP-FPM), e.g. Render |

### Main routes

| Path | Purpose |
|------|---------|
| `/login` | Sign in (GitHub, GitLab OAuth) |
| `/auditor` | Primary review workspace |
| `/imports` | Repository browser, recent PRs/commits |
| `/api/vcs/{provider}/*` | Unified VCS JSON API (repos, branches, PRs, diffs, commits) |
| `/api/ai/*` | Chat, audit, DocGen, transcription |

---

## Authentication & VCS connections

### GitHub

- **Sign-in:** OAuth via `/auth/github` → callback stores encrypted token on the `users` row (`github_id`, `github_access_token`, …).
- **Imports:** Uses the same OAuth token automatically.

### GitLab

- **Sign-in:** OAuth via `/auth/gitlab` → callback stores encrypted token on the `users` row (`gitlab_id`, `gitlab_access_token`, `gitlab_username`, `gitlab_base_url`, …).
- **Imports:** Uses the OAuth token from the user record (same as GitHub).
- **Optional PAT:** Settings → GitLab still accepts a Personal Access Token + custom `base_url` for self-hosted instances when OAuth is not used or as a fallback stored in session.

### Bitbucket & Azure DevOps

- **Sign-in:** Not via OAuth on the login page.
- **Connection:** Settings → paste token + workspace/org fields; stored in session until logout.

### Environment variables (production example: Render)

```env
APP_URL=https://pr-ai.onrender.com

GITHUB_CLIENT_ID=...
GITHUB_CLIENT_SECRET=...
GITHUB_REDIRECT_URI=https://pr-ai.onrender.com/auth/github/callback

GITLAB_CLIENT_ID=...
GITLAB_CLIENT_SECRET=...
GITLAB_REDIRECT_URI=https://pr-ai.onrender.com/auth/gitlab/callback
GITLAB_INSTANCE_URI=https://gitlab.com
```

GitLab OAuth application redirect URI must match `GITLAB_REDIRECT_URI` exactly.

---

## Feature parity: GitHub vs GitLab

Both providers support the same Imports and Auditor flows when the user is signed in (or connected):

| Feature | GitHub | GitLab |
|---------|--------|--------|
| List repositories | Yes | Yes |
| Branches + metadata | Yes | Yes |
| Merge/pull requests per repo | Yes | Yes (merge requests) |
| Recent PRs panel | Yes | Yes |
| Recent Commits panel | Yes | Yes |
| PR/MR diff → Auditor | Yes | Yes |
| Branch compare diff | Yes | Yes |
| Commit diff → Auditor | Yes | Yes (`/api/vcs/{provider}/commit-diff`) |
| Merge conflict import | Yes (metadata only) | Yes (API hunks when GitLab returns `/conflicts`) |
| AI audit + chat | Yes | Yes (includes **Merge Conflict Risk** on normal PR/branch audits) |

Commit audits load diffs from the **remote provider API**, not from a local `git show` in the app container (legacy `/api/git/commit-diff` remains for local-only use).

---

## User workflows

### 1. Sign in

Choose **GitHub** or **GitLab** on `/login`. You are redirected to the provider, approve scopes, and land on `/auditor`.

### 2. Imports page (`/imports`)

- Switch provider in the header dropdown.
- **Recent Pull Requests** and **Recent Commits** side panels load from `/api/vcs/{provider}/recent-pulls` and `recent-commits`.
- Click **Audit** on a PR or commit to stash context in `sessionStorage` and open the Auditor.
- Expand a repository for branches and open MRs; audit branch or PR from there.

### 3. Auditor (`/auditor`)

- Pending import from Imports restores diff + optional PR comments.
- **Import** menu: repo provider modal, upload `.diff`, paste in Monaco.
- **Auto-audit** and chat use the loaded diff text.
- **DocGen**, voice, Mermaid, inline comments (where supported).

### 4. Settings

- VCS connections per provider.
- OpenAI API key (shared vs personal).
- AI preferences and in-app help chapters.

---

## API design (VCS)

All provider calls go through `VcsRepositoryController` and `VcsProviderInterface` implementations:

- `GitHubVcsProvider` → `GitHubApiService`
- `GitLabVcsProvider` → GitLab REST API v4 (`PRIVATE-TOKEN` header)
- `BitbucketVcsProvider`, `AzureDevOpsVcsProvider` → respective APIs

Repo identity is passed as query params: `repo` (full name / path), optional `repo_id` (GitLab project id), plus provider-specific fields.

---

## Data storage

| Data | Where |
|------|--------|
| GitHub/GitLab OAuth tokens | `users` table, encrypted with `APP_KEY` |
| Bitbucket/Azure tokens | Session `vcs_connections` |
| AI preferences / custom OpenAI key | `users` table |
| Audit snapshots | API endpoint (ephemeral/storage per implementation) |

---

## Security notes

- OAuth secrets and tokens belong only in environment variables and encrypted DB columns—never in git.
- Rotate credentials if they are exposed in chat or logs.
- Scopes are minimized: `read_user`, `read_api`, `read_repository` (GitLab); `read:user`, `repo` (GitHub).
- Temporary login on the login page is for development only; disable or remove in production.

---

## Local development

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# Fill GitHub/GitLab OAuth and OPENAI_API_KEY
php artisan migrate
npm run dev
php artisan serve
```

Register OAuth callback URLs for `http://127.0.0.1:8000` (or your local `APP_URL`) in GitHub and GitLab application settings.

---

## Related files

| Area | Path |
|------|------|
| Routes | `routes/web.php` |
| GitHub OAuth | `app/Http/Controllers/Auth/GitHubOAuthController.php` |
| GitLab OAuth | `app/Http/Controllers/Auth/GitLabOAuthController.php` |
| VCS API | `app/Http/Controllers/Vcs/VcsRepositoryController.php` |
| Connections | `app/Services/Vcs/VcsConnectionStore.php` |
| Imports UI | `resources/js/pages/imports/` |
| Auditor UI | `resources/js/pages/auditor/` |

---

## Merge conflict data sources

| Provider | List conflicts | Line-level hunks in app |
|----------|----------------|-------------------------|
| GitHub | Yes — per-PR `mergeable` + `mergeable_state: dirty` via detail API | **No** — REST does not expose conflict markers; UI shows metadata-only panel |
| GitLab | Yes — open MRs with `cannot_be_merged` / conflict status | **Yes** — when `merge_requests/:iid/conflicts` returns marker content |

GitHub imports still run a metadata-based AI audit (causes, local git steps, agent prompt) without fabricating `<<<<<<<` hunks.

## Roadmap ideas

- Bitbucket/Azure OAuth sign-in
- Merge conflict assistant (see product plan from maintainers)
- Split frontend bundles (Auditor vs Imports) for faster loads
- Server-side caching for repo metadata
