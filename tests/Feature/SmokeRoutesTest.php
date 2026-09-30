<?php

namespace Tests\Feature;

use App\Models\Dispute;
use App\Models\Order;
use App\Models\ServiceCategory;
use App\Models\Trip;
use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Renders every page for every role against the demo data set.
 */
class SmokeRoutesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([UserSeeder::class, DemoDataSeeder::class]);
    }

    #[Test]
    public function public_pages_render_for_guests(): void
    {
        foreach (['home', 'services', 'login', 'register', 'password.request'] as $route) {
            $this->get(route($route))->assertOk();
        }

        $this->get(route('password.reset', ['token' => 'abc', 'email' => 'x@y.ac.id']))->assertOk();
    }

    #[Test]
    public function student_pages_render(): void
    {
        $willy = User::query()->where('email', 'willy@student.uph.edu')->firstOrFail();

        $routes = [
            route('dashboard'),
            route('explore'),
            route('explore', ['tab' => 'trips']),
            route('explore', ['tab' => 'requests', 'category' => 1, 'q' => 'ayam', 'campus' => '']),
            route('requests.create'),
            route('orders.index'),
            route('orders.index', ['role' => 'fulfiller', 'status' => 'completed']),
            route('trips.index'),
            route('trips.index', ['status' => 'all']),
            route('trips.create'),
            route('earnings'),
            route('notifications.index'),
            route('settings.profile'),
            route('settings.password'),
            route('settings.payment'),
            route('users.show', $willy),
            route('home'),
            route('services'),
        ];

        foreach (Order::query()->involving($willy)->get() as $order) {
            $routes[] = route('orders.show', $order);

            if ($order->hasPaymentProof()) {
                $routes[] = route('orders.files', [$order, 'proof']);
            }
            if ($order->hasReceipt()) {
                $routes[] = route('orders.files', [$order, 'receipt']);
            }
            if ($order->hasDocument()) {
                $routes[] = route('orders.files', [$order, 'document']);
            }
        }

        foreach (Order::query()->openRequests()->get() as $order) {
            $routes[] = route('orders.show', $order);
        }

        foreach (Trip::query()->get() as $trip) {
            $routes[] = route('trips.show', $trip);
        }

        $joinable = Trip::query()->open()->where('fulfiller_id', '!=', $willy->id)->get()->first(fn (Trip $t) => $t->isJoinable());
        if ($joinable) {
            $routes[] = route('trips.join', $joinable);
        }

        $ownTrip = Trip::query()->where('fulfiller_id', $willy->id)->where('status', 'open')->first();
        if ($ownTrip) {
            $routes[] = route('trips.edit', $ownTrip);
        }

        foreach (Dispute::query()->with('order')->get() as $dispute) {
            if ($dispute->order->isParticipant($willy)) {
                $routes[] = route('disputes.show', $dispute);
            }
        }

        foreach (User::query()->students()->limit(3)->get() as $user) {
            $routes[] = route('users.show', $user);
        }

        foreach (array_unique($routes) as $url) {
            $response = $this->actingAs($willy)->get($url);
            $this->assertSame(200, $response->getStatusCode(), "Route {$url} returned {$response->getStatusCode()}: ".$this->exceptionMessage($response));
        }
    }

    #[Test]
    public function every_seeded_participant_can_open_their_orders(): void
    {
        foreach (User::query()->students()->whereNotNull('email_verified_at')->where('is_suspended', false)->get() as $user) {
            foreach (Order::query()->involving($user)->get() as $order) {
                $response = $this->actingAs($user)->get(route('orders.show', $order));
                $this->assertSame(200, $response->getStatusCode(), "Order {$order->code} for {$user->email}: ".$this->exceptionMessage($response));
            }
        }
    }

    #[Test]
    public function admin_pages_render(): void
    {
        $admin = User::query()->where('email', 'admin@nitip.test')->firstOrFail();

        $routes = [
            route('admin.dashboard'),
            route('admin.users.index'),
            route('admin.users.index', ['status' => 'suspended', 'q' => 'a', 'campus' => 'UPH']),
            route('admin.orders.index'),
            route('admin.orders.index', ['status' => 'completed', 'category' => 1, 'campus' => 'UPH']),
            route('admin.trips.index'),
            route('admin.disputes.index'),
            route('admin.disputes.index', ['status' => 'all']),
            route('admin.categories.index'),
            route('admin.categories.create'),
            route('admin.audit-logs.index'),
            route('admin.audit-logs.index', ['type' => 'completed', 'q' => 'NT']),
            route('notifications.index'),
            route('admin.categories.edit', ServiceCategory::query()->first()),
        ];

        foreach (Order::query()->limit(6)->get() as $order) {
            $routes[] = route('admin.orders.show', $order);
        }

        foreach (User::query()->limit(4)->get() as $user) {
            $routes[] = route('admin.users.show', $user);
        }

        foreach (Dispute::query()->get() as $dispute) {
            $routes[] = route('admin.disputes.show', $dispute);

            if ($dispute->hasEvidence()) {
                $routes[] = route('disputes.evidence', $dispute);
            }
        }

        foreach ($routes as $url) {
            $response = $this->actingAs($admin)->get($url);
            $this->assertSame(200, $response->getStatusCode(), "Route {$url} returned {$response->getStatusCode()}: ".$this->exceptionMessage($response));
        }
    }

    #[Test]
    public function unverified_and_suspended_accounts_land_on_the_right_pages(): void
    {
        $unverified = User::query()->where('email', 'belumverif@student.uph.edu')->firstOrFail();
        $this->actingAs($unverified)->get(route('dashboard'))->assertRedirect(route('verification.notice'));
        $this->actingAs($unverified)->get(route('verification.notice'))->assertOk();

        $suspended = User::query()->where('email', 'suspended@student.uph.edu')->firstOrFail();
        $this->actingAs($suspended)->get(route('dashboard'))->assertForbidden();
    }

    private function exceptionMessage(TestResponse $response): string
    {
        if ($response->getStatusCode() === 200) {
            return '';
        }

        $exception = $response->exception ?? null;

        return $exception instanceof \Throwable ? $exception->getMessage() : '';
    }
}
