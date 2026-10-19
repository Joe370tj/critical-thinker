# SkillBridge AI — Runnable MVP

A responsive local service marketplace based on the supplied SkillBridge AI specification. The website, API, and SQLite database run together locally. This is a functional MVP codebase, not a hosted production service, native APK/IPA, or independently security-audited release.

## Included

- Responsive web interface and Progressive Web App (PWA) install files for supported Android/iOS browsers.
- Client and worker registration/sign-in; hashed passwords; 12-hour signed sessions.
- SQLite database created automatically at first start.
- Client job posting, public job search, worker search and profiles.
- Worker applications; clients can review applications and select a worker.
- Job status tracking, job-specific messages, completed-job reviews, and reports.
- Explainable rule-based match scores using skills (40%), area (25%), availability (20%), and review history (15%). This is not a trained ML model.
- Admin summary, account suspension/activation, report review, and audit log.
- Health endpoint: `GET /api/health`.
- Dockerfile and Docker Compose example with a persistent database volume.
- Draft Privacy Notice and Terms pages. Complete their placeholders and get appropriate review before launch.

## Requirements

- Node.js 20+ and npm.
- On some operating systems, build tools may be needed for `better-sqlite3`.
- Docker and Docker Compose are optional if you prefer container deployment.

## Run locally

**Windows:** extract the ZIP, open PowerShell in the `skillbridge-ai` folder, and run `./run-local.ps1`. If script execution is blocked by your Windows policy, use the manual steps below or run PowerShell with an allowed local-script policy.

**macOS/Linux:** open a terminal in the `skillbridge-ai` folder and run `./run-local.sh`.

**Manual method:** (1) run `npm install`; (2) copy `.env.example` to `.env` and set a long random `JWT_SECRET`; (3) run `npm test`; (4) run `npm start`. Then open `http://localhost:3000` and check `http://localhost:3000/api/health`. The first dependency installation needs internet access.

The SQLite file is created at `backend/skillbridge.db` by default. Back it up safely. Do not delete it if you need saved data. To use a different path, set `DB_PATH` to a writable file path.

## Create the first administrator

The administrator bootstrap endpoint is intentionally protected and should only be used during initial setup. It refuses to create another admin once an admin already exists.

1. Stop the app and temporarily set `ADMIN_SETUP_KEY` in `.env` to a long random value.
2. Restart the app.
3. Send a POST request to `http://localhost:3000/api/setup-admin` with JSON:

   ```json
   {
     "setupKey": "YOUR_TEMPORARY_KEY",
     "name": "Platform Admin",
     "email": "admin@example.com",
     "password": "Use-A-Unique-Password-At-Least-12-Chars"
   }
   ```

4. Sign in with that administrator account.
5. Remove `ADMIN_SETUP_KEY` from `.env` and restart the server. Do not leave the setup key configured in a public deployment.

Use curl or Postman to send the request. Never use the sample email/password as your real credentials.

## Run checks

```bash
npm test
```

This runs JavaScript syntax checks and project smoke checks for required files, API route references, core UI features, and the PWA manifest. It is not a full integration, penetration, browser, or load test. Those checks must still be run before production.

## Docker Compose

1. Install Docker Desktop or Docker Engine with Compose.
2. Set a long secret in your shell environment.
3. Run:

   ```bash
   export JWT_SECRET="replace-with-a-long-random-secret"
   docker compose up --build -d
   ```

4. Open `http://localhost:3000` and check `/api/health`.
5. Database data is kept in the named Docker volume `skillbridge_data`.

Do not expose this directly to the public internet until HTTPS, production secrets, backups, privacy/terms, and security review are completed. A production hosting provider must support persistent storage or you should move the database to a managed PostgreSQL/MySQL service.

## Mobile use

This project contains an installable PWA, not a compiled native Android APK or iOS IPA. For Android, deploy the site behind HTTPS, open it in a supported browser, and select **Install app** or **Add to Home screen**. On iPhone/iPad, open the HTTPS site in Safari and use **Add to Home Screen**. Mobile browsers and platform policies vary. For app-store publishing, a native app or an approved web wrapper plus store-specific assets, declarations, and testing is still required.

## API overview

- `GET /api/health`
- `POST /api/auth/register`, `POST /api/auth/login`, `GET /api/me`, `PATCH /api/me`
- `GET /api/jobs`, `POST /api/jobs`, `PATCH /api/jobs/:id`
- `GET /api/workers`, `GET /api/matches` (worker)
- `POST /api/jobs/:id/apply`, `GET /api/jobs/:id/applications`, `POST /api/applications/:id/select`
- `GET /api/my/jobs`, `PATCH /api/jobs/:id/status`
- `GET/POST /api/jobs/:id/messages`, `POST /api/jobs/:id/reviews`, `POST /api/reports`
- `GET /api/admin/summary`, `PATCH /api/admin/users/:id`, `PATCH /api/admin/reports/:id`

## Release checklist — required before real users

- [ ] Complete and review `public/privacy.html` and `public/terms.html`; replace all placeholders.
- [ ] Configure HTTPS, a domain, strong production secrets, and appropriate hosting.
- [ ] Confirm the database is on persistent storage and test a backup restore.
- [ ] Configure support contact, moderation and dispute processes, and age/safeguarding rules.
- [ ] Test account registration/login, duplicate accounts, permissions, job lifecycle, messages, reviews, reports, and admin actions using separate accounts.
- [ ] Test small-phone layouts, iOS Safari, Android Chrome, desktop browsers, expired sessions, and offline/error cases.
- [ ] Perform a security review and dependency audit; set up monitoring and incident response.
- [ ] Decide whether to retain SQLite or migrate to PostgreSQL/MySQL for public multi-instance hosting.
- [ ] If app stores are required, create and test native Android/iOS builds and prepare store listings, screenshots, privacy declarations, and developer accounts.

## Current limitations

- Matching is a transparent weighted score, not trained machine learning.
- No email verification, password-reset email, push notifications, online payments, identity-document verification, or live GPS tracking.
- Messages are job-linked and access-controlled but are not real-time WebSocket chat.
- SQLite is suitable for a simple single-instance MVP; production scale and multi-instance deployment need a reviewed database plan.
- Privacy and Terms pages are drafts with placeholders and must not be treated as completed legal compliance.
- The code has syntax checks, but a full dependency install and end-to-end runtime test must be completed in the target environment.
