<?php

namespace App\Http\Controllers;

use App\Http\Requests\Orders\StoreRequestRequest;
use App\Models\ServiceCategory;
use App\Services\OrderService;
use App\Support\Campuses;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * REQUEST stream: a requester posts what they need.
 */
class RequestController extends Controller
{
    public function create(Request $request): View
    {
        return view('orders.create', [
            'categories' => ServiceCategory::query()->active()->ordered()->get(),
            'locations' => Campuses::locations($request->user()->campus),
            'selectedCategory' => (int) $request->query('category') ?: null,
        ]);
    }

    public function store(StoreRequestRequest $request, OrderService $orders): RedirectResponse
    {
        $order = $orders->createRequest(
            $request->user(),
            $request->validated(),
            $request->file('document'),
        );

        return redirect()
            ->route('orders.show', $order)
            ->with('success', "Permintaan {$order->code} sudah tayang! Relawan yang searah bisa mengambilnya sekarang.");
    }
}
