<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\Route;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Distinct origins and destinations for the search dropdowns
        $origins = Route::where('status', 'active')
            ->distinct()
            ->pluck('origin')
            ->toArray();

        $destinations = Route::where('status', 'active')
            ->distinct()
            ->pluck('destination')
            ->toArray();

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
