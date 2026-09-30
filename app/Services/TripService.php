<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\TripStatus;
use App\Exceptions\NitipException;
use App\Models\Trip;
use App\Models\User;
use App\Notifications\AppNotification;
use App\Support\Codes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TripService
{
    public function __construct(private readonly OrderWorkflowService $workflow) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $fulfiller, array $data): Trip
    {
        if ($fulfiller->isSuspended()) {
            throw new NitipException('Akunmu sedang ditangguhkan dan tidak bisa membuat rute.');
        }

        if (! $fulfiller->hasPaymentMethod()) {
            throw new NitipException('Atur metode pembayaran (GoPay/BCA/QRIS) sebelum membuka rute agar penitip bisa membayar.');
        }

        $departure = Carbon::parse($data['departure_at']);

        return Trip::query()->create([
            'code' => Codes::trip(),
            'fulfiller_id' => $fulfiller->id,
            'service_category_id' => (int) $data['service_category_id'],
            'campus' => $fulfiller->campus,
            'destination' => $data['destination'],
            'waypoints' => $data['waypoints'] ?? null,
            'departure_at' => $departure,
            'closes_at' => $this->closesAt($departure),
            'transport_mode' => $data['transport_mode'] ?? 'walk',
            'max_slots' => (int) $data['max_slots'],
            'service_fee' => (int) $data['service_fee'],
            'notes' => $data['notes'] ?? null,
            'status' => TripStatus::Open,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Trip $trip, array $data): Trip
    {
        if ($trip->status !== TripStatus::Open) {
            throw new NitipException('Rute yang sudah ditutup tidak bisa diubah.');
        }

        $activeOrders = $trip->activeOrdersCount();

        if ((int) $data['max_slots'] < $activeOrders) {
            throw new NitipException("Kuota tidak boleh lebih kecil dari jumlah titipan aktif ({$activeOrders}).");
        }

        $departure = Carbon::parse($data['departure_at']);

        $trip->fill([
            'service_category_id' => $activeOrders > 0 ? $trip->service_category_id : (int) $data['service_category_id'],
            'destination' => $data['destination'],
            'waypoints' => $data['waypoints'] ?? null,
            'departure_at' => $departure,
            'closes_at' => $this->closesAt($departure),
            'transport_mode' => $data['transport_mode'] ?? 'walk',
            'max_slots' => (int) $data['max_slots'],
            'service_fee' => $activeOrders > 0 ? $trip->service_fee : (int) $data['service_fee'],
            'notes' => $data['notes'] ?? null,
        ])->save();

        return $trip;
    }

    public function close(Trip $trip, User $actor): Trip
    {
        if ($trip->status !== TripStatus::Open) {
            throw new NitipException('Rute sudah tidak terbuka.');
        }

        $trip->forceFill(['status' => TripStatus::Closed, 'closed_at' => now()])->save();

        return $trip;
    }

    /**
     * Cancelling a trip cancels every order that has not been paid yet.
     * Paid orders stay alive: the fulfiller must finish or cancel them individually (refund flagged).
     */
    public function cancel(Trip $trip, User $actor, string $reason): Trip
    {
        if ($trip->status === TripStatus::Cancelled) {
            throw new NitipException('Rute sudah dibatalkan.');
        }

        return DB::transaction(function () use ($trip, $actor, $reason) {
            $unpaid = $trip->orders()
                ->whereIn('status', [OrderStatus::AwaitingPayment->value, OrderStatus::PaymentSubmitted->value])
                ->get();

            foreach ($unpaid as $order) {
                $this->workflow->cancel($order, $actor, "Rute {$trip->code} dibatalkan: {$reason}");
            }

            $trip->forceFill([
                'status' => TripStatus::Cancelled,
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
            ])->save();

            $remaining = $trip->orders()->whereIn('status', [OrderStatus::Paid->value, OrderStatus::InProgress->value])->count();

            if ($remaining > 0 && ! $actor->isAdmin()) {
                $trip->fulfiller->notify(new AppNotification(
                    'Masih ada titipan yang sudah dibayar',
                    "Rute {$trip->code} dibatalkan, tetapi {$remaining} titipan sudah dibayar. Selesaikan atau batalkan satu per satu (dana wajib dikembalikan).",
                    route('trips.show', $trip),
                    'warning',
                    'warning',
                ));
            }

            return $trip;
        });
    }

    /**
     * Orders close automatically shortly before departure (no manual input).
     */
    public function closesAt(Carbon $departure): Carbon
    {
        $buffer = (int) config('nitip.trips.close_before_minutes', 15);
        $closes = $departure->copy()->subMinutes($buffer);

        return $closes->isFuture() ? $closes : $departure->copy();
    }
}
