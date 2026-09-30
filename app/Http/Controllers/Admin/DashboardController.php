<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\Review;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $byStatus = Order::query()->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status');
        $completed = (int) ($byStatus[OrderStatus::Completed->value] ?? 0);
        $cancelled = (int) ($byStatus[OrderStatus::Cancelled->value] ?? 0);
        $matched = Order::query()->whereNotNull('matched_at')->count();

        $completedOrders = Order::query()->where('status', OrderStatus::Completed->value);

        $days = collect(range(13, 0))->map(function (int $daysAgo) {
            $day = now()->subDays($daysAgo)->startOfDay();

            return [
                'label' => $day->translatedFormat('d M'),
                'created' => Order::query()->whereBetween('created_at', [$day, $day->copy()->endOfDay()])->count(),
                'completed' => Order::query()->whereBetween('completed_at', [$day, $day->copy()->endOfDay()])->count(),
            ];
        });

        return view('admin.dashboard', [
            'stats' => [
                'users' => User::query()->students()->count(),
                'verified' => User::query()->students()->whereNotNull('email_verified_at')->count(),
                'suspended' => User::query()->students()->where('is_suspended', true)->count(),
                'new_users_week' => User::query()->students()->where('created_at', '>=', now()->subDays(7))->count(),
                'orders' => Order::query()->count(),
                'orders_active' => Order::query()->active()->count(),
                'completed' => $completed,
                'cancelled' => $cancelled,
                'completion_rate' => $matched > 0 ? round($completed / $matched * 100, 1) : 0,
                'gmv' => (int) (clone $completedOrders)->selectRaw('COALESCE(SUM(service_fee + COALESCE(actual_item_cost, estimated_item_cost)), 0) as gmv')->value('gmv'),
                'fees' => (int) (clone $completedOrders)->sum('service_fee'),
                'avg_rating' => round((float) (Review::query()->avg('rating') ?? 0), 2),
                'reviews' => Review::query()->count(),
                'open_disputes' => Dispute::query()->open()->count(),
                'open_trips' => Trip::query()->open()->count(),
                'open_requests' => Order::query()->openRequests()->count(),
                'needs_refund' => Order::query()->where('needs_refund', true)->count(),
            ],
            'byStatus' => collect(OrderStatus::cases())->map(fn (OrderStatus $s) => [
                'status' => $s,
                'total' => (int) ($byStatus[$s->value] ?? 0),
            ]),
            'days' => $days,
            'maxDay' => max(1, (int) $days->max(fn ($d) => max($d['created'], $d['completed']))),
            'topFulfillers' => User::query()->students()
                ->withCount(['fulfilledOrders as completed_deliveries_count' => fn ($q) => $q->where('status', OrderStatus::Completed->value)])
                ->withAvg('reviewsReceived as reviews_received_avg_rating', 'rating')
                ->orderByDesc('completed_deliveries_count')->limit(5)->get(),
            'recentOrders' => Order::query()->with(['requester', 'fulfiller', 'category'])->latest()->limit(8)->get(),
            'openDisputes' => Dispute::query()->open()->with(['order', 'openedBy'])->latest()->limit(5)->get(),
            'recentEvents' => OrderEvent::query()->with(['order', 'actor'])->latest('created_at')->latest('id')->limit(8)->get(),
        ]);
    }
}
