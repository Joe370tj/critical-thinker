$ErrorActionPreference = 'Stop'
if (-not (Get-Command node -ErrorAction SilentlyContinue)) { throw 'Node.js 20 or newer is required. Install Node.js from https://nodejs.org, then reopen PowerShell.' }
if (-not (Get-Command npm -ErrorAction SilentlyContinue)) { throw 'npm was not found. Reinstall Node.js with npm included, then reopen PowerShell.' }
$major = [int]((node -p "process.versions.node.Split('.')[0]").Trim())
if ($major -lt 20) { throw "Node.js 20 or newer is required. Detected major version $major." }
if (-not (Test-Path '.env')) {
  Copy-Item '.env.example' '.env'
  $bytes = New-Object byte[] 48
  $rng = [System.Security.Cryptography.RandomNumberGenerator]::Create()
  $rng.GetBytes($bytes)
  $secret = [Convert]::ToBase64String($bytes)
  $rng.Dispose()
  $envText = Get-Content '.env' -Raw
  $envText = $envText -replace 'JWT_SECRET=.*', "JWT_SECRET=$secret"
  Set-Content '.env' $envText -Encoding ascii
  Write-Host 'Created .env with a generated JWT secret.' -ForegroundColor Green
}
if (-not (Test-Path 'node_modules')) {
  Write-Host 'Installing dependencies. An internet connection is required...' -ForegroundColor Cyan
  npm install
  if ($LASTEXITCODE -ne 0) { throw 'npm install failed. Check your internet connection and retry npm install.' }
}
npm test
if ($LASTEXITCODE -ne 0) { throw 'Syntax checks failed. Do not continue until the errors are fixed.' }
Write-Host 'Starting SkillBridge AI. Open http://localhost:3000 in your browser.' -ForegroundColor Green
npm start
