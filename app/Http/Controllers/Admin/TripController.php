<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\Route;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TripController extends Controller
{
    public function index(Request $request)
    {
        $query = Trip::with(['bus', 'route'])->latest('departure_at');

        if ($request->filled('route_id')) {
            $query->where('route_id', $request->route_id);
        }
        if ($request->filled('bus_id')) {
            $query->where('bus_id', $request->bus_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date')) {
            $query->whereDate('departure_at', $request->date);
        }

        $trips = $query->paginate(10)->withQueryString();
        $routes = Route::where('status', 'active')->get();
        $buses = Bus::where('status', 'active')->get();

        return view('admin.trips.index', compact('trips', 'routes', 'buses'));
    }

    public function create()
    {
        $routes = Route::where('status', 'active')->get();
        $buses = Bus::where('status', 'active')->get();

        return view('admin.trips.create', compact('routes', 'buses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bus_id' => ['required', 'exists:buses,id'],
            'route_id' => ['required', 'exists:routes,id'],
            'departure_at' => ['required', 'date'],
            'arrival_at' => ['required', 'date', 'after:departure_at'],
            'price' => ['required', 'numeric', 'min:10000'],
            'boarding_point' => ['nullable', 'string', 'max:255'],
            'drop_off_point' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:scheduled,boarding,departed,completed,cancelled'],
        ]);

        $route = Route::findOrFail($validated['route_id']);
        $depTime = Carbon::parse($validated['departure_at']);
        $tripCode = 'TRIP-' . strtoupper(Str::slug(substr($route->origin, 0, 3) . substr($route->destination, 0, 3))) . '-' . $depTime->format('ymd') . '-' . rand(10, 99);

        Trip::create(array_merge($validated, [
            'trip_code' => $tripCode,
            'boarding_point' => $validated['boarding_point'] ?: $route->origin,
            'drop_off_point' => $validated['drop_off_point'] ?: $route->destination,
        ]));

        return redirect()->route('admin.trips.index')
            ->with('success', "Jadwal perjalanan baru ({$tripCode}) berhasil diterbitkan!");
    }

    public function edit(Trip $trip)
    {
        $routes = Route::where('status', 'active')->get();
        $buses = Bus::where('status', 'active')->get();

        return view('admin.trips.edit', compact('trip', 'routes', 'buses'));
    }

    public function update(Request $request, Trip $trip)
    {
        $validated = $request->validate([
            'bus_id' => ['required', 'exists:buses,id'],
            'route_id' => ['required', 'exists:routes,id'],
            'departure_at' => ['required', 'date'],
            'arrival_at' => ['required', 'date', 'after:departure_at'],
            'price' => ['required', 'numeric', 'min:10000'],
            'boarding_point' => ['nullable', 'string', 'max:255'],
            'drop_off_point' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:scheduled,boarding,departed,completed,cancelled'],
        ]);

        $trip->update($validated);

        return redirect()->route('admin.trips.index')
            ->with('success', "Jadwal perjalanan {$trip->trip_code} berhasil diperbarui!");
    }

    public function destroy(Trip $trip)
    {
        $code = $trip->trip_code;
        $trip->delete();

        return redirect()->route('admin.trips.index')
            ->with('success', "Jadwal {$code} berhasil dihapus.");
    }
}
