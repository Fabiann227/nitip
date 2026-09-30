<?php

namespace App\Services;

use App\Enums\OrderEventType;
use App\Enums\OrderStatus;
use App\Exceptions\NitipException;
use App\Models\Order;
use App\Models\ServiceCategory;
use App\Models\Trip;
use App\Models\User;
use App\Notifications\OrderActivity;
use App\Support\Codes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Creates orders through both posting streams:
 *  - REQUEST stream: requester posts an open order, fulfillers claim it.
 *  - OFFER stream: requester joins a fulfiller's trip, order is matched immediately.
 */
class OrderService
{
    public function __construct(
        private readonly OrderWorkflowService $workflow,
        private readonly FileStorageService $files,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createRequest(User $requester, array $data, ?UploadedFile $document = null): Order
    {
        $category = ServiceCategory::query()->active()->findOrFail($data['service_category_id']);

        $this->assertCanPost($requester, $category, $document);

        return DB::transaction(function () use ($requester, $category, $data, $document) {
            $items = $this->normalizeItems($data['items'] ?? []);

            $order = Order::query()->create([
                'code' => Codes::order(),
                'requester_id' => $requester->id,
                'fulfiller_id' => null,
                'trip_id' => null,
                'service_category_id' => $category->id,
                'campus' => $requester->campus,
                'title' => $data['title'],
                'notes' => $data['notes'] ?? null,
                'pickup_location' => $data['pickup_location'],
                'dropoff_location' => $data['dropoff_location'],
                'needed_by' => $data['needed_by'] ?? null,
                'items' => $items ?: null,
                'print_spec' => $category->isPrint() ? $this->printSpec($data['print'] ?? []) : null,
                'service_fee' => (int) $data['service_fee'],
                'estimated_item_cost' => $this->estimatedItemCost($category, $items, $data),
                'status' => OrderStatus::Open,
                'completion_pin' => Codes::pin(),
            ]);

            $this->attachDocument($order, $document);

            $this->workflow->record($order, $requester, OrderEventType::Created, null, OrderStatus::Open,
                "Permintaan diposting oleh {$requester->shortName()}.");

            return $order;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function joinTrip(Trip $trip, User $requester, array $data, ?UploadedFile $document = null): Order
    {
        if ($trip->isOwnedBy($requester)) {
            throw new NitipException('Kamu tidak bisa menitip ke rute milikmu sendiri.');
        }

        return DB::transaction(function () use ($trip, $requester, $data, $document) {
            /** @var Trip $trip */
            $trip = Trip::query()->whereKey($trip->id)->lockForUpdate()->with('category')->firstOrFail();
            $category = $trip->category;

            $this->assertCanPost($requester, $category, $document);

            if (! $trip->isOpen()) {
                throw new NitipException('Rute ini sudah ditutup.');
            }

            if ($trip->isFull()) {
                throw new NitipException('Slot rute ini sudah penuh.');
            }

            $items = $this->normalizeItems($data['items'] ?? []);

            $order = Order::query()->create([
                'code' => Codes::order(),
                'requester_id' => $requester->id,
                'fulfiller_id' => $trip->fulfiller_id,
                'trip_id' => $trip->id,
                'service_category_id' => $category->id,
                'campus' => $trip->campus,
                'title' => $data['title'],
                'notes' => $data['notes'] ?? null,
                'pickup_location' => ($data['pickup_location'] ?? null) ?: $trip->destination,
                'dropoff_location' => $data['dropoff_location'],
                'needed_by' => $trip->departure_at,
                'items' => $items ?: null,
                'print_spec' => $category->isPrint() ? $this->printSpec($data['print'] ?? []) : null,
                'service_fee' => $trip->service_fee,
                'estimated_item_cost' => $this->estimatedItemCost($category, $items, $data),
                'status' => OrderStatus::AwaitingPayment,
                'completion_pin' => Codes::pin(),
                'matched_at' => now(),
            ]);

            $this->attachDocument($order, $document);

            $this->workflow->record($order, $requester, OrderEventType::JoinedTrip, null, OrderStatus::AwaitingPayment,
                "{$requester->shortName()} menitip lewat rute {$trip->code} ({$trip->routeLabel()}).", ['trip_id' => $trip->id]);

            $trip->fulfiller->notify(new OrderActivity(
                $order,
                'Titipan baru di rutemu',
                "{$requester->shortName()} menitip \"{$order->title}\" lewat rute {$trip->routeLabel()}.",
                'add_shopping_cart',
                'success',
            ));

            return $order;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Internals
    |--------------------------------------------------------------------------
    */

    private function assertCanPost(User $requester, ServiceCategory $category, ?UploadedFile $document): void
    {
        if ($requester->isSuspended()) {
            throw new NitipException('Akunmu sedang ditangguhkan dan tidak bisa membuat pesanan.');
        }

        if (! $requester->campus) {
            throw new NitipException('Lengkapi data kampus di profil sebelum membuat pesanan.');
        }

        if ($category->requires_document && $document === null) {
            throw new NitipException('Unggah berkas yang ingin dicetak untuk layanan print & fotokopi.');
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return list<array{name: string, quantity: int, estimated_price: ?int, note: ?string}>
     */
    private function normalizeItems(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            $name = trim((string) ($item['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $normalized[] = [
                'name' => $name,
                'quantity' => max(1, (int) ($item['quantity'] ?? 1)),
                'estimated_price' => isset($item['estimated_price']) && $item['estimated_price'] !== '' ? (int) $item['estimated_price'] : null,
                'note' => isset($item['note']) && trim((string) $item['note']) !== '' ? trim((string) $item['note']) : null,
            ];
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $print
     * @return array<string, mixed>
     */
    private function printSpec(array $print): array
    {
        return [
            'pages' => (int) ($print['pages'] ?? 1),
            'copies' => (int) ($print['copies'] ?? 1),
            'is_color' => (bool) ($print['is_color'] ?? false),
            'paper_size' => $print['paper_size'] ?? 'A4',
            'binding' => $print['binding'] ?? 'none',
            'instructions' => isset($print['instructions']) && trim((string) $print['instructions']) !== '' ? trim((string) $print['instructions']) : null,
        ];
    }

    /**
     * Estimated item cost: sum of item estimates (food) or the user's estimate (print / no prices given).
     *
     * @param  list<array{name: string, quantity: int, estimated_price: ?int, note: ?string}>  $items
     * @param  array<string, mixed>  $data
     */
    private function estimatedItemCost(ServiceCategory $category, array $items, array $data): int
    {
        if (! $category->has_item_cost) {
            return 0;
        }

        $fromItems = (int) array_sum(array_map(fn (array $i) => $i['quantity'] * ($i['estimated_price'] ?? 0), $items));

        if ($category->isPrint() || $fromItems === 0) {
            return (int) ($data['estimated_item_cost'] ?? 0);
        }

        return $fromItems;
    }

    private function attachDocument(Order $order, ?UploadedFile $document): void
    {
        if (! $document) {
            return;
        }

        $order->forceFill([
            'document_path' => $this->files->storePrivate($document, "orders/{$order->id}"),
            'document_name' => Str::limit($document->getClientOriginalName(), 200, ''),
        ])->save();
    }
}
