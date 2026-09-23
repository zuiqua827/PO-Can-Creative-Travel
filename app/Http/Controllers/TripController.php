<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\Route;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function index(Request $request)
    {
        $origin = $request->input('origin');
        $destination = $request->input('destination');
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $busType = $request->input('bus_type');
        $timeSlot = $request->input('time_slot'); // morning, afternoon, night
        $sortBy = $request->input('sort', 'departure_asc'); // price_asc, price_desc, departure_asc, departure_desc

        $query = Trip::with(['bus', 'route'])
            ->where('status', 'scheduled');

        // Filter route origin/destination
        if ($origin || $destination) {
            $query->whereHas('route', function ($q) use ($origin, $destination) {
                if ($origin) {
                    $q->where('origin', 'like', "%{$origin}%");
                }
                if ($destination) {
                    $q->where('destination', 'like', "%{$destination}%");
                }
            });
        }

        // Filter date
        if ($date) {
            $query->whereDate('departure_at', $date);
        } else {
            $query->where('departure_at', '>=', now());
        }

        // Filter bus type
        if ($busType) {
            $query->whereHas('bus', function ($q) use ($busType) {
                $q->where('type', $busType);
            });
        }

        // Filter time slot
        if ($timeSlot === 'morning') {
            $query->whereTime('departure_at', '>=', '05:00:00')
                  ->whereTime('departure_at', '<', '12:00:00');
        } elseif ($timeSlot === 'afternoon') {
            $query->whereTime('departure_at', '>=', '12:00:00')
                  ->whereTime('departure_at', '<', '18:00:00');
        } elseif ($timeSlot === 'night') {
            $query->where(function ($q) {
                $q->whereTime('departure_at', '>=', '18:00:00')
                  ->orWhereTime('departure_at', '<', '05:00:00');
            });
        }

        // Sorting
        match ($sortBy) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'departure_desc' => $query->orderBy('departure_at', 'desc'),
            default => $query->orderBy('departure_at', 'asc'),
        };

        $trips = $query->paginate(10)->withQueryString();

        // Helper options for search bar
        $origins = Route::where('status', 'active')->distinct()->pluck('origin')->toArray();
        $destinations = Route::where('status', 'active')->distinct()->pluck('destination')->toArray();
        $busTypes = ['Executive', 'Royal Suite', 'Sleeper Bus', 'VIP'];

        return view('trips.index', compact(
            'trips',
            'origin',
            'destination',
            'date',
            'busType',
            'timeSlot',
            'sortBy',
            'origins',
            'destinations',
            'busTypes'
        ));
    }

    public function show(Trip $trip)
    {
        $trip->load(['bus.busSeats' => function ($q) {
            $q->orderBy('row', 'asc')->orderBy('column', 'asc');
        }, 'route']);

        // Group seats by row for intuitive bus visual matrix
        $seatsByRow = $trip->bus->busSeats->groupBy('row');
        $bookedSeatIds = $trip->getBookedSeatIds();

        return view('trips.show', compact('trip', 'seatsByRow', 'bookedSeatIds'));
    }
}
