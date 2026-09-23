# CAN Travel — Bus Ticket Reservation System

**CAN Travel** adalah platform pemesanan tiket bus antarkota modern, andal, dan aman yang dibangun dengan **Laravel 10**, **PHP 8.4+**, **MySQL**, **Blade**, **Tailwind CSS**, dan **Vite**. Platform ini dirancang untuk memudahkan calon penumpang dalam mencari jadwal bus secara real-time, memilih kursi interaktif, melakukan pemesanan transparan, menyelesaikan pembayaran dengan tenggat waktu otomatis, dan mencetak E-Tiket resmi.

---

## 1. Fitur Utama

### A. Pengguna / Pelanggan (Customer Journey)
- **Mesin Pencari Tiket Antarkota**: Filter berdasarkan kota asal, kota tujuan, tanggal keberangkatan, kelas bus (Executive, Royal Suite, Sleeper Bus, VIP), dan rentang harga.
- **Peta Kursi Kabin Interaktif (Bus Seat Matrix)**: Denah kabin 2-2 interaktif dengan status real-time (*Tersedia*, *Dipilih [Brand Blue]*, *Terisi / Locked*).
- **Alur Pemesanan 4 Langkah Transparan**:
  - `01. Pilih Jadwal` → `02. Pilih Kursi` → `03. Data Penumpang` → `04. Pembayaran`.
- **Perhitungan Harga Authoritative**: Total harga dihitung mutlak di sisi server berdasarkan jumlah kursi dan tarif resmi trip; input manipulasi client-side diabaikan.
- **Proteksi Concurrency & Double-Booking**: Penguncian baris basis data (`lockForUpdate()`) di dalam transaksi atomik (`DB::transaction`) untuk mencegah dua pengguna memesan kursi yang sama secara bersamaan.
- **Siklus Pembayaran & Batas Waktu Otomatis**:
  - Batas waktu pembayaran 2 jam (`expires_at`).
  - Hitung mundur interaktif (*live countdown timer*).
  - Verifikasi kedaluwarsa di sisi backend: jika waktu habis, pesanan ditandai kedaluwarsa dan kursi dilepaskan kembali secara otomatis.
- **Simulasi Pembayaran & Idempotensi**:
  - Pembayaran aman dengan pencegahan duplikasi (*idempotent processing*).
  - Mode sandbox simulasi 1-klik untuk pengujian teknis.
- **E-Tiket & Boarding Pass Resmi**:
  - Nomor pesanan unik berformat resmi: `CAN-YYYYMMDD-XXXXX`.
  - Simulasi kode batang barcode, manifest lengkap penumpang per kursi, titik kumpul penjemputan/penurunan, dan tata letak siap cetak / simpan PDF (`window.print()`).
- **Manajemen Pesanan & Pembatalan Transaksional**:
  - Pelanggan dapat membatalkan pesanan yang belum dibayar; kursi langsung dilepaskan kembali secara transaksional.
  - Pesanan yang sudah dibayar tidak dapat dibatalkan sembarangan tanpa otorisasi customer service.

### B. Administrator (Admin Operations)
- **Dashboard Operasional Real-Time**:
  - Statistik langsung dari basis data tanpa hardcoded data: Total Pendapatan, Pesanan Hari Ini, Total Pesanan, Pending Pembayaran, Pesanan Dibatalkan, Jadwal Aktif, Keberangkatan Hari Ini, Total Armada, dan Total Pelanggan.
  - Tabel keberangkatan hari ini dengan okupansi kursi bebas N+1 query (`withBookedSeatsCount`).
- **Kelola Armada Bus & Kursi**:
  - Manajemen armada (nama, kode bus, tipe kelas, fasilitas, kapasitas).
  - Generator otomatis denah kursi 2-2.
  - Pengaturan status kursi individual (*available, blocked, maintenance*).
- **Kelola Rute & Jadwal Keberangkatan**:
  - Manajemen rute antarkota dan tarif dasar.
  - Penjadwalan keberangkatan armada bus.
  - Proteksi integritas data: jadwal perjalanan yang sudah memiliki pesanan pelanggan dilindungi dari penghapusan sembarangan.
- **Kelola Pesanan Pelanggan**:
  - Pencarian fleksibel berdasarkan kode order, nama pelanggan, email, nomor telepon, atau nama penumpang.
  - Filter berdasarkan status pemesanan, status pembayaran, dan tanggal transaksi.
  - Pembaruan status pesanan terkontrol dengan validasi *state machine* transisi legal.

---

## 2. Tech Stack

- **Backend Framework**: [Laravel 10.x](https://laravel.com)
- **Bahasa Pemrograman**: PHP 8.3 / 8.4+
- **Basis Data**: MySQL / MariaDB (InnoDB Engine dengan Relational Foreign Keys & Indeks Performa)
- **ORM**: Native Eloquent ORM
- **Frontend / Templating**: Laravel Blade Components
- **CSS Framework**: Tailwind CSS 3.4
- **Asset Bundler**: Vite 4.x
- **Testing Engine**: PHPUnit / Laravel Feature Testing Suite
- **Code Linter & Formatter**: Laravel Pint (PSR-12 standard)

---

## 3. Prasyarat Sistem

- **PHP** >= 8.2 (direkomendasikan PHP 8.3 atau 8.4) dengan ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `bcmath`, `curl`, `tokenizer`, `xml`
- **Composer** >= 2.x
- **MySQL** >= 8.0 atau **MariaDB** >= 10.4
- **Node.js** >= 18.x dan **NPM** >= 9.x

---

## 4. Panduan Instalasi Lokal

1. **Clone Repositori**:
   ```bash
   git clone https://github.com/zuiqua827/PO-Can-Creative-Travel.git
   cd PO-Can-Creative-Travel
   ```

2. **Pasang Dependensi PHP**:
   ```bash
   composer install
   ```

3. **Konfigurasi Environment**:
   Salin berkas `.env.example` ke `.env`:
   ```bash
   cp .env.example .env
   ```

4. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

5. **Konfigurasi Database MySQL**:
   Buka berkas `.env` dan sesuaikan pengaturan database lokal Anda:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=po_can_travel
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Migrasi Database & Seeding**:
   Jalankan migrasi skema tabel beserta data awal armada, rute, dan jadwal:
   ```bash
   php artisan migrate --seed
   ```

7. **Pasang Dependensi Frontend & Kompilasi Asset**:
   ```bash
   npm install
   npm run build
   ```

8. **Jalankan Server Lokal**:
   ```bash
   php artisan serve
   ```
   Aplikasi dapat diakses melalui peramban web di: `http://127.0.0.1:8000`.

---

## 5. Kredensial Pengujian (Demo Accounts)

Sistem telah dilengkapi dengan akun bawaan seeder untuk pengujian teknis:

| Role | Email | Password | Akses |
|---|---|---|---|
| **Administrator** | `admin@pocan.com` | `password` | Panel Admin (`/admin/dashboard`), Kelola Armada, Rute, Jadwal, Pesanan, Pelanggan |
| **Customer** | `budi@gmail.com` | `password` | Pencarian jadwal, pemilihan kursi, checkout, pembayaran, riwayat pesanan, profil |

---

## 6. Arsitektur Keamanan & Validasi

1. **Otorisasi Ketat (OrderPolicy)**:
   - Pelanggan hanya dapat melihat dan membatalkan pesanan milik mereka sendiri (`403 Forbidden` jika mencoba mengakses pesanan pengguna lain / IDOR protection).
2. **Kalkulasi Harga Server-Authoritative**:
   - Harga tiket dan total bayar selalu dihitung di backend berdasarkan tarif trip di database dikali jumlah kursi. Data harga dari form request client tidak pernah dipercaya.
3. **Pencegahan Double-Booking (Concurrency Lock)**:
   - `OrderItem::whereIn(...)->lockForUpdate()` di dalam transaksi database memastikan tidak ada dua transaksi paralel yang dapat memesan kursi yang sama.
4. **Validasi Kursi Armada**:
   - Sistem memverifikasi bahwa kursi yang dipilih benar-benar terdaftar pada armada bus trip bersangkutan dan berstatus operasional (`status = 'available'`).
5. **State Machine Transisi Status**:
   - Mencegah perubahan status tidak valid (contoh: pesanan `cancelled` tidak bisa langsung menjadi `paid`, pesanan `completed` tidak bisa diubah kembali menjadi `pending`).
6. **Form Request Validation**:
   - Seluruh endpoint input data pengguna tervalidasi menggunakan Form Request khusus di `app/Http/Requests`.

---

## 7. Automated Testing

Jalankan test suite menggunakan perintah:
```bash
php artisan test
```

### Cakupan Pengujian (23 Feature & Unit Tests):
1. `test_user_registration_success`
2. `test_user_registration_validation_fails`
3. `test_user_login_success`
4. `test_user_login_with_invalid_credentials_fails`
5. `test_public_pages_load_correctly` (Memastikan branding CAN Travel muncul dan tidak ada PO CAN Travel)
6. `test_trip_search_filters_correctly`
7. `test_unavailable_trip_is_not_bookable`
8. `test_expired_trip_cannot_be_booked`
9. `test_unavailable_seat_cannot_be_selected`
10. `test_cancelled_order_releases_seats`
11. `test_expired_order_releases_seats`
12. `test_customer_cannot_access_another_customer_order` (IDOR Prevention)
13. `test_customer_cannot_modify_another_customer_order`
14. `test_payment_cannot_be_repeated_idempotency` (Idempotent Payment)
15. `test_expired_payment_cannot_be_completed`
16. `test_invalid_order_status_transition_prevented`
17. `test_admin_order_filtering_by_status_and_date`
18. `test_admin_authorization_enforced`
19. `test_order_creation_calculates_price_server_side`
20. `test_multiple_seat_booking_creates_manifest_records`
21. `test_concurrent_seat_booking_is_prevented` (Concurrency Lock Test)
22. `ExampleTest` (Unit)
23. `ExampleTest` (Feature)

---

## 8. Standar Kode & Format (Pint)

Format kode diperiksa dan distandarisasi menggunakan Laravel Pint:
```bash
vendor/bin/pint --test
```

---

## 9. Struktur Direktori Proyek

```
├── app/
│   ├── Http/
│   │   ├── Controllers/         # Controller aplikasi (Thin Controllers)
│   │   │   ├── Admin/           # Controller administrasi CAN Travel
│   │   │   ├── AuthController.php
│   │   │   ├── BookingController.php
│   │   │   ├── HomeController.php
│   │   │   ├── OrderController.php
│   │   │   └── TripController.php
│   │   ├── Middleware/          # Middleware aplikasi (AdminMiddleware, dll.)
│   │   └── Requests/            # Form Request Validation mandiri
│   │       ├── Admin/           # Validasi armada, rute, trip, status pesanan
│   │       ├── Auth/            # Validasi login dan registrasi
│   │       ├── Booking/         # Validasi pemesanan tiket
│   │       └── Profile/         # Validasi update profil
│   ├── Models/                  # Model Eloquent (User, Bus, BusSeat, Route, Trip, Order, OrderItem, Payment)
│   └── Policies/                # Authorization Policies (OrderPolicy)
├── database/
│   ├── factories/               # Model factories untuk testing otomatis
│   ├── migrations/              # Definisi skema tabel & indeks performa
│   └── seeders/                 # Data inisialisasi awal sistem
├── resources/
│   ├── css/                     # Konfigurasi Tailwind CSS
│   ├── js/                      # JavaScript frontend assets
│   └── views/                   # Template Blade (layouts, admin, components, orders, trips, booking)
├── routes/
│   └── web.php                  # Definisi rute web terstruktur (Public, Guest, Auth, Admin)
└── tests/
    └── Feature/                 # Automated feature integration tests
```

---

## 10. Lisensi

Hak Cipta © 2026 **CAN Travel**. Seluruh hak cipta dilindungi undang-undang.
