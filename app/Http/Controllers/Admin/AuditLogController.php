<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderEvent;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Audit trail = every order transition recorded in order_events (PDF: Laravel Audit Trail).
 */
class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', '')) ?: null;
        $type = (string) $request->query('type') ?: null;

        return view('admin.audit-logs', [
            'events' => OrderEvent::query()->with(['order', 'actor'])
                ->when($type, fn ($query) => $query->where('type', $type))
                ->when($q, fn ($query) => $query->where(fn ($w) => $w->where('description', 'like', "%{$q}%")->orWhereHas('order', fn ($o) => $o->where('code', 'like', "%{$q}%"))))
                ->latest('created_at')->latest('id')->paginate(30)->withQueryString(),
            'q' => $q,
            'type' => $type,
            'types' => OrderEvent::query()->select('type')->distinct()->orderBy('type')->pluck('type'),
        ]);
    }
}
