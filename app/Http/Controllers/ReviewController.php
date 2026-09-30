<?php

namespace App\Http\Controllers;

use App\Http\Requests\Orders\StoreReviewRequest;
use App\Models\Order;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request, Order $order, ReviewService $reviews): RedirectResponse
    {
        $this->authorize('review', $order);

        $reviews->create(
            $order,
            $request->user(),
            (int) $request->validated('rating'),
            $request->validated('comment'),
        );

        return back()->with('success', 'Terima kasih! Ulasanmu membantu komunitas Nitip lebih terpercaya.');
    }
}
