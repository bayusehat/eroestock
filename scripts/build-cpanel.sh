#!/bin/bash
set -e

DOMAIN="${1:-yourdomain.com}"

echo "=== Kucatat — Build for cPanel (Rumahweb) ==="
echo "Domain: $DOMAIN"
echo ""

# ─── Build Assets ─────────────────────────────────────
echo "[1/2] Building frontend assets..."

npm install
npm run build

echo "    Assets built."

# ─── Package App ──────────────────────────────────────
echo "[2/2] Packaging app..."

zip -r kucatat.zip . \
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
  -x "*.DS_Store" \
  -x "scripts/*" \
  -x "docs/*"

# Create production .env template
cat > kucatat.env <<EOF
APP_NAME=Kucatat
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://$DOMAIN

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

echo "    kucatat.zip created."

echo ""
echo "============================================"
echo "  BUILD COMPLETE"
echo "============================================"
echo ""
echo "  Files:"
echo "    kucatat.zip   $(du -sh kucatat.zip | cut -f1)"
echo "    kucatat.env   (production .env template)"
echo ""
echo "  Deploy steps → docs/DEPLOY-CPANEL.md"
echo ""
