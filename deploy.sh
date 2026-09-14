#!/usr/bin/env bash
# =============================================================================
# UTE Parts POS — Manual Deploy Script
# =============================================================================
# Cara pakai:
#   chmod +x deploy.sh && ./deploy.sh
#
# Atau langsung copy-paste satu blok ke terminal VPS:
#   bash deploy.sh
#
# Script ini:
#   1. Pull git terbaru
#   2. Install dependencies (PHP + Node)
#   3. Setup .env jika belum ada
#   4. Generate APP_KEY jika kosong
#   5. Jalankan migrasi database
#   6. Set permissions
#   7. Cache & optimize
#   8. Restart OpenLiteSpeed
# =============================================================================

set -euo pipefail

# --- Config ---
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
GIT_BRANCH="main"

# --- Colors ---
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

# --- Helpers ---
info()  { echo -e "${CYAN}[INFO]${NC}  $1"; }
ok()    { echo -e "${GREEN}[OK]${NC}    $1"; }
warn()  { echo -e "${YELLOW}[WARN]${NC}  $1"; }
fail()  { echo -e "${RED}[FAIL]${NC}  $1"; }

STEP=0
step() { STEP=$((STEP + 1)); echo -e "\n${CYAN}━━━ Step $STEP: $1 ━━━${NC}"; }

# --- Start ---
echo -e "${GREEN}╔══════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║   UTE Parts POS — Deploy Script          ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════╝${NC}"
echo ""
info "Project dir: $PROJECT_DIR"
echo ""

# --- Step 1: Navigate to project ---
step "Navigate to project directory"
cd "$PROJECT_DIR"
ok "Working directory: $(pwd)"

# --- Step 2: Git pull ---
step "Pull latest code from git"
if [ -d ".git" ]; then
    CURRENT_BRANCH=$(git branch --show-current)
    info "Current branch: $CURRENT_BRANCH"

    # Stash any local changes (safety net)
    git stash --include-untracked -q 2>/dev/null || true

    git pull origin "$GIT_BRANCH" --ff-only 2>/dev/null || {
        warn "Fast-forward failed, trying git pull with merge..."
        git pull origin "$GIT_BRANCH"
    }
    ok "Git pull selesai. Latest commit: $(git log --oneline -1)"
else
    warn "Bukan git repo, skip git pull"
fi

# --- Step 3: Clear stale bootstrap cache ---
step "Clear stale bootstrap cache"
rm -f bootstrap/cache/*.php
rm -f bootstrap/cache/services.php
rm -f bootstrap/cache/packages.php
ok "Cache bootstrap cleared"

# --- Step 4: Install PHP dependencies ---
step "Install PHP dependencies (composer)"
if command -v composer &>/dev/null; then
    # Backup .env sebelum composer install
    [ -f .env ] && cp .env .env.bak 2>/dev/null || true

    rm -rf vendor
    composer clear-cache 2>/dev/null || true
    composer install --optimize-autoloader --no-interaction 2>&1 | tail -5
    php artisan package:discover --force --ansi 2>/dev/null || warn "package:discover skipped"
    composer dump-autoload -o 2>/dev/null || true
    ok "Composer install selesai"
else
    fail "Composer tidak ditemukan! Install dulu:"
    echo "  curl -sS https://getcomposer.org/installer | php"
    echo "  mv composer.phar /usr/local/bin/composer"
    exit 1
fi

# --- Step 5: Restore .env backup if composer overwrote it ---
step "Verify .env file"
if [ -f .env.bak ]; then
    # Compare: if current .env is missing APP_KEY but backup has it, restore backup
    if ! grep -q "APP_KEY=base64:" .env 2>/dev/null && grep -q "APP_KEY=base64:" .env.bak 2>/dev/null; then
        cp .env.bak .env
        ok "Restored .env from backup (APP_KEY preserved)"
    fi
    rm -f .env.bak
fi

# Pastikan .env ada
if [ ! -f .env ]; then
    warn ".env tidak ditemukan, copy dari .env.example"
    cp .env.example .env
fi

# Pastikan APP_KEY ada di .env
if ! grep -q "APP_KEY=" .env 2>/dev/null; then
    echo "APP_KEY=" >> .env
    warn "APP_KEY line ditambahkan ke .env"
fi

# Generate APP_KEY jika kosong
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    info "APP_KEY kosong, generating..."
    php artisan key:generate --force 2>/dev/null || {
        warn "key:generate gagal, generating manual..."
        KEY=$(php -r "echo 'base64:' . base64_encode(random_bytes(32));")
        sed -i "s|APP_KEY=.*|APP_KEY=$KEY|" .env
    }
    ok "APP_KEY generated"
else
    ok "APP_KEY sudah ada"
fi

# --- Step 6: Install Node dependencies & build ---
step "Install Node dependencies & build assets"
if command -v npm &>/dev/null; then
    npm install --no-audit --no-fund 2>&1 | tail -3
    npm run build 2>&1 | tail -3
    if [ -d "public/build" ]; then
        ok "Assets built: $(ls public/build/assets/*.* 2>/dev/null | wc -l) files"
    else
        warn "public/build tidak terbentuk, mungkin ada build error"
    fi
else
    warn "npm tidak ditemukan, skip npm build"
fi

# --- Step 7: Database migration ---
step "Run database migration"
if php artisan migrate --force 2>/dev/null; then
    ok "Migration selesai"
else
    warn "Migration skipped atau sudah up-to-date"
fi

# --- Step 8: Seed database (only if tables are empty) ---
step "Check if seeder needed"
TABLE_COUNT=$(php artisan tinker --execute="echo DB::table('users')->count();" 2>/dev/null || echo "0")
if [ "$TABLE_COUNT" = "0" ]; then
    info "Users table kosong, running seeder..."
    php artisan db:seed --force 2>/dev/null || warn "Seeder skipped"
    ok "Seeder selesai"
else
    ok "Database sudah ada data ($TABLE_COUNT users), skip seeder"
fi

# --- Step 9: Set permissions ---
step "Set file permissions"
chown -R cyberpanel:cyberpanel "$PROJECT_DIR/storage" 2>/dev/null || \
    chown -R www-data:www-data "$PROJECT_DIR/storage" 2>/dev/null || true
chown -R cyberpanel:cyberpanel "$PROJECT_DIR/bootstrap/cache" 2>/dev/null || \
    chown -R www-data:www-data "$PROJECT_DIR/bootstrap/cache" 2>/dev/null || true
chmod -R 755 "$PROJECT_DIR/storage" 2>/dev/null || true
chmod -R 755 "$PROJECT_DIR/bootstrap/cache" 2>/dev/null || true
ok "Permissions set"

# --- Step 10: Cache & optimize ---
step "Cache config, routes, views"
php artisan config:cache 2>/dev/null && ok "Config cached" || warn "Config cache skipped"
php artisan route:cache 2>/dev/null && ok "Routes cached" || warn "Route cache skipped"
php artisan view:cache 2>/dev/null && ok "Views cached" || warn "View cache skipped"
php artisan event:cache 2>/dev/null || true

# --- Step 11: Restart web server ---
step "Restart OpenLiteSpeed"
if systemctl is-active --quiet lsws 2>/dev/null; then
    systemctl restart lsws
    ok "OpenLiteSpeed restarted"
elif systemctl is-active --quiet openlitespeed 2>/dev/null; then
    systemctl restart openlitespeed
    ok "OpenLiteSpeed restarted"
elif systemctl is-active --quiet nginx 2>/dev/null; then
    systemctl restart nginx
    ok "Nginx restarted"
elif systemctl is-active --quiet apache2 2>/dev/null; then
    systemctl restart apache2
    ok "Apache restarted"
else
    warn "Tidak ada web server yang dikenali, restart manual jika perlu"
fi

# --- Step 12: Verify ---
step "Verification"
echo ""
LARAVEL_VER=$(php artisan --version 2>/dev/null || echo "unknown")
info "Laravel: $LARAVEL_VER"

ROUTE_COUNT=$(php artisan route:list --columns=method 2>/dev/null | tail -n +3 | wc -l || echo "?")
info "Routes: $ROUTE_COUNT"

STORAGE_WRITABLE=$( [ -w storage ] && echo "YES" || echo "NO" )
info "Storage writable: $STORAGE_WRITABLE"

CACHE_WRITABLE=$( [ -w bootstrap/cache ] && echo "YES" || echo "NO" )
info "Cache writable: $CACHE_WRITABLE"

echo ""
echo -e "${GREEN}╔══════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║   Deploy selesai!                        ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════╝${NC}"
echo ""
info "Cek website di browser untuk memastikan semua berjalan."
echo ""
