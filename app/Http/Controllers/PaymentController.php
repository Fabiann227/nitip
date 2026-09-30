<?php

namespace App\Http\Controllers;

use App\Http\Requests\Orders\ReasonRequest;
use App\Http\Requests\Orders\SubmitPaymentRequest;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function store(SubmitPaymentRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('pay', $order);

        $this->payments->submitProof($order, $request->user(), $request->file('proof'), $request->validated('note'));

        return back()->with('success', 'Bukti transfer terkirim. Tunggu relawan memverifikasi pembayaranmu.');
    }

    public function verify(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('verifyPayment', $order);

        $this->payments->verify($order, $request->user());

        return back()->with('success', 'Pembayaran diverifikasi. Silakan mulai proses pesanan.');
    }

    public function reject(ReasonRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('verifyPayment', $order);

        $this->payments->reject($order, $request->user(), $request->validated('reason'));

        return back()->with('success', 'Bukti pembayaran ditolak. Penitip diminta mengunggah ulang.');
    }
}
