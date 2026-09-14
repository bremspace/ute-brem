#!/usr/bin/env bash
# UTE Parts POS Deployment Script
# Usage: bash deploy.sh

export COMPOSER_ALLOW_SUPERUSER=1

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
CYAN='\033[0;36m'; NC='\033[0m'

info()  { echo -e "${CYAN}[INFO]${NC}  $1"; }
ok()    { echo -e "${GREEN}[OK]${NC}    $1"; }
warn()  { echo -e "${YELLOW}[WARN]${NC}  $1"; }
fail()  { echo -e "${RED}[FAIL]${NC}  $1"; }

echo -e "${GREEN}UTE Parts POS — Deploy Script${NC}"

if [ -d ".git" ]; then
    info "Pull git..."
    git stash --include-untracked -q 2>/dev/null || true
    git pull origin main 2>/dev/null || git pull origin main --no-ff
    info "Latest: $(git log --oneline -1)"
fi

info "Clear cache..."
rm -f bootstrap/cache/*.php bootstrap/cache/services.php bootstrap/cache/packages.php

info "Backup .env..."
cp .env .env.bak 2>/dev/null || true

echo ""
info "=== COMPOSER INSTALL ==="
rm -rf vendor
composer clear-cache 2>/dev/null || true
echo -e "${CYAN}  [composer] Starting install...${NC}"
echo ""

if ! composer install --no-interaction 2>&1; then
    echo ""
    fail "COMPOSER INSTALL FAILED!"
    fail "Check the error above."
    fail ""
    fail "Common causes:"
    fail "  - PHP extension missing (php -m | grep -i pdo)"
    fail "  - PHP version too low (need >= 8.2)"
    fail "  - Network issue (try again)"
    fail ""
    fail "Manual fix:"
    fail "  cd $(pwd)"
    fail "  rm -rf vendor"
    fail "  composer clear-cache"
    fail "  composer install --no-interaction --verbose"
    exit 1
fi
ok "Composer install selesai"

echo ""
info "=== PACKAGE DISCOVER ==="
if ! php artisan package:discover --ansi 2>&1; then
    warn "package:discover error, trying fallback..."
    composer dump-autoload 2>&1 || true
fi
ok "Package discovered"

echo ""
info "=== SETUP .ENV ==="
[ -f .env ] || cp .env.example .env
if ! grep -q "APP_KEY=" .env; then
    echo "APP_KEY=" >> .env
fi
if grep -q "APP_KEY=base64:" .env; then
    ok "APP_KEY present: $(grep '^APP_KEY=' .env | cut -d= -f2 | cut -c1-25)..."
else
    info "Generating APP_KEY..."
    php artisan key:generate 2>&1 || {
        warn "key:generate failed, generating manual..."
        KEY=$(php -r "echo 'base64:' . base64_encode(random_bytes(32));")
        sed -i "s|APP_KEY=.*|APP_KEY=$KEY|" .env
    }
    ok "APP_KEY generated"
fi

echo ""
info "=== NPM INSTALL & BUILD ==="
if command -v npm &>/dev/null; then
    rm -rf node_modules
    echo -e "${CYAN}  [npm] Installing packages...${NC}"
    if ! npm install --no-audit --no-fund 2>&1; then
        fail "NPM INSTALL FAILED!"
        fail "Check the error above."
        fail "Manual fix: npm install --no-audit --no-fund"
        exit 1
    fi
    ok "NPM installed"

    echo -e "${CYAN}  [npm] Building assets...${NC}"
    if ! npm run build 2>&1; then
        fail "NPM BUILD FAILED!"
        fail "Manual fix: npm run build"
        exit 1
    fi
    if [ -d "public/build" ]; then
        ok "Assets built: $(ls public/build/assets/ 2>/dev/null | wc -l) files"
    else
        fail "public/build not created!"
    fi
else
    fail "npm not found! Install Node.js first:"
    echo "  curl -fsSL https://deb.nodesource.com/setup_20.x | sudo bash -"
    echo "  sudo apt-get install -y nodejs"
    exit 1
fi

echo ""
info "=== MIGRATE ==="
php artisan migrate --force 2>&1 && ok "Migration done" || warn "Migration skipped or already up"

echo ""
info "=== PERMISSIONS ==="
chown -R cyberpanel:cyberpanel storage bootstrap/cache 2>/dev/null || \
    chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 755 storage bootstrap/cache 2>/dev/null || true
ok "Permissions set"

echo ""
info "=== CACHE ==="
php artisan config:cache 2>&1 && ok "Config cached" || warn "Config cache skipped"
php artisan route:cache 2>&1 && ok "Routes cached" || warn "Route cache skipped"
php artisan view:cache 2>&1 && ok "Views cached" || warn "View cache skipped"

echo ""
info "=== RESTART WEB SERVER ==="
if systemctl restart lsws 2>/dev/null || systemctl restart openlitespeed 2>/dev/null; then
    ok "OpenLiteSpeed restarted"
elif systemctl restart nginx 2>/dev/null; then
    ok "Nginx restarted"
elif systemctl restart apache2 2>/dev/null; then
    ok "Apache restarted"
else
    warn "No web server restarted (manual restart may be needed)"
fi

echo ""
info "=== VERIFY ==="
echo -e "  Laravel: $(php artisan --version 2>&1)"
echo -e "  Routes: $(php artisan route:list --statie 2>/dev/null | wc -l) loaded"

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  DEPLOY SELESAI!                       ${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
info "Jika masih error, cek log:"
warn "  cat storage/logs/laravel.log"
warn "  tail -50 /home/103.57.200.1/logs/error.log"
