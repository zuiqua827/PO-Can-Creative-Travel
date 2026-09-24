# CAN Travel Sprint 10 Final Report
**Sprint 10: Product Optimization, UI/UX Excellence, Feature Completeness & Production Polish**  
**Date:** September 2026  
**Status:** COMPLETE  
**Repository Baseline:** Laravel 10.50.3, PHP 8.4.16, MySQL, Blade, Alpine.js CSP, Tailwind CSS 3, Vite  

---

## 1. Executive Summary

Sprint 10 berfokus pada pengujian mendalam (*deep audit*), perbaikan bug tersembunyi, penyempurnaan UI/UX, aksesibilitas WCAG 2.1 AA, eliminasi dead-end routes, pencegahan human error pada administrasi operasional, verifikasi cross-device visual responsiveness, dan jaminan integritas data serta keamanan produksi.

Semua baseline kualitas sebelumnya dipertahankan tanpa regresi. Seluruh pengujian otomatis bertambah dari 115 tes menjadi **125 tes (462 assertions)** dengan tingkat kelulusan 100%. Tidak ada operasi database yang bersifat destruktif.

---

## 2. Bugs Discovered & Fixed

### 2.1 Bug 1: Sisa Native `window.alert()` pada Progressive Fallback Script (P1)
- **Problem:** Pada `resources/views/trips/show.blade.php` (baris 527), fallback event listener `document.addEventListener('seat-limit-exceeded', ...)` masih menggunakan pemanggilan native `window.alert('Maksimal pemesanan adalah 5 kursi...')`.
- **Root Cause:** Sprint 9 mengganti alert Alpine pada baris 417 namun melewatkan script fallback vanilla JS pada baris 527.
- **Fix:** Menghapus native `alert()` dan menggantikannya dengan pesan interaktif in-DOM `#max-seat-alert-box` yang terhubung dengan `aria-live="polite"` untuk pembaca layar.
- **Verification:** Automated regex scanner memastikan 0 kemunculan `alert(` di seluruh direktori `resources/views`.

### 2.2 Bug 2: Missing HTML Closing Tag `</footer>` pada Layout Utama (P2)
- **Problem:** Berkas `resources/views/layouts/app.blade.php` membuka tag `<footer>` pada baris 112 namun tag penutup `</footer>` terlewat sebelum tag `<script>`.
- **Root Cause:** Kesalahan markup struktural saat penyatuan footer di sprint sebelumnya.
- **Fix:** Menambahkan `</footer>` penutup yang valid secara semantik.

### 2.3 Bug 3: Dead-End Route `/dashboard`, `/orders`, dan `/orders/{order}` (P2)
- **Problem:** Pengguna atau tautan eksternal yang membuka URL standar `/dashboard`, `/orders`, atau `/orders/{order}` mengalami HTTP 404 Not Found karena rute internal customer menggunakan `/my-orders`.
- **Root Cause:** Perbedaan penamaan konvensi rute antara rute default auth Laravel dan endpoint kustom aplikasi.
- **Fix:** Menambahkan redirect alias yang cerdas dan aman di `routes/web.php`:
  - `/dashboard`: otomatis diarahkan ke `admin.dashboard` jika role admin, atau `orders.index` jika customer.
  - `/orders`: dialihkan ke `/my-orders`.
  - `/orders/{order}`: dialihkan ke `/my-orders/{order}`.

### 2.4 Bug 4: Inactive Data Variable `$recentOrders` pada Profile Page (P2)
- **Problem:** `ProfileController::edit()` memuat query `$recentOrders` lengkap dengan eager loading `trip.route` dan limit 5 pesanan, namun tampilan `resources/views/profile/edit.blade.php` tidak merendernya.
- **Root Cause:** Blade template profil belum mengintegrasikan kartu riwayat pesanan.
- **Fix:** Menambahkan kartu *Pesanan Terakhir* di bilah samping profil akun pengguna dengan status pesanan, rute, tanggal keberangkatan, dan tautan langsung ke detail tiket.

### 2.5 Bug 5: Tanpa Konfirmasi Saat Pembatalan Pesanan Manual oleh Admin (P1)
- **Problem:** Pada `resources/views/admin/orders/show.blade.php`, admin dapat memilih status `cancelled` dan langsung menyimpannya tanpa konfirmasi dialog, berisiko membatalkan tiket penumpang secara tidak sengaja.
- **Root Cause:** Form update status pesanan tidak memiliki event listener verifikasi untuk perubahan status destruktif.
- **Fix:** Menambahkan onsubmit confirmation guard khusus jika status yang dipilih adalah `cancelled`.

### 2.6 Bug 6: Dead Boilerplate File `resources/views/welcome.blade.php` (P3)
- **Problem:** Terdapat berkas default Laravel `welcome.blade.php` berukuran 28 KB yang tidak pernah dipakai karena aplikasi menggunakan `home.blade.php`.
- **Fix:** Menghapus berkas tersebut secara bersih untuk menjaga kerapian codebase.

---

## 3. UI/UX & Design System Improvements

1. **Aksesibilitas WCAG 2.1 AA "Skip to Main Content":**
   - Menambahkan tautan aksesibilitas tersembunyi (*visible on focus*) `#main-content` pada layout customer (`layouts/app.blade.php`) dan layout admin (`layouts/admin.blade.php`).
   - Memastikan navigasi keyboard (`Tab`) langsung memindahkan fokus ke area konten utama.
2. **Accessible Form Error State pada Checkout:**
   - Pada `resources/views/booking/checkout.blade.php`, setiap input nama dan telepon penumpang dilengkapi dengan atribut aksesibilitas standar: `id`, `for`, `aria-invalid`, dan `aria-describedby` ke elemen pesan error.
3. **Fitur Salin Kode Pesanan 1-Klik:**
   - Pada `resources/views/orders/show.blade.php`, ditambahkan tombol aksi interaktif dengan umpan balik visual (*tooltip* dan perubahan ikon) untuk menyalin kode pesanan ke clipboard pengguna.
4. **Optimalisasi Grid & Legenda Kursi pada Layar Sangat Sempit (320px–375px):**
   - Menyelaraskan ukuran font legenda dan tata letak flex-wrap agar tidak pernah memicu horizontal scrollbar / layout broken pada perangkat mobile ultra-kompak.

---

## 4. Security & Data Integrity

1. **Zero Secret Exposure & Scrubbing:**
   - Memastikan tidak ada token, password, atau credential yang terpapar di template Blade, URL query, maupun log audit.
2. **Server-Authoritative Calculation:**
   - Seluruh nominal pembayaran dan status ketersediaan kursi diverifikasi langsung oleh server di level database transaction.
3. **CSRF & Double-Submit Protection:**
   - Semua form transaksi dilindungi token CSRF dan disable-on-submit attribute untuk mencegah transaksi ganda.
4. **Admin Protection:**
   - Middleware `auth` dan `admin` melindungi seluruh operasi manajemen armada, rute, jadwal, dan pesanan.

---

## 5. Performance & Resource Optimization

1. **Asset Build Production:**
   - Vite memproduksi aset minified (CSS: 50.80 kB, JS: 51.38 kB) dengan gzip footprint kurang dari 20 kB.
2. **Query Optimization:**
   - Penggunaan eager loading konsisten (`with(['trip.route', 'trip.bus', 'orderItems'])`) menghilangkan potensi N+1 query.
3. **No Unused Code:**
   - Penghapusan template Laravel boilerplate lama `welcome.blade.php`.

---

## 6. Automated Testing Suite

Sebanyak **10 automated test cases baru** ditambahkan dalam `tests/Feature/Sprint10OptimizationTest.php`:
1. `test_guest_is_redirected_to_login_from_dashboard_and_orders`: Verifikasi proteksi auth rute alias.
2. `test_customer_accessing_dashboard_redirects_to_my_orders`: Verifikasi redirect cerdas customer.
3. `test_admin_accessing_dashboard_redirects_to_admin_dashboard`: Verifikasi redirect cerdas admin.
4. `test_orders_alias_redirects_customer_to_my_orders`: Verifikasi alias `/orders`.
5. `test_order_detail_alias_redirects_customer_to_my_orders_show`: Verifikasi alias `/orders/{id}`.
6. `test_profile_page_renders_recent_orders_card`: Verifikasi integrasi data pesanan terakhir di profil.
7. `test_skip_to_content_links_present_in_public_and_admin_layouts`: Verifikasi WCAG skip link di publik dan admin.
8. `test_zero_native_alert_calls_in_any_blade_template`: Scanner statis memastikan 0 native `alert()` di seluruh Blade view.
9. `test_order_cancellation_confirmation_guard_in_admin_order_detail`: Verifikasi konfirmasi pembatalan status order admin.
10. `test_one_click_copy_button_present_in_customer_order_detail`: Verifikasi tombol salin kode order.

### Hasil Eksekusi Uji Otomatis:
```
Tests:    125 passed (462 assertions)
Duration: 13.28s
Failures: 0
```

---

## 7. Real Browser QA

Pengujian browser subagent dilakukan pada server aktif `http://localhost:8000`:
- **Desktop (1440x900):**
  - Homepage (`/`): Header, Hero search form, Fleet showcase, Fasilitas, FAQ accordion, Footer. **PASS**.
  - Jadwal & Tiket (`/trips`): Filter asal, tujuan, tanggal, kartu jadwal, status kursi. **PASS**.
  - Detail & Pemilihan Kursi (`/trips/1` & `/trips/3`): Denah interaktif, seleksi kursi (4A, 4B), toggle state, summary bar real-time. **PASS**.
  - Otentikasi & Profil Customer: Login customer Budi, redirect `/dashboard` -> `/my-orders`, kartu pesanan terakhir pada `/profile`. **PASS**.
  - Admin Operations: Login Admin, operational dashboard, statistik pendapatan & okupansi, tabel `/admin/orders`. **PASS**.
- **Tablet (1024x768):**
  - Tata letak denah kursi dan summary bar menyesuaikan tanpa horizontal break. **PASS**.
- **Mobile (390x844, 375x812, 320x800):**
  - Responsive navigation drawer, input form search stacked dengan rapi, denah kursi tidak melebihi viewport width (`scrollWidth <= innerWidth`). **PASS**.

---

## 8. Quality Gates Status

| Quality Gate | Perintah | Status | Keterangan |
|---|---|---|---|
| **PHP Linter & Code Style** | `vendor/bin/pint --test` | **PASS** | 100% PSR-12 / Laravel standards |
| **Automated Tests** | `php artisan test` | **PASS** | 125 passed, 462 assertions, 0 failed |
| **Frontend Production Build**| `npm run build` | **PASS** | Vite built clean (0 errors) |
| **Database Migrations** | `php artisan migrate:status` | **PASS** | Seluruh 15 migrasi `[Ran]` |
| **Route List Integrity** | `php artisan route:list` | **PASS** | 58 routes terdaftar valid |
| **Git Diff Validation** | `git diff --check` | **PASS** | Bebas whitespace/conflict issues |
| **Cache Optimization** | `php artisan optimize:clear` | **PASS** | Bootstrap & view cache bersih |
| **Brand Audit** | Regex Search `PO CAN` & `PCT-` | **PASS** | 0 legacy brand violations |

---

## 9. Modified & Added Files

- `resources/views/trips/show.blade.php`: Penghapusan sisa native `alert()`, penyesuaian responsive legend.
- `resources/views/layouts/app.blade.php`: Perbaikan penutup `</footer>`, penambahan skip to main content link.
- `resources/views/layouts/admin.blade.php`: Penambahan skip to main content link.
- `resources/views/booking/checkout.blade.php`: Penambahan atribut aksesibilitas input form (`aria-describedby`, dll).
- `resources/views/profile/edit.blade.php`: Penambahan kartu Pesanan Terakhir (`$recentOrders`).
- `resources/views/orders/show.blade.php`: Penambahan tombol 1-klik salin kode order.
- `resources/views/admin/orders/show.blade.php`: Penambahan dialog konfirmasi pembatalan pesanan admin.
- `resources/views/welcome.blade.php`: Berkas boilerplate tidak terpakai dihapus.
- `routes/web.php`: Penambahan rute alias cerdas `/dashboard`, `/orders`, dan `/orders/{order}`.
- `docs/sprint-10-audit.md`: Dokumen audit menyeluruh Sprint 10.
- `docs/sprint-10-final-report.md`: Dokumen laporan akhir Sprint 10.
- `tests/Feature/Sprint10OptimizationTest.php`: Berkas unit & feature regression testing Sprint 10.

---

## 10. Remaining Limitations & Recommended Future Work

1. **WhatsApp Notification Gateway:**
   - Sistem saat ini siap antrean (*queue-ready*) dengan email/database notification driver. Integrasi WhatsApp Business API (misalnya Fonnte/Twilio) disarankan sebagai *plug-in external integration* di fase berikutnya.
2. **Interactive Live Seat Lock via WebSockets:**
   - Penahanan kursi saat ini menggunakan database lock and expiration timestamp. Di masa depan dapat ditingkatkan dengan Laravel Reverb/Pusher untuk real-time multi-device seat sync tanpa refresh.
