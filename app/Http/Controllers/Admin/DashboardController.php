<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\Order;
use App\Models\Route;
use App\Models\Trip;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Real KPI metrics from database
        $totalRevenue = (float) Order::where('payment_status', 'paid')->sum('total_amount');
        $totalOrders = Order::count();
        $paidOrdersCount = Order::where('payment_status', 'paid')->count();
        $pendingPaymentsCount = Order::where('payment_status', 'unpaid')->where('status', 'pending')->count();
        $cancelledOrdersCount = Order::where('status', 'cancelled')->count();
        $todayBookingsCount = Order::whereDate('created_at', Carbon::today())->count();

        $totalCustomers = User::where('role', 'customer')->count();
        $totalBuses = Bus::where('status', 'active')->count();
        $totalRoutes = Route::where('status', 'active')->count();
        $activeTrips = Trip::where('status', 'scheduled')
            ->where('departure_at', '>=', now())
            ->count();
        $todayDeparturesCount = Trip::whereDate('departure_at', Carbon::today())->count();

        // Recent orders activity with eager loading
        $recentOrders = Order::with(['user', 'trip.route', 'trip.bus', 'orderItems'])
            ->latest()
            ->take(8)
            ->get();

        // Today's departures with eager booked seats count (No N+1)
        $todayDepartures = Trip::with(['bus', 'route'])
            ->withBookedSeatsCount()
            ->whereDate('departure_at', Carbon::today())
            ->orderBy('departure_at', 'asc')
            ->get();

        return view('admin.dashboard', compact(
            'totalRevenue',
            'totalOrders',
            'paidOrdersCount',
            'pendingPaymentsCount',
            'cancelledOrdersCount',
            'todayBookingsCount',
            'totalBuses',
            'totalRoutes',
            'activeTrips',
            'totalCustomers',
            'todayDeparturesCount',
            'recentOrders',
            'todayDepartures'
        ));
    }
}
