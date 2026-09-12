# Tutorial Deploy UTE Parts POS ke CyberPanel

> Dari 0 sampai live, tanpa domain sekalipun.

---

## Daftar Isi

1. [Pra-syarat](#1-pra-syarat)
2. [Install CyberPanel di VPS](#2-install-cyberpanel-di-vps)
3. [Setup CyberPanel - Buat Website & Database](#3-setup-cyberpanel---buat-website--database)
4. [Upload Project](#4-upload-project)
5. [Setup via SSH](#5-setup-via-ssh)
6. [Konfigurasi OpenLiteSpeed](#6-konfigurasi-openlitespeed)
7. [SSL (HTTPS)](#7-ssl-https)
8. [Verifikasi & Login](#8-verifikasi--login)
9. [Tanpa Domain? Pakai IP!](#9-tanpa-domain-pakai-ip)
10. [Troubleshooting](#10-troubleshooting)

---

## 1. Pra-syarat

| Kebutuhan        | Keterangan                                      |
| ---------------- | ----------------------------------------------- |
| VPS              | Minimal 1 vCPU / 1GB RAM / 25GB SSD            |
| OS               | Ubuntu 20.04 / 22.04 / Debian 11 / 12          |
| SSH Access       | Username `root` atau user dengan sudo            |
| Domain (opsional) | Bisa pakai IP langsung tanpa domain             |

### Cek versi OS

```bash
cat /etc/os-release
```

### Update system

```bash
apt update && apt upgrade -y
```

---

## 2. Install CyberPanel di VPS

### 2.1 — Download script install

```bash
sh <(curl https://cyberpanel.net/install.sh || wget -O - https://cyberpanel.net/install.sh)
```

### 2.2 — Pilih opsi install

```
1. Install CyberPanel
2. Full installation (with OpenLiteSpeed)
3. Enter password untuk CyberPanel admin
4. Install Memcached? -> Y
5. Install Redis? -> Y
6. Install FTP? -> Y
```

**Rekomendasi**: Pilih **Full installation** + semua optional Y.

### 2.3 — Tunggu selesai

Proses install ~5-15 menit. Setelah selesai, akan muncul informasi:

```
CyberPanel Admin URL: https://YOUR_SERVER_IP:8090
Admin Username: admin
Admin Password: (password yang kamu input)
```

**Catat semua info di atas!**

### 2.4 — Login CyberPanel

Buka browser → `https://YOUR_SERVER_IP:8090`

- Username: `admin`
- Password: (yang tadi)

> **Note**: Browser akan muncul warning "Not Secure" karena Self-Signed SSL. Klik **Advanced** → **Proceed**.

---

## 3. Setup CyberPanel - Buat Website & Database

### 3.1 — Buat Website

1. Login CyberPanel
2. Sidebar → **Websites** → **Create Website**
3. Isi form:

| Field           | Isi                                                      |
| --------------- | -------------------------------------------------------- |
| Select Package  | Default                                                  |
| Domain Name     | `IP_ADDRESS` (pakai IP, contoh: `103.57.200.1`)         |
| Email           | `admin@localhost.local`                                  |
| Select PHP      | **8.2** (atau yang tersedia >=8.2)                       |
| Select JDK      | No                                                       |
| Disc Quota      | 1024 (MB) atau lebih                                     |
| Bandwidth       | 10240 (MB) atau lebih                                    |

4. Klik **Create Website**
5. Tunggu sampai muncul "Website Created Successfully"

### 3.2 — Buat Database

1. Sidebar → **Databases** → **Create Database**
2. Isi:

| Field         | Isi                     |
| ------------- | ----------------------- |
| Domain        | `103.57.200.1`          |
| Database Name | `ute_parts_pos`         |
| DB Username   | `ute_parts`             |
| Password      | `BuatPasswordKuat123!`  |

3. Klik **Create Database**

**Catat kredensial database ini!**

| Keterangan      | Value               |
| --------------- | ------------------- |
| DB Host         | `127.0.0.1`         |
| DB Name         | `ute_parts_pos`     |
| DB Username     | `ute_parts`         |
| DB Password     | `BuatPasswordKuat123!` |

---

## 4. Upload Project

### 4.1 — Download project di local

Pastikan project sudah di ZIP di komputer lokal kamu.

**Windows (PowerShell):**

```powershell
cd C:\path\to\project
tar -czf uteparts.tar.gz --exclude='.git' --exclude='node_modules' --exclude='vendor' .
```

**Atau pakai ZIP biasa:**

```powershell
# Pakai 7-Zip atau WinRAR
# ZIP semua file project, KECUALI folder .git, node_modules, vendor
```

### 4.2 — Upload via File Manager CyberPanel

1. CyberPanel → **File Manager**
2. Navigate ke: `/home/103.57.200.1/public_html/`
3. Klik **Upload** → pilih file `uteparts.tar.gz`
4. Setelah upload selesai, klik kanan file → **Extract**
5. **Hapus file `uteparts.tar.gz`** setelah extract

### 4.3 — Upload via SSH / SCP (Alternatif)

Dari komputer lokal:

```bash
# Windows (PowerShell) - pakai WinSCP atau:
scp -r C:\path\to\project\* root@YOUR_SERVER_IP:/home/103.57.200.1/public_html/

# Linux/Mac
scp -r /path/to/project/* root@YOUR_SERVER_IP:/home/103.57.200.1/public_html/
```

### 4.4 — Upload via Git (Alternatif)

#### 4.4.1 — Setup SSH Key agar `git pull` tidak perlu credential

Jika pake HTTPS, `git pull` akan nanyak username/password. Solusi pakai SSH key (recommended):

```bash
# Di VPS — generate SSH key (tanpa passphrase biar auto-login)
ssh-keygen -t ed25519 -C "deploy@vps" -f ~/.ssh/id_ed25519_deploy -N ""
cat ~/.ssh/id_ed25519_deploy.pub
```

Salin output (public key). Di GitHub repo → Settings → Deploy Keys → **Add deploy key**:
- Title: `vps-deploy`
- Key: tempel public key di atas
- **Berikan centang "Allow write access"** (agar `git pull` & `git push` bisa)

Setelah itu konversi remote URL dari HTTPS ke SSH:

```bash
cd /home/103.57.200.1/public_html/
git config --global --add safe.directory '*'
git remote set-url origin git@github.com:bremspace/ute-brem.git
ssh -T git@github.com     # akan muncul: "Hi bremspace! You've successfully authenticated..."
```

Testing pull:

```bash
git pull origin main
```

Jika muncul error `Host key verification failed` atau `Permission denied (publickey)`, pastikan:
```bash
ssh-keyscan -t ed25519 github.com >> ~/.ssh/known_hosts
```

#### 4.4.2 — Git clone pertama kali

```bash
cd /home/103.57.200.1/public_html/
git clone git@github.com:bremspace/ute-brem.git .
```

> **Jika tetap pakai HTTPS**: buat Personal Access Token (GitHub → Settings → Developer settings → Personal access tokens → Generate new token, beri scope `repo` & `workflow`), lalu gunakan sebagai password saat diminta. Tapi ini perlu refresh tiap 30-90 hari.

### 4.5 — Pastikan struktur benar

Setelah upload, hasilnya harus seperti ini:

```
/home/103.57.200.1/public_html/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
│   ├── .htaccess
│   ├── index.php
│   └── ...
├── resources/
├── routes/
├── storage/
├── vendor/
├── .env.example
├── artisan
├── composer.json
├── package.json
└── vite.config.ts
```

---

## 5. Setup via SSH

SSH ke server:

```bash
ssh root@YOUR_SERVER_IP
```

### 5.1 — Install Composer (Kalau Belum Ada)

```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
composer --version
```

### 5.2 — Install Node.js 20+ (Kalau Belum Ada)

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt-get install -y nodejs
node -v   # Pastikan v20+
npm -v
```

### 5.3 — Install PHP Dependencies

```bash
rm -rf vendor                           # paksa fresh download (hindari vendor corrupt)
composer clear-cache
composer install --no-dev --optimize-autoloader 2>&1 | tail -10
```

### 5.4 — Install Node & Build Assets

```bash
npm install
npm run build
```

**Pastikan `public/build/` terbentuk:**

```bash
ls -la public/build/
```

Harusnya ada file `.css` dan `.js` hasil Vite build.

### 5.5 — Buat File .env

```bash
cp .env.example .env
```

### 5.6 — Generate App Key & Cache (setelah .env ada)

```bash
rm -f bootstrap/cache/*.php              # hapus cache corrupt → fix "make() on null"
php artisan key:generate --force && \
php artisan package:discover --ansi && \
php artisan config:cache && \
php artisan route:cache
```

> **Bila `key:generate` masih error `make() on null`** meskipun sudah `rm -f bootstrap/cache/*.php`:
> berarti `vendor/` corrupt atau PHP extension konflik. Fix paksa:
> ```bash
> cd /home/sp.uteparts.id/public_html
> rm -rf vendor
> composer clear-cache
> composer install --no-dev --optimize-autoloader
> php artisan key:generate --force
> ```

> **Still error & melihat `ionCube PHP Loader` di `php -v`:** ionCube Loader bisa corrupt Laravel bootstrap. Non-aktifkan sementara via SSH:
> ```bash
> php -n -d extension=openssl -d extension=pdo_mysql -d extension=mbstring \
>   -d extension=ctype -d extension=json -d extension=tokenizer -d extension=xml \
>   -d extension=curl -d extension=gd -d extension=zip -d extension=fileinfo \
>   artisan key:generate --force
> ```
> Jika berhasil, kontak host untuk disable ionCube permanen untuk PHP CLI.

### 5.7 — Edit .env

```bash
nano .env
```

**Isi/sesuaikan baris-baris berikut:**

```env
APP_NAME="UTE Parts POS"
APP_ENV=production
APP_KEY=base64:(otomatis sudah ada dari key:generate)
APP_DEBUG=false
APP_URL=http://103.57.200.1
APP_FORCE_HTTPS=false

APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=id_ID

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ute_parts_pos
DB_USERNAME=ute_parts
DB_PASSWORD=BuatPasswordKuat123!

SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="admin@localhost.local"
MAIL_FROM_NAME="${APP_NAME}"

VITE_APP_NAME="${APP_NAME}"

DUITKU_MERCHANT_CODE=
DUITKU_API_KEY=
DUITKU_SANDBOX_MODE=true

SPA_URL=http://103.57.200.1
```

**Simpan file**: Ctrl+O → Enter → Ctrl+X

### 5.8 — Import Database

**Opsi A: Pakai file SQL yang sudah ada (rekomendasi)**

```bash
cd /home/103.57.200.1/public_html
mysql -u ute_parts -p ute_parts_pos < db_uteparts.sql
# Masukkan password database saat diminta

# Atau pakai latest.sql
mysql -u ute_parts -p ute_parts_pos < latest.sql
```

**Opsi B: Pakai Migration + Seeder**

```bash
php artisan migrate --force
php artisan db:seed --force
```

### 5.9 — Set Permission

```bash
chown -R cyberpanel:cyberpanel /home/103.57.200.1/public_html/storage
chown -R cyberpanel:cyberpanel /home/103.57.200.1/public_html/bootstrap/cache
chmod -R 755 /home/103.57.200.1/public_html/storage
chmod -R 755 /home/103.57.200.1/public_html/bootstrap/cache
```

### 5.10 — Cache & Optimize

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

## 6. Konfigurasi OpenLiteSpeed

### 6.1 — Set Document Root ke public/

CyberPanel → **Websites** → **List Websites** → **Manage** pada website kamu → **vhost Conf**

Cari atau tambahkan:

```
docRoot                 /home/103.57.200.1/public_html/public
```

**Atau edit via SSH:**

```bash
nano /usr/local/lsws/conf/vhosts/103.57.200.1/vhost.conf
```

Ubah `docRoot` ke:

```
docRoot /home/103.57.200.1/public_html/public
```

### 6.2 — Pastikan Rewrite Aktif

`.htaccess` sudah ada di `public/`, OpenLiteSpeed mendukungnya. Tapi kalau rewrite tidak jalan, tambahkan manual di vhost config:

```
rewrite {
  enable                  1
  rules                   <<<END
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
  END
}
```

### 6.3 — Restart OpenLiteSpeed

```bash
systemctl restart lsws
```

Atau via CyberPanel: **Restart** → **LiteSpeed Web Server**

---

## 7. SSL (HTTPS)

### 7.1 — Install SSL

> **Catatan**: SSL Let's Encrypt butuh domain, bukan IP.

**Kalau pakai domain:**

1. CyberPanel → **Websites** → **Manage** → **SSL**
2. Klik **Issue SSL**
3. Tunggu selesai
4. Aktifkan **Force HTTPS**

**Kalau pakai IP (tanpa domain):**

SSL Let's Encrypt tidak bisa untuk IP. Opsi:

- **Opsi A**: Beli domain murah (~Rp 100rb/tahun) → pointed ke IP → issue SSL
- **Opsi B**: Pakai HTTP saja (tidak disarankan untuk production)
- **Opsi C**: Self-signed SSL (muncul warning di browser)

### 7.2 — Force HTTPS (Kalau Pakai Domain + SSL)

Edit `.env`:

```env
APP_URL=https://domainkamu.com
APP_FORCE_HTTPS=true
```

Lalu clear cache:

```bash
php artisan config:cache
```

---

## 8. Verifikasi & Login

### 8.1 — Buka Website

**Tanpa domain:**
```
http://103.57.200.1
```

**Dengan domain:**
```
https://domainkamu.com
```

### 8.2 — Login

| Role    | Email                  | Password    |
| ------- | ---------------------- | ----------- |
| Owner   | owner@uteparts.test    | Owner123!   |
| Manager | manager@uteparts.test  | Manager123! |
| Kasir   | kasir@uteparts.test    | Kasir123!   |

> **PENTING**: Ganti password default setelah login pertama!

---

## 9. Tanpa Domain? Pakai IP!

**Bisa! Tapi dengan catatan:**

| Fitur                  | Keterangan                                    |
| ---------------------- | --------------------------------------------- |
| Akses Website          | ✅ `http://YOUR_IP`                           |
| Login & Semua Fitur    | ✅ Berjalan normal                             |
| SSL / HTTPS            | ⚠️ Let's Encrypt tidak support IP              |
| Email Notifications    | ⚠️ Perlu domain untuk email profesional       |
| DuitKu Payment         | ⚠️ Perlu domain untuk callback URL             |

### Alternatif: Beli Domain Murah

| Provider      | Harga Mulai    | URL                        |
| ------------- | -------------- | -------------------------- |
| Niagahoster   | Rp 9.900/tahun | niagahoster.co.id          |
| Domainesia    | Rp 9.900/tahun | domainesia.com             |
| IDCloudHost   | Rp 9.900/tahun | idcloudhost.com            |
| Namecheap     | ~$1/tahun      | namecheap.com              |

Setelah beli domain:
1. Set **DNS A Record** → pointing ke IP VPS
2. Tunggu propagation (5-60 menit)
3. Buat ulang website di CyberPanel dengan domain baru
4. Issue SSL

---

## 10. Troubleshooting

### 10.1 — Halaman Blank / Error 500

```bash
# Cek error log OpenLiteSpeed
tail -50 /home/103.57.200.1/logs/error.log

# Cek Laravel log
tail -50 /home/103.57.200.1/public_html/storage/logs/laravel.log
```

### 10.2 — CSS / JS Tidak Muncul

```bash
cd /home/103.57.200.1/public_html
npm run build
ls -la public/build/
```

Pastikan folder `public/build/` ada dan berisi file.

### 10.3 — Route 404 / URL Tidak Buka

- Pastikan `docRoot` mengarah ke `.../public_html/public`
- Pastikan `.htaccess` ada di folder `public/`
- Restart OpenLiteSpeed: `systemctl restart lsws`

### 10.4 — Permission Denied

```bash
chown -R cyberpanel:cyberpanel /home/103.57.200.1/public_html
chmod -R 755 /home/103.57.200.1/public_html/storage
chmod -R 755 /home/103.57.200.1/public_html/bootstrap/cache
```

### 10.5 — Database Connection Error

```bash
# Cek apakah MySQL berjalan
systemctl status mysql

# Test koneksi
mysql -u ute_parts -p -h 127.0.0.1 ute_parts_pos
```

### 10.6 — Livewire Component Not Found

```bash
cd /home/103.57.200.1/public_html
php artisan livewire:discover
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
php artisan optimize
```

### 10.7 — PHP Version Terlalu Rendah

CyberPanel → **PHP** → **Install Extensions** → pilih PHP 8.2 → pastikan semua extension terinstall.

---

## Checklist Deploy

- [ ] VPS aktif & SSH bisa diakses
- [ ] CyberPanel terinstall
- [ ] Website dibuat di CyberPanel
- [ ] Database dibuat (name + user + password)
- [ ] File project terupload ke `public_html/`
- [ ] Composer install selesai
- [ ] NPM install + build selesai
- [ ] `.env` dikonfigurasi dengan benar
- [ ] App key di-generate
- [ ] Database ter-import (SQL atau migrate+seed)
- [ ] Permission storage & bootstrap/cache set
- [ ] Document root = `public/`
- [ ] OpenLiteSpeed di-restart
- [ ] Website bisa diakses
- [ ] Login berhasil
- [ ] SSL terpasang (kalau pakai domain)

---

## Perintah Cepat (Copy-Paste)

Berikut rangkuman perintah yang perlu dijalankan via SSH:

```bash
# === SETUP SERVER ===
apt update && apt upgrade -y
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt-get install -y nodejs
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# === SETUP PROJECT ===
cd /home/IP_ADDRESS/public_html
cp .env.example .env
php artisan key:generate

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Edit .env (sesuaikan IP, DB credentials)
nano .env

# Import database
mysql -u DB_USER -p DB_NAME < db_uteparts.sql

# Set permissions
chown -R cyberpanel:cyberpanel storage bootstrap/cache
chmod -R 755 storage bootstrap/cache

# Cache & optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Restart web server
systemctl restart lsws
```

---

## Update / Redeploy

Kalau ada update code, jalankan:

```bash
cd /home/IP_ADDRESS/public_html

# Pull update (kalau pakai git)
git pull origin main

# Install new dependencies (kalau ada)
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Run migration (kalau ada perubahan database)
php artisan migrate --force

# Clear & rebuild cache
php artisan optimize:clear
php artisan optimize

# Restart
systemctl restart lsws
```

---

*Terakhir diperbarui: September 2026*