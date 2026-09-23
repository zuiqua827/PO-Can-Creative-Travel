<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusSeat;
use Illuminate\Http\Request;

class BusSeatController extends Controller
{
    public function updateStatus(Request $request, BusSeat $busSeat)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:available,blocked,maintenance'],
        ]);

        $busSeat->update(['status' => $validated['status']]);

        return back()->with('success', "Status kursi {$busSeat->seat_number} berhasil diubah menjadi {$validated['status']}.");
    }
}
