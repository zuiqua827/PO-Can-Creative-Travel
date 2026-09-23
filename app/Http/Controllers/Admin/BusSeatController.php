<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BusSeatStatusRequest;
use App\Models\BusSeat;

class BusSeatController extends Controller
{
    public function updateStatus(BusSeatStatusRequest $request, BusSeat $busSeat)
    {
        $validated = $request->validated();

        $busSeat->update(['status' => $validated['status']]);

        return back()->with('success', "Status kursi {$busSeat->seat_number} berhasil diubah menjadi {$validated['status']}.");
    }
}
