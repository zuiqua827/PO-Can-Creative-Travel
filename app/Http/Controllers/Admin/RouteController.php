<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RouteRequest;
use App\Models\Route;

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

    public function store(RouteRequest $request)
    {
        $validated = $request->validated();

        Route::create($validated);

        return redirect()->route('admin.routes.index')
            ->with('success', "Rute baru {$validated['origin']} → {$validated['destination']} berhasil ditambahkan!");
    }

    public function edit(Route $route)
    {
        return view('admin.routes.edit', compact('route'));
    }

    public function update(RouteRequest $request, Route $route)
    {
        $validated = $request->validated();

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
