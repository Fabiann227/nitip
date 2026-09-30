<?php

namespace App\Http\Controllers;

use App\Http\Requests\Orders\CompleteOrderRequest;
use App\Http\Requests\Orders\DeliverRequest;
use App\Http\Requests\Orders\MarkDeliveredRequest;
use App\Http\Requests\Orders\ReasonRequest;
use App\Models\Order;
use App\Services\OrderWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Lifecycle actions. Every transition goes through OrderWorkflowService.
 */
class OrderActionController extends Controller
{
    public function __construct(private readonly OrderWorkflowService $workflow) {}

    public function claim(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('claim', $order);

        $this->workflow->claim($order, $request->user());

        return redirect()->route('orders.show', $order)
            ->with('success', 'Kamu mengambil titipan ini! Hubungi penitip dan tunggu pembayaran masuk.');
    }

    public function release(ReasonRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('release', $order);

        $this->workflow->release($order, $request->user(), $request->validated('reason'));

        return redirect()->route('orders.index', ['role' => 'fulfiller'])
            ->with('success', 'Pesanan dilepas dan kembali tayang untuk relawan lain.');
    }

    public function start(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('start', $order);

        $this->workflow->start($order, $request->user());

        return back()->with('success', 'Status diperbarui: pesanan sedang diproses.');
    }

    public function deliver(DeliverRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('deliver', $order);

        $this->workflow->startDelivering(
            $order,
            $request->user(),
            $request->filled('actual_item_cost') ? (int) $request->validated('actual_item_cost') : null,
            $request->file('receipt'),
        );

        return back()->with('success', 'Biaya riil tercatat. Status: pesanan sedang diantar.');
    }

    public function delivered(MarkDeliveredRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('markDelivered', $order);

        $this->workflow->markDelivered($order, $request->user(), $request->file('receipt'), $request->validated('note'));

        return back()->with('success', 'Serah terima dicatat. Minta penitip konfirmasi atau masukkan PIN-nya.');
    }

    public function complete(CompleteOrderRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('complete', $order);

        $this->workflow->complete($order, $request->user(), $request->validated('pin'));

        return back()->with('success', 'Transaksi selesai! Jangan lupa beri ulasan.');
    }

    public function cancel(ReasonRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('cancel', $order);

        $this->workflow->cancel($order, $request->user(), $request->validated('reason'));

        return back()->with('success', 'Pesanan dibatalkan.');
    }
}
