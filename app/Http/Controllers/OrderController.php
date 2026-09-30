<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $role = $request->query('role') === 'fulfiller' ? 'fulfiller' : 'requester';
        $filter = $request->query('status', 'active');
        $q = trim((string) $request->query('q', '')) ?: null;

        $query = Order::query()->where($role === 'fulfiller' ? 'fulfiller_id' : 'requester_id', $user->id)->search($q);

        match ($filter) {
            'completed' => $query->where('status', OrderStatus::Completed->value),
            'cancelled' => $query->where('status', OrderStatus::Cancelled->value),
            'all' => $query,
            default => $query->active(),
        };

        return view('orders.index', [
            'orders' => $query->with(['category', 'requester', 'fulfiller', 'trip'])->latest('updated_at')->paginate(10)->withQueryString(),
            'role' => $role,
            'filter' => in_array($filter, ['active', 'completed', 'cancelled', 'all'], true) ? $filter : 'active',
            'q' => $q,
            'counts' => [
                'requester' => Order::query()->where('requester_id', $user->id)->active()->count(),
                'fulfiller' => Order::query()->where('fulfiller_id', $user->id)->active()->count(),
            ],
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        $this->authorize('view', $order);

        $order->load(['requester', 'fulfiller', 'trip', 'category', 'events.actor', 'reviews.reviewer', 'dispute.openedBy', 'dispute.resolvedBy', 'cancelledBy']);

        $user = $request->user();
        $counterpart = $order->counterpartFor($user);

        return view('orders.show', [
            'order' => $order,
            'role' => $order->roleFor($user),
            'counterpart' => $counterpart,
            'whatsappUrl' => $counterpart?->whatsappUrl($order->whatsappText($user)),
            'myReview' => $order->reviews->firstWhere('reviewer_id', $user->id),
            'theirReview' => $counterpart ? $order->reviews->firstWhere('reviewer_id', $counterpart->id) : null,
        ]);
    }
}
