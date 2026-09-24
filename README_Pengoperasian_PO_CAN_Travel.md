# Dokumentasi Pengoperasian PO CAN Travel

## 1. Deskripsi

**PO CAN Travel** adalah platform pemesanan tiket bus berbasis **Laravel
10**. Sistem memungkinkan pengguna mencari jadwal perjalanan, memilih
bus dan kursi, melakukan pemesanan, pembayaran, serta mendapatkan
e-ticket dengan QR/token untuk verifikasi.

Sistem memiliki dua area utama:

-   **Customer/User**: pencarian perjalanan, pemilihan kursi, checkout,
    pembayaran, order, e-ticket, dan profile.
-   **Admin**: dashboard, pengelolaan bus, kursi, rute, trip/jadwal,
    order, customer, profile, dan audit log.

Website tetap mengikuti spesifikasi awal proyek: **ERD, Laravel 10,
login/register, order tiket, migration, Eloquent ORM, validasi input,
dan pengembangan case yang masih berkaitan dengan pemesanan tiket bus.**

------------------------------------------------------------------------

## 2. Teknologi

  -----------------------------------------------------------------------
  Komponen                            Teknologi
  ----------------------------------- -----------------------------------
  Framework                           Laravel 10

  Backend                             PHP

  Database                            MySQL

  ORM                                 Eloquent ORM

  Frontend                            Blade, Tailwind CSS,
                                      JavaScript/Alpine sesuai kebutuhan
                                      UI

  Build                               Vite

  Payment                             Payment gateway/Midtrans pada
                                      payment flow

  E-ticket                            QR/Token

  Testing                             PHPUnit/Laravel Feature Test

  Local server                        Laragon / `php artisan serve`
  -----------------------------------------------------------------------

------------------------------------------------------------------------

## 3. Fitur Website

### Homepage

-   Logo CAN Travel
-   Navbar
-   Beranda
-   Jadwal & Tiket
-   Armada
-   Fasilitas
-   Bantuan
-   Login dan Register
-   Hero section dengan background perjalanan
-   CTA pencarian tiket
-   Search berdasarkan kota asal, kota tujuan, dan tanggal
-   Rute populer
-   Informasi layanan

### Customer

-   Register
-   Login/logout
-   Cari dan filter jadwal
-   Melihat detail trip
-   Memilih kursi
-   Maksimal 5 kursi per order
-   Checkout
-   Pembayaran
-   Riwayat order
-   Detail order
-   E-ticket
-   QR/token verification
-   Profile

### Admin

-   Dashboard
-   Manajemen bus
-   Manajemen kursi bus
-   Manajemen route
-   Manajemen trip/jadwal
-   Manajemen order
-   Data customer
-   Profile
-   Audit log

------------------------------------------------------------------------

## 4. Cara Menjalankan Project

### Install dependency

``` bash
composer install
npm install
```

### Environment

Salin `.env.example` menjadi `.env`, lalu sesuaikan database:

``` env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=po_can_travel
DB_USERNAME=root
DB_PASSWORD=
```

Kemudian:

``` bash
php artisan key:generate
php artisan migrate
php artisan db:seed
```

Seeder menyediakan data dasar/demo. **Dummy booking dan okupansi kursi
tidak dibuat sebagai data default**, sehingga kursi tidak otomatis penuh
karena data dummy.

### Menjalankan aplikasi

Terminal 1:

``` bash
php artisan serve
```

Terminal 2:

``` bash
npm run dev
```

Buka:

``` text
http://127.0.0.1:8000
```

Untuk build frontend:

``` bash
npm run build
```

------------------------------------------------------------------------

## 5. Pengoperasian Sebagai Customer

### 5.1 Register

1.  Klik **Daftar Sekarang**.
2.  Isi data yang diminta.
3.  Pastikan data valid.
4.  Submit registrasi.
5.  Login menggunakan akun tersebut.

### 5.2 Login

1.  Klik **Masuk**.
2.  Masukkan email dan password.
3.  Submit login.

### 5.3 Mencari Tiket

1.  Pilih **Kota Asal**.
2.  Pilih **Kota Tujuan**.
3.  Pilih **Tanggal Keberangkatan**.
4.  Klik **Cari Tiket**.
5.  Sistem menampilkan jadwal yang sesuai di halaman **Jadwal & Tiket**.

Menu **Jadwal & Tiket** juga dapat digunakan langsung.

### 5.4 Memilih Trip

Periksa: - rute, - bus, - waktu keberangkatan, - waktu kedatangan, -
harga, - status perjalanan.

Pilih trip untuk melanjutkan booking.

### 5.5 Memilih Kursi

1.  Sistem menampilkan denah kursi.
2.  Pilih kursi yang tersedia.
3.  Kursi yang sudah tidak tersedia tidak dapat dipilih.
4.  Maksimal **5 kursi** dalam satu order.
5.  Sistem menghitung harga berdasarkan data server.

Ketersediaan kursi diverifikasi kembali di server. Proses booking
menggunakan transaction dan locking database untuk membantu mencegah
benturan pemesanan kursi.

### 5.6 Checkout

Periksa: - trip, - kursi, - harga, - total order.

Lanjutkan checkout untuk membuat order.

### 5.7 Pembayaran

Setelah order dibuat, lanjutkan ke halaman pembayaran. Payment flow
menggunakan konfigurasi payment gateway yang tersedia, termasuk
integrasi Midtrans pada bagian yang telah disediakan.

Status pembayaran dapat diperbarui melalui mekanisme payment/webhook.

### 5.8 E-ticket

Setelah pembayaran berhasil: 1. Buka **My Orders/Pesanan Saya**. 2.
Pilih order. 3. Buka detail tiket. 4. E-ticket menampilkan kode/QR
token. 5. Token dapat digunakan untuk proses verifikasi tiket.

### 5.9 Profile dan Riwayat

Customer dapat membuka profile serta melihat daftar dan detail order
miliknya.

------------------------------------------------------------------------

## 6. Pengoperasian Sebagai Admin

Contoh akun demo admin yang tersedia pada seed:

``` text
admin@pocan.com
```

Password mengikuti konfigurasi seed/project dan tidak dicantumkan pada
dokumentasi publik.

### Dashboard

Dashboard memberikan akses ke area administrasi.

### Bus

Admin dapat mengelola: - nama bus, - tipe bus, - nomor kendaraan, -
kapasitas, - kursi bus.

### Bus Seats

Kursi terhubung dengan bus dan digunakan pada proses pemilihan kursi
customer.

### Route

Admin mengelola rute, termasuk kota asal dan kota tujuan serta informasi
perjalanan yang terkait.

### Trip

Admin mengelola: - bus, - route, - tanggal keberangkatan, - waktu
keberangkatan, - waktu kedatangan, - harga, - status trip.

Trip ditampilkan pada halaman **Jadwal & Tiket**.

### Order

Admin dapat melihat/mengelola order, termasuk: - customer, - kode
order, - trip, - kursi, - total harga, - status, - informasi
pembayaran, - expiration/hold bila berlaku.

### Customer

Admin dapat melihat data customer yang terdaftar.

### Audit Log

Aktivitas administrasi tertentu dicatat melalui audit log, seperti user,
action, description, IP address, dan waktu aktivitas.

------------------------------------------------------------------------

## 7. Alur Sistem

``` text
Homepage
   ↓
Pilih Asal + Tujuan + Tanggal
   ↓
Cari Tiket
   ↓
Jadwal & Tiket
   ↓
Pilih Trip
   ↓
Pilih Kursi
   ↓
Checkout
   ↓
Order
   ↓
Pembayaran
   ↓
Payment Berhasil
   ↓
E-ticket
   ↓
QR / Token Verification
```

------------------------------------------------------------------------

## 8. Struktur Database Utama

Entitas bisnis utama:

-   `users`
-   `buses`
-   `bus_seats`
-   `routes`
-   `trips`
-   `orders`
-   `order_items`
-   `payments`
-   `tickets`
-   `audit_logs`

Relasi utama:

``` text
USERS
  │
  └── ORDERS
        │
        ├── ORDER_ITEMS ─── BUS_SEATS ─── BUSES
        │
        ├── PAYMENTS
        │
        └── TICKETS

BUSES
  │
  ├── BUS_SEATS
  │
  └── TRIPS ─── ROUTES
```

Secara konsep: - User memiliki banyak Order. - Bus memiliki banyak Bus
Seat. - Bus digunakan oleh Trip. - Route memiliki banyak Trip. - Trip
memiliki order. - Order memiliki Order Item. - Order Item mengacu pada
kursi. - Order memiliki Payment. - Order Item menghasilkan Ticket. -
Aktivitas user/admin tertentu dapat dicatat pada Audit Log.

------------------------------------------------------------------------

## 9. Validasi dan Keamanan

Project menerapkan: - validasi input, - batas maksimal 5 kursi, -
perhitungan harga berdasarkan server, - transaction dan seat locking, -
authorization/IDOR protection, - Content Security Policy pada area yang
relevan, - QR/token ticket verification.

Customer tidak seharusnya dapat membuka order milik customer lain hanya
dengan mengganti identifier pada URL.

------------------------------------------------------------------------

## 10. Data Demo

Akun demo yang tersedia:

``` text
admin@pocan.com
budi@gmail.com
siti@gmail.com
ahmad@gmail.com
demo@pocan.com
```

Akun tersebut digunakan untuk demonstrasi login/account panel.

**Dummy booking/okupansi kursi tidak dibuat secara default.** Dengan
demikian, kursi dapat digunakan untuk simulasi pemilihan tanpa sengaja
dipenuhi oleh transaksi dummy.

------------------------------------------------------------------------

## 11. Responsive dan UI/UX

Website dibuat responsive untuk desktop, tablet, dan mobile.

Area UI utama: - navbar tunggal, - hero perjalanan, - search form, -
jadwal dan tiket, - bus/armada, - fasilitas, - bantuan/FAQ, - order dan
ticket, - admin panel.

Navbar final menggunakan **satu bar utama** dan warna konsisten. Tidak
menggunakan dua top bar, marquee, atau perubahan warna navbar ketika
scroll.

UI menggunakan identitas CAN Travel: - Deep Navy `#062A52` - Navy
`#0B3A70` - Primary Blue `#1268B3` - Bright Blue `#1685D8` - Accent
Orange `#F5A623` - Bright Orange `#FFB52E` - Light Background
`#F5F8FC` - Text `#102A43` - Subtext `#52667A` - Border `#D9E3EF`

Emoji tidak digunakan sebagai pengganti icon UI; icon interface
menggunakan icon/SVG yang sesuai.

------------------------------------------------------------------------

## 12. Testing

Pada tahap pengembangan yang telah dilakukan, test suite mencapai:

``` text
153 tests passed
563 assertions
0 failed
```

Angka tersebut adalah hasil pada tahap pengembangan dan dapat berubah
setelah ada perubahan kode.

Area yang diuji mencakup authentication, booking, seat selection, order,
payment, authorization, validation, ticket, admin, dan security.

------------------------------------------------------------------------

## 13. Catatan Sistem

### Payment

Payment gateway membutuhkan credential dan konfigurasi environment yang
sesuai untuk transaksi sebenarnya.

### WhatsApp

Gateway WhatsApp pihak ketiga belum menjadi bagian dari konfigurasi
provider produksi. Notifikasi yang tersedia mengikuti
implementasi/configuration project.

### Real-time Seat

Konsistensi kursi menggunakan database transaction, locking, expiration,
dan refresh. Sistem belum menggunakan WebSocket untuk sinkronisasi seat
secara live antar banyak perangkat.

------------------------------------------------------------------------

## 14. Kesesuaian dengan Spesifikasi Tugas

### 1. ERD

Database memiliki relasi untuk user, bus, kursi, route, trip, order,
item order, payment, ticket, dan audit log.

### 2. Laravel 10

Sistem dibuat dengan Laravel 10 dan menyediakan login, register, serta
order tiket.

### 3. Pengembangan Case

Pengembangan tambahan tetap berhubungan dengan proses pemesanan bus: -
seat selection, - checkout, - payment, - e-ticket, - QR/token
verification, - order history, - admin management, - audit log, -
responsive UI.

### 4. Migration

Database dibuat menggunakan Laravel migration.

### 5. Eloquent dan Validasi

Interaksi database menggunakan Eloquent ORM dan input penting
divalidasi.

### 6. GitHub

Project dapat diunggah ke GitHub. Sebelum commit, jangan masukkan `.env`
atau credential/payment secret ke repository.

------------------------------------------------------------------------

## 15. Checklist Demo Customer

-   [ ] Register
-   [ ] Login
-   [ ] Cari jadwal
-   [ ] Pilih asal
-   [ ] Pilih tujuan
-   [ ] Pilih tanggal
-   [ ] Pilih trip
-   [ ] Pilih kursi
-   [ ] Maksimal 5 kursi
-   [ ] Checkout
-   [ ] Pembayaran
-   [ ] Lihat order
-   [ ] Lihat e-ticket
-   [ ] Verifikasi QR/token
-   [ ] Profile

## 16. Checklist Demo Admin

-   [ ] Login admin
-   [ ] Dashboard
-   [ ] Bus
-   [ ] Bus seats
-   [ ] Route
-   [ ] Trip
-   [ ] Order
-   [ ] Customer
-   [ ] Audit log
-   [ ] Profile

------------------------------------------------------------------------

## 17. Kesimpulan

PO CAN Travel adalah sistem pemesanan tiket bus berbasis Laravel 10 yang
mencakup proses utama:

**Cari perjalanan → pilih trip → pilih kursi → checkout → pembayaran →
e-ticket.**

Sistem juga menyediakan area admin untuk mengelola armada, kursi, rute,
jadwal, order, customer, dan aktivitas administrasi.

Dokumentasi ini dibuat berdasarkan fitur project yang telah dikembangkan
dan tetap berpedoman pada spesifikasi awal tugas: **ERD database,
Laravel 10, login/register, order tiket, migration, Eloquent ORM,
validasi data, dan pengembangan case yang relevan.**
