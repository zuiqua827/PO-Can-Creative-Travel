<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\Route;
use App\Models\Trip;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function index(Request $request)
    {
        $origin = $request->input('origin');
        $destination = $request->input('destination');
        $date = $request->input('date');
        $busType = $request->input('bus_type');
        $timeSlot = $request->input('time_slot'); // morning, afternoon, night
        $sortBy = $request->input('sort', 'departure_asc'); // price_asc, price_desc, departure_asc, departure_desc
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');

        $query = Trip::with(['bus', 'route'])
            ->withBookedSeatsCount()
            ->where('status', 'scheduled')
            ->where('departure_at', '>', now());

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
        }

        // Filter bus type / class
        if ($busType) {
            $query->whereHas('bus', function ($q) use ($busType) {
                $q->where('type', $busType);
            });
        }

        // Filter price range
        if ($minPrice !== null && $minPrice !== '') {
            $query->where('price', '>=', (float) $minPrice);
        }
        if ($maxPrice !== null && $maxPrice !== '') {
            $query->where('price', '<=', (float) $maxPrice);
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
            'minPrice',
            'maxPrice',
            'origins',
            'destinations',
            'busTypes'
        ));
    }

    public function show(Trip $trip)
    {
        // Trip must be bookable
        if ($trip->status !== 'scheduled' || $trip->departure_at->isPast()) {
            return redirect()->route('trips.index')
                ->with('error', 'Jadwal perjalanan ini sudah tidak dapat dipesan atau telah lewat.');
        }

        $trip->load(['bus.busSeats' => function ($q) {
            $q->orderBy('row', 'asc')->orderBy('column', 'asc');
        }, 'route']);

        // Group seats by row for intuitive bus visual matrix
        $seatsByRow = $trip->bus->busSeats->groupBy('row');
        $bookedSeatIds = $trip->getBookedSeatIds();

        return view('trips.show', compact('trip', 'seatsByRow', 'bookedSeatIds'));
    }
}
