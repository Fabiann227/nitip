<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ServiceCategory;
use App\Models\Trip;
use App\Support\Campuses;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExploreController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $tab = $request->query('tab') === 'trips' ? 'trips' : 'requests';
        $categoryId = (int) $request->query('category') ?: null;
        $campus = $request->has('campus') ? ((string) $request->query('campus') ?: null) : $user->campus;
        $q = trim((string) $request->query('q', '')) ?: null;

        $requestsQuery = Order::query()->openRequests()->forCampus($campus)
            ->when($categoryId, fn ($query) => $query->where('service_category_id', $categoryId))
            ->search($q);

        $tripsQuery = Trip::query()->open()->forCampus($campus)
            ->when($categoryId, fn ($query) => $query->where('service_category_id', $categoryId))
            ->search($q);

        $counts = [
            'requests' => (clone $requestsQuery)->count(),
            'trips' => (clone $tripsQuery)->count(),
        ];

        return view('pages.explore', [
            'tab' => $tab,
            'requests' => $tab === 'requests'
                ? $requestsQuery->with(['requester', 'category'])->orderBy('needed_by')->paginate(12)->withQueryString()
                : null,
            'trips' => $tab === 'trips'
                ? $tripsQuery->with(['fulfiller', 'category'])->withCount('activeOrders')->orderBy('departure_at')->paginate(12)->withQueryString()
                : null,
            'counts' => $counts,
            'categories' => ServiceCategory::query()->active()->ordered()->get(),
            'campuses' => Campuses::all(),
            'filters' => ['category' => $categoryId, 'campus' => $campus, 'q' => $q],
        ]);
    }
}
