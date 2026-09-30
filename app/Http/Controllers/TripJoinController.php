<?php

namespace App\Http\Controllers;

use App\Http\Requests\Orders\JoinTripRequest;
use App\Models\Trip;
use App\Services\OrderService;
use App\Support\Campuses;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TripJoinController extends Controller
{
    public function create(Request $request, Trip $trip): View
    {
        $this->authorize('join', $trip);

        $trip->load(['fulfiller', 'category'])->loadCount('activeOrders');

        return view('trips.join', [
            'trip' => $trip,
            'category' => $trip->category,
            'locations' => Campuses::locations($trip->campus),
        ]);
    }

    public function store(JoinTripRequest $request, Trip $trip, OrderService $orders): RedirectResponse
    {
        $this->authorize('join', $trip);

        $order = $orders->joinTrip($trip, $request->user(), $request->validated(), $request->file('document'));

        return redirect()->route('orders.show', $order)
            ->with('success', "Titipan {$order->code} masuk ke rute {$trip->fulfiller->shortName()}. Lanjutkan pembayaran.");
    }
}
