<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\ReasonRequest;
use App\Models\Order;
use App\Models\ServiceCategory;
use App\Services\OrderWorkflowService;
use App\Support\Campuses;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', '')) ?: null;
        $status = $request->query('status');
        $categoryId = (int) $request->query('category') ?: null;
        $campus = (string) $request->query('campus') ?: null;

        $orders = Order::query()
            ->with(['requester', 'fulfiller', 'category'])
            ->search($q)
            ->when($status && OrderStatus::tryFrom($status), fn ($query) => $query->where('status', $status))
            ->when($status === 'refund', fn ($query) => $query->where('needs_refund', true))
            ->when($categoryId, fn ($query) => $query->where('service_category_id', $categoryId))
            ->when($campus, fn ($query) => $query->where('campus', $campus))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'statuses' => OrderStatus::cases(),
            'categories' => ServiceCategory::query()->ordered()->get(),
            'campuses' => Campuses::all(),
            'filters' => ['q' => $q, 'status' => $status, 'category' => $categoryId, 'campus' => $campus],
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['requester', 'fulfiller', 'trip', 'category', 'events.actor', 'reviews.reviewer', 'dispute.openedBy', 'cancelledBy']);

        return view('admin.orders.show', ['order' => $order]);
    }

    public function cancel(ReasonRequest $request, Order $order, OrderWorkflowService $workflow): RedirectResponse
    {
        $workflow->cancel($order, $request->user(), $request->validated('reason'));

        return back()->with('success', "Pesanan {$order->code} dibatalkan oleh admin.");
    }
}
