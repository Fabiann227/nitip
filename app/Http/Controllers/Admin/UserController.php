<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\ReasonRequest;
use App\Models\Order;
use App\Models\User;
use App\Services\AccountService;
use App\Support\Campuses;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private readonly AccountService $accounts) {}

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', '')) ?: null;
        $status = $request->query('status');
        $campus = (string) $request->query('campus') ?: null;

        $users = User::query()
            ->withCount(['fulfilledOrders as completed_deliveries_count' => fn ($query) => $query->where('status', OrderStatus::Completed->value)])
            ->withAvg('reviewsReceived as reviews_received_avg_rating', 'rating')
            ->search($q)
            ->when($campus, fn ($query) => $query->where('campus', $campus))
            ->when($status === 'suspended', fn ($query) => $query->where('is_suspended', true))
            ->when($status === 'unverified', fn ($query) => $query->whereNull('email_verified_at'))
            ->when($status === 'admin', fn ($query) => $query->admins())
            ->when($status === 'active', fn ($query) => $query->students()->active()->whereNotNull('email_verified_at'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'campuses' => Campuses::all(),
            'filters' => ['q' => $q, 'status' => $status, 'campus' => $campus],
        ]);
    }

    public function show(User $user): View
    {
        return view('admin.users.show', [
            'user' => $user,
            'stats' => [
                'requests' => Order::query()->where('requester_id', $user->id)->count(),
                'deliveries' => Order::query()->where('fulfiller_id', $user->id)->where('status', OrderStatus::Completed->value)->count(),
                'cancelled' => Order::query()->where('cancelled_by', $user->id)->count(),
                'earned' => (int) Order::query()->where('fulfiller_id', $user->id)->where('status', OrderStatus::Completed->value)->sum('service_fee'),
                'rating' => $user->ratingAverage(),
                'rating_count' => $user->ratingCount(),
            ],
            'orders' => Order::query()->involving($user)->with(['category', 'requester', 'fulfiller'])->latest()->limit(10)->get(),
        ]);
    }

    public function suspend(ReasonRequest $request, User $user): RedirectResponse
    {
        abort_if($user->isAdmin(), 403, 'Akun admin tidak bisa ditangguhkan.');

        $this->accounts->suspend($user, $request->user(), $request->validated('reason'));

        return back()->with('success', "Akun {$user->name} ditangguhkan.");
    }

    public function unsuspend(Request $request, User $user): RedirectResponse
    {
        $this->accounts->unsuspend($user, $request->user());

        return back()->with('success', "Penangguhan akun {$user->name} dicabut.");
    }

    public function verify(Request $request, User $user): RedirectResponse
    {
        $this->accounts->verifyManually($user, $request->user());

        return back()->with('success', "Email {$user->email} ditandai terverifikasi.");
    }
}
