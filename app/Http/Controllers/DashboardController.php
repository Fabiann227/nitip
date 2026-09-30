<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $requesterActive = Order::query()->where('requester_id', $user->id)->active()
            ->with(['category', 'fulfiller'])->latest('updated_at')->limit(5)->get();

        $fulfillerActive = Order::query()->where('fulfiller_id', $user->id)->active()
            ->with(['category', 'requester'])->latest('updated_at')->limit(5)->get();

        $needsAction = $requesterActive->filter(fn (Order $o) => in_array($o->status, [OrderStatus::AwaitingPayment, OrderStatus::Delivered], true))
            ->merge($fulfillerActive->filter(fn (Order $o) => in_array($o->status, [OrderStatus::PaymentSubmitted, OrderStatus::Paid], true)));

        $monthStart = now()->startOfMonth();

        return view('pages.dashboard', [
            'user' => $user,
            'stats' => [
                'active_requests' => Order::query()->where('requester_id', $user->id)->active()->count(),
                'active_jobs' => Order::query()->where('fulfiller_id', $user->id)->active()->count(),
                'completed_jobs' => Order::query()->where('fulfiller_id', $user->id)->where('status', OrderStatus::Completed->value)->count(),
                'earnings_month' => (int) Order::query()->where('fulfiller_id', $user->id)->where('status', OrderStatus::Completed->value)
                    ->where('completed_at', '>=', $monthStart)->sum('service_fee'),
                'rating' => $user->ratingAverage(),
                'rating_count' => $user->ratingCount(),
            ],
            'needsAction' => $needsAction->unique('id')->values(),
            'requesterActive' => $requesterActive,
            'fulfillerActive' => $fulfillerActive,
            'myOpenTrips' => Trip::query()->where('fulfiller_id', $user->id)->open()->withCount('activeOrders')->orderBy('departure_at')->limit(3)->get(),
            'feedRequests' => Order::query()->openRequests()->forCampus($user->campus)
                ->where('requester_id', '!=', $user->id)
                ->with(['requester', 'category'])->orderBy('needed_by')->limit(4)->get(),
            'feedTrips' => Trip::query()->open()->forCampus($user->campus)
                ->where('fulfiller_id', '!=', $user->id)
                ->with(['fulfiller', 'category'])->withCount('activeOrders')->orderBy('departure_at')->limit(3)->get(),
            'hasPaymentMethod' => $user->hasPaymentMethod(),
            'recentNotifications' => $user->notifications()->latest()->limit(5)->get(),
        ]);
    }
}
