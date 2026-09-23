# CAN Travel — Sprint 3: Production-Ready Architecture & Logic Walkthrough

Aplikasi web pemesanan tiket bus profesional **CAN Travel** telah berhasil ditingkatkan pada **Sprint 3** menuju sistem *production-ready* yang tangguh, aman, dan berkinerja tinggi.

---

## 1. Ringkasan Peningkatan Utama

### A. Mesin Pencarian Jadwal & Ketersediaan Kursi (Tasks 1 & 2)
- **Database-Authoritative Query**: Seluruh filter (asal, tujuan, tanggal keberangkatan, kelas bus, rentang harga `min_price`/`max_price`) dieksekusi secara native pada level basis data MySQL menggunakan indeks yang tersedia.
- **Validasi Keberangkatan**: Hanya perjalanan berstatus `scheduled` dengan waktu berangkat di masa depan (`departure_at > now()`) yang ditampilkan kepada publik. Jadwal yang telah lewat atau dibatalkan otomatis disaring.
- **Seat Availability Precision**: Kursi dianggap terpesan hanya jika dimiliki oleh pesanan aktif (`paid` atau `unpaid` yang belum melewati `expires_at`). Pesanan yang berstatus `cancelled` atau `expired` seketika melepaskan kursi.
- **Eager Booked Seats Count (Bebas N+1)**: Ditambahkan scope `withBookedSeatsCount()` pada model `Trip` yang menghitung okupansi kursi secara langsung dalam query agregasi utama.

### B. Siklus Pesanan & Batas Waktu Pembayaran (Tasks 3, 4, 5, 7)
- **Siklus Status Terstandarisasi**: Mengatur transisi status pesanan (`pending` → `confirmed` / `cancelled`) dan status pembayaran (`unpaid` → `paid` / `expired` / `refunded`).
- **Verifikasi Kedaluwarsa Backend-Authoritative**: Waktu kedaluwarsa 2 jam diverifikasi secara ketat di sisi backend. Apabila waktu habis, proses pembayaran diblokir, status diperbarui menjadi kedaluwarsa, dan kursi dikembalikan.
- **Idempotensi Pembayaran**: Pengiriman ulang form pembayaran pada pesanan yang telah lunas ditangani dengan aman tanpa menduplikasi data atau menimbulkan *double-charge*.
- **Pembatalan Pesanan Transaksional**: Pelanggan dapat membatalkan pesanan pending miliknya secara transaksional (`DB::transaction`), yang seketika mengembalikan ketersediaan kursi ke sistem.

### C. Alur Operasional Administrator (Tasks 8, 9, 10)
- **Dashboard Metrik Riil**: Menghitung secara langsung dari database: Total Pendapatan, Pesanan Hari Ini, Total Pesanan, Pending Pembayaran, Pesanan Dibatalkan, Jadwal Aktif, Keberangkatan Hari Ini, Total Armada, dan Total Pelanggan.
- **Penyaringan Pesanan Admin**: Mendukung pencarian teks (kode order, nama pemesan, email, HP, nama penumpang), filter status pesanan, filter status pembayaran, dan filter tanggal transaksi.
- **Integritas Jadwal & Pencegahan Kerusakan Relasi**: Menolak penghapusan jadwal yang sudah memiliki riwayat pemesanan pelanggan untuk menjaga integritas data audit keuangan.

### D. Konsistensi Branding & Keamanan (Tasks 13, 17, 18)
- **Pembersihan Brand Menyeluruh**: Menghilangkan seluruh sisa penyebutan "PO CAN Travel" pada `AuthController`, `AdminMiddleware`, `web.php`, dan `README.md`.
- **Standarisasi Kode Pesanan**: Format kode pesanan diperbarui menjadi `CAN-YYYYMMDD-XXXXX`.
- **Dokumentasi Komprehensif**: Berkas `README.md` diperbarui lengkap dengan panduan instalasi, arsitektur, flow operasional, dan pengujian.

---

## 2. Hasil Verifikasi Otomatis

### A. PHPUnit Feature & Unit Tests
```bash
php artisan test
```
Hasil: **23 passed (80 assertions) — 100% Green (0 skipped, 0 failed)**.
- Mencakup seluruh 16 skenario pengujian bisnis yang diwajibkan:
  1. `test_trip_search_filters_correctly`
  2. `test_unavailable_trip_is_not_bookable`
  3. `test_expired_trip_cannot_be_booked`
  4. `test_unavailable_seat_cannot_be_selected`
  5. `test_cancelled_order_releases_seats`
  6. `test_expired_order_releases_seats`
  7. `test_customer_cannot_access_another_customer_order`
  8. `test_customer_cannot_modify_another_customer_order`
  9. `test_payment_cannot_be_repeated_idempotency`
  10. `test_expired_payment_cannot_be_completed`
  11. `test_invalid_order_status_transition_prevented`
  12. `test_admin_order_filtering_by_status_and_date`
  13. `test_admin_authorization_enforced`
  14. `test_order_creation_calculates_price_server_side`
  15. `test_multiple_seat_booking_creates_manifest_records`
  16. `test_concurrent_seat_booking_is_prevented`
  - Ditambah pengujian registrasi, login, dan halaman publik.

### B. Standar Kode (Laravel Pint)
```bash
vendor/bin/pint --test
```
Hasil: **PASSED** (Semua berkas mematuhi standar PSR-12 / Laravel).

### C. Kompilasi Aset Frontend (Vite)
```bash
npm run build
```
Hasil: **PASSED** (Terbangun dalam 2.00 detik tanpa error).
