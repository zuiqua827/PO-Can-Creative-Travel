# CAN Travel — Production Operational Runbook

Dokumen ini merupakan panduan operasional teknis resmi (*operational runbook*) untuk sistem **CAN Travel** pada lingkungan *production*. Panduan ini dirancang untuk tim DevOps, SysAdmin, dan Backend Engineer dalam mengelola *deployment*, pemeliharaan (*maintenance*), pemulihan bencana (*disaster recovery*), serta penanganan insiden operasional.

---

## 1. Production Deployment Procedure

Deployment aplikasi CAN Travel dirancang agar aman, *zero-downtime*, dan dapat diulang (*reproducible*).

### Alur Rilis Produksi (Standard Deployment Flow)

```bash
# 1. Masuk ke direktori aplikasi
cd /var/www/cantravel

# 2. Aktifkan Maintenance Mode dengan secret bypass (opsional, disarankan jika ada migrasi besar)
php artisan down --secret="can-travel-ops-bypass-2026" --render="errors::503"

# 3. Tarik versi terbaru dari repositori branch main/production
git fetch origin
git checkout main
git pull origin main

# 4. Pasang dependensi PHP tanpa dev-dependencies
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# 5. Pasang dependensi frontend dan bangun aset produksi
npm ci
npm run build

# 6. Jalankan migrasi database (hanya migrasi aditif/non-destruktif)
php artisan migrate --force

# 7. Bersihkan dan kompilasi ulang seluruh cache Laravel
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 8. Restart queue worker agar memuat kode aplikasi terbaru
php artisan queue:restart

# 9. Nonaktifkan Maintenance Mode
php artisan up
```

---

## 2. Database Migration Rules & Safety

### Prinsip Utama Database CAN Travel
1. **DILARANG KERAS** menjalankan perintah destruktif:
   - `php artisan migrate:fresh`
   - `php artisan migrate:reset`
   - `php artisan db:wipe`
   - Menghapus tabel atau kolom produksi yang sedang digunakan.
2. Seluruh migrasi harus bersifat **additive** (hanya menambah tabel, kolom nullable, atau indeks baru).
3. Sebelum menjalankan migrasi pada server produksi, selalu verifikasi status:
   ```bash
   php artisan migrate:status
   ```
4. Jika migrasi gagal, periksa pesan galat pada log `storage/logs/laravel.log`. Jika perlu rollback 1 langkah:
   ```bash
   php artisan migrate:rollback --step=1 --force
   ```

---

## 3. Storage & Symlink Maintenance

Aplikasi CAN Travel menyimpan aset unggahan bukti pembayaran dan manifes pada storage disk `public` (`storage/app/public`).

1. **Pastikan Symlink Aktif**:
   ```bash
   php artisan storage:link
   ```
2. **Izin Hak Akses Direktori (Linux / Nginx)**:
   ```bash
   sudo chown -R www-data:www-data /var/www/cantravel/storage /var/www/cantravel/bootstrap/cache
   sudo chmod -R 775 /var/www/cantravel/storage /var/www/cantravel/bootstrap/cache
   ```
3. **Penyimpanan Bukti Bayar**:
   - Bukti bayar tersimpan di `storage/app/public/payment_proofs/`.
   - Nama file dienkripsi acak (`hash.ext`) demi mencegah eksekusi skrip jahat dan path traversal.

---

## 4. Queue Worker & Supervisor Configuration

Notifikasi pemesanan (`BookingCreatedNotification`, `PaymentReceivedNotification`, `BookingCancelledNotification`) dan tugas asinkron berjalan via Laravel Queue.

### Konfigurasi Supervisor (`/etc/supervisor/conf.d/cantravel-worker.conf`)

```ini
[program:cantravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/cantravel/artisan queue:work --sleep=3 --tries=3 --backoff=5,15,60 --timeout=90 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/cantravel/storage/logs/worker.log
stopwaitsecs=3600
```

### Manajemen Supervisor
```bash
# Reload konfigurasi baru
sudo supervisorctl reread
sudo supervisorctl update

# Status worker
sudo supervisorctl status cantravel-worker:*

# Restart worker setelah deploy
sudo supervisorctl restart cantravel-worker:*
```

### Penanganan Antrean Gagal (Failed Jobs)
```bash
# Cek daftar job yang gagal
php artisan queue:failed

# Retry seluruh failed jobs setelah kendala upstream terselesaikan
php artisan queue:retry all

# Hapus job gagal tertentu jika tidak relevan
php artisan queue:forget <id>
```

---

## 5. Scheduler & Crontab Setup

Sistem CAN Travel memerlukan scheduler untuk menjalankan:
1. `orders:expire` — Membatalkan pesanan kedaluwarsa & melepaskan alokasi kursi (setiap menit).
2. `payments:reconcile --hours=24` — Rekonsiliasi berkala terhadap status Midtrans (setiap 15 menit).

### Konfigurasi Crontab Sistem (`crontab -e -u www-data`)

```crontab
* * * * * cd /var/www/cantravel && php artisan schedule:run >> /dev/null 2>&1
```

Kedua scheduled tasks dilindungi oleh `withoutOverlapping()` untuk mencegah *race condition* dan eksekusi duplikat saat antrean sedang sibuk.

---

## 6. Payment Gateway & Real Midtrans Operations

CAN Travel mendukung dua driver pembayaran:
- `fake` — Mode simulasi lokal/sandbox internal (default untuk testing & offline demo).
- `midtrans` — Driver integrasi gateway resmi Midtrans Snap & Core API.

### Konfigurasi `.env` untuk Aktivasi Midtrans Nyata
```env
PAYMENT_DRIVER=midtrans
PAYMENT_CURRENCY=IDR
PAYMENT_EXPIRY_MINUTES=120
MIDTRANS_IS_PRODUCTION=true
MIDTRANS_SERVER_KEY="Mid-server-xxxxxxxxxxxxxxxxx"
MIDTRANS_CLIENT_KEY="Mid-client-xxxxxxxxxxxxxxxxx"
MIDTRANS_SNAP_URL="https://app.midtrans.com/snap/v1/transactions"
MIDTRANS_API_BASE_URL="https://api.midtrans.com"
```

> **PERINGATAN**: Jangan pernah commit kunci rahasia (*Server Key*) ke dalam Git. Kunci harus dimasukkan langsung ke file `.env` server produksi.

### Konfigurasi Webhook di Midtrans Merchant Portal
1. Buka dashboard Midtrans: **Settings > Configuration > Payment Notification URL**.
2. Masukkan URL: `https://cantravel.co.id/payments/webhook`.
3. Metode: `POST`.
4. Protokol: Wajib `HTTPS` dengan sertifikat SSL valid.

---

## 7. Payment Reconciliation Strategy

Untuk mengatasi webhook Midtrans yang terlambat, gagal terkirim, atau kegagalan koneksi saat pelanggan membayar:

### Perintah Rekonsiliasi Otomatis & Manual
```bash
# Jalankan simulasi rekonsiliasi tanpa mengubah database (Dry Run)
php artisan payments:reconcile --hours=48 --dry-run

# Jalankan sinkronisasi nyata untuk 48 jam terakhir
php artisan payments:reconcile --hours=48

# Output contoh:
# ----------------------------------------
# CAN Travel — Hasil Rekonsiliasi Pembayaran
# ----------------------------------------
# Total Pesanan Diperiksa : 25
# Berhasil Disinkronkan   : 3
# Sudah Konsisten         : 21
# Gagal / Error          : 1
# Mode                    : Real Synchronization
# Waktu Eksekusi         : 0.84 detik
# ----------------------------------------
```

Rekonsiliasi menggunakan `lockForUpdate()` dan database transaction, sehingga aman dijalankan bersamaan dengan webhook tanpa risiko *double credit* atau *over-confirmation*.

---

## 8. Health Check Probe & Monitoring

Endpoint kesehatan publik tersedia di:
`GET /health`

### Spesifikasi Respons
- **Status HTTP**: `200 OK` jika sehat; `503 Service Unavailable` jika dependensi inti down.
- **Rate Limit**: 60 request / menit per IP.
- **Keamanan**: Tidak membocorkan nama server, path filesystem, ataupun kredensial database.

Contoh payload respons:
```json
{
  "status": "ok",
  "app": "CAN Travel",
  "timestamp": "2026-09-23T12:00:00+00:00",
  "checks": {
    "database": "ok",
    "cache": "ok",
    "storage": "ok"
  }
}
```

Dapat diintegrasikan langsung dengan uptime monitoring tools seperti **UptimeRobot**, **Pingdom**, **Better Uptime**, atau **Kubernetes Liveness Probe**.

---

## 9. Backup & Disaster Recovery Strategy

### 1. Backup Database MySQL (Harian)
```bash
#!/usr/bin/env bash
BACKUP_DIR="/var/backups/cantravel/mysql"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
FILENAME="$BACKUP_DIR/cantravel_db_$TIMESTAMP.sql.gz"

mkdir -p "$BACKUP_DIR"

# Dump database dengan transactional consistency
mysqldump -u cantravel_user -p'SECRET_DB_PASS' \
    --single-transaction \
    --quick \
    --routines \
    --triggers \
    cantravel_prod | gzip -9 > "$FILENAME"

# Hapus backup yang lebih tua dari 14 hari
find "$BACKUP_DIR" -type f -name "*.sql.gz" -mtime +14 -delete
```

### 2. Backup Aset Unggahan (`storage/app/public`)
```bash
tar -czf /var/backups/cantravel/storage_public_$TIMESTAMP.tar.gz -C /var/www/cantravel/storage/app public
```

### 3. Prosedur Restore Database
```bash
# 1. Masuk ke maintenance mode
php artisan down

# 2. Ekstrak dan impor file SQL
gunzip < /var/backups/cantravel/mysql/cantravel_db_YYYYMMDD_HHMMSS.sql.gz | mysql -u cantravel_user -p cantravel_prod

# 3. Jalankan migrasi tambahan jika backup lebih lama
php artisan migrate --force

# 4. Bersihkan cache
php artisan optimize:clear
php artisan config:cache

# 5. Buka kembali aplikasi
php artisan up
```

---

## 10. Incident Response & Troubleshooting

### Skenario 1: Webhook Midtrans Gagal Diterima Pelanggan
1. Periksa log webhook:
   ```bash
   grep "Payment webhook" storage/logs/payments-*.log | tail -n 50
   ```
2. Cari pesanan berdasarkan kode pesanan di Admin Portal: `/admin/orders`.
3. Jalankan rekonsiliasi langsung melalui Artisan:
   ```bash
   php artisan payments:reconcile --hours=6
   ```

### Skenario 2: Database Mengalami Lock / Antrean Menumpuk
1. Periksa processlist MySQL:
   ```sql
   SHOW FULL PROCESSLIST;
   ```
2. Periksa apakah ada lock transaksi yang menggantung:
   ```sql
   SELECT * FROM information_schema.innodb_trx;
   ```
3. Restart queue worker untuk membersihkan koneksi yang terhambat:
   ```bash
   sudo supervisorctl restart cantravel-worker:*
   ```

### Skenario 3: Disk Server Penuh
1. Periksa penggunaan disk: `df -h`.
2. Hapus log lama yang telah terotasi:
   ```bash
   find /var/www/cantravel/storage/logs -name "*.log" -mtime +30 -delete
   ```
3. Bersihkan view cache yang lama:
   ```bash
   php artisan view:clear
   ```

---

## 11. Rollback Procedure

Jika versi rilis baru menimbulkan galat kritis di produksi:

```bash
# 1. Aktifkan maintenance mode
php artisan down

# 2. Kembalikan kode ke commit/tag sebelumnya
git checkout <previous_stable_commit_or_tag>

# 3. Pasang ulang dependensi versi stabil tersebut
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# 4. Rollback migrasi jika commit baru menyertakan migrasi yang bermasalah
php artisan migrate:rollback --step=1 --force

# 5. Segarkan cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Restart queue worker
php artisan queue:restart

# 7. Buka kembali aplikasi
php artisan up
```

---

## 12. Security Operations Checklist

- [ ] `APP_DEBUG=false` dipastikan aktif di file `.env`.
- [ ] `APP_KEY` terkonfigurasi dengan enkripsi 256-bit valid.
- [ ] Seluruh endpoint sensitif (`/login`, `/register`, `/booking`, `/payments/webhook`, `/tickets/verify`) terlindungi oleh rate limiting.
- [ ] Header keamanan produksi aktif: `X-Frame-Options`, `X-Content-Type-Options`, `Content-Security-Policy`, `Referrer-Policy`, `X-Request-ID`.
- [ ] Kredensial Midtrans Server Key dan Database Password tidak pernah dikomit ke repositori publik.
- [ ] Akses portal `/admin` hanya dapat diakses oleh akun bertipe `role = admin`.
- [ ] Verifikasi tiket menggunakan token UUID acak 32 karakter tahan enumerasi (*anti-enumeration*).
- [ ] File unggahan bukti bayar hanya mengizinkan MIME type: `jpg`, `jpeg`, `png`, `pdf` dengan batas maksimal 2MB.
- [ ] Audit log mencatat login admin, perubahan status pesanan, ekspor CSV, dan sinkronisasi pembayaran.

---

## 13. FINAL PRODUCTION RELEASE CHECKLIST

Daftar periksa verifikasi akhir (*Final Release Quality Gate*) sebelum meluncurkan CAN Travel ke publik:

### A. Pre-Flight Configuration
- [ ] **Environment**: `APP_ENV=production` dan `APP_DEBUG=false` pada `.env`.
- [ ] **Application Key**: `APP_KEY` terkonfigurasi dengan valid (AES-256-CBC).
- [ ] **Base URL**: `APP_URL=https://cantravel.co.id` menggunakan HTTPS aktif.
- [ ] **No Secrets in Repo**: File `.env` tidak ter-track oleh Git (`.gitignore` diverifikasi).
- [ ] **Brand Consistency**: Seluruh halaman publik memuat identitas **CAN Travel**. Bebas dari string warisan (`PO CAN` = 0, `PCT-` = 0).

### B. Database & Schema
- [ ] **Database Engine**: MySQL 8.0+ / MariaDB 10.4+ dengan InnoDB engine dan UTF-8 multibyte (`utf8mb4`).
- [ ] **Migration Status**: Seluruh 15 migrasi tercatat status `[Ran]`.
- [ ] **Performance Indexes**: Indeks komposit aktif:
  - `idx_orders_status_payment_expires` pada `orders(status, payment_status, expires_at)` (optimalisasi scheduler 1 menit).
  - `idx_orders_trip_status_payment` pada `orders(trip_id, status, payment_status)`.
  - `idx_trips_departure_status` pada `trips(departure_at, status)`.
  - `idx_routes_origin_dest_status` pada `routes(origin, destination, status)`.
  - `idx_payments_ref_order` pada `payments(payment_reference, order_id)`.
- [ ] **Zero Destructive Queries**: Tidak pernah menjalankan `migrate:fresh` atau `db:wipe` di server produksi.

### C. UX, Accessibility & Error Handling
- [ ] **Double Submit Prevention**: Tombol submit checkout dan konfirmasi pembayaran dilindungi oleh Alpine.js `submitting` state dengan visual loading spinner dan penonaktifan tombol klik berulang.
- [ ] **WCAG 2.1 AA Accessibility**: Pemilihan kursi interaktif menyertakan live announcer screen reader via `aria-live="polite"` dan atribut `aria-label`/`aria-pressed`.
- [ ] **Production Error Pages**: Seluruh halaman kesalahan HTTP (`403`, `404`, `419`, `422`, `429`, `500`, `503`) menggunakan tata letak bermerek CAN Travel tanpa membocorkan trace SQL atau path direktori internal.
- [ ] **Sensitive Field Scrubbing**: Exception handler `$dontFlash` membersihkan parameter rahasia (`password`, `token`, `server_key`, `client_key`, `secret`, `signature`, `cvv`, `card_number`).

### D. Queue, Scheduler & Operations
- [ ] **Queue Supervisor**: Supervisor mengelola minimal 2 worker `cantravel-worker` untuk notifikasi asinkron dan email e-tiket.
- [ ] **Cron Scheduler**: Cron OS menjalankan `* * * * * cd /var/www/cantravel && php artisan schedule:run >> /dev/null 2>&1`.
  - `orders:expire` berjalan setiap menit dengan `withoutOverlapping()`.
  - `payments:reconcile` berjalan setiap 15 menit dengan `withoutOverlapping()`.
- [ ] **Health Monitoring**: Probe `GET /health` mengembalikan HTTP 200 dan JSON `status: "ok"` tanpa mengekspos kredensial.
- [ ] **Audit Trail**: Seluruh tindakan administratif (hapus armada/rute/jadwal, update pesanan, ekspor CSV, sweep kedaluwarsa) terekam di tabel `audit_logs`.

---

## 14. Midtrans Production Activation Step-by-Step Guide

Secara bawaan sistem CAN Travel berjalan dalam mode aman (*Safe Simulation / Sandbox Adapter*). Ikuti panduan manual berikut saat beralih ke transaksi nyata Midtrans:

### Langkah 1: Pendaftaran Akun Midtrans Production
1. Akses [Midtrans Merchant Administration Portal (MAP)](https://dashboard.midtrans.com).
2. Selesaikan proses verifikasi KYC dan aktivasi merchant bisnis CAN Travel hingga disetujui (*Live Mode Approval*).

### Langkah 2: Dapatkan Kredensial Produksi
1. Masuk ke **Settings** > **Access Keys**.
2. Pastikan toggle di sudut kiri atas berada pada posisi **Production** (bukan *Sandbox*).
3. Salin:
   - **Merchant ID**: Misal `G123456789`
   - **Client Key**: Misal `Mid-client-xxxxxxxxxxxxxxxx`
   - **Server Key**: Misal `Mid-server-xxxxxxxxxxxxxxxx`

### Langkah 3: Konfigurasi Webhook URL di Portal Midtrans
1. Masuk ke **Settings** > **Configuration**.
2. Pada field **Payment Notification URL**, isi URL publik HTTPS aplikasi CAN Travel:
   ```
   https://cantravel.co.id/payments/webhook
   ```
3. Set **Finish Redirect URL**:
   ```
   https://cantravel.co.id/my-orders
   ```
4. Set **Unfinish Redirect URL**:
   ```
   https://cantravel.co.id/my-orders
   ```
5. Set **Error Redirect URL**:
   ```
   https://cantravel.co.id/my-orders
   ```
6. Simpan perubahan konfigurasi (*Save Changes*).

### Langkah 4: Pembaruan Environment Server Produksi (`.env`)
Perbarui file `.env` pada server produksi (JANGAN commit ke repositori Git):
```ini
# Pengalihan Driver Pembayaran
PAYMENT_DRIVER=midtrans
PAYMENT_CURRENCY=IDR
PAYMENT_EXPIRY_MINUTES=120

# Kredensial Resmi Midtrans Produksi
MIDTRANS_SERVER_KEY=Mid-server-YOUR_ACTUAL_PRODUCTION_SERVER_KEY
MIDTRANS_CLIENT_KEY=Mid-client-YOUR_ACTUAL_PRODUCTION_CLIENT_KEY
MIDTRANS_MERCHANT_ID=G_YOUR_PRODUCTION_MERCHANT_ID
MIDTRANS_IS_PRODUCTION=true
MIDTRANS_SNAP_URL="https://app.midtrans.com/snap/v1/transactions"
MIDTRANS_API_BASE_URL="https://api.midtrans.com"
```

### Langkah 5: Muat Ulang Cache Konfigurasi
Jalankan di server produksi:
```bash
php artisan config:clear
php artisan config:cache
php artisan queue:restart
```

### Langkah 6: Verifikasi Transaksi Perdana
1. Lakukan pemesanan 1 kursi uji coba di portal CAN Travel.
2. Selesaikan pembayaran menggunakan kanal riil (QRIS / Virtual Account) bernilai kecil.
3. Pastikan webhook Midtrans diterima (`storage/logs/laravel.log` channel `payments` mencatat status `settlement`).
4. Pastikan pesanan otomatis beralih ke `confirmed` dan e-tiket resmi diterbitkan dengan QR code valid.
