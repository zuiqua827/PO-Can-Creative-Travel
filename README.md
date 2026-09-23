# CAN Travel — Bus Ticket Booking Application

**CAN Travel** adalah platform pemesanan tiket bus antarkota modern, andal, aman, dan siap produksi (*production-hardened*) yang dibangun dengan **Laravel 10**, **PHP 8.4+**, **MySQL**, **Blade**, **Tailwind CSS**, dan **Vite**.

Platform ini mengintegrasikan siklus hidup reservasi kursi server-authoritative, arsitektur pembayaran modular dengan abstraksi gateway, webhook asynchronous yang terverifikasi dan idempoten, otomatisasi kedaluwarsa pesanan via Laravel Scheduler, proteksi keamanan tingkat tinggi (Security Headers, Rate Limiting, IDOR safeguards), dashboard analitik operasional dengan ekspor CSV, serta boarding pass E-Tiket berbasis QR code verifikasi real-time.

---

## 1. Project Overview

CAN Travel memodernisasi pemesanan tiket bus antarkota dengan fokus utama pada keandalan transaksional dan keamanan data:
- **Pelanggan**: Mencari rute bus, memilih kursi interaktif secara real-time, mengisi manifest penumpang, membayar dengan tenggat waktu otomatis, mendapatkan E-Tiket resmi dengan QR Code verifikasi.
- **Kru & Konduktor**: Memindai QR Code boarding pass untuk verifikasi keabsahan tiket secara instan tanpa login atau manipulasi client.
- **Administrator**: Memantau analitik KPI keuangan & operasional (Today, This Week, This Month), okupansi armada, rute terpopuler, mengelola armada bus & rute perjalanan, memfilter pesanan, dan mengekspor laporan transaksi ke format streaming CSV.

---

## 2. Technology Stack

- **Backend Framework**: [Laravel 10.x](https://laravel.com)
- **Bahasa Pemrograman**: PHP 8.3 / 8.4+
- **Basis Data**: MySQL 8.0+ / MariaDB 10.4+ (InnoDB Engine dengan Relational Foreign Keys & Indeks Performa)
- **ORM**: Native Eloquent ORM
- **Frontend / Templating**: Laravel Blade Components
- **CSS Framework**: Tailwind CSS 3.4
- **Asset Bundler**: Vite 4.x
- **Testing Engine**: PHPUnit 10.x & Laravel Feature Testing Suite
- **Code Formatter & Linter**: Laravel Pint (PSR-12 standard)

---

## 3. Panduan Instalasi (Installation)

1. **Clone Repositori**:
   ```bash
   git clone https://github.com/zuiqua827/PO-Can-Creative-Travel.git
   cd PO-Can-Creative-Travel
   ```

2. **Pasang Dependensi PHP**:
   ```bash
   composer install
   ```

3. **Pasang Dependensi Frontend & Build Aset**:
   ```bash
   npm install
   npm run build
   ```

4. **Salin Berkas Environment**:
   ```bash
   cp .env.example .env
   ```

5. **Generate Kunci Aplikasi**:
   ```bash
   php artisan key:generate
   ```

---

## 4. Konfigurasi Environment (.env)

Pastikan konfigurasi `.env` telah disesuaikan:

```env
APP_NAME="CAN Travel"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=po_can_travel
DB_USERNAME=root
DB_PASSWORD=

# Logging & Testing
LOG_CHANNEL=stack
MAIL_MAILER=log
QUEUE_CONNECTION=sync
SESSION_DRIVER=file

# Payment Gateway Configuration
PAYMENT_GATEWAY=simulation
PAYMENT_WEBHOOK_SECRET=your_secure_webhook_secret_here
PAYMENT_SIMULATION_AUTO_CONFIRM=false

# Placeholders for Future Production Payment Gateways (Optional)
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false

XENDIT_SECRET_KEY=
XENDIT_PUBLIC_KEY=
```

---

## 5. Database Setup & Migration

Jalankan migrasi basis data secara terstruktur:

```bash
php artisan migrate
```

*Catatan Keamanan Basis Data*:
- Seluruh migrasi bersifat non-destruktif dan *backward-compatible*.
- Kolom `payments.payment_reference` dipertahankan untuk kompatibilitas riwayat transaksi.
- Tabel `payments` telah diperkuat dengan kolom audit: `provider`, `provider_transaction_id`, `failed_at`, `expired_at`, `webhook_processed_at`, dan `metadata`.
- Tabel `order_items` dilengkapi dengan kolom unik `ticket_token` untuk validasi QR tiket.

---

## 6. Seeder & Akun Demo

Untuk mengisi data awal armada bus, denah kursi, rute perjalanan, jadwal keberangkatan, dan pengguna:

```bash
php artisan db:seed
```

### Akun Bawaan (Default Credentials):
- **Administrator**:
  - Email: `admin@cantravel.com`
  - Password: `password`
- **Customer / Penumpang**:
  - Email: `budi@gmail.com`
  - Password: `password`

---

## 7. Autentikasi & Keamanan Sesi

- **Session Regeneration**: Sesi di-regenerate secara otomatis pada saat login untuk mencegah serangan *Session Fixation*.
- **Password Hashing**: Menggunakan Bcrypt hashing dengan cast terproteksi.
- **Log Peristiwa Keamanan**: Login gagal, login berhasil, dan logout dicatat ke sistem audit log dengan IP address pengguna.
- **Proteksi Akses Admin**: Rute `/admin/*` dilindungi middleware ganda `['auth', 'admin']` (`AdminMiddleware`) dengan pencatatan upaya akses ilegal.

---

## 8. Alur Pemesanan Pelanggan (Customer Booking Flow)

1. **Pencarian Jadwal (`/trips`)**: Pelanggan memfilter rute berdasarkan kota asal, tujuan, tanggal, dan kelas bus.
2. **Peta Kursi Interaktif (`/trips/{trip}`)**: Denah kabin bus 2-2 interaktif menampilkan kursi *Tersedia*, *Dipilih*, atau *Terisi*.
3. **Checkout & Manifest (`/trips/{trip}/checkout`)**: Input data manifest identitas penumpang untuk setiap kursi yang dipilih.
4. **Pemesanan Atomik (`POST /trips/{trip}/booking`)**: Sistem server mengunci kursi dengan `lockForUpdate()`, membuat pesanan `CAN-YYYYMMDD-XXXXX`, membuat token tiket, dan menetapkan batas pembayaran 2 jam.
5. **Pembayaran (`/orders/{order}/payment`)**: Menampilkan instruksi pembayaran, hitung mundur kedaluwarsa, dan form simulasi transfer / konfirmasi.
6. **E-Tiket & Boarding Pass (`/my-orders/{order}`)**: E-Tiket terbit seketika dengan status lunas, nomor kursi, manifest penumpang, dan SVG QR Code verifikasi.

---

## 9. Arsitektur Pembayaran (Payment Architecture)

> [!IMPORTANT]
> **SIMULASI PEMBAYARAN vs REAL PAYMENT GATEWAY**:
> Saat ini, aplikasi menggunakan gateway pengujian terkonfigurasi (**`FakePaymentGateway` / Simulation Mode**) untuk memfasilitasi pengujian menyeluruh, demonstrasi teknis, dan verifikasi alur otomatis. Integrasi gerbang pembayaran eksternal riil (seperti Midtrans atau Xendit) belum aktif pada lingkungan ini, namun kontrak arsitekturnya telah siap secara plug-and-play.

### Struktur Arsitektur Pembayaran
```
app/Services/Payment/
├── PaymentGatewayInterface.php   # Kontrak standar gateway
├── PaymentResult.php             # Value Object status pembayaran
└── FakePaymentGateway.php        # Implementasi simulasi & webhook
```

### Karakteristik & Keandalan Pembayaran:
- **Authoritative & Server-Side**: Status pembayaran ditentukan sepenuhnya oleh server; manipulasi status dari sisi klien dicegah secara mutlak.
- **Idempotensi**: Pemanggilan pembayaran ganda tidak akan menduplikasi status atau memotong saldo dua kali.
- **Webhook Terproteksi (`POST /payments/webhook`)**:
  - Dikecualikan dari CSRF token via `VerifyCsrfToken::$except`.
  - Verifikasi tanda tangan kriptografis HMAC-SHA256 (`X-CAN-Signature`).
  - Penguncian baris basis data (`lockForUpdate`) untuk memastikan atomisitas.
  - Idempoten: Webhook yang dikirim ulang dengan payload sama direspon HTTP 200 `already_processed` tanpa efek samping duplikat.
  - Menangani status: `settlement`/`success`, `failed`, `expired`, dan `pending`.

---

## 10. Siklus Hidup Pesanan (Order Lifecycle)

| Status Pesanan (`orders.status`) | Status Bayar (`orders.payment_status`) | Keterangan |
|---|---|---|
| `pending` | `unpaid` | Pesanan baru dibuat, kursi di-*hold* selama 2 jam. |
| `confirmed` | `paid` | Pembayaran lunas, E-Tiket terbit, kursi berstatus *booked*. |
| `completed` | `paid` | Perjalanan bus telah selesai dilaksanakan. |
| `cancelled` | `expired` | Waktu habis atau dibatalkan, kursi otomatis berstatus *released*. |

---

## 11. Siklus Hidup Kursi (Seat Reservation Lifecycle)

Sistem menerapkan definisi 4 status reservasi kursi:
1. **AVAILABLE**: Kursi belum memiliki reservasi aktif pada perjalanan terkait.
2. **HELD**: Kursi direservasi oleh pesanan `pending` & `unpaid` yang belum melewati tenggat `expires_at`.
3. **BOOKED**: Kursi telah dikonfirmasi dan dibayar lunas (`status = confirmed/completed`, `payment_status = paid`).
4. **RELEASED**: Kursi yang sebelumnya dipesan telah dibatalkan (`status = cancelled`) atau kedaluwarsa (`payment_status = expired`), sehingga langsung tersedia kembali bagi calon penumpang lain.

---

## 12. Otomatisasi Kedaluwarsa & Laravel Scheduler

Aplikasi memiliki perintah konsol otomatis untuk membersihkan pesanan yang telah melewati batas waktu pembayaran:

```bash
php artisan orders:expire
```

Perintah ini:
1. Memindai pesanan berstatus `pending` & `unpaid` dengan `expires_at <= now()`.
2. Menjalankan transaksi atomik dengan `lockForUpdate()`.
3. Memperbarui status pesanan menjadi `cancelled` dan `payment_status` menjadi `expired`.
4. Memperbarui pembayaran menjadi `expired` dengan stempel `expired_at`.
5. Melepaskan kursi secara instan ke sistem pencarian tiket.
6. Mengirimkan notifikasi kedaluwarsa kepada pelanggan.
7. Aman dijalankan berulang kali (*idempotent*).

### Menjalankan Scheduler di Lingkungan Lokal:
```bash
php artisan schedule:work
```

### Konfigurasi Scheduler di Server Produksi (Crontab):
Tambahkan entri crontab berikut pada server Linux produksi:
```cron
* * * * * cd /path-ke-proyek/PO-CAN_Creative_Travel && php artisan schedule:run >> /dev/null 2>&1
```

---

## 13. Dashboard Analitik Admin & Laporan CSV

Akses portal admin melalui `/admin/dashboard`:
- **Filter Periode Dinamis**: Saring metrik transaksi berdasarkan *Hari Ini*, *Minggu Ini*, *Bulan Ini*, atau *Semua Waktu*.
- **Tingkat Okupansi Armada (Occupancy Rate %)**: Menghitung persentase keterisian kursi seluruh armada yang aktif beroperasi.
- **Rute Terpopuler (Top Routes)**: Peringkat rute teratas berdasarkan volume pemesanan tiket lunas dan kontribusi pendapatan.
- **Ekspor Laporan Transaksi CSV (`/admin/orders/export`)**:
  - Menghasilkan berkas CSV terstruktur (`CAN_Travel_Laporan_Pesanan_{timestamp}.csv`).
  - Menggunakan teknik streaming response (`StreamedResponse`) untuk memproses ribuan baris data tanpa membebani memori server (*zero memory bloat*).
  - Dilengkapi UTF-8 BOM untuk kompatibilitas tampilan karakter di Microsoft Excel.
  - Mematuhi filter aktif (tanggal, status order, status bayar, kata kunci pencarian).

---

## 14. Keamanan Sistem (Security Hardening)

- **HTTP Security Headers Middleware (`SecurityHeaders.php`)**:
  - `X-Content-Type-Options: nosniff`
  - `X-Frame-Options: SAMEORIGIN`
  - `Referrer-Policy: strict-origin-when-cross-origin`
  - `Permissions-Policy: camera=(), microphone=(), geolocation=()`
  - `Content-Security-Policy`: Kebijakan CSP yang kompatibel dengan Vite HMR, Google Fonts, dan SVG.
- **Fine-Grained Rate Limiting (`RouteServiceProvider.php`)**:
  - `login`: 5 percobaan / menit per IP + email.
  - `register`: 5 pendaftaran / menit per IP.
  - `booking`: 15 pemesanan / menit per pengguna / IP.
  - `payment`: 15 transaksi / menit per pengguna / IP.
  - `payment-webhook`: 60 panggilan / menit per IP.
  - `ticket-verify`: 60 verifikasi / menit per IP.
- **Proteksi IDOR (Insecure Direct Object References)**:
  - Validasi otorisasi `OrderPolicy` pada rute pesanan dan pembayaran; pelanggan dilarang keras melihat atau membatalkan pesanan milik pelanggan lain (mengembalikan HTTP 403 Forbidden).
- **Proteksi Informasi Sensitif**:
  - Token tiket digital (`ticket_token`) digunakan sebagai kunci verifikasi QR Code publik alih-alih mengekspos ID rahasia atau identitas kredensial pelanggan.
  - Tidak menyimpan nomor kartu kredit mentah, PIN, CVV, atau password perbankan.

---

## 15. E-Tiket & Verifikasi Boarding Pass QR Code

- Setiap kursi penumpang memiliki token tiket unik: `TKT-2026-XXXXXXXXXX`.
- **SVG QR Code Generator Murni (`QrCodeService.php`)**:
  - Dihasilkan secara native menggunakan representasi matriks SVG murni.
  - **Zero External Dependencies**: Tidak membutuhkan pustaka pihak ketiga berukuran besar atau ekstensi PHP GD / Imagick.
  - Skalabel, tajam di resolusi tinggi (*retina ready*), dan siap cetak dokumen PDF.
- **Portal Verifikasi Tiket Publik (`/tickets/verify/{token}`)**:
  - Dapat dipindai oleh kru bus atau konduktor di lokasi penjemputan.
  - Menampilkan badge status real-time (*Resmi & Terverifikasi*, *Menunggu Pembayaran*, *Dibatalkan*, atau *Kedaluwarsa*).
  - Menampilkan manifest nama penumpang, nomor kursi, nama armada, titik naik, dan waktu verifikasi.

---

## 16. Arsitektur Notifikasi (Notifications)

Sistem notifikasi native Laravel (`app/Notifications/`):
- `BookingCreatedNotification`: Notifikasi pembuatan pesanan dan batas pembayaran.
- `PaymentReceivedNotification`: Notifikasi konfirmasi pelunasan dan E-Tiket siap pakai.
- `BookingCancelledNotification`: Notifikasi pembatalan pesanan dan pelepasan kursi.
- `BookingExpiredNotification`: Notifikasi batas waktu habis otomatis.

*Catatan Lingkungan Lokal*: Menggunakan `MAIL_MAILER=log` sehingga seluruh notifikasi tercatat langsung di berkas log tanpa membutuhkan kredensial SMTP eksternal.

---

## 17. Pengujian Otomatis (Automated Testing)

Aplikasi dilengkapi rangkaian pengujian unit dan fitur lengkap:

```bash
# Jalankan seluruh test suite
php artisan test

# Jalankan pengujian khusus Sprint 4 Hardening
php artisan test tests/Feature/Sprint4HardeningTest.php

# Jalankan pengecekan gaya kode PSR-12
vendor/bin/pint --test
```

### Cakupan Pengujian (Total: 41 Tests, 155 Assertions - 100% Passing):
- **Payment Lifecycle**: Pembuatan pembayaran, verifikasi field hardened, simulasi sukses, simulasi gagal, idempotensi ganda, proteksi pembayaran kadaluwarsa.
- **Webhook Handling**: Penolakan signature palsu, konfirmasi transaksi sukses, idempotensi webhook ganda, penanganan event expired dan failure.
- **Scheduler & Expiration**: Perintah `orders:expire` membatalkan pesanan overdue dan melepas kursi, idempotensi eksekusi berulang, pesanan aktif/lunas tidak tersentuh.
- **Keamanan & Otorisasi**: Kehadiran security headers, proteksi IDOR, otorisasi portal admin, otorisasi ekspor CSV.
- **E-Tiket & Verifikasi**: Validasi token tiket valid, penolakan token fiktif (404), rendering QR code SVG.

---

## 18. Panduan Troubleshooting

1. **Jadwal Tidak Terbuka / Tidak Muncul di Pencarian**:
   - Pastikan jadwal memiliki status `scheduled` dan waktu keberangkatan `departure_at` berada di masa depan (`> now()`).
2. **Pengujian Gagal Terkait Mail / Timeout**:
   - Pastikan `.env` menggunakan `MAIL_MAILER=log` atau `MAIL_MAILER=array` pada `phpunit.xml`.
3. **Aset CSS / JS Tidak Tampil Sempurna**:
   - Jalankan `npm run build` untuk mengompilasi bundel produksi Vite.
4. **Membersihkan Cache Aplikasi**:
   ```bash
   php artisan optimize:clear
   ```
