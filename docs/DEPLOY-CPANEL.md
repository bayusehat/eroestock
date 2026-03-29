# Deploy Kucatat ke cPanel Rumahweb

Panduan deploy Kucatat (Laravel API + Next.js frontend) ke cPanel Rumahweb.

## Persyaratan

| Kebutuhan | Minimum |
|-----------|---------|
| Paket | Cloud Hosting (RAM 4GB, 2vCPU) |
| PHP | 8.2+ |
| Node.js | 20+ (via Node.js Selector) |
| Database | MySQL 8+ |
| SSH Access | Wajib |

## Arsitektur

```
Domain utama: yourdomain.com        ← Next.js (Node.js Selector)
Subdomain:    api.yourdomain.com    ← Laravel API
```

- **Frontend** berjalan via cPanel Node.js Selector
- **Backend** berjalan sebagai PHP app biasa di subdomain `api`
- Frontend memanggil API di `https://api.yourdomain.com/api/v1/...`

## Step 1: Build di Lokal

```bash
bash scripts/build-cpanel.sh yourdomain.com
```

Hasil:
- `kucatat-frontend.zip` — Next.js app (sudah di-build)
- `kucatat-backend.zip` — Laravel API
- `kucatat-backend.env` — template .env production

## Step 2: Setup Backend (Laravel)

### 2.1 Buat Subdomain

1. Login cPanel → **Domains** → **Create A New Domain**
2. Domain: `api.yourdomain.com`
3. Document Root: `api.yourdomain.com` (otomatis)

### 2.2 Upload & Extract

1. **File Manager** → masuk ke `~/api.yourdomain.com/`
2. Hapus semua file default
3. Upload `kucatat-backend.zip` → klik kanan → **Extract**
4. Pastikan file Laravel ada langsung di `~/api.yourdomain.com/` (bukan di subfolder)

### 2.3 Buat Database

1. cPanel → **MySQL Databases**
2. Buat database baru (contoh: `username_kucatat`)
3. Buat user baru → assign ke database → All Privileges

### 2.4 Konfigurasi via SSH

```bash
ssh username@yourdomain.com

cd ~/api.yourdomain.com

# Copy template .env dan edit
cp ~/kucatat-backend.env .env
nano .env
```

Edit `.env` — isi bagian database:

```
DB_DATABASE=username_kucatat
DB_USERNAME=username_dbuser
DB_PASSWORD=your_password_here
```

Lanjutkan setup:

```bash
# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Generate app key
php artisan key:generate

# Migrasi database
php artisan migrate --force

# Seed data awal (roles, chart of accounts, dll)
php artisan db:seed --force

# Optimasi production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link

# Set permissions
chmod -R 775 storage bootstrap/cache
```

### 2.5 Setup PHP Version

1. cPanel → **MultiPHP Manager**
2. Pilih `api.yourdomain.com` → set ke **PHP 8.2** atau **8.3**
3. cPanel → **Select PHP Version** untuk domain `api`
4. Aktifkan extensions: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `bcmath`, `fileinfo`, `gd`

### 2.6 Verifikasi

Buka `https://api.yourdomain.com/api/v1/auth/login` — harus return JSON response.

## Step 3: Setup Frontend (Next.js)

### 3.1 Upload & Extract

1. **File Manager** → masuk ke home directory (`~/`)
2. Buat folder `kucatat` → masuk ke dalamnya
3. Upload `kucatat-frontend.zip` → **Extract**

### 3.2 Setup Node.js App

1. cPanel → **Setup Node.js App** → **Create Application**
2. Isi:
   - **Node.js version**: 20.x (atau yang tersedia, min 20)
   - **Application mode**: Production
   - **Application root**: `kucatat`
   - **Application URL**: pilih domain utama (`yourdomain.com`)
   - **Application startup file**: `server.js`
3. Klik **Create**

### 3.3 Install Dependencies

1. Klik **Stop App**
2. Klik **Run NPM Install** (tunggu beberapa menit)
3. Klik **Start App**

### 3.4 Verifikasi

Buka `https://yourdomain.com` — halaman login Kucatat harus muncul.

## Step 4: CORS & API Connection

Pastikan `.env` di backend sudah benar:

```
APP_URL=https://api.yourdomain.com
FRONTEND_URL=https://yourdomain.com
SANCTUM_STATEFUL_DOMAINS=yourdomain.com
SESSION_DOMAIN=.yourdomain.com
```

Lalu re-cache:

```bash
cd ~/api.yourdomain.com
php artisan config:cache
```

## Login Default

| | |
|---|---|
| Email | admin@example.com |
| Password | password |

**Ganti password segera setelah deploy!**

## Troubleshooting

### 500 Error di API
```bash
cd ~/api.yourdomain.com
tail -50 storage/logs/laravel.log
chmod -R 775 storage bootstrap/cache
```

### Next.js Error / Blank Page
Di cPanel Node.js App, klik **Log** untuk lihat error.
Pastikan Node.js version ≥ 20.

### CORS Error
Cek `FRONTEND_URL` di `.env` backend harus persis sama dengan domain frontend (termasuk https).

```bash
cd ~/api.yourdomain.com
php artisan config:cache
```

### Login Gagal (401)
```
SANCTUM_STATEFUL_DOMAINS=yourdomain.com
SESSION_DOMAIN=.yourdomain.com
```
Note: `SESSION_DOMAIN` pakai dot prefix (`.yourdomain.com`) agar cookie berlaku untuk semua subdomain.

## Update / Redeploy

```bash
# Lokal: rebuild
bash scripts/build-cpanel.sh yourdomain.com

# Upload kucatat-frontend.zip ke ~/kucatat/ → extract (overwrite)
# Upload kucatat-backend.zip ke ~/api.yourdomain.com/ → extract (overwrite)

# SSH: update backend
cd ~/api.yourdomain.com
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart frontend
# cPanel → Node.js App → Restart
```
