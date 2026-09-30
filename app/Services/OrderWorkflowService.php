<?php

namespace App\Services;

use App\Enums\OrderEventType;
use App\Enums\OrderStatus;
use App\Exceptions\InvalidOrderTransitionException;
use App\Exceptions\NitipException;
use App\Models\Order;
use App\Models\OrderEvent;
use App\Models\User;
use App\Notifications\OrderActivity;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Single entry point for every order status transition.
 * Enforces the PDF lifecycle: Post & Match -> Pay & Verify -> In Progress -> Delivering -> Completed.
 */
class OrderWorkflowService
{
    public function __construct(private readonly FileStorageService $files) {}

    /*
    |--------------------------------------------------------------------------
    | 1. Post & Match
    |--------------------------------------------------------------------------
    */

    public function claim(Order $order, User $fulfiller): Order
    {
        return DB::transaction(function () use ($order, $fulfiller) {
            $order = $this->lock($order);

            $this->assertStatus($order, [OrderStatus::Open], 'Permintaan ini sudah diambil relawan lain.');

            if ($order->isRequester($fulfiller)) {
                throw new NitipException('Kamu tidak bisa mengambil permintaanmu sendiri.');
            }

            if ($order->isExpired()) {
                throw new NitipException('Permintaan ini sudah melewati batas waktu yang dibutuhkan.');
            }

            if (! $fulfiller->hasPaymentMethod()) {
                throw new NitipException('Atur metode pembayaran (GoPay/BCA/QRIS) di pengaturan sebelum mengambil titipan.');
            }

            $order->forceFill([
                'fulfiller_id' => $fulfiller->id,
                'status' => OrderStatus::AwaitingPayment,
                'matched_at' => now(),
            ])->save();

            $this->record($order, $fulfiller, OrderEventType::Claimed, OrderStatus::Open, OrderStatus::AwaitingPayment,
                "{$fulfiller->shortName()} mengambil permintaan ini.");

            $order->requester->notify(new OrderActivity(
                $order,
                'Relawan ditemukan!',
                "{$fulfiller->shortName()} siap membantu titipan \"{$order->title}\". Lakukan pembayaran untuk melanjutkan.",
                'handshake',
                'success',
            ));

            return $order;
        });
    }

    public function release(Order $order, User $fulfiller, ?string $reason = null): Order
    {
        return DB::transaction(function () use ($order, $fulfiller, $reason) {
            $order = $this->lock($order);

            $this->assertStatus($order, [OrderStatus::AwaitingPayment, OrderStatus::PaymentSubmitted],
                'Pesanan tidak bisa dilepas pada tahap ini.');

            if (! $order->isRequest()) {
                throw new NitipException('Pesanan dari rute hanya bisa dibatalkan, bukan dilepas.');
            }

            $from = $order->status;

            $this->files->deletePrivate($order->payment_proof_path);

            $order->forceFill([
                'fulfiller_id' => null,
                'status' => OrderStatus::Open,
                'matched_at' => null,
                'payment_method' => null,
                'payment_proof_path' => null,
                'payment_note' => null,
                'payment_submitted_at' => null,
                'payment_rejection_reason' => null,
            ])->save();

            $this->record($order, $fulfiller, OrderEventType::Released, $from, OrderStatus::Open,
                "{$fulfiller->shortName()} melepas pesanan. Permintaan kembali tayang.".($reason ? " Alasan: {$reason}" : ''));

            $order->requester->notify(new OrderActivity(
                $order,
                'Relawan melepas pesananmu',
                "Permintaan \"{$order->title}\" kembali tayang dan mencari relawan lain.",
                'undo',
                'warning',
            ));

            return $order;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | 2. Pay & Verify  (payment fields are set by PaymentService)
    |--------------------------------------------------------------------------
    */

    public function markPaymentSubmitted(Order $order, User $payer): Order
    {
        $this->assertStatus($order, [OrderStatus::AwaitingPayment], 'Pesanan tidak sedang menunggu pembayaran.');

        $order->forceFill(['status' => OrderStatus::PaymentSubmitted])->save();

        $this->record($order, $payer, OrderEventType::PaymentSubmitted, OrderStatus::AwaitingPayment, OrderStatus::PaymentSubmitted,
            'Bukti transfer sebesar '.rupiah($order->estimatedTotal()).' diunggah via '.$order->payment_method.'.');

        $order->fulfiller?->notify(new OrderActivity(
            $order,
            'Bukti pembayaran masuk',
            "{$payer->shortName()} mengunggah bukti transfer ".rupiah($order->estimatedTotal()).'. Cek mutasi dan verifikasi.',
            'receipt_long',
            'info',
        ));

        return $order;
    }

    public function markPaid(Order $order, User $actor): Order
    {
        $this->assertStatus($order, [OrderStatus::PaymentSubmitted], 'Tidak ada pembayaran yang perlu diverifikasi.');

        $order->forceFill(['status' => OrderStatus::Paid, 'paid_at' => now(), 'payment_verified_at' => now()])->save();

        $this->record($order, $actor, OrderEventType::PaymentVerified, OrderStatus::PaymentSubmitted, OrderStatus::Paid,
            'Pembayaran '.rupiah($order->estimatedTotal()).' diverifikasi oleh relawan.');

        $order->requester->notify(new OrderActivity(
            $order,
            'Pembayaran terverifikasi',
            "Relawan sudah menerima pembayaranmu untuk \"{$order->title}\". Pesanan segera diproses.",
            'verified',
            'success',
        ));

        return $order;
    }

    public function markPaymentRejected(Order $order, User $actor, string $reason): Order
    {
        $this->assertStatus($order, [OrderStatus::PaymentSubmitted], 'Tidak ada pembayaran yang bisa ditolak.');

        $order->forceFill(['status' => OrderStatus::AwaitingPayment, 'payment_rejection_reason' => $reason])->save();

        $this->record($order, $actor, OrderEventType::PaymentRejected, OrderStatus::PaymentSubmitted, OrderStatus::AwaitingPayment,
            "Bukti pembayaran ditolak: {$reason}");

        $order->requester->notify(new OrderActivity(
            $order,
            'Bukti pembayaran ditolak',
            "Relawan menolak bukti transfer untuk \"{$order->title}\": {$reason}. Unggah ulang bukti yang benar.",
            'error',
            'danger',
        ));

        return $order;
    }

    /*
    |--------------------------------------------------------------------------
    | 3. In Progress  &  4. Delivering
    |--------------------------------------------------------------------------
    */

    public function start(Order $order, User $fulfiller): Order
    {
        $this->assertStatus($order, [OrderStatus::Paid], 'Pesanan belum dibayar atau sudah diproses.');

        $order->forceFill(['status' => OrderStatus::InProgress, 'started_at' => now()])->save();

        $verb = $order->category->isPrint() ? 'mencetak berkas' : 'membeli pesanan';
        $this->record($order, $fulfiller, OrderEventType::Started, OrderStatus::Paid, OrderStatus::InProgress,
            "Relawan mulai {$verb}.");

        $order->requester->notify(new OrderActivity(
            $order,
            'Pesanan sedang diproses',
            "{$fulfiller->shortName()} sedang {$verb} untuk \"{$order->title}\".",
            'shopping_cart_checkout',
        ));

        return $order;
    }

    /**
     * Purchase done: the fulfiller records the real price from the cashier receipt and starts delivering.
     * The requester immediately sees the difference against the estimate.
     */
    public function startDelivering(Order $order, User $fulfiller, ?int $actualItemCost = null, ?UploadedFile $receipt = null): Order
    {
        $this->assertStatus($order, [OrderStatus::InProgress], 'Pesanan belum diproses.');

        $needsReceipt = $order->category->has_item_cost;

        if ($needsReceipt && $actualItemCost === null) {
            throw new NitipException('Masukkan biaya riil sesuai struk sebelum mengantar.');
        }

        if ($needsReceipt && ! $receipt && ! $order->hasReceipt()) {
            throw new NitipException('Foto struk kasir wajib dilampirkan sebagai bukti biaya riil.');
        }

        $attributes = ['status' => OrderStatus::Delivering, 'delivering_at' => now()];

        if ($needsReceipt) {
            $attributes['actual_item_cost'] = $actualItemCost;
        }

        if ($receipt) {
            $attributes['receipt_path'] = $this->files->replacePrivate($order->receipt_path, $receipt, "orders/{$order->id}");
        }

        $order->forceFill($attributes)->save();

        $diffText = $this->differenceText($order);

        $this->record($order, $fulfiller, OrderEventType::Delivering, OrderStatus::InProgress, OrderStatus::Delivering,
            "Pesanan sedang diantar ke {$order->dropoff_location}.".($needsReceipt ? ' Biaya riil '.rupiah($order->actual_item_cost).". {$diffText}" : ''),
            ['actual_item_cost' => $order->actual_item_cost, 'difference' => $order->settlementDifference()]);

        $order->requester->notify(new OrderActivity(
            $order,
            'Pesanan sedang diantar',
            "{$fulfiller->shortName()} sedang menuju {$order->dropoff_location}.".($needsReceipt ? " {$diffText}" : '').' Siapkan PIN serah terima kamu.',
            'directions_walk',
        ));

        return $order;
    }

    /*
    |--------------------------------------------------------------------------
    | 5. Completed
    |--------------------------------------------------------------------------
    */

    /**
     * Fulfiller hands over the goods (receipt can still be added/replaced here).
     */
    public function markDelivered(Order $order, User $fulfiller, ?UploadedFile $receipt = null, ?string $note = null): Order
    {
        $this->assertStatus($order, [OrderStatus::Delivering], 'Pesanan belum dalam tahap pengantaran.');

        if ($receipt) {
            $order->receipt_path = $this->files->replacePrivate($order->receipt_path, $receipt, "orders/{$order->id}");
        }

        if ($order->category->has_item_cost && ! $order->hasReceipt()) {
            throw new NitipException('Foto struk kasir wajib dilampirkan saat serah terima.');
        }

        $order->forceFill([
            'status' => OrderStatus::Delivered,
            'delivered_at' => now(),
            'actual_item_cost' => $order->actual_item_cost ?? $order->estimated_item_cost,
        ])->save();

        $diffText = $this->differenceText($order);

        $this->record($order, $fulfiller, OrderEventType::Delivered, OrderStatus::Delivering, OrderStatus::Delivered,
            "Pesanan diserahkan. {$diffText}".($note ? " Catatan: {$note}" : ''));

        $order->requester->notify(new OrderActivity(
            $order,
            'Pesanan sudah diserahkan',
            "Relawan menandai \"{$order->title}\" sudah diserahkan. {$diffText} Konfirmasi penerimaan untuk menyelesaikan.",
            'handshake',
            'success',
        ));

        return $order;
    }

    /**
     * Requester confirms receipt, or fulfiller enters the requester's 4-digit PIN at hand-off.
     */
    public function complete(Order $order, User $actor, ?string $pin = null): Order
    {
        return DB::transaction(function () use ($order, $actor, $pin) {
            $order = $this->lock($order);

            if ($order->isFulfiller($actor)) {
                $this->assertStatus($order, [OrderStatus::Delivering, OrderStatus::Delivered], 'Pesanan belum siap diselesaikan.');

                if ($pin === null || ! hash_equals($order->completion_pin, $pin)) {
                    throw new NitipException('PIN serah terima salah. Minta PIN 4 digit kepada penitip.');
                }

                if ($order->status === OrderStatus::Delivering) {
                    if ($order->category->has_item_cost && ! $order->hasReceipt()) {
                        throw new NitipException('Unggah foto struk lewat "Tandai sudah diserahkan" sebelum memasukkan PIN.');
                    }
                    $order->forceFill(['delivered_at' => now(), 'actual_item_cost' => $order->actual_item_cost ?? $order->estimated_item_cost]);
                }

                $method = 'PIN serah terima';
            } elseif ($order->isRequester($actor)) {
                $this->assertStatus($order, [OrderStatus::Delivered], 'Pesanan belum ditandai diserahkan oleh relawan.');
                $method = 'konfirmasi penitip';
            } else {
                throw new NitipException('Kamu bukan pihak dalam pesanan ini.');
            }

            $from = $order->status;
            $order->forceFill(['status' => OrderStatus::Completed, 'completed_at' => now()])->save();

            $this->record($order, $actor, OrderEventType::Completed, $from, OrderStatus::Completed,
                "Transaksi selesai via {$method}. Total akhir ".rupiah($order->finalTotal()).'.');

            $order->counterpartFor($actor)?->notify(new OrderActivity(
                $order,
                'Transaksi selesai',
                "Pesanan \"{$order->title}\" selesai. Beri ulasan untuk {$actor->shortName()}!",
                'task_alt',
                'success',
            ));

            return $order;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Cancellation & disputes
    |--------------------------------------------------------------------------
    */

    public function cancel(Order $order, User $actor, string $reason): Order
    {
        return DB::transaction(function () use ($order, $actor, $reason) {
            $order = $this->lock($order);

            if ($order->status->isTerminal()) {
                throw new InvalidOrderTransitionException('Pesanan sudah selesai atau dibatalkan.');
            }

            $allowed = match (true) {
                $actor->isAdmin() => OrderStatus::activeStatuses(),
                $order->isRequester($actor) => [OrderStatus::Open, OrderStatus::AwaitingPayment, OrderStatus::PaymentSubmitted],
                $order->isFulfiller($actor) => [OrderStatus::AwaitingPayment, OrderStatus::PaymentSubmitted, OrderStatus::Paid, OrderStatus::InProgress],
                default => [],
            };

            $this->assertStatus($order, $allowed, 'Pesanan tidak bisa dibatalkan pada tahap ini. Gunakan fitur sengketa jika ada masalah.');

            $from = $order->status;
            $needsRefund = in_array($from, [OrderStatus::Paid, OrderStatus::InProgress, OrderStatus::Delivering, OrderStatus::Delivered, OrderStatus::Disputed], true);

            $order->forceFill([
                'status' => OrderStatus::Cancelled,
                'cancelled_at' => now(),
                'cancelled_by' => $actor->id,
                'cancel_reason' => $reason,
                'needs_refund' => $needsRefund,
            ])->save();

            $who = $actor->isAdmin() ? 'Admin' : $actor->shortName();
            $this->record($order, $actor, OrderEventType::Cancelled, $from, OrderStatus::Cancelled,
                "{$who} membatalkan pesanan. Alasan: {$reason}".($needsRefund ? ' (Perlu refund ke penitip)' : ''));

            foreach (array_filter([$order->requester, $order->fulfiller]) as $party) {
                if ($party->id === $actor->id) {
                    continue;
                }
                $party->notify(new OrderActivity(
                    $order,
                    'Pesanan dibatalkan',
                    "{$who} membatalkan \"{$order->title}\". Alasan: {$reason}".($needsRefund && $party->id === $order->requester_id ? ' Relawan wajib mengembalikan dana.' : ''),
                    'cancel',
                    'danger',
                ));
            }

            return $order;
        });
    }

    public function markDisputed(Order $order, User $opener, string $summary): Order
    {
        $this->assertStatus($order, [OrderStatus::Paid, OrderStatus::InProgress, OrderStatus::Delivering, OrderStatus::Delivered],
            'Sengketa hanya bisa dibuka setelah pembayaran terverifikasi dan sebelum pesanan selesai.');

        $from = $order->status;
        $order->forceFill(['status' => OrderStatus::Disputed])->save();

        $this->record($order, $opener, OrderEventType::DisputeOpened, $from, OrderStatus::Disputed,
            "{$opener->shortName()} membuka sengketa: {$summary}", ['previous_status' => $from->value]);

        $order->counterpartFor($opener)?->notify(new OrderActivity(
            $order,
            'Sengketa dibuka',
            "{$opener->shortName()} membuka sengketa untuk \"{$order->title}\". Admin Nitip akan meninjau.",
            'gavel',
            'danger',
        ));

        return $order;
    }

    public function resolveDispute(Order $order, User $admin, OrderStatus $finalStatus, string $note, bool $needsRefund = false): Order
    {
        $this->assertStatus($order, [OrderStatus::Disputed], 'Pesanan tidak sedang dalam sengketa.');

        if (! in_array($finalStatus, [OrderStatus::Completed, OrderStatus::Cancelled], true)) {
            throw new InvalidOrderTransitionException('Resolusi sengketa hanya boleh menjadi selesai atau dibatalkan.');
        }

        $attributes = ['status' => $finalStatus];

        if ($finalStatus === OrderStatus::Completed) {
            $attributes['completed_at'] = now();
            $attributes['actual_item_cost'] = $order->actual_item_cost ?? $order->estimated_item_cost;
        } else {
            $attributes['cancelled_at'] = now();
            $attributes['cancelled_by'] = $admin->id;
            $attributes['cancel_reason'] = 'Resolusi sengketa: '.$note;
            $attributes['needs_refund'] = $needsRefund;
        }

        $order->forceFill($attributes)->save();

        $this->record($order, $admin, OrderEventType::DisputeResolved, OrderStatus::Disputed, $finalStatus,
            "Admin menyelesaikan sengketa: {$note}");

        foreach (array_filter([$order->requester, $order->fulfiller]) as $party) {
            $party->notify(new OrderActivity(
                $order,
                'Sengketa diselesaikan',
                "Admin memutuskan pesanan \"{$order->title}\" menjadi {$finalStatus->label()}. {$note}",
                'balance',
                $finalStatus === OrderStatus::Completed ? 'success' : 'warning',
            ));
        }

        return $order;
    }

    /*
    |--------------------------------------------------------------------------
    | Internals
    |--------------------------------------------------------------------------
    */

    public function record(
        Order $order,
        ?User $actor,
        OrderEventType $type,
        ?OrderStatus $from,
        ?OrderStatus $to,
        string $description,
        array $meta = [],
    ): OrderEvent {
        return $order->events()->create([
            'actor_id' => $actor?->id,
            'type' => $type,
            'from_status' => $from,
            'to_status' => $to,
            'description' => $description,
            'meta' => $meta ?: null,
            'created_at' => now(),
        ]);
    }

    private function differenceText(Order $order): string
    {
        $diff = $order->settlementDifference();

        return match (true) {
            $diff === null || $diff === 0 => 'Biaya barang sesuai perkiraan.',
            $diff > 0 => 'Selisih nota: penitip perlu menambah '.rupiah($diff).' saat serah terima.',
            default => 'Selisih nota: relawan mengembalikan '.rupiah(abs($diff)).' saat serah terima.',
        };
    }

    /**
     * @param  list<OrderStatus>  $allowed
     */
    private function assertStatus(Order $order, array $allowed, string $message): void
    {
        if (! in_array($order->status, $allowed, true)) {
            throw new InvalidOrderTransitionException($message);
        }
    }

    private function lock(Order $order): Order
    {
        return Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
    }
}
