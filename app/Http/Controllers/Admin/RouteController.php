<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Route;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    public function index()
    {
        $routes = Route::withCount('trips')->latest()->paginate(10);
        return view('admin.routes.index', compact('routes'));
    }

    public function create()
    {
        return view('admin.routes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'origin' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255'],
            'distance' => ['nullable', 'string', 'max:100'],
            'estimated_duration' => ['nullable', 'string', 'max:100'],
            'base_price' => ['required', 'numeric', 'min:10000'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        Route::create($validated);

        return redirect()->route('admin.routes.index')
            ->with('success', "Rute baru {$validated['origin']} → {$validated['destination']} berhasil ditambahkan!");
    }

    public function edit(Route $route)
    {
        return view('admin.routes.edit', compact('route'));
    }

    public function update(Request $request, Route $route)
    {
        $validated = $request->validate([
            'origin' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255'],
            'distance' => ['nullable', 'string', 'max:100'],
            'estimated_duration' => ['nullable', 'string', 'max:100'],
            'base_price' => ['required', 'numeric', 'min:10000'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $route->update($validated);

        return redirect()->route('admin.routes.index')
            ->with('success', "Rute {$route->origin} → {$route->destination} berhasil diperbarui!");
    }

    public function destroy(Route $route)
    {
        $name = "{$route->origin} → {$route->destination}";
        $route->delete();

        return redirect()->route('admin.routes.index')
            ->with('success', "Rute {$name} berhasil dihapus.");
    }
}
