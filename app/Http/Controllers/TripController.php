<?php

namespace App\Http\Controllers;

use App\Enums\TripStatus;
use App\Http\Requests\Orders\ReasonRequest;
use App\Http\Requests\Trips\StoreTripRequest;
use App\Models\ServiceCategory;
use App\Models\Trip;
use App\Services\TripService;
use App\Support\Campuses;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * OFFER stream: a fulfiller posts a route with quota; requesters join it.
 */
class TripController extends Controller
{
    public function __construct(private readonly TripService $trips) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $filter = $request->query('status', 'open');

        $query = Trip::query()->where('fulfiller_id', $user->id);

        match ($filter) {
            'closed' => $query->where('status', TripStatus::Closed->value),
            'cancelled' => $query->where('status', TripStatus::Cancelled->value),
            'all' => $query,
            default => $query->where('status', TripStatus::Open->value),
        };

        return view('trips.index', [
            'trips' => $query->with('category')->withCount('activeOrders')->latest('departure_at')->paginate(10)->withQueryString(),
            'filter' => in_array($filter, ['open', 'closed', 'cancelled', 'all'], true) ? $filter : 'open',
        ]);
    }

    public function create(Request $request): View
    {
        return view('trips.create', $this->formData($request) + ['trip' => null]);
    }

    public function store(StoreTripRequest $request): RedirectResponse
    {
        $trip = $this->trips->create($request->user(), $request->validated());

        return redirect()->route('trips.show', $trip)
            ->with('success', "Rute {$trip->code} sudah tayang! Penitip di kampusmu bisa langsung ikut.");
    }

    public function show(Request $request, Trip $trip): View
    {
        $this->authorize('view', $trip);

        $trip->load(['fulfiller', 'category'])->loadCount('activeOrders');

        $user = $request->user();
        $isOwner = $trip->isOwnedBy($user);

        $orders = $isOwner || $user->isAdmin()
            ? $trip->orders()->with(['requester', 'category'])->latest()->get()
            : $trip->orders()->where('requester_id', $user->id)->with(['category'])->latest()->get();

        return view('trips.show', [
            'trip' => $trip,
            'isOwner' => $isOwner,
            'orders' => $orders,
            'fulfillerRating' => $trip->fulfiller->ratingAverage(),
            'fulfillerRatingCount' => $trip->fulfiller->ratingCount(),
            'fulfillerTrips' => $trip->fulfiller->completedDeliveriesCount(),
        ]);
    }

    public function edit(Request $request, Trip $trip): View
    {
        $this->authorize('update', $trip);

        $trip->loadCount('activeOrders');

        return view('trips.create', $this->formData($request) + ['trip' => $trip]);
    }

    public function update(StoreTripRequest $request, Trip $trip): RedirectResponse
    {
        $this->authorize('update', $trip);

        $this->trips->update($trip, $request->validated());

        return redirect()->route('trips.show', $trip)->with('success', 'Rute diperbarui.');
    }

    public function close(Request $request, Trip $trip): RedirectResponse
    {
        $this->authorize('close', $trip);

        $this->trips->close($trip, $request->user());

        return back()->with('success', 'Rute ditutup. Titipan yang sudah masuk tetap berjalan.');
    }

    public function cancel(ReasonRequest $request, Trip $trip): RedirectResponse
    {
        $this->authorize('cancel', $trip);

        $this->trips->cancel($trip, $request->user(), $request->validated('reason'));

        return back()->with('success', 'Rute dibatalkan. Titipan yang belum dibayar ikut dibatalkan.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Request $request): array
    {
        return [
            'categories' => ServiceCategory::query()->active()->ordered()->get(),
            'locations' => Campuses::locations($request->user()->campus),
            'closeBefore' => (int) config('nitip.trips.close_before_minutes', 15),
        ];
    }
}
