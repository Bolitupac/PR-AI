# PR-AI (AI-assisted Pull Request & Code Auditor)

<p align="center">
  <img src="public/images/git-pull-ai-Logo%20tp%20bg%20512.png" width="160" alt="PR-AI Logo">
</p>

PR-AI is an advanced, premium, real-time code auditor and pull request assistant. It integrates directly with major Version Control Systems (VCS) like GitHub and GitLab to allow developers to automatically audit pull requests, branches, and commits, and chat dynamically with AI assistants to get recommendations, inline comments, risk scores, and architectural diagrams.

This project was built during an internship at the **Nigerian National Petroleum Corporation (NNPC) Limited, Nigeria**.

---

## 🚀 Key Features & Capabilities

### 1. Multi-Source Code Import
- **GitHub Connection:** Integrated via OAuth with support for pulling repositories, pull requests, branches, commits, and diff files.
- **GitLab Connection:** Full OAuth integration, support for Merge Requests (MR), and self-hosted instances.
- **Bitbucket Connection:** Token-based authentication support.
- **Azure DevOps Connection:** Basic repository and code retrieval support.
- **Manual Import:** Capability to upload raw git diff files, copy-paste snippets, and use the custom-built Monaco editor.
- **Commit-Level Auditing:** Drill down to analyze individual commits and their structural changes.

### 2. AI-Powered Audit Modes
- **Pull Request Audits:** Conducts comprehensive context-aware PR reviews and security/logical audits.
- **Branch Compare Audits:** Compares code structures between arbitrary branches (e.g., development vs. main).
- **Commit Audits:** Single-commit review and security scanning.
- **Merge Conflict Audits:** Structured conflict detection and step-by-step resolution guidance.
- **Editor & Snippet Audits:** Ad-hoc scanning of manual code pastes and active scratchpads.

### 3. Chat History & Context Management
- **Persistent Chat History:** Seamlessly saves and restores conversations dynamically. Chat history is nested inside the sidebar and automatically updates in real-time.
- **Active Audit Context:** Preserves complete pull request, file-level, and branch diff data across manual prompt sessions.
- **AI-Powered Follow-Up Suggestions:** Proactively suggests subsequent queries and actions to help refine reviews.
- **Inline Comment Extraction:** Captures and translates line-specific developer feedback directly into AI contexts.

### 4. Advanced Document Generation (DocGen)
- **Intent-Aware Generation:** Dedicated DocGen mode with automated intent analysis.
- **Document Typologies:** Automatically builds high-quality README files, technical specifications, product requirement documents, SOWs, executive memos, and engineering guidelines.
- **Rich Export Capabilities:** Downloads generated documents in **PDF, DOCX, Markdown, HTML, JSON, YAML, CSV, XLSX, PowerPoint (PPTX), and LaTeX**.
- **Interactive Refinement Q&A:** The AI asks targeted clarifying questions to shape and customize output.
- **Real-Time Previewer:** Full markdown document preview in a download-ready interface.

### 5. Voice Input & Audio Transcription
- **Whisper Integration:** Integrates OpenAI's Whisper model for voice-to-text conversion.
- **Audio Formats Supported:** Processes `.webm`, `.ogg`, `.wav`, `.mp3`, and `.mpeg`.
- **Real-Time Voice Prompts:** Instantly record voice prompts to interact with the auditor.
- **Language-Aware Transcription:** Translates or transcribes multi-lingual audio inputs.

### 6. Advanced Code & Security Analytics
- **Risk Scoring Engine:** Analyzes changes to compute dynamic scores for overall **risk, security vulnerabilities, scalability bottlenecks, and code reliability**.
- **Change Type Classification:** Automatically classifies commits/diffs into features, bugfixes, refactorings, documentation, tests, or hotfixes.
- **VAPT Scan Diagnostics:** Tracks security flaws, vulnerability findings, and compliance patterns.
- **Mermaid Diagram Generation:** Visualizes system architecture, databases, state machines, and execution flow charts directly within markdown responses.
- **Structured Audit Snapshots:** Saves precise system states and diff representations to improve audit persistence.

### 7. Multi-Provider AI Engine
- **OpenAI:** Supports GPT-4o, GPT-4o-mini, and custom endpoints.
- **DeepSeek:** Alternative high-performance developer reasoning models.
- **Flexible Keys:** Configurable per-user custom API key overrides alongside system-wide fallback credentials.
- **On-The-Fly Swapping:** Instantly switch between models or providers in the UI during an active session.

### 8. UI/UX Elements
- **Responsive Workspace:** Optimized sidebars that tuck away cleanly to maximize editor space.
- **Side-by-Side Diff Viewer:** Renders beautiful, interactive diff outputs powered by `diff2html`.
- **Interactive Custom Dropdowns:** Custom hover menus for selecting AI providers and models, offering a premium and polished developer experience.
- **Markdown Renderer:** Built-in syntax highlighting and code block copying.

---

## 🛠️ Stack & Technologies Used

- **Framework:** Laravel 12 (PHP 8.2+)
- **Build System & Asset Bundler:** Vite
- **Database:** Supabase (PostgreSQL / Supabase integration)
- **CSS Engine:** Vanilla CSS (Glassmorphism, custom dark/light color schemes, fluid transition models)
- **AI Integrations:**
  - **OpenAI API** (GPT-4o, Whisper)
  - **DeepSeek API** (DeepSeek Coder/Chat models)
- **VCS APIs:** GitHub API, GitLab API/OAuth, Bitbucket, Azure DevOps
- **Streaming Protocol:** Server-Sent Events (SSE) for smooth token-by-token rendering.

---

## ⚙️ Installation & Setup

Follow these steps to set up the project locally:

### 1. Prerequisites
Ensure you have the following installed on your machine:
- PHP >= 8.2
- Composer
- Node.js & npm
- PostgreSQL/Supabase account (or local database instance)

### 2. Install Dependencies
```bash
# Clone the repository
git clone https://github.com/Bolitupac/PR-AI.git
cd PR-AI

# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### 3. Environment Configuration
Copy the `.env.example` template to create your `.env` file:
```bash
cp .env.example .env
```
Open the `.env` file and configure:
- Your Supabase database credentials:
  ```env
  DB_CONNECTION=pgsql
  DB_HOST=your-supabase-db-host
  DB_PORT=5432
  DB_DATABASE=postgres
  DB_USERNAME=postgres
  DB_PASSWORD=your-supabase-password
  ```
- AI service keys:
  ```env
  OPENAI_API_KEY=your_openai_api_key
  DEEPSEEK_API_KEY=your_deepseek_api_key
  ```
- VCS OAuth keys (GitHub and GitLab developer app credentials):
  ```env
  GITHUB_CLIENT_ID=your_github_id
  GITHUB_CLIENT_SECRET=your_github_secret
  GITHUB_REDIRECT_URI=http://localhost:8000/auth/github/callback

  GITLAB_CLIENT_ID=your_gitlab_id
  GITLAB_CLIENT_SECRET=your_gitlab_secret
  GITLAB_REDIRECT_URI=http://localhost:8000/auth/gitlab/callback
  ```

### 4. Database Setup & Migrations
```bash
# Generate the application encryption key
php artisan key:generate

# Run database migrations to set up core tables (users, connections, chats, messages, etc.)
php artisan migrate
```

---

## 🏃 Running the Application

To start the local development environment, you must run both the frontend dev server and the backend Laravel application.

### Start the Vite Dev Server:
```bash
npm run dev
```

### Start the Laravel Application Server:
```bash
php artisan serve
```
By default, the server runs at [http://127.0.0.1:8000](http://127.0.0.1:8000).

To compile production-ready assets:
```bash
npm run build
```

---

## 👥 Contributors & Credits

- **Lead Software Engineer:** Nanbol Dassak (Built and integrated the platform during internship)
- **Project Supervisor:** Samuel Akinseinde (Supervised development)
- **Sponsoring Institution:** **NNPC Limited, Nigeria**

---

## 📄 License
This application is open-sourced software licensed under the [MIT license](LICENSE).
