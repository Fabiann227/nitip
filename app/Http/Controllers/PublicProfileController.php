<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicProfileController extends Controller
{
    public function show(Request $request, User $user): View
    {
        abort_unless($user->isStudent() || $request->user()->isAdmin(), 404);

        return view('pages.profile', [
            'profile' => $user,
            'isMe' => $user->id === $request->user()->id,
            'stats' => [
                'deliveries' => Order::query()->where('fulfiller_id', $user->id)->where('status', OrderStatus::Completed->value)->count(),
                'requests' => Order::query()->where('requester_id', $user->id)->where('status', OrderStatus::Completed->value)->count(),
                'rating' => $user->ratingAverage(),
                'rating_count' => $user->ratingCount(),
                'member_since' => $user->created_at,
            ],
            'reviews' => $user->reviewsReceived()->with(['reviewer', 'order'])->latest()->limit(10)->get(),
            'openTrips' => Trip::query()->where('fulfiller_id', $user->id)->open()->with('category')->withCount('activeOrders')->orderBy('departure_at')->get(),
            'openRequests' => Order::query()->openRequests()->where('requester_id', $user->id)->with('category')->orderBy('needed_by')->get(),
        ]);
    }
}
