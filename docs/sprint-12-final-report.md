# CAN TRAVEL — SPRINT 12 FINAL REPORT
## Laporan Akhir Penyelesaian Masalah Kursi, Booking, Checkout, Payment & E-Ticket

**Project:** PO-CAN_Creative_Travel (CAN Travel)  
**Tanggal:** 24 September 2026  
**Klasifikasi:** Core Booking Engine, UI/UX Excellence & Data Hygiene  
**Status Eksekusi:** SELESAI & PRODUCTION-READY (All 153 Tests Passed)

---

## 1. EXECUTIVE SUMMARY

Sprint 12 difokuskan untuk menyelesaikan kendala kritis pada sistem pemesanan tiket CAN Travel, khususnya:
1. Pengguna tidak dapat memilih kursi kosong pada halaman `/trips/{trip}` karena deadlock Alpine CSP.
2. Tampilan denah kursi (*seat map*) yang terlihat penuh atau berstatus HELD/BOOKED secara tidak semestinya akibat polusi data dummy dari test suite dan seeder.
3. Alur checkout yang terblokir karena tombol pemesanan tidak aktif.
4. Jaminan sinkronisasi end-to-end dari pemilihan kursi &rarr; pengisian identitas penumpang &rarr; pembuatan order atomik &rarr; pembayaran simulasi/gateway &rarr; penerbitan tiket dan e-ticket interaktif.

Melalui investigasi menyeluruh dan perbaikan komprehensif, seluruh komponen telah dipulihkan dan dioptimalkan ke standar aplikasi kelas komersial (*commercial-grade*).

---

## 2. PROJECT STATUS DASHBOARD

```text
============================================================
PROJECT STATUS — SPRINT 12 AUDIT & REMEDIATION
============================================================
Seat Selection:          PASS (Real-time reactive, CSP-safe)
Checkout:                PASS (Trip, seat IDs, and passenger form intact)
Order Creation:          PASS (Atomic transaction, lockForUpdate race guard)
Payment Flow:            PASS (Midtrans snap & sandbox simulator ready)
Ticket:                  PASS (Official CAN Travel Boarding Pass & QR)
Dummy Data:              CLEAN (901 stray orders purged; demo accounts safe)
Responsive:              PASS (320px to 1440px+ zero overflow)
Security:                PASS (CSP compliant, IDOR protected, strict pricing)
Automated Tests:         153 passed / 0 failed (563 assertions)
Build:                   PASS (Vite production bundle built in 1.69s)
Pint:                    PASS (Laravel Pint 0 code style issues)
Migration:               PASS (15 migrations applied cleanly)
Routes:                  PASS (58 routes active, no collisions)
Browser QA:              PASS (End-to-end booking flow verified)
============================================================
```

---

## 3. ROOT CAUSE SUMMARY & RECTIFICATION

### 1. Frontend: Deadlock Alpine CSP pada Seat Selection
- **Penyebab:** Layout utama menggunakan `@alpinejs/csp` untuk menegakkan aturan keamanan CSP ketat tanpa `unsafe-eval`. Skrip denah kursi sebelumnya mencoba mengevaluasi inline handler di dalam `x-data`. Ketika fallback JavaScript dijalankan, pengecekan `window.Alpine` mendeteksi objek Alpine yang telah dimuat, sehingga kode fallback mengasumsikan Alpine telah mengelola state, padahal array kursi kosong (`[]`).
- **Solusi:** Denah kursi di [resources/views/trips/show.blade.php](file:///c:/laragon/www/PO-CAN_Creative_Travel/resources/views/trips/show.blade.php) ditulis ulang menggunakan arsitektur Vanilla JS yang tangguh (*resilient progressive enhancement*). Event listener dipasang langsung pada tombol kursi, mengelola class visual secara langsung, memperbarui counter, mengkalkulasi harga server-authoritative, mengisi input hidden form `seat_ids`, dan mengaktifkan tombol checkout tanpa ketergantungan pada evaluasi dinamis.

### 2. Database: Polusi Kursi oleh Missing Transaction pada Test Suite
- **Penyebab:** File [tests/Feature/BusBookingSystemTest.php](file:///c:/laragon/www/PO-CAN_Creative_Travel/tests/Feature/BusBookingSystemTest.php) tidak menggunakan trait `DatabaseTransactions`. Karena koneksi default pada `phpunit.xml` mengarah ke database MySQL nyata, setiap kali test dijalankan ratusan order dan tiket dummy berstatus pending/paid tertinggal di tabel MySQL, menahan kursi 4A-7D pada Trip 3. Seeder juga membuat dummy booking awal untuk 1A-1B.
- **Solusi:**
  - Ditambahkan `use DatabaseTransactions;` pada `BusBookingSystemTest.php`.
  - [database/seeders/DatabaseSeeder.php](file:///c:/laragon/www/PO-CAN_Creative_Travel/database/seeders/DatabaseSeeder.php) disesuaikan agar pembuatan demo transaksi bersifat opt-in (`seedDemoBookings()`, default `false`).
  - Dibuat command artisan [CleanDummyTransactionsCommand.php](file:///c:/laragon/www/PO-CAN_Creative_Travel/app/Console/Commands/CleanDummyTransactionsCommand.php) yang membersihkan 901 order liar dan mengembalikan seluruh kursi ke status AVAILABLE sambil mempertahankan seluruh akun demo login.

---

## 4. DAFTAR PERUBAHAN CODEBASE

1. `resources/views/trips/show.blade.php`
   - Implementasi state denah kursi: AVAILABLE (putih/border), SELECTED (biru/checkmark), HELD (amber), BOOKED (slate/disabled).
   - Penambahan integrasi live dynamic accessibility region (`aria-live="polite"`).
   - Sinkronisasi desktop dan mobile floating booking bar.
2. `database/seeders/DatabaseSeeder.php`
   - Prinsip **DUMMY USER ≠ DUMMY SEAT OCCUPANCY**: akun demo tetap tersedia (`demo@pocan.com`, `admin@pocan.com`, dll.), tetapi tidak otomatis menahan kursi atau membuat transaksi palsu.
3. `tests/Feature/BusBookingSystemTest.php`
   - Ditambahkan trait `DatabaseTransactions` untuk isolasi pengujian tanpa efek samping.
4. `app/Console/Commands/CleanDummyTransactionsCommand.php`
   - Command pembersihan aman: `php artisan bookings:clean-dummy-transactions`.
5. `app/Http/Controllers/BookingController.php`
   - Ditambahkan validasi batas maksimal 5 kursi pada endpoint `checkout()`.
6. `tests/Feature/Sprint12SeatBookingTest.php`
   - Dibuat 18 skenario pengujian komprehensif menguji flow seat &rarr; checkout &rarr; payment &rarr; e-ticket &rarr; security.

---

## 5. HASIL VERIFIKASI QUALITY GATE

- **PHPUnit / Pest Test Suite:**  
  `153 passed, 0 failed, 563 assertions (Duration: ~14.86s)`
- **Laravel Pint Code Linter:**  
  `vendor\bin\pint --test` &rarr; Passed (0 violations)
- **Vite Asset Bundler:**  
  `npm run build` &rarr; Built successfully (52.79 kB CSS, 51.38 kB JS)
- **Database Migrations:**  
  `php artisan migrate:status` &rarr; 15/15 migrations Ran
- **Routing Integrity:**  
  `php artisan route:list` &rarr; 58 routes checked and verified
- **Git Tree Cleanliness:**  
  `git diff --check` &rarr; Passed cleanly (0 whitespace/conflict errors)

---

## 6. KESIMPULAN

Sistem pemesanan tiket, denah kursi interaktif, alur checkout, transaksi pembayaran, dan penerbitan tiket CAN Travel kini beroperasi dengan andal, aman, dan konsisten di seluruh lapisan arsitektur. Data dummy telah dipisahkan dengan bersih antara akun pengguna demo dan data operasional transaksi, menjamin kenyamanan pengguna dan stabilitas platform.
