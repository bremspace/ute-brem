#!/usr/bin/env bash
# UTE Parts POS Deployment Script
# Copy-paste: bash deploy.sh
set -euo pipefail

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
CYAN='\033[0;36m'; BLUE='\033[0;34m'; NC='\033[0m'

echo -e "${GREEN}UTE Parts POS — Deploy Script${NC}"
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

info()  { echo -e "${CYAN}[INFO]${NC}  $1"; }
ok()    { echo -e "${GREEN}[OK]${NC}    $1"; }
warn()  { echo -e "${YELLOW}[WARN]${NC}  $1"; }
fail()  { echo -e "${RED}[FAIL]${NC}    $1"; }
load()  { echo -n "${BLUE}[${1}]${NC} ... "; }

cd "$PROJECT_DIR"

echo ""
load "Navigate to project"
cd "$PROJECT_DIR"; ok "Working: $(pwd)"

if [ -d ".git" ]; then
    CURRENT_BRANCH=$(git branch --show-current)
    load "Pull git $CURRENT_BRANCH"
    git stash --include-untracked -q 2>/dev/null || true
    git pull origin "$CURRENT_BRANCH" 2>/dev/null || git pull origin "$CURRENT_BRANCH" --no-ff
    GIT_COMMIT=$(git log --oneline -1)
    info "Latest: $GIT_COMMIT"
    ok "Git pull selesai"
else
    warn "Bukan git repo, skip pull"
fi

load "Clear bootstrap cache"
rm -f bootstrap/cache/*.php bootstrap/cache/services.php bootstrap/cache/packages.php
ok "Cache cleared"

load "Backup .env (jika ada)"
cp .env .env.bak 2>/dev/null || true
ok "Backup selesai"

echo ""
load "Composer install (30 mnt)"
rm -rf vendor
composer clear-cache 2>/dev/null || true

echo -ne "${CYAN}  Running composer install...${NC}"
timeout 1800 composer install --no-interaction --verbose 2>&1 && ok || {
echo ""
warn "Composer install timeout / error"
warn "Coba manual di VPS:"
warn "  cd /home/sp.uteparts.id/public_html"
warn "  composer install --no-interaction --verbose"
warn ""
warn "Cek output error composer di atas"
echo ""
info "Mencoba fallback..."
composer install --no-interaction -o 2>&1 | grep -E "Success|Error|Failed" | tail -5 || true
exit 1
}

echo ""
load "Package discovery"
timeout 600 php artisan package:discover --force --ansi 2>&1 && ok "Package discovered" || {
    warn "Timeout, coba manual:"
    composer dump-autoload -o
    ok "Autoloader optimized"
}

echo ""
ok "Autoloader optimized"

echo ""
load "Setup .env"
[ -f .env ] || cp .env.example .env
if ! grep -q "APP_KEY=" .env; then
    echo "APP_KEY=" >> .env
fi
if grep -q "APP_KEY=base64:" .env; then
    APP_KEY=$(grep "^APP_KEY=" .env | cut -d= -f2 | cut -c1-30)  
    ok "APP_KEY: ${APP_KEY}..."
else
    info "Generating APP_KEY..."
    timeout 60 php artisan key:generate --force 2>&1 || {
        KEY=$(php -r "echo 'base64:' . base64_encode(random_bytes(32));")
        sed -i "s|APP_KEY=.*|APP_KEY=$KEY|" .env
    }
    ok "APP_KEY ready"
fi

echo ""
if command -v npm &>/dev/null; then
    rm -rf node_modules
    info "Installing npm packages..."
    timeout 600 npm install --no-audit --no-fund 2>&1 && ok "NPM installed" || {
        warn "NPM install error/timeuot"
        warn "Coba: npm install --no-audit --no-fund"
    }

    echo ""
    load "Build assets (Vite - 30 mnt)"
    timeout 1800 npm run build 2>&1 && ok "Assets built" || {
        warn "Build error/timeout"
        warn "Coba: npm run build"
    }
else
    warn "npm belum terinstall"
    exit 1
fi

echo ""
load "Database migration"
timeout 600 php artisan migrate --force 2>&1 && ok "Migrate selesai" || {
    warn "Migration error / already up"
}

echo ""
echo -ne "${CYAN}  Total users di DB: ${NC}"
TABLE_COUNT=$(php artisan tinker --execute="echo DB::table('users')->count();" 2>/dev/null || echo "?")
echo "$TABLE_COUNT"
if [ "$TABLE_COUNT" = "0" ]; then
    info "Seeder running..."
    timeout 600 php artisan db:seed --force 2>&1 && ok "Seeder selesai"
fi

echo ""
load "Set permissions"
chown -R cyberpanel:cyberpanel storage bootstrap/cache 2>/dev/null || \
    chown -R www-data:www-data storage bootstrap/cache
chmod -R 755 storage bootstrap/cache 2>/dev/null || true
ok "Permissions set"

echo ""
load "Cache config"
timeout 300 php artisan config:cache 2>&1 && ok "Config cached"
echo ""
load "Route cache"
timeout 300 php artisan route:cache 2>&1 && ok "Routes cached"
echo ""
load "View cache"
timeout 300 php artisan view:cache 2>&1 && ok "Views cached"

echo ""
load "Restart web server"
if systemctl restart lsws 2>/dev/null || systemctl restart openlitespeed 2>/dev/null; then
    ok "Server restarted"
elif systemctl restart nginx 2>/dev/null; then
    ok "Nginx restarted"
elif systemctl restart apache2 2>/dev/null; then
    ok "Apache restarted"
else
    warn "Gagal restart - restart manual jika perlu"
fi

echo ""
echo -e "${GREEN}≡≡≡ DEPLOY SELESAI ≡≡≡${NC}"
info "Access: http://DOMAIN_ULANGANAMILIK"

echo ""
warn "JALANKAN TESTING:"
warn "  1. Buka browser ke website"
warn "  2. Coba login via /admin/login"
warn "  3. Check error log jika ada: cat storage/logs/laravel.log"
echo ""