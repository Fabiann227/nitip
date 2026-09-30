<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TripStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\ReasonRequest;
use App\Models\Trip;
use App\Services\TripService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TripController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $q = trim((string) $request->query('q', '')) ?: null;

        return view('admin.trips.index', [
            'trips' => Trip::query()
                ->with(['fulfiller', 'category'])
                ->withCount(['orders', 'activeOrders'])
                ->search($q)
                ->when($status && TripStatus::tryFrom($status), fn ($query) => $query->where('status', $status))
                ->latest('departure_at')
                ->paginate(15)
                ->withQueryString(),
            'filters' => ['status' => $status, 'q' => $q],
            'statuses' => TripStatus::cases(),
        ]);
    }

    public function cancel(ReasonRequest $request, Trip $trip, TripService $trips): RedirectResponse
    {
        $trips->cancel($trip, $request->user(), $request->validated('reason'));

        return back()->with('success', "Rute {$trip->code} dibatalkan oleh admin.");
    }
}
