<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\Order;
use App\Models\Route;
use App\Models\Trip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', 'all');

        // Date range scope builder
        $applyPeriod = function ($query) use ($period) {
            return match ($period) {
                'today' => $query->whereDate('orders.created_at', Carbon::today()),
                'week' => $query->whereBetween('orders.created_at', [now()->startOfWeek(), now()->endOfWeek()]),
                'month' => $query->whereBetween('orders.created_at', [now()->startOfMonth(), now()->endOfMonth()]),
                default => $query,
            };
        };

        // Aggregated Financial & Order KPIs (Respecting Period)
        $totalRevenueQuery = Order::where('payment_status', 'paid');
        $applyPeriod($totalRevenueQuery);
        $totalRevenue = (float) $totalRevenueQuery->sum('total_amount');

        $totalOrdersQuery = Order::query();
        $applyPeriod($totalOrdersQuery);
        $totalOrders = $totalOrdersQuery->count();

        $paidOrdersQuery = Order::where('payment_status', 'paid');
        $applyPeriod($paidOrdersQuery);
        $paidOrdersCount = $paidOrdersQuery->count();

        $pendingPaymentsQuery = Order::where('payment_status', 'unpaid')->where('status', 'pending');
        $applyPeriod($pendingPaymentsQuery);
        $pendingPaymentsCount = $pendingPaymentsQuery->count();

        $cancelledOrdersQuery = Order::where('status', 'cancelled');
        $applyPeriod($cancelledOrdersQuery);
        $cancelledOrdersCount = $cancelledOrdersQuery->count();

        $todayBookingsCount = Order::whereDate('created_at', Carbon::today())->count();

        // Operational Fleet & Customer Metrics
        $totalCustomers = User::where('role', 'customer')->count();
        $totalBuses = Bus::where('status', 'active')->count();
        $totalRoutes = Route::where('status', 'active')->count();
        $activeTrips = Trip::where('status', 'scheduled')
            ->where('departure_at', '>=', now())
            ->count();
        $todayDeparturesCount = Trip::whereDate('departure_at', Carbon::today())->count();

        // Seat Occupancy Analytics across Active Scheduled Trips
        $occupancyQuery = Trip::with('bus')
            ->withBookedSeatsCount()
            ->where('status', 'scheduled')
            ->where('departure_at', '>=', now()->subDays(7));

        $scheduledTrips = $occupancyQuery->get();
        $totalCapacity = $scheduledTrips->sum(fn ($t) => $t->bus ? $t->bus->seat_capacity : 0);
        $totalBooked = $scheduledTrips->sum(fn ($t) => (int) ($t->active_booked_seats_count ?? 0));
        $occupancyRate = $totalCapacity > 0 ? round(($totalBooked / $totalCapacity) * 100, 1) : 0;

        // Top Performing Routes (Aggregated via SQL)
        $topRoutesQuery = DB::table('orders')
            ->join('trips', 'orders.trip_id', '=', 'trips.id')
            ->join('routes', 'trips.route_id', '=', 'routes.id')
            ->where('orders.payment_status', 'paid');

        $applyPeriod($topRoutesQuery);

        $topRoutes = $topRoutesQuery->select(
            'routes.origin',
            'routes.destination',
            DB::raw('COUNT(orders.id) as bookings_count'),
            DB::raw('SUM(orders.total_amount) as total_route_revenue')
        )
            ->groupBy('routes.id', 'routes.origin', 'routes.destination')
            ->orderByDesc('bookings_count')
            ->take(5)
            ->get();

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
            'period',
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
            'occupancyRate',
            'totalCapacity',
            'totalBooked',
            'topRoutes',
            'recentOrders',
            'todayDepartures'
        ));
    }
}
