# CAN Travel Sprint 11 Audit
**Brand Identity, UI Consistency & Final Product Polish**  
**Date:** September 2026  
**Auditor:** Senior Full-Stack & UI/UX Design System Engineer  
**Baseline Status:** 125 tests passed, 462 assertions, 0 failures, Pint PASS, Vite build PASS  

---

## 1. Executive Summary

Sprint 11 berfokus pada transformasi visual penuh (*comprehensive visual & brand identity elevation*) untuk aplikasi CAN Travel. Sprint 10 telah mengunci stabilitas fungsional, keamanan, integritas data, dan pencegahan error (125 tests passing). Namun, audit visual menunjukkan bahwa identitas brand masih terfragmentasi: logo sebelumnya masih merupakan aproksimasi CSS/SVG sederhana, warna antarmuka masih menggunakan turunan palet biru generik Laravel/Tailwind, dan beberapa halaman kritis (seperti error pages, verifikasi tiket, payment, dan checkout) belum menampilkan visual identity resmi CAN Travel secara konsisten.

Sprint 11 mengintegrasikan **Logo Resmi CAN Travel** (ikon bus dalam busur lingkaran C dengan aksen oranye dan tipografi resmi) ke seluruh titik kontak customer dan admin, menyelaraskan seluruh antarmuka dengan palet warna resmi (70% Navy/Blue, 20% White/Light, 10% Orange Accent), serta menstandardisasi komponen antarmuka (Navbar, Footer, Hero, Trip Cards, Seat Map, Checkout, E-Ticket, dan Admin Portal).

---

## 2. Current Architecture & Baseline Quality

- **Framework**: Laravel 10.50.3 on PHP 8.4.16
- **Frontend Engine**: Blade Templates + Alpine.js (Strict CSP build 3.14.8) + Tailwind CSS 3.4
- **Bundler**: Vite 4.5.14
- **Database**: MySQL 8.0 (15 Ran Migrations, Zero Destructive Changes)
- **Automated Tests**: 125 tests passed (462 assertions) across 8 test suites
- **Branding Audit**: 0 legacy branding (`PO CAN` = 0, `PCT-` = 0)

---

## 3. Brand Identity & Logo Audit

### 3.1 Official Logo Assets Provided
- **Logo Komponen**:
  1. Ikon Bus Aerodinamis Modern di dalam busur lingkaran `C`.
  2. Garis lintasan berkecepatan (*swoosh track*) berwarna Biru Primer dan Aksen Oranye.
  3. Pemisah vertikal ramping `|`.
  4. Tipografi tebal `CAN` dengan jembatan aksen oranye pada huruf `A`.
  5. Sub-wordmark `TRAVEL` dengan *letter-spacing* proporsional.
- **Berkas Aset Resmi Tersimpan**:
  - `public/images/logo/can-travel-logo.png` (707x353, RGBA Truecolor dengan transparansi penuh alpha channel).
  - `public/images/logo/can-travel-logo-white-bg.png` (1024x512, resolusi tinggi untuk kebutuhan kartu/cetak).

### 3.2 Audit Implementasi Logo pada Halaman-Halaman Eksisting
| Halaman / Komponen | Kondisi Eksisting | Kebutuhan Sprint 11 |
|---|---|---|
| **Navbar Customer** | Inline SVG bus sederhana + teks CSS | Pasang `<x-logo>` dengan aset PNG resmi transparan |
| **Mobile Navbar** | Inline SVG bus sederhana + teks CSS | Pasang `<x-logo>` proporsional, tanpa layout overflow |
| **Footer Publik** | Inline SVG bus di atas background hitam | Pasang `<x-logo variant="light">` dengan container kapsul kontras tinggi |
| **Login Customer** | Inline SVG bus + teks CSS | Pasang `<x-logo size="lg">` resmi |
| **Register Customer**| Inline SVG bus + teks CSS | Pasang `<x-logo size="lg">` resmi |
| **Homepage Hero** | Teks headline tanpa logo langsung | Perkuat visual hierarchy hero dengan identitas CAN Travel |
| **Checkout Page** | Header ringkas tanpa logo brand | Tambahkan branding resmi CAN Travel pada header checkout |
| **Payment Page** | Header ringkas tanpa logo brand | Tambahkan branding resmi CAN Travel pada header pembayaran |
| **E-Ticket (My Order)**| Inline SVG bus di header tiket gelap | Pasang logo resmi CAN Travel pada header tiket boarding pass |
| **Ticket Verification**| Header minimalis | Tambahkan logo resmi CAN Travel pada kartu verifikasi tiket |
| **Admin Sidebar** | Inline SVG kecil di sidebar gelap | Pasang `<x-logo variant="light">` dengan wadah kapsul kontras |
| **Admin Drawer (Mobile)**| Inline SVG kecil | Sinkronisasi dengan logo resmi |
| **Error Pages (404/429/500)**| Teks error generik Laravel | Redesign dengan logo resmi CAN Travel dan tombol kembali navigasi |
| **Favicon & Metadata** | Favicon SVG lama | Update favicon SVG/PNG dan Open Graph meta tags |

---

## 4. Color Palette & Design Tokens Audit

### 4.1 Official Color Palette
Sesuai arahan spesifikasi brand:
- **Deep Navy**: `#062A52` (Background utama elemen executive, footer, admin sidebar)
- **Navy**: `#0B3A70` (Warna brand sekunder, header navigasi, card border aktif)
- **Primary Blue**: `#1268B3` (Aksi tombol utama, link interaktif, selected seat)
- **Bright Blue**: `#1685D8` (Gradient highlight, status aktif, hover states)
- **Accent Orange**: `#F5A623` (Aksen kunci: badge promo, CTA search, status pending, highlight harga)
- **Bright Orange**: `#FFB52E` (Aksen terang, hover state aksen)
- **White**: `#FFFFFF` (Surface cards, text on dark)
- **Light Background**: `#F5F8FC` (Background halaman utama)
- **Soft Blue**: `#EAF3FB` (Background container sekunder, chip info)
- **Text (Primary)**: `#102A43` (Teks judul dan isi utama dengan kontras tinggi)
- **Secondary Text**: `#52667A` (Teks deskripsi, label, placeholder)
- **Border**: `#D9E3EF` (Garis pemisah, border card, border input)

### 4.2 Visual Balance Ratio Target
- **70% Navy / Blue**: Nuansa dominan profesional transportasi terpercaya.
- **20% White / Light**: Area bernafas, latar kartu, ruang baca yang bersih.
- **10% Orange Accent**: Fokus perhatian mata (*visual weight* & CTA terarah).

---

## 5. UI/UX & Component Findings

### 5.1 Issue 1: Komponen Logo Eksisting Belum Menggunakan Brand Mark Resmi (P1 - Brand)
- **Problem**: `resources/views/components/logo.blade.php` menggambar bus generik dengan SVG dan teks terpisah, bukan aset logo resmi CAN Travel yang memiliki hak cipta dan identitas unik.
- **Root Cause**: Logo resmi baru diserahkan pada Sprint 11.
- **Fix**: Perbarui `components/logo.blade.php` untuk merender berkas gambar resmi `can-travel-logo.png` dengan mempertahankan aspect ratio 2:1, mendukung berbagai ukuran (`xs`, `sm`, `md`, `lg`, `xl`), dan varian kontras (`dark` untuk background terang, `light` dengan kapsul putih kontras untuk background gelap).

### 5.2 Issue 2: Palet Warna Tailwind Masih Memakai Biru Default (P2 - Visual)
- **Problem**: `tailwind.config.js` mengonfigurasi `brand-*` dengan biru Tailwind default (`#0270c7`, dll), sehingga warna situs tidak serasi dengan biru resmi `#1268B3` dan deep navy `#062A52` pada logo.
- **Fix**: Konfigurasi ulang `tailwind.config.js` untuk memetakan token warna resmi (`brand`, `navy`, `accent`, `can`) dan jalankan `npm run build`.

### 5.3 Issue 3: Halaman Error (404, 429, 500) Masih Polos dan Tidak Berlogo (P2 - UX)
- **Problem**: Template error di `resources/views/errors/` belum menyematkan logo CAN Travel dan tombol CTA bantuan resmi.
- **Fix**: Desain ulang ketiga halaman error dengan identitas resmi CAN Travel, ilustrasi/ikon status bertema travel, dan tombol kembali ke beranda atau pencarian tiket.

### 5.4 Issue 4: E-Ticket Perlu Standar Boarding Pass Premium (P2 - Visual)
- **Problem**: Tampilan e-ticket pada `orders/show.blade.php` sudah fungsional namun styling header tiket dapat ditingkatkan agar terlihat seperti dokumen perjalanan resmi premium dengan logo CAN Travel, QR verifikasi, dan watermark/garis perforasi tiket modern.
- **Fix**: Perhalus estetika kartu boarding pass e-ticket dengan palet resmi dan logo resmi.

### 5.5 Issue 5: Seat Selection Legend & Color Mapping (P2 - UX)
- **Problem**: Warna status kursi (AVAILABLE, HELD, SELECTED, BOOKED) harus diselaraskan secara akurat dengan brand system:
  - AVAILABLE: Netral (border abu-abu lembut `#D9E3EF` dengan background putih).
  - HELD: Orange Aksen (`#F5A623`).
  - SELECTED: CAN Blue (`#1268B3`).
  - BOOKED: Dark/Disabled (slate netral).
- **Fix**: Sinkronkan warna pada tombol kursi dan legenda di `trips/show.blade.php`.

---

## 6. Prioritized Action Plan

| Priority | ID | Area | Task |
|---|---|---|---|
| **P1** | S11-01 | Brand | Implementasi logo resmi PNG transparan pada `components/logo.blade.php` |
| **P1** | S11-02 | Brand | Update favicon SVG & PNG, meta theme-color, dan Open Graph |
| **P1** | S11-03 | Design System | Update `tailwind.config.js` dengan palet resmi (Navy, Blue, Orange, Light BG) |
| **P2** | S11-04 | Public | Redesign Hero Homepage & Form Pencarian Tiket dengan hierarki visual kuat |
| **P2** | S11-05 | Booking | Penyelarasan Trip Cards & Filter di `/trips` dengan waktu pindai < 3 detik |
| **P2** | S11-06 | Booking | Penyelarasan warna state denah kursi (Available, Held, Selected, Booked) |
| **P2** | S11-07 | Booking | Checkout & Payment visual progress & branding header |
| **P2** | S11-08 | Customer | E-Ticket boarding pass visual polish dengan logo resmi |
| **P2** | S11-09 | Admin | Admin sidebar & topbar styling dengan Deep Navy `#062A52` dan logo kapsul kontras |
| **P2** | S11-10 | Error & Auth | Redesign Login, Register, 404, 429, 500 dengan branding resmi |
| **P3** | S11-11 | QA | Responsive Browser QA pada 7 ukuran viewport dan automated regression |
