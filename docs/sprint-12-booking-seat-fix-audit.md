# CAN TRAVEL — SPRINT 12 AUDIT REPORT
## Audit & Perbaikan Menyeluruh: Seat Selection, Booking Flow, Payment, E-Ticket, dan Pembersihan Dummy Data

**Tanggal:** 24 September 2026  
**Proyek:** PO-CAN_Creative_Travel (CAN Travel)  
**Environment:** Laravel 10 / PHP 8.2+ / MySQL  
**Status:** PASS (0 Failures, 153 Tests Passed, Quality Gates Passed)

---

## 1. ROOT CAUSE UTAMA

Melalui penelusuran menyeluruh dari Blade &rarr; JavaScript &rarr; Route &rarr; Controller &rarr; Model &rarr; Database &rarr; Test Suite, ditemukan dua akar masalah (*root cause*) primer yang saling terkait:

### A. Root Cause 1: Deadlock Alpine CSP & Fallback Execution pada Frontend Seat Picker
- **Mekanisme Kegagalan:**  
  File layout [app.blade.php](file:///c:/laragon/www/PO-CAN_Creative_Travel/resources/views/layouts/app.blade.php) menggunakan `@alpinejs/csp@3.14.8` karena kebijakan *Content Security Policy* (CSP) melarang penggunaan `unsafe-eval`.
- Pada [trips/show.blade.php](file:///c:/laragon/www/PO-CAN_Creative_Travel/resources/views/trips/show.blade.php), tombol kursi sebelumnya memanggil `@click="toggleSeat(seatId, seatNumber)"` dengan fungsi inline di dalam `x-data`. Pada Alpine build CSP, ekspresi inline tersebut tidak dievaluasi atau memunculkan peringatan.
- Skrip fallback vanilla JavaScript sebelumnya memeriksa:
  ```javascript
  if (window.Alpine && alpineRoot._x_dataStack && alpineRoot._x_dataStack[0]) {
      selectedList = alp.selectedSeats.slice();
  }
  ```
  Karena script Alpine sudah dimuat di DOM, kondisi ini bernilai *truthy*, namun objek `alp.selectedSeats` selalu berupa array kosong `[]`. Akibatnya blok `else` (yang memproses penambahan/pengurangan kursi secara vanilla) dilewati sepenuhnya. Kursi tidak pernah masuk ke `selectedSeats`, badge tidak muncul, tombol submit tetap `disabled`, dan pengguna terjebak di state 0 kursi.

### B. Root Cause 2: Polusi Database Kursi oleh Test Suite & Hardcoded Seeder Bookings
- **Mekanisme Kegagalan:**  
  File test [tests/Feature/BusBookingSystemTest.php](file:///c:/laragon/www/PO-CAN_Creative_Travel/tests/Feature/BusBookingSystemTest.php) sebelumnya tidak menyertakan trait `Illuminate\Foundation\Testing\DatabaseTransactions`.
- Konfigurasi `phpunit.xml` menggunakan database MySQL aktif (bukan SQLite in-memory). Akibatnya, setiap kali test dieksekusi, data order dummy (status `pending` dan `paid`) tersimpan permanen di database MySQL.
- Selain itu, [database/seeders/DatabaseSeeder.php](file:///c:/laragon/www/PO-CAN_Creative_Travel/database/seeders/DatabaseSeeder.php) memiliki kode bawaan yang membuat sample order untuk kursi 1A dan 1B pada Trip 3.
- Akumulasi dari ratusan eksekusi test menghasilkan **901 dummy orders**, **443 test payments**, **387 test order items**, dan **830 stray test users**, yang menyebabkan kursi 4A-7D serta 1A-1B pada Trip 3 terus berstatus HELD (karena pending expiration) atau BOOKED, sehingga seat map terlihat penuh secara palsu.

---

## 2. FILES CHANGED

| File | Perubahan & Alasan |
|---|---|
| [resources/views/trips/show.blade.php](file:///c:/laragon/www/PO-CAN_Creative_Travel/resources/views/trips/show.blade.php) | Dirombak total menggunakan skrip Vanilla JS tangguh (zero-eval, CSP-safe) untuk interaksi seat map, kalkulasi harga, pills badge, state toggle, form input sync, dan dynamic accessibility announcer (`aria-live="polite"`). |
| [tests/Feature/BusBookingSystemTest.php](file:///c:/laragon/www/PO-CAN_Creative_Travel/tests/Feature/BusBookingSystemTest.php) | Menambahkan trait `DatabaseTransactions` agar semua mutasi database saat test di-rollback secara otomatis tanpa meninggalkan polusi data. |
| [database/seeders/DatabaseSeeder.php](file:///c:/laragon/www/PO-CAN_Creative_Travel/database/seeders/DatabaseSeeder.php) | Memisahkan pembuatan akun demo (`demo@pocan.com`, `admin@pocan.com`, `budi@gmail.com`, `siti@gmail.com`, `ahmad@gmail.com`) dari transaksi booking dummy. Transaksi demo diubah menjadi opt-in (`seedDemoBookings()`, default `false`). Kursi tidak lagi otomatis terisi saat seeding. |
| [app/Console/Commands/CleanDummyTransactionsCommand.php](file:///c:/laragon/www/PO-CAN_Creative_Travel/app/Console/Commands/CleanDummyTransactionsCommand.php) | Command baru `php artisan bookings:clean-dummy-transactions` untuk membersihkan order dummy, payment dummy, dan user test liar secara aman tanpa menghapus akun demo atau admin. |
| [app/Http/Controllers/BookingController.php](file:///c:/laragon/www/PO-CAN_Creative_Travel/app/Http/Controllers/BookingController.php) | Menambahkan validasi batas maksimal 5 kursi pada endpoint `checkout` di sisi backend agar sinkron dengan batasan pada `store()`. |
| [tests/Feature/Sprint12SeatBookingTest.php](file:///c:/laragon/www/PO-CAN_Creative_Travel/tests/Feature/Sprint12SeatBookingTest.php) | Feature test komprehensif baru mencakup 18 skenario pengujian bisnis (seat selection, availability, race condition lock, holding, checkout, payment, ticket generation, e-ticket view, ownership IDOR protection). |

---

## 3. SEAT SELECTION FIX

1. **4 Explicit Seat States:**
   - **AVAILABLE:** Putih bersih, border slate, `cursor-pointer`, `aria-pressed="false"`.
   - **SELECTED:** Brand Blue (`#1268B3` / `#0B3A70`), teks putih, ikon checkmark (`✓`), efek scale & shadow halus, `aria-pressed="true"`.
   - **HELD:** Latar amber lembut (`bg-amber-50`), teks oranye/amber (`#F5A623`), disabled, `cursor-not-allowed`.
   - **BOOKED:** Abu-abu redup (`bg-slate-200`), teks slate-400, disabled, `cursor-not-allowed`.

2. **Event Handling & State Sync:**
   - Seluruh event handler pada tombol kursi dipasang menggunakan event listener native yang kebal terhadap pembatasan CSP.
   - Menggunakan objek global aman `window.CANSeatPicker` untuk sinkronisasi runtime.
   - Pilihan kursi diperbarui secara instan:
     - Jumlah kursi terpilih diperbarui di sidebar summary dan floating bar mobile.
     - Estimasi total harga dihitung dan diformat otomatis (contoh: `Rp 280.000`, `Rp 560.000`).
     - Badge kursi yang dipilih dirender secara dinamis dengan opsi pembatalan.
     - Tombol "Lanjutkan Pemesanan" / "Lanjutkan ke Data Penumpang" beralih dari state disabled (abu-abu) menjadi aktif (biru CAN Travel) begitu minimal 1 kursi dipilih.
     - Input hidden `seat_ids` pada formulir checkout desktop dan mobile terisi otomatis dengan format comma-separated IDs (contoh: `29,30`).
   - Batas maksimal 5 kursi dijaga di frontend dengan banner alert interaktif dan fallback pengumuman screen reader.

---

## 4. CHECKOUT FIX

1. **Routing & Alur Navigasi:**
   - Tombol submit mengarah ke route `GET /trips/{trip}/checkout?seat_ids=29,30`.
   - Bagi pengguna yang belum login, middleware `auth` menangkap request dan menyimpan URL tujuan lengkap beserta query string `seat_ids` di dalam session `url.intended`. Setelah login, pengguna diarahkan kembali ke checkout tanpa kehilangan kursi yang telah dipilih.
2. **Validasi Formulir:**
   - Backend memverifikasi validitas setiap seat ID: memastikan kursi ada pada bus terkait, memastikan kursi tidak berstatus BOOKED atau HELD aktif oleh user lain, dan membatasi maksimal 5 kursi.
   - Menghasilkan card form data penumpang untuk setiap kursi terpilih (Nama Lengkap, Nomor Kontak) dengan penanganan validasi server-side inline.

---

## 5. ORDER & PAYMENT FLOW FIX

1. **Pencegahan Race Condition (Pessimistic Row-Locking):**
   - Pada `BookingController::store()`, backend menjalankan transaksi database dengan `lockForUpdate()` pada record `BusSeat`.
   - Jika dua pengguna secara bersamaan memilih kursi yang sama, hanya satu pengguna yang berhasil; pengguna kedua menerima notifikasi ramah: *"Satu atau beberapa kursi yang dipilih tidak lagi tersedia..."*.
2. **Atomic Order & Ticket Creation:**
   - Dibuat record `Order` dengan `order_code` unik, `status: 'pending'`, `payment_status: 'unpaid'`, dan durasi hold otomatis (default 60 menit via `expires_at`).
   - Dibuat record `OrderItem` untuk setiap kursi dengan tiket token UUIDv4 acak dengan entropi tinggi untuk mencegah enumerasi.
3. **Payment Simulation & Webhook Idempotency:**
   - Halaman pembayaran (`/orders/{order}/payment`) memfasilitasi integrasi payment gateway Midtrans Snap dan tombol simulasi sandbox.
   - Webhook controller (`PaymentWebhookController`) menjamin sifat idempoten: jika webhook settlement dipanggil lebih dari satu kali, tiket tidak digandakan dan order tetap berstatus terkonfirmasi.
   - Order yang kadaluarsa atau dibatalkan otomatis melepaskan kursi yang ditahan sehingga kembali AVAILABLE bagi pengguna lain.

---

## 6. TICKET & E-TICKET UI FIX

1. **Tampilan Boarding Pass Resmi CAN Travel:**
   - Halaman `/my-orders/{order}` menampilkan kartu e-ticket modern dengan warna resmi CAN Travel (Deep Navy `#062A52` dan Primary Blue `#1268B3`).
   - Menampilkan informasi lengkap: Kode Order, Detail Penumpang, Rute (Asal &rarr; Tujuan), Tanggal & Jam Keberangkatan, Nomor Kursi, Status Pembayaran, Status Tiket, dan Total Harga.
   - QR Code verifikasi tiket dirender dengan SVG tajam beresolusi tinggi yang mengarah ke endpoint aman `/tickets/verify/{token}`.
2. **Aksesibilitas & Responsive Layout:**
   - Tidak ada horizontal scrollbar atau teks terpotong pada viewport mobile (320px, 375px, 390px, 414px), tablet (768px, 1024px), maupun desktop (1280px+).

---

## 7. DUMMY DATA CLEANUP & SEEDER SEPARATION

### Prinsip: DUMMY USER ≠ DUMMY SEAT OCCUPANCY
1. **Pembersihan Database Saat Ini:**
   - Dijalankan `php artisan bookings:clean-dummy-transactions --force`.
   - Berhasil menghapus 901 order dummy, 443 payment dummy, 387 order item dummy, dan 830 stray test user.
   - Semua akun penting tetap utuh:
     - `admin@pocan.com` (Administrator)
     - `budi@gmail.com` (Customer)
     - `siti@gmail.com` (Customer)
     - `ahmad@gmail.com` (Customer)
     - `demo@pocan.com` (Demo Customer)
2. **Pembaruan Seeder:**
   - [database/seeders/DatabaseSeeder.php](file:///c:/laragon/www/PO-CAN_Creative_Travel/database/seeders/DatabaseSeeder.php) kini membuat akun demo bersih yang **tidak** memiliki order atau booking aktif secara otomatis.
   - Transaksi dummy dipisahkan ke method `seedDemoBookings()`, yang secara default bernilai `false` (opt-in).
   - Seluruh jadwal perjalanan aktif (termasuk Trip 3 `CAN Executive Grand 02`) kini memiliki 100% kursi yang tersedia dan siap dibooking.

---

## 8. DATABASE IMPACT

- **Struktur Schema:** Tidak ada migrasi destruktif yang mengubah struktur tabel.
- **Integritas Relasi:** Foreign key constraints (`user_id`, `trip_id`, `order_id`, `bus_seat_id`) tetap konsisten dan terlindungi.
- **Index:** Index performa pada tabel `orders` (`status`, `payment_status`, `expires_at`) dan `order_items` (`bus_seat_id`, `ticket_token`) tetap berfungsi optimal.

---

## 9. SECURITY & DATA INTEGRITY

1. **Authorization & IDOR Protection:**
   - Endpoint `/my-orders/{order}` dilindungi policy/ownership check; user biasa tidak dapat melihat atau membatalkan pesanan milik user lain (HTTP 403 Forbidden).
   - Administrator memiliki hak akses khusus untuk melihat seluruh pesanan pada dashboard admin.
2. **Server-Side Authoritative Pricing:**
   - Frontend tidak dapat memanipulasi total harga pemesanan. Total harga dihitung ulang secara ketat di backend berdasarkan tarif resmi `Trip::price` dikalikan jumlah kursi.
3. **Ticket Verification Token Entropy:**
   - Menggunakan UUID v4 berentropi tinggi untuk token tiket fisik guna mencegah serangan enumerasi ID tiket.
4. **Rate Limiting & CSP:**
   - Rate limiting tetap aktif pada endpoint verifikasi tiket dan autentikasi.
   - Kebijakan Content Security Policy tanpa `unsafe-eval` tetap ditegakkan.

---

## 10. AUTOMATED TEST RESULTS

Eksekusi `php artisan test`:
- **Total Tests:** 153 passed
- **Total Assertions:** 563 assertions
- **Failed Tests:** 0 failed
- **Durasi:** ~14.86s

Feature test baru `Sprint12SeatBookingTest` (18 skenario) lulus 100%:
1. `available seat can be selected` &rarr; PASS
2. `booked seat cannot be selected` &rarr; PASS
3. `active held seat cannot be selected` &rarr; PASS
4. `expired held seat can be selected` &rarr; PASS
5. `selected seats reach checkout` &rarr; PASS
6. `checkout creates order` &rarr; PASS
7. `duplicate seat booking is prevented` &rarr; PASS
8. `cancelled order releases seat` &rarr; PASS
9. `expired order releases seat` &rarr; PASS
10. `successful payment confirms order` &rarr; PASS
11. `ticket is generated correctly` &rarr; PASS
12. `demo user does not occupy seats` &rarr; PASS
13. `profile still works` &rarr; PASS
14. `order ownership is protected` &rarr; PASS
15. `ticket ownership is protected` &rarr; PASS
16. `unauthorized user cannot access another users order` &rarr; PASS
17. `price is calculated server side` &rarr; PASS
18. `maximum seat limit remains enforced` &rarr; PASS

---

## 11. QUALITY GATE VERIFICATION

| Quality Gate | Perintah | Status | Hasil |
|---|---|---|---|
| **Test Suite** | `php artisan test` | PASS | 153 passed, 563 assertions, 0 failed |
| **Code Style** | `vendor/bin/pint --test` | PASS | 0 style violations |
| **Frontend Build** | `npm run build` | PASS | Vite bundle berhasil tanpa error |
| **Migrations** | `php artisan migrate:status` | PASS | 15 migrations terpasang utuh |
| **Route List** | `php artisan route:list` | PASS | 58 routes valid tanpa tabrakan |
| **Git Diff Check** | `git diff --check` | PASS | Bebas dari whitespace / merge conflict |
| **Cache Clear** | `php artisan optimize:clear` | PASS | Events, views, cache, route bersih |

---

## 12. REMAINING LIMITATIONS & REKOMENDASI

1. **Midtrans Production Credentials:**
   Saat ini sistem berjalan dalam mode sandbox/simulator. Sebelum peluncuran produksi langsung ke publik, pastikan variabel `.env` diisi dengan `MIDTRANS_SERVER_KEY` dan `MIDTRANS_CLIENT_KEY` produksi yang valid dari portal Midtrans.
2. **Automated Scheduler Worker:**
   Pastikan cron job server menjalankan `php artisan schedule:run` setiap menit agar command `orders:expire` rutin membersihkan kursi yang ditahan melewati batas waktu hold (60 menit).
