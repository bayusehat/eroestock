# Deploy Kucatat ke cPanel Rumahweb

Panduan deploy Kucatat (Laravel monolith) ke cPanel Rumahweb.

## Persyaratan

| Kebutuhan | Minimum |
|-----------|---------|
| Paket | Cloud Hosting (RAM 4GB, 2vCPU) |
| PHP | 8.3+ |
| Node.js | 20+ (untuk build assets) |
| Database | MySQL 8+ |
| SSH Access | Wajib |

## Step 1: Build di Lokal

```bash
bash scripts/build-cpanel.sh yourdomain.com
```

Hasil:
- `kucatat.zip` — Laravel app (sudah di-build)
- `kucatat.env` — template .env production

## Step 2: Setup di cPanel

### 2.1 Upload & Extract

1. **File Manager** → masuk ke document root domain (`~/public_html/` atau sesuai domain)
2. Hapus semua file default
3. Upload `kucatat.zip` → klik kanan → **Extract**
4. Pastikan file Laravel ada langsung di document root (bukan di subfolder)

### 2.2 Buat Database

1. cPanel → **MySQL Databases**
2. Buat database baru (contoh: `username_kucatat`)
3. Buat user baru → assign ke database → All Privileges

### 2.3 Konfigurasi via SSH

```bash
ssh username@yourdomain.com

cd ~/public_html

# Copy template .env dan edit
cp ~/kucatat.env .env
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
composer install --no-dev --optimize-autoloader

php artisan key:generate
php artisan migrate --force
php artisan db:seed --force

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link

chmod -R 775 storage bootstrap/cache
```

### 2.4 Setup PHP Version

1. cPanel → **MultiPHP Manager**
2. Pilih domain → set ke **PHP 8.3**
3. cPanel → **Select PHP Version**
4. Aktifkan extensions: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `bcmath`, `fileinfo`, `gd`

### 2.5 Verifikasi

Buka `https://yourdomain.com` — halaman login Kucatat harus muncul.

## Login Default

| | |
|---|---|
| Email | admin@example.com |
| Password | password |

**Ganti password segera setelah deploy!**

## Troubleshooting

### 500 Error
```bash
cd ~/public_html
tail -50 storage/logs/laravel.log
chmod -R 775 storage bootstrap/cache
```

### CORS Error (API)
Cek `APP_URL` di `.env` harus persis sama dengan domain (termasuk https).

```bash
php artisan config:cache
```

## Update / Redeploy

```bash
# Lokal: rebuild
bash scripts/build-cpanel.sh yourdomain.com

# Upload kucatat.zip ke document root → extract (overwrite)

# SSH: update
cd ~/public_html
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
