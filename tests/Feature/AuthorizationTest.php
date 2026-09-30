<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Trip;
use App\Services\AccountService;
use App\Services\OrderService;
use App\Services\OrderWorkflowService;
use App\Services\PaymentService;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    #[Test]
    public function open_requests_are_visible_to_other_students_but_matched_orders_are_private(): void
    {
        $requester = $this->student();
        $fulfiller = $this->student();
        $stranger = $this->student();

        $order = app(OrderService::class)->createRequest($requester, $this->requestPayload());

        $this->actingAs($stranger)->get(route('orders.show', $order))->assertOk();

        app(OrderWorkflowService::class)->claim($order, $fulfiller);

        $this->actingAs($stranger)->get(route('orders.show', $order))->assertForbidden();
        $this->actingAs($requester)->get(route('orders.show', $order))->assertOk();
        $this->actingAs($fulfiller)->get(route('orders.show', $order))->assertOk();
        $this->actingAs($this->admin())->get(route('admin.orders.show', $order))->assertOk();
    }

    #[Test]
    public function only_the_fulfiller_can_verify_a_payment_and_only_participants_can_see_the_proof(): void
    {
        $requester = $this->student();
        $fulfiller = $this->student();
        $stranger = $this->student();

        $order = app(OrderService::class)->createRequest($requester, $this->requestPayload());
        app(OrderWorkflowService::class)->claim($order, $fulfiller);
        app(PaymentService::class)->submitProof($order->fresh(), $requester, UploadedFile::fake()->image('bukti.jpg'));

        $this->actingAs($stranger)->post(route('orders.payment.verify', $order))->assertForbidden();
        $this->actingAs($requester)->post(route('orders.payment.verify', $order))->assertForbidden();
        $this->assertSame(OrderStatus::PaymentSubmitted, $order->fresh()->status);

        $this->actingAs($stranger)->get(route('orders.files', [$order, 'proof']))->assertForbidden();
        $this->actingAs($requester)->get(route('orders.files', [$order, 'proof']))->assertOk();
        $this->actingAs($fulfiller)->get(route('orders.files', [$order, 'proof']))->assertOk();
        $this->actingAs($this->admin())->get(route('orders.files', [$order, 'proof']))->assertOk();
    }

    #[Test]
    public function students_cannot_cancel_orders_they_are_not_part_of(): void
    {
        $order = app(OrderService::class)->createRequest($this->student(), $this->requestPayload());

        $this->actingAs($this->student())->post(route('orders.cancel', $order), ['reason' => 'iseng saja'])->assertForbidden();
        $this->assertSame(OrderStatus::Open, $order->fresh()->status);
    }

    #[Test]
    public function private_documents_are_only_served_to_participants_and_admins(): void
    {
        $requester = $this->student();
        $order = app(OrderService::class)->createRequest($requester, $this->printPayload(), UploadedFile::fake()->create('makalah.pdf', 100, 'application/pdf'));

        $this->actingAs($this->student())->get(route('orders.files', [$order, 'document']))->assertForbidden();
        $this->actingAs($requester)->get(route('orders.files', [$order, 'document']))->assertOk();
        $this->actingAs($this->admin())->get(route('orders.files', [$order, 'document']))->assertOk();
        $this->actingAs($requester)->get(route('orders.files', [$order, 'receipt']))->assertNotFound();
        $this->actingAs($requester)->get('/orders/'.$order->code.'/files/secret')->assertNotFound();
    }

    #[Test]
    public function students_cannot_access_the_admin_area(): void
    {
        $student = $this->student();

        $this->actingAs($student)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($student)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($student)->post(route('admin.users.suspend', $this->student()), ['reason' => 'test alasan'])->assertForbidden();
    }

    #[Test]
    public function admins_can_access_administrative_features(): void
    {
        $admin = $this->admin();
        $student = $this->student();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.users.show', $student))->assertOk();

        $this->actingAs($admin)->post(route('admin.users.suspend', $student), ['reason' => 'Melanggar ketentuan layanan'])->assertRedirect();
        $this->assertTrue($student->fresh()->isSuspended());

        $this->actingAs($admin)->post(route('admin.users.unsuspend', $student))->assertRedirect();
        $this->assertFalse($student->fresh()->isSuspended());
    }

    #[Test]
    public function suspended_students_are_blocked_from_the_application_area(): void
    {
        $student = $this->student(['is_suspended' => true, 'suspended_at' => now(), 'suspension_reason' => 'Spam']);

        $this->actingAs($student)->get(route('dashboard'))->assertForbidden()->assertSee('ditangguhkan');
        $this->actingAs($student)->post(route('requests.store'), $this->requestPayload())->assertForbidden();
    }

    #[Test]
    public function users_manage_their_own_payment_destination(): void
    {
        $user = $this->student([], false);

        $this->assertFalse($user->hasPaymentMethod());

        $this->actingAs($user)->put(route('settings.payment.update'), [
            'type' => 'bank_transfer',
            'provider_name' => 'BCA',
            'account_number' => '8830012345',
            'account_name' => 'Budi',
        ])->assertRedirect()->assertSessionHas('success');

        $fresh = $user->fresh();
        $this->assertTrue($fresh->hasPaymentMethod());
        $this->assertSame('Bank BCA · 8830012345 · a.n. Budi', $fresh->paymentSummary());

        $this->actingAs($user)->put(route('settings.payment.update'), ['type' => 'qris', 'account_name' => 'Budi'])
            ->assertSessionHasErrors('qris_image');

        $this->actingAs($user)->delete(route('settings.payment.destroy'))->assertRedirect();
        $this->assertFalse($user->fresh()->hasPaymentMethod());
    }

    #[Test]
    public function only_trip_owners_can_edit_close_or_cancel_a_trip(): void
    {
        $owner = $this->student();
        $trip = Trip::factory()->for($owner, 'fulfiller')->create(['campus' => $owner->campus]);

        $other = $this->student();

        $this->actingAs($other)->get(route('trips.edit', $trip))->assertForbidden();
        $this->actingAs($other)->post(route('trips.close', $trip))->assertForbidden();
        $this->actingAs($other)->post(route('trips.cancel', $trip), ['reason' => 'bukan punya saya'])->assertForbidden();

        $this->actingAs($owner)->post(route('trips.close', $trip))->assertRedirect();
        $this->assertSame('closed', $trip->fresh()->status->value);
    }

    #[Test]
    public function payment_cannot_be_submitted_when_the_fulfiller_has_no_payment_destination(): void
    {
        $requester = $this->student();
        $fulfiller = $this->student();
        $order = app(OrderService::class)->createRequest($requester, $this->requestPayload());
        app(OrderWorkflowService::class)->claim($order, $fulfiller);

        app(AccountService::class)->clearPaymentMethod($fulfiller);

        $this->actingAs($requester)
            ->from(route('orders.show', $order))
            ->post(route('orders.payment.store', $order), ['proof' => UploadedFile::fake()->image('bukti.jpg')])
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('error');

        $this->assertSame(OrderStatus::AwaitingPayment, $order->fresh()->status);
    }
}
