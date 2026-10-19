'use strict';
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const required = [
  'public/index.html', 'public/styles.css', 'public/app.js', 'public/manifest.webmanifest',
  'public/sw.js', 'public/privacy.html', 'public/terms.html', 'backend/server.js', '.env.example',
  'run-local.ps1', 'run-local.sh', 'Dockerfile', 'compose.yaml'
];
const missing = required.filter(file => !fs.existsSync(path.join(root, file)));
if (missing.length) {
  console.error('Missing required project files:', missing.join(', '));
  process.exit(1);
}
const server = fs.readFileSync(path.join(root, 'backend/server.js'), 'utf8');
const app = fs.readFileSync(path.join(root, 'public/app.js'), 'utf8');
const endpoints = [
  '/api/health', '/api/auth/register', '/api/auth/login', '/api/me', '/api/jobs',
  '/api/workers', '/api/matches', '/api/my/jobs', '/api/reports', '/api/admin/summary'
];
const missingEndpoints = endpoints.filter(route => !server.includes(route));
if (missingEndpoints.length) {
  console.error('Expected API route references were not found:', missingEndpoints.join(', '));
  process.exit(1);
}
for (const feature of ['openConversation', 'openReview', 'openReport', 'loadDashboard', 'loadAdmin']) {
  if (!app.includes(`function ${feature}`) && !app.includes(`async function ${feature}`)) {
    console.error(`Expected UI feature is missing: ${feature}`);
    process.exit(1);
  }
}
try {
  const manifest = JSON.parse(fs.readFileSync(path.join(root, 'public/manifest.webmanifest'), 'utf8'));
  if (!manifest.name || !manifest.start_url || !manifest.icons?.length) throw new Error('Required manifest fields are missing.');
} catch (error) {
  console.error('PWA manifest validation failed:', error.message);
  process.exit(1);
}
console.log(`Smoke checks passed: ${required.length} required files, ${endpoints.length} API route references, core UI features, and valid PWA manifest.`);
console.log('Note: these checks do not replace live API, browser, security, or production deployment testing.');
