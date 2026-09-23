<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BusRequest;
use App\Models\Bus;
use App\Services\Audit\AuditLogger;

class BusController extends Controller
{
    public function index()
    {
        $buses = Bus::withCount('busSeats')->latest()->paginate(10);

        return view('admin.buses.index', compact('buses'));
    }

    public function create()
    {
        return view('admin.buses.create');
    }

    public function store(BusRequest $request)
    {
        $validated = $request->validated();

        $facilitiesArray = [];
        if (! empty($validated['facilities'])) {
            $facilitiesArray = array_values(array_filter(array_map('trim', explode("\n", str_replace(',', "\n", $validated['facilities'])))));
        }

        $bus = Bus::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'type' => $validated['type'],
            'seat_capacity' => $validated['seat_capacity'],
            'facilities' => $facilitiesArray,
            'description' => $validated['description'],
            'status' => $validated['status'],
        ]);

        // Automatically generate seats for the new bus
        $bus->generateSeats($bus->seat_capacity);

        return redirect()->route('admin.buses.index')
            ->with('success', "Armada {$bus->name} ({$bus->code}) berhasil ditambahkan beserta {$bus->seat_capacity} kursi!");
    }

    public function show(Bus $bus)
    {
        $bus->load(['busSeats' => function ($q) {
            $q->orderBy('row')->orderBy('column');
        }]);

        $seatsByRow = $bus->busSeats->groupBy('row');

        return view('admin.buses.show', compact('bus', 'seatsByRow'));
    }

    public function edit(Bus $bus)
    {
        return view('admin.buses.edit', compact('bus'));
    }

    public function update(BusRequest $request, Bus $bus)
    {
        $validated = $request->validated();

        $facilitiesArray = [];
        if (! empty($validated['facilities'])) {
            $facilitiesArray = array_values(array_filter(array_map('trim', explode("\n", str_replace(',', "\n", $validated['facilities'])))));
        }

        $oldCapacity = $bus->seat_capacity;

        $bus->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'type' => $validated['type'],
            'seat_capacity' => $validated['seat_capacity'],
            'facilities' => $facilitiesArray,
            'description' => $validated['description'],
            'status' => $validated['status'],
        ]);

        if ($request->boolean('regenerate_seats') || $oldCapacity !== (int) $validated['seat_capacity']) {
            $bus->generateSeats($bus->seat_capacity);
        }

        return redirect()->route('admin.buses.index')
            ->with('success', "Data armada {$bus->name} berhasil diperbarui!");
    }

    public function destroy(Bus $bus)
    {
        if ($bus->trips()->exists()) {
            return back()->with('error', "Armada {$bus->name} masih terkait dengan jadwal perjalanan dan tidak dapat dihapus. Nonaktifkan status armada jika sedang tidak beroperasi.");
        }

        $busId = $bus->id;
        $name = $bus->name;
        $code = $bus->code;
        $bus->delete();

        AuditLogger::log('bus_deleted', 'bus', $busId, [
            'name' => $name,
            'code' => $code,
        ]);

        return redirect()->route('admin.buses.index')
            ->with('success', "Armada {$name} ({$code}) berhasil dihapus.");
    }
}
