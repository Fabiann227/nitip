<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Review;
use App\Models\ServiceCategory;
use App\Models\Trip;
use App\Models\User;
use App\Support\Campuses;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $campus = $user?->campus;

        $completed = Order::query()->where('status', OrderStatus::Completed->value);

        return view('pages.home', [
            'openRequests' => Order::query()->openRequests()->forCampus($campus)
                ->with(['requester', 'category'])->orderBy('needed_by')->limit(3)->get(),
            'openTrips' => Trip::query()->open()->forCampus($campus)
                ->with(['fulfiller', 'category'])->withCount('activeOrders')->orderBy('departure_at')->limit(2)->get(),
            'categories' => ServiceCategory::query()->active()->ordered()->get(),
            'campuses' => Campuses::all(),
            'stats' => [
                'students' => User::query()->students()->whereNotNull('email_verified_at')->count(),
                'completed' => (clone $completed)->count(),
                'fees_paid' => (int) (clone $completed)->sum('service_fee'),
                'avg_rating' => round((float) (Review::query()->avg('rating') ?? 0), 1),
                'open_requests' => Order::query()->openRequests()->count(),
                'open_trips' => Trip::query()->open()->count(),
            ],
        ]);
    }

    public function services(): View
    {
        return view('pages.services', [
            'categories' => ServiceCategory::query()->active()->ordered()->get(),
            'campuses' => Campuses::all(),
        ]);
    }
}
