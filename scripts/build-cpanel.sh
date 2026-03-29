#!/bin/bash
set -e

DOMAIN="${1:-yourdomain.com}"

echo "=== Kucatat — Build for cPanel (Rumahweb) ==="
echo "Domain: $DOMAIN"
echo ""

# ─── Frontend Build ────────────────────────────────────
echo "[1/3] Building frontend..."

cd frontend
cat > .env.production.local <<EOF
NEXT_PUBLIC_API_URL=https://api.$DOMAIN/api/v1
EOF

npm install --legacy-peer-deps
npm run build
cd ..

echo "    Frontend built."

# ─── Package Frontend ──────────────────────────────────
echo "[2/3] Packaging frontend..."

cd frontend
zip -r ../kucatat-frontend.zip \
  .next \
  public \
  server.js \
  package.json \
  package-lock.json \
  next.config.ts \
  tsconfig.json \
  .env.production.local \
  -x "*.DS_Store"
cd ..

echo "    kucatat-frontend.zip created."

# ─── Package Backend ───────────────────────────────────
echo "[3/3] Packaging backend..."

cd backend
zip -r ../kucatat-backend.zip . \
  -x "node_modules/*" \
  -x ".git/*" \
  -x "vendor/*" \
  -x "tests/*" \
  -x ".env" \
  -x "database/database.sqlite" \
  -x "storage/logs/*.log" \
  -x "storage/framework/cache/data/*" \
  -x "storage/framework/sessions/*" \
  -x "storage/framework/views/*.php" \
  -x "*.DS_Store"
cd ..

# Create production .env template for Laravel
cat > kucatat-backend.env <<EOF
APP_NAME=Kucatat
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://api.$DOMAIN
FRONTEND_URL=https://$DOMAIN
SANCTUM_STATEFUL_DOMAINS=$DOMAIN,api.$DOMAIN

APP_LOCALE=en
APP_FALLBACK_LOCALE=en

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=CHANGE_ME
DB_USERNAME=CHANGE_ME
DB_PASSWORD=CHANGE_ME

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=.$DOMAIN

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync

CACHE_STORE=file

MAIL_MAILER=smtp
MAIL_HOST=CHANGE_ME
MAIL_PORT=465
MAIL_USERNAME=CHANGE_ME
MAIL_PASSWORD=CHANGE_ME
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="noreply@$DOMAIN"
MAIL_FROM_NAME="\${APP_NAME}"
EOF

echo "    kucatat-backend.zip created."

echo ""
echo "============================================"
echo "  BUILD COMPLETE"
echo "============================================"
echo ""
echo "  Files:"
echo "    kucatat-frontend.zip  $(du -sh kucatat-frontend.zip | cut -f1)"
echo "    kucatat-backend.zip   $(du -sh kucatat-backend.zip | cut -f1)"
echo "    kucatat-backend.env   (production .env template)"
echo ""
echo "  Deploy steps → docs/DEPLOY-CPANEL.md"
echo ""
