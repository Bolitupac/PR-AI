import { runTutorial, welcomeStep, step } from '../../shared/tutorial';

const TERMS_URL = '/terms';

/**
 * Imports Page Tutorial Steps
 */
export function startImportsTutorial() {
    const steps = [
        welcomeStep({
            title: 'Import Repository Tutorial 📥',
            subtitle: 'Learn how to bring code into PR-AI',
            text: 'This walkthrough covers how to browse repositories, select branches and pull requests, and import code for AI-powered auditing.',
            nextLabel: 'Start Import Tour →',
            termsUrl: TERMS_URL,
        }),

        // Step 1: Import Page Title
        step(
            'Import Page Overview',
            '.imports-page-title',
            'Welcome to the Imports page! From here you can browse repositories connected to your GitHub or GitLab account, explore branches, pull requests, commits, and recent activity.',
            { placement: 'bottom' }
        ),

        // Step 2: Recent Activity
        step(
            'Recent Activity',
            '.imports-recent-section',
            'This section shows your recent pull requests, commits, and merge conflicts across connected repositories. Click any item to quickly import and audit it.',
            { placement: 'bottom' }
        ),

        // Step 3: Repository List
        step(
            'Repository Browser',
            '.imports-repo-list',
            'Browse all repositories accessible through your connected VCS accounts. Click a repository to expand it and see its branches, pull requests, and commits.',
            { placement: 'right' }
        ),

        // Step 4: Importing a Pull Request
        step(
            'Pull Request Import',
            '.imports-repo-list',
            'Click any pull request to import its diff into the Auditor. PR-AI will automatically fetch the changed files, PR metadata, comments, and run a full VAPT + OWASP security audit.',
            { placement: 'right' }
        ),

        // Step 5: Importing a Branch
        step(
            'Branch Auditing',
            '.imports-repo-list',
            'You can also audit entire branches! Click a branch to compute the diff against the default branch (main) and send it to the Auditor for analysis. Great for feature branch reviews before opening a PR.',
            { placement: 'right' }
        ),

        // Step 6: Manual Import Options
        step(
            'Manual Import Options',
            '.imports-repo-list',
            'Prefer to work with local files? You can also upload .diff or .patch files directly, or paste code into the built-in Monaco editor. These options are available from the Auditor\'s Import button (➕).',
            { placement: 'right',
                nextLabel: 'Finish Tutorial ✓' }
        ),
    ];

    return runTutorial(steps, {
        page: 'imports',
        onComplete: (completed) => {
            if (completed) {
                // Could redirect to auditor or just show a success message
                console.log('Imports tutorial completed');
            }
        },
    });
}
