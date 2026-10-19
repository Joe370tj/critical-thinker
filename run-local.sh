#!/usr/bin/env bash
set -euo pipefail
command -v node >/dev/null || { echo 'Install Node.js 20 or newer first.' >&2; exit 1; }
command -v npm >/dev/null || { echo 'npm is required.' >&2; exit 1; }
node -e 'if(Number(process.versions.node.split(".")[0])<20) process.exit(1)' || { echo 'Node.js 20 or newer is required.' >&2; exit 1; }
if [ ! -f .env ]; then
  cp .env.example .env
  SECRET="$(node -e 'process.stdout.write(require("crypto").randomBytes(48).toString("base64"))')"
  sed -i.bak "s|^JWT_SECRET=.*|JWT_SECRET=$SECRET|" .env
  rm -f .env.bak
  echo 'Created .env with a generated JWT secret.'
fi
if [ ! -d node_modules ]; then npm install; fi
npm test
exec npm start
