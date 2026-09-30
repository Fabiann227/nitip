<?php

namespace App\Http\Controllers;

use App\Enums\DisputeReason;
use App\Http\Requests\Orders\StoreDisputeRequest;
use App\Models\Dispute;
use App\Models\Order;
use App\Services\DisputeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DisputeController extends Controller
{
    public function store(StoreDisputeRequest $request, Order $order, DisputeService $disputes): RedirectResponse
    {
        $this->authorize('dispute', $order);

        $dispute = $disputes->open(
            $order,
            $request->user(),
            DisputeReason::from($request->validated('reason')),
            $request->validated('description'),
            $request->file('evidence'),
        );

        return redirect()->route('disputes.show', $dispute)
            ->with('success', 'Sengketa dibuka. Admin Nitip akan meninjau dan menghubungi kedua pihak.');
    }

    public function show(Dispute $dispute): View
    {
        $this->authorize('view', $dispute);

        $dispute->load(['order.requester', 'order.fulfiller', 'order.category', 'openedBy', 'resolvedBy']);

        return view('orders.dispute', ['dispute' => $dispute, 'order' => $dispute->order]);
    }
}
