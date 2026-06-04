# PR-AI (AI-assisted Pull Request & Code Auditor)

PR-AI is an advanced, premium, real-time code auditor and pull request assistant. It integrates directly with major Version Control Systems (VCS) like GitHub and GitLab to allow developers to automatically audit pull requests, branches, and commits, and chat dynamically with AI assistants to get recommendations, inline comments, risk scores, and architectural diagrams.

This project was built during an internship at the **Nigerian National Petroleum Corporation (NNPC) Limited, Nigeria**.

---

## 🚀 Key Features

- **Multi-Provider VCS Integration:** Connects with GitHub and GitLab using OAuth for secure, authorized access to repositories, branches, commits, and pull requests.
- **Real-Time Streaming Audits:** Streams code analysis line-by-line via Server-Sent Events (SSE), utilizing models from **OpenAI** and **DeepSeek**.
- **Interactive Auditor Workspace:** A dashboard showing file diffs, inline AI suggestions, code risk assessments, and VAPT (Vulnerability Assessment & Penetration Testing) finding statistics.
- **Persistent Chat History:** Seamlessly saves and restores conversations dynamically. Chat history is nested inside the sidebar and automatically updates in real-time as conversations are created, loaded, or deleted.
- **Responsive Custom Dropdown Menus:** Hand-crafted, theme-aware custom hover dropdowns for selecting AI providers and models, offering a premium and polished developer experience.
- **Robust Security Policies:** Enterprise-grade session handling and GitLab OAuth flows aligned with modern auth protocols.

---

## 🛠️ Stack & Technologies Used

- **Framework:** Laravel 12 (PHP 8.2+)
- **Build System & Asset Bundler:** Vite
- **Database:** MySQL
- **CSS Engine:** Vanilla CSS (Glassmorphism, custom dark/light color schemes, fluid transition models)
- **AI Integrations:**
  - **OpenAI API** (GPT-4o, GPT-4o-mini, and other developer models)
  - **DeepSeek API** (DeepSeek Coder/Chat models)
- **VCS APIs:** GitHub API, GitLab API/OAuth
- **Streaming Protocol:** Server-Sent Events (SSE) for smooth token-by-token rendering.

---

## ⚙️ Installation & Setup

Follow these steps to set up the project locally:

### 1. Prerequisites
Ensure you have the following installed on your machine:
- PHP >= 8.2
- Composer
- Node.js & npm
- MySQL

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
- Your Database credentials (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
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
By default, the server runs at [http://127.0.5.1:8000](http://127.0.0.1:8000).

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
