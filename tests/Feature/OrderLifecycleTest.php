<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Trip;
use App\Notifications\AppNotification;
use App\Notifications\OrderActivity;
use App\Services\OrderService;
use App\Services\OrderWorkflowService;
use App\Services\PaymentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderLifecycleTest extends TestCase
{
    #[Test]
    public function a_request_can_be_posted_and_completed_end_to_end(): void
    {
        Notification::fake();

        $requester = $this->student();
        $fulfiller = $this->student();

        // 1. Post & Match
        $this->actingAs($requester)->post(route('requests.store'), $this->requestPayload())->assertRedirect();

        $order = Order::query()->firstOrFail();

        $this->assertSame(OrderStatus::Open, $order->status);
        $this->assertNull($order->fulfiller_id);
        $this->assertSame('UPH', $order->campus);
        $this->assertSame(28000, $order->estimated_item_cost);
        $this->assertCount(2, $order->itemList());
        $this->assertDatabaseHas('order_events', ['order_id' => $order->id, 'type' => 'created']);

        $this->actingAs($fulfiller)->post(route('orders.claim', $order))->assertRedirect(route('orders.show', $order));

        $order->refresh();
        $this->assertSame(OrderStatus::AwaitingPayment, $order->status);
        $this->assertSame($fulfiller->id, $order->fulfiller_id);
        Notification::assertSentTo($requester, OrderActivity::class);

        // 2. Pay & Verify
        $this->actingAs($requester)->post(route('orders.payment.store', $order), [
            'proof' => UploadedFile::fake()->image('bukti.jpg'),
            'note' => 'Sudah transfer',
        ])->assertRedirect()->assertSessionHas('success');

        $order->refresh();
        $this->assertSame(OrderStatus::PaymentSubmitted, $order->status);
        $this->assertNotNull($order->payment_proof_path);
        $this->assertStringContainsString('GoPay', $order->payment_method);
        Storage::disk('local')->assertExists($order->payment_proof_path);

        $this->actingAs($fulfiller)->post(route('orders.payment.verify', $order))->assertRedirect();

        $order->refresh();
        $this->assertSame(OrderStatus::Paid, $order->status);
        $this->assertNotNull($order->paid_at);
        $this->assertNotNull($order->payment_verified_at);

        // 3. In Progress
        $this->actingAs($fulfiller)->post(route('orders.start', $order))->assertRedirect();
        $this->assertSame(OrderStatus::InProgress, $order->fresh()->status);

        // 4. Delivering: real price + receipt recorded right after purchase
        $this->actingAs($fulfiller)->post(route('orders.deliver', $order), [
            'actual_item_cost' => 30000,
            'receipt' => UploadedFile::fake()->image('struk.jpg'),
        ])->assertRedirect()->assertSessionHas('success');

        $order->refresh();
        $this->assertSame(OrderStatus::Delivering, $order->status);
        $this->assertSame(30000, $order->actual_item_cost);
        $this->assertSame(2000, $order->settlementDifference());
        $this->assertTrue($order->hasReceipt());
        Storage::disk('local')->assertExists($order->receipt_path);

        $this->actingAs($fulfiller)->post(route('orders.delivered', $order))->assertRedirect()->assertSessionHas('success');
        $this->assertSame(OrderStatus::Delivered, $order->fresh()->status);

        // 5. Completed
        $this->actingAs($requester)->post(route('orders.complete', $order))->assertRedirect();

        $order->refresh();
        $this->assertSame(OrderStatus::Completed, $order->status);
        $this->assertNotNull($order->completed_at);
        $this->assertSame(33500, $order->finalTotal());

        $types = $order->events()->pluck('type')->map(fn ($t) => $t->value)->all();
        $this->assertSame(['created', 'claimed', 'payment_submitted', 'payment_verified', 'started', 'delivering', 'delivered', 'completed'], $types);

        // Review
        $this->actingAs($requester)->post(route('orders.reviews.store', $order), ['rating' => 5, 'comment' => 'Mantap'])->assertRedirect();
        $this->assertDatabaseHas('reviews', ['order_id' => $order->id, 'reviewer_id' => $requester->id, 'reviewee_id' => $fulfiller->id, 'rating' => 5]);
        $this->assertSame(5.0, $fulfiller->fresh()->ratingAverage());

        $this->actingAs($requester)->post(route('orders.reviews.store', $order), ['rating' => 4])->assertForbidden();
    }

    #[Test]
    public function delivering_requires_the_real_price_and_receipt(): void
    {
        $order = $this->orderAt(OrderStatus::InProgress);

        $this->actingAs($order->fulfiller)
            ->from(route('orders.show', $order))
            ->post(route('orders.deliver', $order), [])
            ->assertSessionHasErrors(['actual_item_cost', 'receipt']);

        $this->assertSame(OrderStatus::InProgress, $order->fresh()->status);
    }

    #[Test]
    public function fulfiller_can_close_the_order_with_the_requester_pin(): void
    {
        $order = $this->orderAt(OrderStatus::Delivering);
        $fulfiller = $order->fulfiller;

        $this->actingAs($fulfiller)
            ->from(route('orders.show', $order))
            ->post(route('orders.complete', $order), ['digits' => ['9', '9', '9', '9']])
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('error');

        $this->assertSame(OrderStatus::Delivering, $order->fresh()->status);

        $this->actingAs($fulfiller)
            ->post(route('orders.complete', $order), ['digits' => str_split($order->completion_pin)])
            ->assertSessionHas('success');

        $this->assertSame(OrderStatus::Completed, $order->fresh()->status);
    }

    #[Test]
    public function rejected_payment_proof_returns_the_order_to_awaiting_payment(): void
    {
        $order = $this->orderAt(OrderStatus::PaymentSubmitted);

        $this->actingAs($order->fulfiller)
            ->post(route('orders.payment.reject', $order), ['reason' => 'Nominal tidak sesuai'])
            ->assertRedirect();

        $fresh = $order->fresh();
        $this->assertSame(OrderStatus::AwaitingPayment, $fresh->status);
        $this->assertSame('Nominal tidak sesuai', $fresh->payment_rejection_reason);

        // Requester re-uploads and the rejection is cleared.
        $this->actingAs($order->requester)->post(route('orders.payment.store', $order), [
            'proof' => UploadedFile::fake()->image('bukti2.jpg'),
        ])->assertRedirect();

        $fresh = $order->fresh();
        $this->assertSame(OrderStatus::PaymentSubmitted, $fresh->status);
        $this->assertNull($fresh->payment_rejection_reason);
    }

    #[Test]
    public function status_transitions_cannot_be_skipped(): void
    {
        $order = $this->orderAt(OrderStatus::AwaitingPayment);

        $this->actingAs($order->fulfiller)->post(route('orders.start', $order))->assertForbidden();
        $this->assertSame(OrderStatus::AwaitingPayment, $order->fresh()->status);

        $this->actingAs($order->requester)->post(route('orders.complete', $order))->assertForbidden();
    }

    #[Test]
    public function requester_cannot_claim_their_own_request(): void
    {
        $order = $this->orderAt(OrderStatus::Open);

        $this->actingAs($order->requester)->post(route('orders.claim', $order))->assertForbidden();
    }

    #[Test]
    public function fulfiller_without_payment_method_cannot_claim(): void
    {
        $order = $this->orderAt(OrderStatus::Open);
        $fulfiller = $this->student([], false);

        $this->actingAs($fulfiller)
            ->from(route('orders.show', $order))
            ->post(route('orders.claim', $order))
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('error');

        $this->assertSame(OrderStatus::Open, $order->fresh()->status);
    }

    #[Test]
    public function print_requests_require_a_document(): void
    {
        $requester = $this->student();

        $this->actingAs($requester)
            ->from(route('requests.create'))
            ->post(route('requests.store'), $this->printPayload())
            ->assertSessionHasErrors('document');

        $this->actingAs($requester)
            ->post(route('requests.store'), $this->printPayload([
                'document' => UploadedFile::fake()->create('makalah.pdf', 200, 'application/pdf'),
            ]))
            ->assertSessionHasNoErrors();

        $order = Order::query()->firstOrFail();
        $this->assertSame(20000, $order->estimated_item_cost);
        $this->assertSame(20, $order->print_spec['pages']);
        $this->assertSame('makalah.pdf', $order->document_name);
        Storage::disk('local')->assertExists($order->document_path);
    }

    #[Test]
    public function service_fee_must_stay_within_the_category_range(): void
    {
        $this->actingAs($this->student())
            ->post(route('requests.store'), $this->requestPayload(['service_fee' => 10000]))
            ->assertSessionHasErrors('service_fee');
    }

    #[Test]
    public function requester_can_cancel_before_payment_and_fulfiller_cancel_after_payment_flags_refund(): void
    {
        $open = $this->orderAt(OrderStatus::Open);
        $this->actingAs($open->requester)->post(route('orders.cancel', $open), ['reason' => 'Tidak jadi butuh'])->assertRedirect();
        $this->assertSame(OrderStatus::Cancelled, $open->fresh()->status);
        $this->assertFalse($open->fresh()->needs_refund);

        $paid = $this->orderAt(OrderStatus::Paid);
        $this->actingAs($paid->requester)->post(route('orders.cancel', $paid), ['reason' => 'Berubah pikiran'])->assertForbidden();

        $this->actingAs($paid->fulfiller)->post(route('orders.cancel', $paid), ['reason' => 'Kantin tutup mendadak'])->assertRedirect();
        $this->assertSame(OrderStatus::Cancelled, $paid->fresh()->status);
        $this->assertTrue($paid->fresh()->needs_refund);
    }

    #[Test]
    public function fulfiller_can_release_a_claimed_request_back_to_the_feed(): void
    {
        $order = $this->orderAt(OrderStatus::PaymentSubmitted);

        $this->actingAs($order->fulfiller)->post(route('orders.release', $order), ['reason' => 'Hujan deras'])->assertRedirect();

        $fresh = $order->fresh();
        $this->assertSame(OrderStatus::Open, $fresh->status);
        $this->assertNull($fresh->fulfiller_id);
        $this->assertNull($fresh->payment_proof_path);
    }

    #[Test]
    public function trips_close_automatically_before_departure_and_accept_joins_until_slots_run_out(): void
    {
        Notification::fake();

        $fulfiller = $this->student();
        $first = $this->student();
        $second = $this->student();

        $departure = now()->addHours(2)->startOfMinute();

        $this->actingAs($fulfiller)->post(route('trips.store'), [
            'service_category_id' => $this->category()->id,
            'destination' => 'Food Court UPH',
            'departure_at' => $departure->format('Y-m-d H:i'),
            'transport_mode' => 'walk',
            'max_slots' => 1,
            'service_fee' => 3500,
        ])->assertRedirect();

        $trip = Trip::query()->firstOrFail();
        $this->assertTrue($trip->isJoinable());
        $this->assertSame($departure->copy()->subMinutes(15)->toDateTimeString(), $trip->closes_at->toDateTimeString());
        $this->assertSame($this->category()->id, $trip->service_category_id);

        $joinPayload = [
            'title' => 'Nasi Uduk',
            'dropoff_location' => 'Gedung D Lt. 5',
            'items' => [['name' => 'Nasi Uduk', 'quantity' => 1, 'estimated_price' => 15000]],
        ];

        $this->actingAs($first)->post(route('trips.join.store', $trip), $joinPayload)->assertRedirect();

        $order = Order::query()->firstOrFail();
        $this->assertSame(OrderStatus::AwaitingPayment, $order->status);
        $this->assertSame($fulfiller->id, $order->fulfiller_id);
        $this->assertSame($trip->id, $order->trip_id);
        $this->assertSame('Food Court UPH', $order->pickup_location);
        $this->assertSame(3500, $order->service_fee);
        $this->assertSame(0, $trip->fresh()->remainingSlots());
        Notification::assertSentTo($fulfiller, OrderActivity::class);

        $this->actingAs($second)->get(route('trips.join', $trip))->assertForbidden();
        $this->assertSame(1, Order::query()->count());

        $this->actingAs($fulfiller)->get(route('trips.join', $trip))->assertForbidden();
    }

    #[Test]
    public function cancelling_a_trip_cancels_unpaid_orders_only(): void
    {
        $fulfiller = $this->student();
        $trip = Trip::factory()->for($fulfiller, 'fulfiller')->create(['campus' => $fulfiller->campus, 'max_slots' => 3, 'service_category_id' => $this->category()->id]);

        $unpaid = Order::factory()->for($this->student(), 'requester')->fromTrip($trip)->create();
        $paid = Order::factory()->for($this->student(), 'requester')->fromTrip($trip)->create(['status' => OrderStatus::Paid, 'paid_at' => now()]);

        $this->actingAs($fulfiller)->post(route('trips.cancel', $trip), ['reason' => 'Kelas mendadak'])->assertRedirect();

        $this->assertSame(OrderStatus::Cancelled, $unpaid->fresh()->status);
        $this->assertSame(OrderStatus::Paid, $paid->fresh()->status);
        $this->assertSame('cancelled', $trip->fresh()->status->value);
    }

    #[Test]
    public function a_dispute_freezes_the_order_until_an_admin_resolves_it(): void
    {
        Notification::fake();

        $order = $this->orderAt(OrderStatus::InProgress);
        $admin = $this->admin();

        $this->actingAs($order->requester)->post(route('orders.disputes.store', $order), [
            'reason' => 'unresponsive',
            'description' => 'Relawan tidak bisa dihubungi selama tiga jam setelah pembayaran.',
            'evidence' => UploadedFile::fake()->image('chat.png'),
        ])->assertRedirect();

        $order->refresh();
        $this->assertSame(OrderStatus::Disputed, $order->status);
        $this->assertNotNull($order->dispute);
        $this->assertTrue($order->dispute->hasEvidence());
        Notification::assertSentTo($admin, AppNotification::class);

        $this->actingAs($order->fulfiller)->post(route('orders.deliver', $order))->assertForbidden();

        $this->actingAs($order->requester)->get(route('disputes.show', $order->dispute))->assertOk();
        $this->actingAs($order->requester)->get(route('disputes.evidence', $order->dispute))->assertOk();
        $this->actingAs($this->student())->get(route('disputes.show', $order->dispute))->assertForbidden();

        $this->actingAs($admin)->post(route('admin.disputes.resolve', $order->dispute), [
            'resolution' => 'cancel_with_refund',
            'note' => 'Relawan tidak merespons, pesanan dibatalkan dengan refund penuh.',
        ])->assertRedirect(route('admin.disputes.show', $order->dispute));

        $order->refresh();
        $this->assertSame(OrderStatus::Cancelled, $order->status);
        $this->assertTrue($order->needs_refund);
        $this->assertSame('resolved', $order->dispute->status->value);
        $this->assertDatabaseHas('order_events', ['order_id' => $order->id, 'type' => 'dispute_resolved', 'actor_id' => $admin->id]);
    }

    /**
     * Build an order that has progressed to the given status via the real workflow services.
     */
    private function orderAt(OrderStatus $target): Order
    {
        $requester = $this->student();
        $fulfiller = $this->student();

        $orders = app(OrderService::class);
        $workflow = app(OrderWorkflowService::class);
        $payments = app(PaymentService::class);

        $order = $orders->createRequest($requester, $this->requestPayload());

        $steps = [
            OrderStatus::AwaitingPayment->value => fn () => $workflow->claim($order->fresh(), $fulfiller),
            OrderStatus::PaymentSubmitted->value => fn () => $payments->submitProof($order->fresh(), $requester, UploadedFile::fake()->image('bukti.jpg')),
            OrderStatus::Paid->value => fn () => $payments->verify($order->fresh(), $fulfiller),
            OrderStatus::InProgress->value => fn () => $workflow->start($order->fresh(), $fulfiller),
            OrderStatus::Delivering->value => fn () => $workflow->startDelivering($order->fresh(), $fulfiller, 28000, UploadedFile::fake()->image('struk.jpg')),
            OrderStatus::Delivered->value => fn () => $workflow->markDelivered($order->fresh(), $fulfiller),
            OrderStatus::Completed->value => fn () => $workflow->complete($order->fresh(), $requester),
        ];

        foreach ($steps as $status => $step) {
            if ($target === OrderStatus::Open) {
                break;
            }

            $step();

            if ($status === $target->value) {
                break;
            }
        }

        return $order->fresh()->load(['requester', 'fulfiller']);
    }
}
