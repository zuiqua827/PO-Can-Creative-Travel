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
        // Metric counters
        $totalRevenue = Order::where('payment_status', 'paid')->sum('total_amount');
        $totalOrders = Order::count();
        $paidOrdersCount = Order::where('payment_status', 'paid')->count();
        $pendingOrdersCount = Order::where('payment_status', 'unpaid')->where('status', 'pending')->count();

        $totalBuses = Bus::where('status', 'active')->count();
        $totalRoutes = Route::where('status', 'active')->count();
        $activeTrips = Trip::where('status', 'scheduled')
            ->where('departure_at', '>=', now())
            ->count();
        $totalCustomers = User::where('role', 'customer')->count();

        // Recent orders
        $recentOrders = Order::with(['user', 'trip.route', 'trip.bus', 'orderItems'])
            ->latest()
            ->take(8)
            ->get();

        // Today's upcoming departures
        $todayDepartures = Trip::with(['bus', 'route', 'orders'])
            ->whereDate('departure_at', Carbon::today())
            ->orderBy('departure_at', 'asc')
            ->get();

        return view('admin.dashboard', compact(
            'totalRevenue',
            'totalOrders',
            'paidOrdersCount',
            'pendingOrdersCount',
            'totalBuses',
            'totalRoutes',
            'activeTrips',
            'totalCustomers',
            'recentOrders',
            'todayDepartures'
        ));
    }
}
