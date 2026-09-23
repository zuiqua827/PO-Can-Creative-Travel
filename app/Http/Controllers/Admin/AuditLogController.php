<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display a paginated listing of system and security audit logs.
     */
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->latest('created_at');

        if ($request->filled('action')) {
            $query->where('action', 'like', "%{$request->action}%");
        }

        if ($request->filled('actor')) {
            $actor = $request->actor;
            $query->where(function ($q) use ($actor) {
                $q->where('actor_name', 'like', "%{$actor}%")
                    ->orWhereHas('user', function ($sub) use ($actor) {
                        $sub->where('name', 'like', "%{$actor}%")
                            ->orWhere('email', 'like', "%{$actor}%");
                    });
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->paginate(20)->withQueryString();

        return view('admin.audit-logs.index', compact('logs'));
    }
}
