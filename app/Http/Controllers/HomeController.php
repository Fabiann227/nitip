<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\ServiceCategory;
use App\Models\Trip;
use App\Models\User;
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
                ->with(['requester', 'category'])->orderBy('needed_by')->limit(1)->get(),
            'openTrips' => Trip::query()->open()->forCampus($campus)
                ->with(['fulfiller', 'category'])->withCount('activeOrders')->orderBy('departure_at')->limit(1)->get(),
            'categories' => ServiceCategory::query()->active()->ordered()->get(),
            'stats' => [
                'students' => User::query()->students()->whereNotNull('email_verified_at')->count(),
                'completed' => (clone $completed)->count(),
            ],
        ]);
    }
}
