# CAN Travel — Bus Ticket Reservation System

PO CAN Travel adalah platform pemesanan tiket bus antarkota modern, andal, dan aman yang dibangun dengan Laravel 10. Platform ini dirancang untuk memudahkan calon penumpang dalam mencari rute, memilih jadwal dan armada bus, memilih nomor kursi secara real-time, hingga melakukan pemesanan dan konfirmasi pembayaran tiket secara transparan.

---

## Features

- **Authentication & Authorization**: Registrasi akun pelanggan, login aman berbasis session, proteksi brute force, serta kontrol akses ketat menggunakan Laravel Policies (Role: `admin` & `customer`).
- **Trip Search & Schedule**: Pencarian jadwal bus interaktif berdasarkan terminal asal, terminal tujuan, tanggal keberangkatan, dan kelas bus.
- **Seat Selection (Interactive Bus Layout)**: Tampilan denah kursi bus interaktif (format 2-2) dengan indikator status jelas (Tersedia, Terpilih, Terisi).
- **Authoritative Booking & Concurrency Protection**: Perhitungan tarif dan validasi tiket mutlak di sisi server, dilengkapi transaksi atomik (`DB::transaction`) dan penguncian baris (`lockForUpdate`) untuk mencegah *double booking*.
- **Order & Invoice Management**: Manajemen pemesanan tiket dengan kode unik (`PCT-YYYYMMDD-XXXXX`), histori transaksi pelanggan, dan pencetakan e-ticket/tiket digital.
- **Payment Status Simulation**: Sistem simulasi konfirmasi pembayaran (Transfer Bank, QRIS, Virtual Account) dengan validasi referensi transaksi.
- **Customer Profile**: Pengelolaan profil pelanggan, update nomor telepon dan alamat email secara aman.
- **Comprehensive Admin Management**:
  - Dashboard analitik ringkasan operasional dan pendapatan.
  - Manajemen armada bus (kapasitas, kelas, fasilitas, plat nomor).
  - Manajemen denah dan status kursi bus.
  - Manajemen rute perjalanan dan estimasi durasi.
  - Manajemen jadwal perjalanan bus (*trips*).
  - Manajemen dan pemantauan seluruh pesanan tiket pelanggan.

---

## Tech Stack

- **Backend Framework**: [Laravel 10.x](https://laravel.com)
- **Language**: PHP 8.2 / 8.3+
- **Database & ORM**: MySQL / MariaDB dengan Eloquent ORM murni
- **Templating**: Laravel Blade
- **Styling**: Tailwind CSS 3.4
- **Asset Bundler**: Vite
- **Testing**: PHPUnit / Laravel Test Suite

---

## Requirements

Sebelum menjalankan aplikasi di server lokal, pastikan perangkat Anda memiliki:

- **PHP** >= 8.2 (disarankan PHP 8.3 / 8.4) dengan ekstensi `pdo_mysql`, `mbstring`, `openssl`, `bcmath`, `curl`
- **Composer** >= 2.x
- **MySQL** >= 8.0 atau **MariaDB** >= 10.4
- **Node.js** >= 18.x dan **NPM** >= 9.x

---

## Installation

Ikuti langkah-langkah berikut untuk mengatur proyek di lingkungan lokal:

1. **Clone repositori**:
   ```bash
   git clone https://github.com/zuiqua827/PO-Can-Creative-Travel.git
   cd PO-Can-Creative-Travel
   ```

2. **Install PHP dependencies**:
   ```bash
   composer install
   ```

3. **Konfigurasi Environment**:
   Salin berkas konfigurasi template ke `.env`:
   ```bash
   cp .env.example .env
   ```

4. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

5. **Konfigurasi Database**:
   Buka berkas `.env` dan sesuaikan pengaturan koneksi basis data MySQL Anda:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=po_can_travel
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Jalankan Migrasi & Seeder**:
   Siapkan struktur tabel serta data awal (admin, rute, armada, kursi, jadwal trip):
   ```bash
   php artisan migrate --seed
   ```

7. **Install Node Dependencies & Build Assets**:
   ```bash
   npm install
   npm run build
   ```

8. **Jalankan Server Pengembangan**:
   ```bash
   php artisan serve
   ```
   Aplikasi akan aktif di `http://127.0.0.1:8000`.

---

## Environment Configuration

Aplikasi menggunakan konfigurasi berbasis standar Laravel `.env`. Parameter penting yang perlu diperhatikan:

- `APP_NAME`: Nama aplikasi (`"PO CAN Travel"`).
- `APP_ENV`: Lingkungan aplikasi (`local` untuk pengembangan, `production` untuk live server).
- `APP_DEBUG`: Mode debug (`true` hanya untuk development).
- `APP_URL`: URL utama aplikasi (`http://127.0.0.1:8000`).
- `DB_*`: Kredensial koneksi MySQL.
- `SESSION_DRIVER`: Driver session (default: `file`).

> **PENTING**: Jangan pernah mencantumkan berkas `.env` asli atau kredensial rahasia ke dalam sistem version control (Git). Berkas `.gitignore` telah dikonfigurasi untuk melindungi data sensitif Anda.

---

## Database

Arsitektur basis data didesain dengan integritas relasional tinggi:

- **users**: Menyimpan akun pengguna dengan otorisasi berbasis kolom `role` (`admin` / `customer`).
- **buses**: Data armada bus PO CAN Travel (Executive, Super Executive, Sleeper Suite) beserta kapasitas kursi dan fasilitas.
- **bus_seats**: Data kursi individual per armada beserta nomor kursi dan tipe posisi.
- **routes**: Rute perjalanan resmi antarkota (asal, tujuan, jarak, estimasi durasi waktu).
- **trips**: Jadwal operasional perjalanan spesifik yang menghubungkan rute, armada bus, jam berangkat/tiba, dan harga tiket dasar.
- **orders**: Data header pemesanan tiket dengan kode order unik `PCT-YYYYMMDD-XXXXX`, referensi pemesan, total tagihan, dan status (`pending`, `paid`, `cancelled`, `completed`).
- **order_items**: Rincian tiket per kursi dalam satu transaksi pemesanan beserta nama penumpang dan identitas.
- **payments**: Pencatatan histori pembayaran tiket, metode transaksi, waktu pembayaran, dan referensi pembayaran (`payment_reference`).

---

## Authentication & Authorization

Sistem membedakan hak akses secara ketat:

- **Customer**:
  - Registrasi & Login.
  - Memilih rute, armada, jadwal, dan denah kursi.
  - Membuat pemesanan tiket dan melakukan konfirmasi pembayaran.
  - Melihat riwayat pesanan milik sendiri.
  - Dilindungi oleh `OrderPolicy`: Pelanggan A **dilarang keras** mengakses pesanan milik Pelanggan B (mengembalikan kode HTTP `403 Forbidden`).
- **Admin**:
  - Mengakses panel administratif di `/admin/dashboard`.
  - Mengelola data bus, kursi, rute perjalanan, jadwal trip, serta memantau dan mengubah status pemesanan pelanggan.
  - Dilindungi middleware `auth` dan `is_admin`.

**Akun Demo Bawaan Seeder**:
- **Admin**: `admin@pocan.com` / `password`
- **Customer**: `budi@gmail.com` / `password`

---

## Testing

Aplikasi dilengkapi dengan automated testing yang komprehensif menguji skenario autentikasi, otorisasi kepemilikan pesanan, integritas harga server-side, ketersediaan kursi, hingga pencegahan *race-condition double booking*:

```bash
php artisan test
```

Pengujian mencakup:
- `user_registration_success` & `user_registration_validation_fails`
- `user_login_success` & `user_login_with_invalid_credentials_fails`
- `customer_cannot_view_another_customer_order` (Authorization Policy test)
- `admin_authorization_enforced`
- `public_pages_load_correctly`
- `double_booking_is_prevented` (Row-lock concurrency test)
- `order_creation_calculates_price_server_side` (Authoritative calculation test)
- `payment_simulation_marks_order_as_paid_and_valid`

---

## Development

Untuk menjalankan aplikasi selama tahap pengembangan aktif:

Terminal 1 (Backend Server):
```bash
php artisan serve
```

Terminal 2 (Vite Hot Module Replacement):
```bash
npm run dev
```

---

## Project Structure

```
├── app/
│   ├── Http/
│   │   ├── Controllers/         # Controller aplikasi (Thin Controllers)
│   │   │   └── Admin/          # Controller administrasi PO CAN Travel
│   │   ├── Middleware/          # Middleware aplikasi (CheckAdmin, dll.)
│   │   └── Requests/            # Form Request Validation mandiri
│   │       ├── Admin/          # Validasi form rute, armada, trip, status
│   │       ├── Auth/           # Validasi login dan registrasi
│   │       ├── Booking/        # Validasi pemesanan tiket
│   │       └── Profile/        # Validasi update profil
│   ├── Models/                  # Model Eloquent (User, Bus, Trip, Order, dll.)
│   └── Policies/                # Authorization Policies (OrderPolicy)
├── database/
│   ├── factories/               # Model factories untuk testing otomatis
│   ├── migrations/              # Definisi skema tabel & indeks performa
│   └── seeders/                 # Data inisialisasi awal sistem
├── resources/
│   ├── css/                     # Konfigurasi Tailwind CSS
│   ├── js/                      # JavaScript frontend assets
│   └── views/                   # Template Blade (layouts, admin, customer, errors)
│       └── errors/              # Halaman error kustom (404, 403, 419, 422, 500)
├── routes/
│   └── web.php                  # Definisi rute web terstruktur (Public, Guest, Auth, Admin)
└── tests/
    └── Feature/                 # Automated feature integration tests
```

---

## License

Proyek ini dikembangkan secara eksklusif untuk PO CAN Travel dan dilindungi di bawah lisensi proprietary.
