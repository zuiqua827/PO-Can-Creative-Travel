<?php

use App\Http\Controllers\Admin\AuditLogController as AdminAuditLogController;
use App\Http\Controllers\Admin\BusController as AdminBusController;
use App\Http\Controllers\Admin\BusSeatController as AdminBusSeatController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\RouteController as AdminRouteController;
use App\Http\Controllers\Admin\TripController as AdminTripController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketVerificationController;
use App\Http\Controllers\TripController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - CAN Travel
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/trips', [TripController::class, 'index'])->name('trips.index');
Route::get('/trips/{trip}', [TripController::class, 'show'])->name('trips.show');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('throttle:login');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post')->middleware('throttle:register');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Customer Protected routes
Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Booking flow
    Route::get('/trips/{trip}/checkout', [BookingController::class, 'checkout'])->name('booking.checkout');
    Route::post('/trips/{trip}/booking', [BookingController::class, 'store'])->name('booking.store')->middleware('throttle:booking');
    Route::get('/orders/{order}/payment', [BookingController::class, 'payment'])->name('booking.payment');
    Route::post('/orders/{order}/payment', [BookingController::class, 'processPayment'])->name('booking.processPayment')->middleware('throttle:payment');

    // Orders & E-Tickets
    Route::get('/my-orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/my-orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/my-orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
});

// Admin Management Portal routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });

    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Buses & Seats
    Route::resource('buses', AdminBusController::class);
    Route::put('/seats/{busSeat}', [AdminBusSeatController::class, 'updateStatus'])->name('seats.update');

    // Routes & Trips
    Route::resource('routes', AdminRouteController::class);
    Route::resource('trips', AdminTripController::class);

    // Orders
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/export', [AdminOrderController::class, 'export'])->name('orders.export');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::put('/orders/{order}', [AdminOrderController::class, 'updateStatus'])->name('orders.update');

    // Customers
    Route::get('/customers', [AdminCustomerController::class, 'index'])->name('customers.index');

    // Audit Logs
    Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');
});

// Production Health Check Probe
Route::get('/health', [HealthCheckController::class, 'check'])
    ->name('health')
    ->middleware('throttle:60,1');

// Public Ticket Verification (QR Code scanning)
Route::get('/tickets/verify/{token}', [TicketVerificationController::class, 'verify'])
    ->name('tickets.verify')
    ->middleware('throttle:ticket-verify');

// Payment Gateway Webhook (External Gateway Callback)
Route::post('/payments/webhook', [PaymentWebhookController::class, 'handle'])
    ->name('payments.webhook')
    ->middleware('throttle:payment-webhook');
