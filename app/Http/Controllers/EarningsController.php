<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EarningsController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $completed = Order::query()->where('fulfiller_id', $user->id)->where('status', OrderStatus::Completed->value);

        $monthStart = now()->startOfMonth();
        $sixMonthsAgo = now()->startOfMonth()->subMonths(5);

        $rows = (clone $completed)->where('completed_at', '>=', $sixMonthsAgo)
            ->get(['service_fee', 'completed_at']);

        $monthly = collect(range(0, 5))->map(function (int $i) use ($sixMonthsAgo, $rows) {
            $month = $sixMonthsAgo->copy()->addMonths($i);
            $inMonth = $rows->filter(fn (Order $o) => $o->completed_at && $o->completed_at->isSameMonth($month));

            return [
                'label' => $month->translatedFormat('M'),
                'total' => (int) $inMonth->sum('service_fee'),
                'count' => $inMonth->count(),
            ];
        });

        $pending = Order::query()->where('fulfiller_id', $user->id)->active()->sum('service_fee');

        return view('pages.earnings', [
            'stats' => [
                'total' => (int) (clone $completed)->sum('service_fee'),
                'count' => (clone $completed)->count(),
                'month' => (int) (clone $completed)->where('completed_at', '>=', $monthStart)->sum('service_fee'),
                'month_count' => (clone $completed)->where('completed_at', '>=', $monthStart)->count(),
                'pending' => (int) $pending,
                'rating' => $user->ratingAverage(),
                'rating_count' => $user->ratingCount(),
            ],
            'monthly' => $monthly,
            'maxMonthly' => max(1, (int) $monthly->max('total')),
            'recent' => (clone $completed)->with(['requester', 'category'])->latest('completed_at')->paginate(10),
            'reviews' => $user->reviewsReceived()->with(['reviewer', 'order'])->latest()->limit(5)->get(),
        ]);
    }
}
