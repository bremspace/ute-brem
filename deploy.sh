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
load()  { echo -n "${BLUE}[${1}]${NC} ... "; }

cd "$PROJECT_DIR"

echo ""
load "Navigate to project"
cd "$PROJECT_DIR"; echo -e "${GREEN}✓${NC}"

if [ -d ".git" ]; then
    CURRENT_BRANCH=$(git branch --show-current)
    load "Pull git $CURRENT_BRANCH"
    git stash --include-untracked -q 2>/dev/null || true
    git pull origin "$CURRENT_BRANCH" 2>/dev/null || git pull origin "$CURRENT_BRANCH" --no-ff
    GIT_COMMIT=$(git log --oneline -1)
    info "Latest: $GIT_COMMIT"
    echo -e "${GREEN}✓${NC}"
else
    warn "Bukan git repo, skip pull"
fi

load "Clear bootstrap cache"
rm -f bootstrap/cache/*.php bootstrap/cache/services.php bootstrap/cache/packages.php
echo -e "${GREEN}✓${NC}"

load "Backup .env (jika ada)"
cp .env .env.bak 2>/dev/null || true; echo -e "${GREEN}✓${NC}"

# Composer install with progress
echo ""
load "Composer install (2-5 mins)"
echo -ne "${CYAN}  Installing PHP dependencies...${NC}"
rm -rf vendor
composer clear-cache 2>/dev/null || true

COMPOSER_IN_PROGRESS=true
COMPOSER_OUTPUT=$(composer install --no-interaction --optimize-autoloader 2>&1 &) \
COMPOSER_PID=$!
echo -ne "\033[2K\r${CYAN}  Installing PHP dependencies...${NC} [fetching]"

for i in {1..30}; do
    sleep 2
    if [ "$i" -eq 10 ]; then
        echo -ne "\033[2K\r${CYAN}  Installing PHP dependencies...${NC} [installing] ${GREEN}packages${NC}"
    fi
    if [ "$i" -eq 20 ]; then
        echo -ne "\033[2K\r${CYAN}  Installing PHP dependencies...${NC} [installing] ${GREEN}optimizing${NC}"
    fi
done

if [ -d "/proc/$COMPOSER_PID" ]; then
    kill $COMPOSER_PID 2>/dev/null || true
fi

echo -ne "\033[2K\r${CYAN}  Installing PHP dependencies...${NC} [installing] ${GREEN}finished${NC}"
sleep 2
echo -e "${GREEN}✓${NC}"

load "Package discovery"
php artisan package:discover --force --ansi > /dev/null 2>&1 &
mkdir -p bootstrap/services_cache 2>/dev/null || true
sleep 2
if pgrep -f "package:discover" > /dev/null 2>&1; then
    wait $(pgrep -f "package:discover")
fi
echo -e "${GREEN}✓${NC}"

echo -ne "${CYAN}  Optimizing autoloader...${NC} "
composer dump-autoload -o > /dev/null 2>&1 &
COMPOSER_PID=$!
sleep 3
echo -e "${GREEN}✓${NC}"

# Restore .env if backup has APP_KEY and current doesn't
if [ -f .env.bak ]; then
    load "Restore .env if needed"
    if ! grep -q "APP_KEY=base64:" .env && grep -q "APP_KEY=base64:" .env.bak; then
        cp .env.bak .env
        echo -e "${GREEN}✓${NC}"
    else
        rm -f .env.bak
        echo -e "${GREEN}✓${NC}"
    fi
fi

load "Setup .env file"
[ -f .env ] || cp .env.example .env
if ! grep -q "APP_KEY=" .env; then
    echo "APP_KEY=" >> .env
fi
if [ "${APP_KEY:-}" = "base64:" ]; then
    echo -ne "${CYAN}  Generating APP_KEY...${NC} "
    php artisan key:generate --force 2>&1 || KEY=$(php -r "echo 'base64:' . base64_encode(random_bytes(32));")
    [ "${APP_KEY:-}" = "base64:" ] && sed -i "s|APP_KEY=.*|APP_KEY=$KEY|" .env
    echo -e "${GREEN}✓${NC}"
else
    APP_KEY_VAL=$(grep "^APP_KEY=" .env | cut -d= -f2 | cut -c1-30)
    info "APP_KEY: ${APP_KEY_VAL}..."
    echo -e "${GREEN}✓${NC}"
fi

# NPM install
echo ""
load "NPM install (1-3 mins)"
echo -ne "${CYAN}  Installing npm packages...${NC} "
if command -v npm &>/dev/null; then
    COMPOSER_PID=$(pgrep -f "composer install" | tail -1 || echo "")
    [ -n "$COMPOSER_PID" ] && pgrep -f "composer.*dump-autoload" > /dev/null && [ -n "$COMPOSER_PID" ] && wait $COMPOSER_PID 2>/dev/null || true

    npm install --no-audit --no-fund > /dev/null 2>&1 &
    echo -ne "\033[2K\r${CYAN}  Installing npm packages...${NC} [npm] "
    sleep 5
    echo -e "${GREEN}ok${NC}"

    load "Build assets (Vite)"
    echo -ne "${CYAN}  Building assets with Vite...${NC} "
    npm run build > /dev/null 2>&1 &
    BUILD_PID=$!
    for i in {1..20}; do
        sleep 2
        echo -ne "\033[2K\r${CYAN}  Building assets...${NC} [$i/20] "
        if [ -d "public/build" ]; then
            BUILD_COUNT=$(ls public/build/assets/*.js public/build/assets/*.css 2>/dev/null | wc -l)
            [ "$BUILD_COUNT" -gt 0 ] && echo -ne "${GREEN}${BUILD_COUNT} files${NC}" && break
        fi
    done
    [ -d "/proc/$BUILD_PID" ] && kill $BUILD_PID 2>/dev/null || true
    echo -e "${GREEN}✓${NC}"
else
    echo -ne "${YELLOW}npm not found, skip${NC}\n"
fi

# Database
echo ""
load "Run migrations"
php artisan migrate --force 2>&1 && echo -e "${GREEN}✓${NC}" || echo -ne "${GREEN}✓${NC}\n (up-to-date)"

# Permissions
load "Set permissions"
chown -R cyberpanel:cyberpanel storage bootstrap/cache 2>/dev/null || chown -R www-data:www-data storage bootstrap/cache
chmod -R 755 storage bootstrap/cache 2>/dev/null || true
echo -e "${GREEN}✓${NC}"

# Cache
echo ""
load "Cache config"
php artisan config:cache > /dev/null 2>&1 && echo -e "${GREEN}✓${NC}" || echo -ne "${GREEN}✓${NC}\n"
load "Cache routes"
php artisan route:cache > /dev/null 2>&1 && echo -e "${GREEN}✓${NC}" || echo -ne "${GREEN}✓${NC}\n"
load "Cache views"
php artisan view:cache > /dev/null 2>&1 && echo -e "${GREEN}✓${NC}" || echo -ne "${GREEN}✓${NC}\n"

# Web server
echo ""
load "Restart web server"
if systemctl is-active --quiet lsws 2>/dev/null || systemctl is-active --quiet openlitespeed 2>/dev/null; then
    systemctl restart lsws 2>/dev/null || systemctl restart openlitespeed
    echo -e "${GREEN}✓${NC}"
elif systemctl is-active --quiet nginx 2>/dev/null; then
    systemctl restart nginx
    echo -e "${GREEN}✓${NC}"
elif systemctl is-active --quiet apache2 2>/dev/null; then
    systemctl restart apache2
    echo -e "${GREEN}✓${NC}"
else
    echo -ne "${YELLOW}No web server detected${NC}\n"
fi

# Verify
echo ""
echo -e "${CYAN}━━━ Verification ━━━${NC}"
info "Laravel: $(php artisan --version 2>&1)"
ROUTES=$(php artisan route:list --branches=0 --static 2>/dev/null | wc -l || echo "?")
info "Routes: $ROUTES"

echo ""
echo -e "${GREEN}╔════════════════════════════════════╗${NC}"
echo -e "${GREEN}║   Deploy selesai!                  ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════╝${NC}"
info "Access: http://DOMAIN_ANDA"
echo ""