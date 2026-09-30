<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Exceptions\NitipException;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Direct P2P payment: requester transfers to the fulfiller's e-wallet / bank account (users.payment_*),
 * uploads the transfer receipt, fulfiller verifies the incoming funds. State lives on the order.
 */
class PaymentService
{
    public function __construct(
        private readonly OrderWorkflowService $workflow,
        private readonly FileStorageService $files,
    ) {}

    public function submitProof(Order $order, User $payer, UploadedFile $proof, ?string $note = null): Order
    {
        if (! $order->isRequester($payer)) {
            throw new NitipException('Hanya penitip yang bisa mengunggah bukti pembayaran.');
        }

        if ($order->status !== OrderStatus::AwaitingPayment) {
            throw new NitipException('Pesanan tidak sedang menunggu pembayaran.');
        }

        $fulfiller = $order->fulfiller;

        if (! $fulfiller || ! $fulfiller->hasPaymentMethod()) {
            throw new NitipException('Relawan belum mengatur metode pembayaran. Hubungi relawan via WhatsApp.');
        }

        return DB::transaction(function () use ($order, $payer, $fulfiller, $proof, $note) {
            $order->forceFill([
                'payment_method' => $fulfiller->paymentSummary(),
                'payment_proof_path' => $this->files->replacePrivate($order->payment_proof_path, $proof, "orders/{$order->id}"),
                'payment_note' => $note,
                'payment_submitted_at' => now(),
                'payment_verified_at' => null,
                'payment_rejection_reason' => null,
            ])->save();

            return $this->workflow->markPaymentSubmitted($order, $payer);
        });
    }

    public function verify(Order $order, User $actor): Order
    {
        if (! $order->isFulfiller($actor)) {
            throw new NitipException('Hanya relawan yang bisa memverifikasi pembayaran.');
        }

        return $this->workflow->markPaid($order, $actor);
    }

    public function reject(Order $order, User $actor, string $reason): Order
    {
        if (! $order->isFulfiller($actor)) {
            throw new NitipException('Hanya relawan yang bisa menolak pembayaran.');
        }

        return $this->workflow->markPaymentRejected($order, $actor, $reason);
    }
}
