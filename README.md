# CAN Travel — Commercial-Grade Bus Ticket Booking Platform

**CAN Travel** adalah platform pemesanan tiket bus antarkota modern, andal, aman, dan berstandar *deployment-ready commercial grade* yang dibangun dengan **Laravel 10**, **PHP 8.4+**, **MySQL**, **Blade**, **Tailwind CSS 3**, dan **Vite**.

Platform ini mengintegrasikan siklus hidup reservasi kursi *server-authoritative*, arsitektur pembayaran modular dengan abstraksi gateway (simulasi dan Midtrans-ready), *webhook asynchronous* yang terverifikasi dan idempoten, otomatisasi kedaluwarsa pesanan via Laravel Scheduler, proteksi keamanan tingkat tinggi (Security Headers, Rate Limiting, CSP tanpa `unsafe-eval`, IDOR safeguards, validasi unggahan bukti bayar), dashboard analitik operasional dengan tren 7 hari real-time, antrean notifikasi asynchronous (*queue-ready*), serta *boarding pass* E-Tiket berbasis QR code verifikasi real-time dengan token anti-enumerasi.

---

## 1. System Requirements

- **PHP**: >= 8.3 / 8.4 (Ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `curl`, `fileinfo`)
- **Web Server**: Nginx atau Apache
- **Database Engine**: MySQL 8.0+ / MariaDB 10.4+ (InnoDB Engine dengan Relational Foreign Keys & Indeks Komposit)
- **Node.js**: >= 18.x & NPM >= 9.x
- **Composer**: >= 2.5
- **Process Manager**: Supervisor / Systemd (untuk Queue Worker dan Scheduler)

---

## 2. Installation Guide

1. **Clone Repositori**:
   ```bash
   git clone https://github.com/zuiqua827/PO-Can-Creative-Travel.git
   cd PO-Can-Creative-Travel
   ```

2. **Pasang Dependensi PHP**:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. **Pasang Dependensi Frontend & Build Aset**:
   ```bash
   npm ci
   npm run build
   ```

4. **Konfigurasi Lingkungan (.env)**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Hubungkan Storage Symlink**:
   ```bash
   php artisan storage:link
   ```

---

## 3. Environment Configuration (.env)

Contoh konfigurasi standar untuk production:

```env
APP_NAME="CAN Travel"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://cantravel.co.id

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=info

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=can_travel_prod
DB_USERNAME=can_travel_user
DB_PASSWORD=your_strong_db_password

BROADCAST_DRIVER=log
CACHE_STORE=file
QUEUE_CONNECTION=database
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your_smtp_username
MAIL_PASSWORD=your_smtp_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@cantravel.co.id"
MAIL_FROM_NAME="CAN Travel"

# Payment Architecture
PAYMENT_DRIVER=midtrans
PAYMENT_CURRENCY=IDR
PAYMENT_EXPIRY_MINUTES=120
PAYMENT_WEBHOOK_SECRET=your_secure_hmac_webhook_secret

# Midtrans Production Gateway
MIDTRANS_SERVER_KEY=Mid-server-xxxxxxxxx
MIDTRANS_CLIENT_KEY=Mid-client-xxxxxxxxx
MIDTRANS_MERCHANT_ID=Gxxxxxxxxx
MIDTRANS_IS_PRODUCTION=true
```

---

## 4. Database Setup, Migration & Seeding

1. **Jalankan Migrasi Database**:
   ```bash
   php artisan migrate --force
   ```

2. **Jalankan Database Seeder (Inisialisasi Data Armada, Rute & Akun Admin)**:
   ```bash
   php artisan db:seed --force
   ```

Data awal yang dibuat secara otomatis meliputi:
- **Akun Administrator**: `admin@pocan.com` / kata sandi: `password`
- **Armada Bus**: Kelas Executive, Royal Suite, Sleeper Bus, VIP dengan kapasitas 20-30 kursi
- **Rute Perjalanan Antarkota**: Jakarta — Bandung, Jakarta — Yogyakarta, Jakarta — Surabaya, dsb.

---

## 5. Scheduler Setup (Otomatisasi Kedaluwarsa Pesanan)

CAN Travel menjalankan perintah `orders:expire` setiap menit untuk membatalkan pesanan yang melampaui tenggat waktu 120 menit dan melepaskan alokasi kursi kembali ke inventori.

Tambahkan entri cron berikut pada server Linux:

```bash
* * * * * cd /path-to-project/PO-Can-Creative-Travel && php artisan schedule:run >> /dev/null 2>&1
```

Perintah yang dieksekusi secara otomatis:
- `orders:expire`: Membatalkan pesanan unpaid yang kadaluwarsa, menandai payment expired, dan merilis kursi.

---

## 6. Queue Worker Setup (Supervisor)

Semua notifikasi email transaksi (`BookingCreatedNotification`, `PaymentReceivedNotification`, `BookingCancelledNotification`, `BookingExpiredNotification`) mengimplementasikan `ShouldQueue` agar transaksi booking dan webhook tidak bergantung pada latensi jaringan mail server.

Konfigurasi Supervisor (`/etc/supervisor/conf.d/can-travel-worker.conf`):

```ini
[program:can-travel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path-to-project/PO-Can-Creative-Travel/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path-to-project/PO-Can-Creative-Travel/storage/logs/worker.log
stopwaitsecs=3600
```

Aktifkan worker:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start can-travel-worker:*
```

---

## 7. Payment Gateway & Webhook Architecture

CAN Travel mengadopsi pola *Gateway Driver Abstraction*:
```
PaymentGatewayInterface
       ├── FakePaymentGateway (Simulasi Lokal & Testing Suite)
       └── MidtransPaymentGateway (Snap API, Status API & Webhook Verification)
```

### Konfigurasi Driver (`config/payment.php`):
- `PAYMENT_DRIVER=fake`: Default untuk lokal/testing. Mensimulasikan charge instan dan verifikasi webhook via HMAC-SHA256.
- `PAYMENT_DRIVER=midtrans`: Menggunakan Midtrans Snap API. Jika server key belum diisi di `.env`, adapter secara anggun berjalan dalam mode *sandbox simulation* tanpa merusak antarmuka.

### Keamanan Webhook (`/payments/webhook`):
1. **Verifikasi Tanda Tangan**: Midtrans SHA-512 `hash('sha512', order_id + status_code + gross_amount + server_key)` atau header `X-CAN-Signature` HMAC-SHA256.
2. **Perlindungan Replay & Idempotensi**: Transaksi yang telah diproses (`webhook_processed_at !== null` atau status `success`) langsung mengembalikan respons `200 OK` tanpa mutasi berulang.
3. **Validasi Nominal & Mata Uang**: Webhook menolak pembayaran dengan selisih nilai (`gross_amount != amount`) atau mata uang selain IDR dengan kode `422 Unprocessable Entity`.
4. **Validasi State Machine**: Transaksi ditolak jika pesanan sudah berstatus `cancelled` atau `expired`.

---

## 8. State Machine Matrix

Siklus hidup status pesanan dan pembayaran dikendalikan secara ketat pada model `Order`:

| Status Awal | Event | Status Akhir | Status Pembayaran |
|---|---|---|---|
| `pending` + `unpaid` | Pembayaran diverifikasi | `confirmed` | `paid` |
| `pending` + `unpaid` | Waktu habis (120 mnt) | `cancelled` | `expired` |
| `pending` + `unpaid` | Dibatalkan pelanggan | `cancelled` | `expired` |
| `confirmed` + `paid` | Perjalanan selesai | `completed` | `paid` |
| `confirmed` + `paid` | Pembatalan resmi | `cancelled` | `refunded` |

**Transisi yang dilarang keras (*forbidden*):**
- `cancelled` → `paid` (Ditolak)
- `expired` → `paid` (Ditolak)
- `completed` → `pending` (Ditolak)
- `refunded` → `paid` (Ditolak)

---

## 9. Observability & Logging Channels

Aplikasi mengarahkan log ke saluran (*dedicated channels*) terpisah di `storage/logs/`:
- `storage/logs/payments.log`: Log transaksi, charge, webhook status, dan kegagalan gateway.
- `storage/logs/security.log`: Percobaan login gagal, penolakan tanda tangan webhook, pelanggaran IDOR, upaya manipulasi nominal.
- `storage/logs/booking.log`: Pembuatan reservasi, pelepasan kursi, dan audit tiket.
- `storage/logs/laravel.log`: Log aplikasi umum dan exception handler.

*Kerahasiaan data terjamin: Kata sandi, secret key, nomor CVV, dan data pribadi sensitif tidak pernah dicatat ke dalam berkas log.*

---

## 10. Keamanan Sistem & Security Headers

Middleware `SecurityHeaders` diterapkan pada semua rute web:
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: SAMEORIGIN`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: camera=(), microphone=(), geolocation=()`
- `Content-Security-Policy`:
  `default-src 'self'; script-src 'self' 'unsafe-inline' https:; style-src 'self' 'unsafe-inline' https:; font-src 'self' data: https:; img-src 'self' data: https: blob:; connect-src 'self' ws: wss: https:; frame-ancestors 'self';`
  *(Bebas dari `unsafe-eval` demi mencegah injeksi skrip dinamis).*

Validasi Unggahan Bukti Bayar:
- Format berkas: `jpg`, `jpeg`, `png`, `webp`, `pdf`
- Batas ukuran: Maksimal 2048 KB (2 MB)
- Penyimpanan: Penamaan acak SHA-1 40 karakter di disk `public` untuk mencegah *path traversal* atau eksekusi *web shell*.

---

## 11. Production Optimization Commands

Sebelum aplikasi melayani trafik produksi, jalankan perintah caching berikut:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

Jika melakukan pembaruan rute atau konfigurasi:
```bash
php artisan optimize:clear
```

---

## 12. Backup & Disaster Recovery Strategy

### Komponen yang Wajib Dibackup:
1. **Basis Data MySQL**: Seluruh tabel transaksi, kursi, rute, dan pengguna.
2. **Berkas Storage**: Direktori `storage/app/public/payment_proofs/`.
3. **Berkas Konfigurasi**: Berkas `.env` (disimpan pada brankas kunci terenkripsi / secret manager).

### Prosedur Pencadangan (Backup):
- **Otomatis Harian** (Database):
  ```bash
  mysqldump -u can_travel_user -p can_travel_prod | gzip > /backups/db_cantravel_$(date +\%F).sql.gz
  ```
- **Pencadangan Storage**:
  ```bash
  tar -czf /backups/storage_cantravel_$(date +\%F).tar.gz -C /path-to-project/storage/app/public .
  ```

### Prosedur Pemulihan (Disaster Recovery):
1. Siapkan database baru dan impor berkas SQL:
   ```bash
   gunzip < /backups/db_cantravel_YYYY-MM-DD.sql.gz | mysql -u can_travel_user -p can_travel_prod
   ```
2. Ekstrak kembali berkas storage:
   ```bash
   tar -xzf /backups/storage_cantravel_YYYY-MM-DD.tar.gz -C /path-to-project/storage/app/public/
   ```
3. Sinkronkan symlink storage dan bersihkan cache:
   ```bash
   php artisan storage:link
   php artisan optimize:clear
   php artisan config:cache
   ```

---

## 13. Deployment Checklist

- [ ] Repository ditarik dari tag / rilis stabil `main`.
- [ ] `composer install --no-dev --optimize-autoloader` berhasil dijalankan.
- [ ] `npm ci && npm run build` menghasilkan bundle aset teroptimasi.
- [ ] File `.env` memiliki `APP_ENV=production` dan `APP_DEBUG=false`.
- [ ] Kunci enkripsi aplikasi telah terpasang (`APP_KEY`).
- [ ] `php artisan migrate --force` berhasil tanpa error.
- [ ] `php artisan storage:link` aktif.
- [ ] Worker Supervisor aktif dan memproses antrean `database`.
- [ ] Crontab `schedule:run` terpasang setiap menit.
- [ ] `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache` aktif.
- [ ] Log channel `payments.log`, `security.log`, `booking.log` dapat ditulis (`chmod 775`).

---

## 14. Rollback Strategy

Jika deployment rilis baru menemui kendala:

1. **Aktifkan Maintenance Mode**:
   ```bash
   php artisan down --render="errors::503" --secret="cantravel-recovery"
   ```
2. **Kembalikan Kode ke Tag/Komit Sebelumnya**:
   ```bash
   git checkout <previous-stable-tag>
   ```
3. **Rebuild Aset & Install Dependensi Sesuai Versi Lama**:
   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   ```
4. **Rollback Migrasi (Jika Ada Perubahan Skema Baru)**:
   ```bash
   php artisan migrate:rollback --step=1 --force
   ```
5. **Bersihkan dan Segarkan Cache**:
   ```bash
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
6. **Nonaktifkan Maintenance Mode**:
   ```bash
   php artisan up
   ```

---

## 15. Automated Testing & Quality Assurance

Jalankan seluruh test suite otomatis:

```bash
php artisan test
```

Hasil verifikasi (Sprint 6 Production Ready):
- **Total Tests**: 85 Feature & Unit Tests (100% Passed)
- **Total Assertions**: 289 Assertions
- **Failures / Errors**: 0

Pemeriksaan gaya kode sesuai standar PSR-12:
```bash
vendor/bin/pint --test
```

Kompilasi aset frontend produksi:
```bash
npm run build
```

---

## 16. Troubleshooting Common Issues

1. **Error 419 (Page Expired)**:
   - Pastikan direktori `storage/framework/sessions` memiliki izin tulis (`chmod -R 775 storage`).
   - Periksa konfigurasi `SESSION_DOMAIN` di `.env`.

2. **Notifikasi Email Gagal Dikirim**:
   - Periksa log antrean worker di `storage/logs/worker.log`.
   - Jalankan `php artisan queue:failed` untuk melihat job yang tertunda.
   - Jalankan `php artisan queue:retry all` setelah memperbaiki kredensial SMTP.

3. **QR Code Tiket Tidak Muncul**:
   - CAN Travel menggunakan Native Inline SVG QR Renderer. Tidak memerlukan ekstensi eksternal atau koneksi API pihak ketiga. Cukup pastikan browser mendukung SVG.

---

# Production Deployment

Panduan operasional dan kesiapan peluncuran produksi (*production launch*) untuk aplikasi CAN Travel. Dokumentasi mendalam tersedia di [`docs/production-runbook.md`](file:///c:/laragon/www/PO-CAN_Creative_Travel/docs/production-runbook.md).

## Environment
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://cantravel.co.id`
- `APP_KEY` digenerate via `php artisan key:generate` (256-bit AES)
- Pastikan tidak ada kredensial asli yang ter-commit ke Git (`.env` masuk dalam `.gitignore`).

## Database
- MySQL 8.0+ / MariaDB 10.4+ dengan InnoDB Engine.
- Aturan ketat: **DILARANG** melakukan `migrate:fresh`, `db:wipe`, atau `TRUNCATE` pada production.
- Jalankan migrasi aditif: `php artisan migrate --force`.
- Cek status migrasi sebelum dan sesudah rilis: `php artisan migrate:status`.

## Storage
- Hubungkan symlink publik: `php artisan storage:link`.
- Pastikan hak akses folder `storage/` dan `bootstrap/cache/` adalah `775` dengan kepemilikan `www-data:www-data`.
- Bukti transfer disimpan di disk `public` dengan enkripsi nama file dan validasi tipe berkas ketat.

## Queue Worker
- Gunakan Supervisor atau Systemd di server produksi.
- Perintah worker:
  ```bash
  php /var/www/cantravel/artisan queue:work --sleep=3 --tries=3 --backoff=5,15,60 --timeout=90
  ```
- Restart antrean setelah setiap deployment: `php artisan queue:restart`.
- Monitoring job gagal: `php artisan queue:failed` dan recovery: `php artisan queue:retry all`.

## Scheduler
- Tambahkan entri tunggal crontab server:
  ```crontab
  * * * * * cd /var/www/cantravel && php artisan schedule:run >> /dev/null 2>&1
  ```
- Task terjadwal meliputi:
  - `orders:expire` (setiap menit, dilindungi `withoutOverlapping()`).
  - `payments:reconcile --hours=24` (setiap 15 menit, dilindungi `withoutOverlapping()`).

## Payment Gateway
- Abstraksi pembayaran mendukung dua driver:
  - `fake` (simulasi sandbox internal untuk testing/demo).
  - `midtrans` (integrasi resmi Midtrans Snap & Core API).
- Nominal pembayaran 100% *server-authoritative*, dihitung dari harga rute dan kursi pada basis data server.

## Midtrans Webhook
- Endpoint webhook: `POST https://cantravel.co.id/payments/webhook`.
- Divalidasi dengan signature SHA-512 resmi Midtrans (`order_id + status_code + gross_amount + server_key`).
- Dilindungi proteksi idempoten (*idempotency*), anti-replay, dan pencegahan perubahan status dari pesanan yang telah dibatalkan atau kedaluwarsa.

## Mail
- Konfigurasi `MAIL_MAILER=smtp` dengan kredensial penyedia terpercaya (Mailgun, SES, Sendgrid).
- Seluruh notifikasi (`BookingCreated`, `PaymentReceived`, `BookingCancelled`, `BookingExpired`) berjalan via *ShouldQueue* agar tidak memperlambat respon HTTP.

## Logging
- Menggunakan channel logging terstruktur dan terisolasi:
  - `payments` (`storage/logs/payments.log`)
  - `security` (`storage/logs/security.log`)
  - `laravel` (`storage/logs/laravel.log`)
- Disertai header korelasi `X-Request-ID` untuk melacak perjalanan request dari ingress hingga basis data.
- Kredensial rahasia (password, secret key, cvv) otomatis disaring dan dilarang masuk ke log.

## Health Check
- Probe kesehatan publik: `GET /health`.
- Memeriksa konektivitas PDO Database, Cache Read/Write, dan Storage Access.
- Menghasilkan status HTTP `200 OK` tanpa mengekspos variabel lingkungan, path absolut, atau kata sandi.

## Backup
- Lakukan dump harian database MySQL menggunakan opsi `--single-transaction`:
  ```bash
  mysqldump -u cantravel_user -p --single-transaction --quick cantravel_prod | gzip -9 > /backups/db_$(date +%F).sql.gz
  ```
- Cadangkan direktori berkas `storage/app/public` secara rutin.
- Simpan cadangan terenkripsi di server terpisah atau *object storage* sekunder.

## Restore
- Prosedur pemulihan:
  1. Aktifkan maintenance: `php artisan down`.
  2. Impor database: `gunzip < /backups/db_YYYY-MM-DD.sql.gz | mysql -u cantravel_user -p cantravel_prod`.
  3. Jalankan migrasi: `php artisan migrate --force`.
  4. Segarkan cache: `php artisan optimize:clear && php artisan config:cache`.
  5. Buka aplikasi: `php artisan up`.

## Monitoring
- Monitor status HTTP 200 pada `GET /health` melalui UptimeRobot, Better Uptime, atau Prometheus blackbox exporter.
- Monitor log pengecualian pada `storage/logs/laravel.log`.
- Pantau aktivitas antrean melalui `php artisan queue:failed`.

## Incident Response
- Jika gateway Midtrans mengalami downtime: sistem otomatis menahan pesanan dalam status `pending` dan perintah `payments:reconcile` akan menyinkronkannya setelah gateway pulih.
- Jika antrean antarmuka tertahan: periksa koneksi basis data atau restart worker via `sudo supervisorctl restart cantravel-worker:*`.
- Seluruh tindakan sensitif admin dan kegagalan verifikasi tiket tercatat dalam tabel `audit_logs`.

## Rollback
- Jika terjadi kegagalan deployment:
  ```bash
  php artisan down
  git checkout <previous-tag-or-commit>
  composer install --no-dev --optimize-autoloader
  npm ci && npm run build
  php artisan optimize:clear && php artisan config:cache
  php artisan queue:restart
  php artisan up
  ```

## Security Checklist
- [x] `APP_DEBUG=false` di lingkungan produksi.
- [x] Rate limiting aktif pada seluruh titik akses penting.
- [x] Security headers aktif: `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Content-Security-Policy` (tanpa `unsafe-eval`), `X-Request-ID`.
- [x] Proteksi IDOR pada seluruh endpoint pesanan, tiket, dan pembayaran.
- [x] Proteksi terhadap eksekusi file berbahaya pada unggahan bukti bayar.
- [x] Token tiket E-Ticket memiliki entropi tinggi anti-enumerasi (UUID 32-karakter).
- [x] Tidak ada kredensial atau rahasia yang tercatat dalam log audit maupun berkas Git.
