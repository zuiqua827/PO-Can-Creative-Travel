<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\Route;
use App\Models\Trip;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        // Safe caching for static route metadata (1 hour)
        $origins = Cache::remember('home_route_origins', 3600, function () {
            return Route::where('status', 'active')
                ->distinct()
                ->pluck('origin')
                ->toArray();
        });

        $destinations = Cache::remember('home_route_destinations', 3600, function () {
            return Route::where('status', 'active')
                ->distinct()
                ->pluck('destination')
                ->toArray();
        });

        // Featured luxury buses
        $buses = Bus::where('status', 'active')->take(4)->get();

        // Popular routes with lowest available trip prices
        $popularRoutes = Route::where('status', 'active')
            ->with(['trips' => function ($query) {
                $query->where('status', 'scheduled')
                    ->where('departure_at', '>=', now())
                    ->orderBy('price', 'asc');
            }])
            ->take(6)
            ->get();

        // Upcoming trips today & tomorrow
        $upcomingTrips = Trip::with(['bus', 'route'])
            ->where('status', 'scheduled')
            ->where('departure_at', '>=', now())
            ->orderBy('departure_at', 'asc')
            ->take(4)
            ->get();

        return view('home', compact('origins', 'destinations', 'buses', 'popularRoutes', 'upcomingTrips'));
    }
}
