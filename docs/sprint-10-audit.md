# CAN Travel Sprint 10 Audit

**Repository Path:** `C:\laragon\www\PO-CAN_Creative_Travel`  
**Application Name:** CAN Travel  
**Sprint Cycle:** Sprint 10 (Product Optimization, UI/UX Excellence, Feature Completeness & Production Polish)  
**Roles:** Senior Full-Stack Engineer, Product Engineer, UI/UX Engineer, QA Engineer, Security Engineer, Technical Reviewer  
**Date:** September 2026  

---

## 1. Executive Summary

Sprint 10 marks the culmination of architectural hardening and user experience optimization for **CAN Travel**, an online intercity bus ticketing platform built with Laravel 10, Blade, Alpine.js, Tailwind CSS, and MySQL.

Following the achievements of Sprints 6 through 9—which established a verified baseline of 115 passing automated tests (435 assertions), server-authoritative pricing, zero legacy strings, strict IDOR protection, idempotent webhooks, and rate-limited endpoints—Sprint 10 conducts an exhaustive end-to-end review of every customer and admin touchpoint.

The objective is to eliminate remaining usability friction, prevent dead-end navigation, resolve progressive enhancement edge cases (such as blocking native browser dialogs in fallback scripts), guarantee responsive stability across all viewports (from 320px ultra-mobile to 1440px desktop), ensure WCAG 2.1 AA accessibility compliance, and verify that the application operates reliably in a real browser environment.

---

## 2. Current Architecture

- **Backend Framework:** Laravel 10.50.3 on PHP 8.4.16
- **Database:** MySQL with relational foreign keys, performance indexes on search columns, and compound indexes on `orders (status, payment_status, expires_at)`
- **Frontend Layer:** Laravel Blade templates styled with Tailwind CSS (v3) and powered by Alpine.js (CSP-compliant build)
- **Asset Pipeline:** Vite 4.5.14 compiling minimal, unbloated CSS and JS bundles (~53 kB CSS, ~51 kB JS gzip-optimized)
- **Payment Architecture:** Dual-driver design supporting `FakePaymentGateway` for rapid local testing and `MidtransPaymentGateway` with HMAC-SHA512 webhook signature verification and server-authoritative order totals
- **Security & Reliability:**
  - Strict Content Security Policy (excluding `unsafe-eval`), HSTS, X-Content-Type-Options, X-Frame-Options
  - Concurrency locking (`lockForUpdate`) on seat reservations to eliminate race conditions
  - Automatic 2-hour payment expiration with graceful seat release
  - Comprehensive audit trail via `AuditLogger` tracking sensitive mutations
  - Request ID tracing and production `/health` probe exposing 0 secrets

---

## 3. Existing Features

1. **Public Portal:**
   - Hero banner with quick route search (Origin, Destination, Date)
   - Real-time route search with multi-parameter filtering (Origin, Destination, Date, Bus Class, Price Range, Time Slot)
   - Interactive bus cabin seat picker with live visual status indications
   - E-Ticket public boarding pass verification via token lookup (`/tickets/verify/{token}`)
   - Account authentication (Register, Login with 1-click test credentials, Remember Me, Logout)

2. **Customer Experience:**
   - Multi-step booking checkout (Schedule -> Seat -> Passenger Details -> Payment)
   - Old input preservation on checkout validation errors
   - Real-time countdown timer on pending payments with 1-click Virtual Account copying
   - Simulated instant payment for testing & verification
   - Customer order management (`/my-orders`) with status filtering (Unpaid, Confirmed, Completed, Cancelled)
   - Comprehensive digital boarding pass & print-ready E-Ticket with SVG QR code
   - User profile and password management

3. **Admin Experience:**
   - Operational dashboard with financial KPIs, 7-day revenue/volume trend bars, and today's departures
   - Bus fleet management with interactive seat status matrix (Available, Blocked, Maintenance)
   - Intercity route management with active trip count guards
   - Trip schedule management with capacity validation
   - Customer directory with aggregate order volume and spending metrics
   - Order management with multi-field search, status filtering, and UTF-8 BOM CSV export
   - System audit logs tracking administrative actions, cancellations, and reconciliations

---

## 4. UI/UX Findings

1. **Seat Picker Native Alert in Fallback Script:** While Sprint 9 replaced Alpine.js alert dialogs with a reactive banner, the progressive enhancement fallback script in `resources/views/trips/show.blade.php` still contained `alert('Maksimal pemesanan adalah ' + maxSeats + ' kursi per transaksi.')`. On mobile browsers where Alpine might be slow to initialize or in fallback mode, clicking a 6th seat freezes the browser interface.
2. **Missing Customer Activity in Profile:** `ProfileController::edit()` retrieved `$recentOrders` via Eloquent with eager loading, but `resources/views/profile/edit.blade.php` completely omitted rendering the recent orders list.
3. **Copy Order Code Usability:** On `/my-orders/{order}`, customers frequently need to copy their alphanumeric order code (e.g. `CAN-20260924-XXXXX`) to communicate with conductors or support. Manually highlighting text on touch devices creates unnecessary friction.
4. **Interactive Status Confirmation:** In Admin Order Show, updating an order's status to `cancelled` had no confirmation barrier, risking accidental ticket revocation.

---

## 5. Responsive Findings

1. **Seat Selection Legend Wrapping:** On ultra-mobile screens (320px to 375px), large seat preview blocks in the cabin legend caused cramped, irregular wrapping.
2. **Admin Tables on Small Viewports:** In viewports narrower than 640px, wide tabular data (e.g., Orders and Trips) scrolled horizontally without a clear visual cue indicating additional content to the right.
3. **Mobile Drawer Navigation:** Admin layout provides a slide-in drawer on mobile with an overlay backdrop, but the transition and escape key listener required validation across modern touch viewports.

---

## 6. Booking Flow Findings

1. **Old Input & Seat Retention:** The checkout flow reliably recovers selected seats via `$seatIdsRaw = $request->input('seat_ids') ?? old('seats');` when passenger validation fails.
2. **Accessible Form Feedback:** Passenger input fields in `booking/checkout.blade.php` lacked `aria-invalid` attributes and explicit `aria-describedby` error element linkage for screen readers.
3. **Double Submission Prevention:** Both checkout and payment forms correctly disable submit buttons and display spinning loaders upon form submission.

---

## 7. Payment Flow Findings

1. **Server-Authoritative Pricing:** Verified that client-submitted totals are ignored; prices are computed strictly as `trip->price * count(seats)`.
2. **Countdown Timer Expiration:** When countdown timer hits 0 on `booking/payment.blade.php`, the timer immediately synchronizes with the server. A clearer inline visual transition before page reload improves customer reassurance.
3. **Safe Idempotency:** Paid orders navigating to the payment screen are safely redirected to the order detail page with informative flash notifications.

---

## 8. Customer Experience Findings

1. **Route Prefill on Re-Booking:** Cancelled and expired orders on `/my-orders/{order}` provide a "Cari Rute Serupa" button pre-populating origin and destination query parameters.
2. **E-Ticket Printing:** The boarding pass card includes `@media print` rules, cleanly suppressing header, footer, and navigation bars when printing or saving as PDF.
3. **Feedback Alerts:** Profile updates and password changes display dismissible success and error alert banners.

---

## 9. Admin Experience Findings

1. **Operational Metrics:** Dashboard presents 5 top KPI cards (Total Revenue, Total Bookings, Occupancy Rate, Status Counts, Fleet & Customer Counts) with period filtering (`today`, `week`, `month`, `all`).
2. **Trip & Route Deletion Guards:** Deleting buses or routes with active trips is blocked with descriptive user feedback and recorded in the audit log.
3. **Export Integrity:** The CSV export streams UTF-8 BOM encoded data, ensuring Indonesian characters and timestamps render properly in spreadsheet applications.

---

## 10. Security Findings

1. **Authorization & IDOR:** Strict policy checks (`$this->authorize('view', $order)` and `$this->authorize('cancel', $order)`) prevent cross-customer order manipulation (HTTP 403).
2. **CSP Compliance:** No `unsafe-eval` is present in CSP headers; Alpine.js operates on its dedicated CSP build.
3. **Secret Hygiene:** Health endpoint `/health` returns database connectivity and cache status without exposing credentials or keys.

---

## 11. Performance Findings

1. **Eager Loading:** Controllers employ comprehensive eager loading (`with(['trip.route', 'trip.bus', 'orderItems.busSeat'])`) across customer order and admin listings, avoiding N+1 queries.
2. **Scheduler Performance Index:** Compound index `orders_status_payment_status_expires_at_index` accelerates expiration queries.
3. **Asset Footprint:** CSS and JS bundles remain minimal and fast-loading.

---

## 12. Accessibility Findings

1. **Skip-to-Content Mechanism:** Neither `layouts/app.blade.php` nor `layouts/admin.blade.php` provided a keyboard bypass link (`Skip to main content`), creating friction for keyboard navigation (WCAG 2.1 Success Criterion 2.4.1).
2. **ARIA Live Announcers:** Seat picker incorporates an `aria-live="polite"` region notifying assistive technology users of seat additions, removals, and capacity warnings.
3. **Form Association:** Inline error messages in checkout require explicit `id` and `aria-describedby` links.

---

## 13. Error Handling Findings

1. **Custom Error Pages:** Dedicated Blade error pages exist for 403, 404, 419, 422, 429, 500, and 503 with user-friendly Indonesian copy and navigation back to Home.
2. **Exception Scrubbing:** Production exception handlers prevent leaking environment secrets in session flash or response payloads.

---

## 14. Navigation Findings

1. **Dead-End URLs:** Navigating directly to `/dashboard` or `/orders` resulted in a 404 Not Found error because customer orders are canonicalized under `/my-orders` and admin dashboard under `/admin/dashboard`.
2. **Smart Redirect Aliases Needed:** Adding smart aliases for `/dashboard`, `/orders`, and `/orders/{order}` resolves user navigation drop-offs.

---

## 15. Data Integrity Findings

1. **Seat Availability Integrity:** Concurrency lock ensures two customers cannot concurrently check out the same seat on the same trip.
2. **Non-Destructive Database Operations:** Zero destructive database operations (no fresh, wipe, truncate, drop); all migrations remain additive.

---

## 16. Missing Features & Inactive Code

1. **Dead Boilerplate View:** `resources/views/welcome.blade.php` is an unused 28 KB starter template remaining from Laravel initialization.
2. **Missing Closing Tag:** `resources/views/layouts/app.blade.php` omitted `</footer>` before the script and closing body tags.

---

## 17. Technical Debt

- Progressive enhancement script in `trips/show.blade.php` retained legacy native `alert()`.
- Profile view did not render the `$recentOrders` data provided by the controller.
- Missing accessible skip links on layout wrappers.

---

## 18. Prioritized Issues

### Summary Matrix

| ID | Severity | Area | Problem | User Impact | Recommended Solution |
| :--- | :---: | :--- | :--- | :--- | :--- |
| **`ISSUE-S10-01`** | **P1** | Seat Selection | Native `alert()` called in fallback JS on seat limit (>5). | Freezes browser UI on touch/mobile devices. | Replace with non-blocking reactive alert banner and ARIA announcement. |
| **`ISSUE-S10-02`** | **P2** | Routing / Navigation | Direct navigation to `/dashboard` or `/orders` yields 404 Not Found. | Dead-end error page for common URL patterns. | Add smart aliases redirecting `/dashboard` and `/orders` appropriately. |
| **`ISSUE-S10-03`** | **P2** | Customer Profile | Controller loads `$recentOrders` but view does not render them. | Customer cannot see recent orders on their profile page. | Add "Aktivitas Pesanan Terakhir" card to `profile/edit.blade.php`. |
| **`ISSUE-S10-04`** | **P2** | HTML Structure | Missing `</footer>` closing tag in `layouts/app.blade.php`. | Invalid HTML DOM tree structure. | Insert `</footer>` tag before bottom scripts. |
| **`ISSUE-S10-05`** | **P2** | Accessibility | Layouts lack "Skip to main content" link for keyboard users. | Keyboard and screen reader users must tab through all nav links. | Add visually-hidden focusable skip link (WCAG 2.1 AA). |
| **`ISSUE-S10-06`** | **P2** | Checkout UX | Form validation errors lack `aria-invalid` and `aria-describedby`. | Screen reader users miss inline field error alerts. | Link errors with `aria-invalid="true"` and `aria-describedby`. |
| **`ISSUE-S10-07`** | **P2** | Customer UX | No 1-click button to copy Order Code on order detail page. | Manual text selection is clumsy on touch devices. | Add 1-click "Salin Kode" button with tooltip feedback. |
| **`ISSUE-S10-08`** | **P2** | Responsive UX | Seat legend wraps awkwardly on 320px–375px viewports. | Visual crowding on narrow mobile devices. | Refine responsive gap and font sizing. |
| **`ISSUE-S10-09`** | **P2** | Admin Experience | Wide tables lack visual scroll cue indicators on mobile. | Admins may miss offscreen table columns on phones. | Add smooth scroll indicators and compact padding. |
| **`ISSUE-S10-10`** | **P3** | Admin Orders | Status update to `cancelled` lacks client-side confirmation check. | Risk of accidental cancellation by admin misclick. | Add interactive confirmation dialog on cancellation. |
| **`ISSUE-S10-11`** | **P3** | Code Hygiene | Unused starter file `welcome.blade.php` remains in views. | Repository clutter and dead code. | Safely delete unused template. |

---

### Detailed Issue Specifications

#### ISSUE-S10-01 (P1 - High)
- **ID:** `ISSUE-S10-01`
- **Severity:** P1
- **Area:** Seat Selection UI/UX
- **Problem:** In `resources/views/trips/show.blade.php` (line 527), the vanilla fallback script triggers `alert('Maksimal pemesanan adalah ' + maxSeats + ' kursi per transaksi.');`.
- **Root Cause:** Sprint 9 updated the Alpine.js handler but left the native `alert()` inside the progressive fallback event listener.
- **User Impact:** Native browser alerts block UI rendering and touch interactions, jarring mobile users.
- **Recommended Solution:** Replace `alert()` with a function showing the in-DOM alert banner and updating `#accessibility-announcer`.
- **Verification Method:** Attempt to select 6 seats in browser; verify non-blocking banner displays without native dialog.

#### ISSUE-S10-02 (P2 - Medium)
- **ID:** `ISSUE-S10-02`
- **Severity:** P2
- **Area:** Routing & Navigation
- **Problem:** Accessing `/dashboard` or `/orders` results in a 404 dead end.
- **Root Cause:** Admin dashboard is under `/admin/dashboard` and customer orders under `/my-orders`. No root aliases exist.
- **User Impact:** Dead-end navigation for users typing standard intuitive URLs.
- **Recommended Solution:** Add route aliases in `routes/web.php`:
  - `/dashboard`: redirect to `admin.dashboard` if admin, `orders.index` if customer, or `login` if guest.
  - `/orders`: redirect to `orders.index`.
  - `/orders/{order}`: redirect to `orders.show`.
- **Verification Method:** Feature tests and browser visits to `/dashboard` and `/orders` ensuring HTTP 302 redirects.

#### ISSUE-S10-03 (P2 - Medium)
- **ID:** `ISSUE-S10-03`
- **Severity:** P2
- **Area:** Customer Experience / Profile
- **Problem:** `ProfileController::edit()` fetches `$recentOrders`, but `profile/edit.blade.php` never renders them.
- **Root Cause:** View template omitted the section for recent orders.
- **User Impact:** Customer cannot review their recent bookings or jump to e-tickets directly from their profile.
- **Recommended Solution:** Render a sleek "Aktivitas Pesanan Terakhir" card in `profile/edit.blade.php` listing recent orders with status badges and links to `/my-orders/{order}`.
- **Verification Method:** Visit `/profile` with customer having orders and verify card renders cleanly.

#### ISSUE-S10-04 (P2 - Medium)
- **ID:** `ISSUE-S10-04`
- **Severity:** P2
- **Area:** Layout / HTML Validity
- **Problem:** Missing `</footer>` closing tag in `resources/views/layouts/app.blade.php`.
- **Root Cause:** Closing tag was accidentally removed in an earlier layout edit.
- **User Impact:** Malformed DOM tree structure.
- **Recommended Solution:** Add `</footer>` tag immediately after line 351.
- **Verification Method:** Validate DOM tree in browser developer tools.

#### ISSUE-S10-05 (P2 - Medium)
- **ID:** `ISSUE-S10-05`
- **Severity:** P2
- **Area:** Accessibility (WCAG 2.1 AA)
- **Problem:** No skip-to-content links exist in `layouts/app.blade.php` or `layouts/admin.blade.php`.
- **Root Cause:** Skip link mechanism was not previously included.
- **User Impact:** Keyboard and screen-reader users must navigate repetitive header elements on every page load.
- **Recommended Solution:** Add an accessible, visually-hidden `Skip to main content` anchor targeting `#main-content`.
- **Verification Method:** Press `Tab` upon initial page load and verify skip link receives focus and jumps directly to `<main id="main-content">`.

---
