<?php

namespace App\Http\Controllers\Settings;

use App\Enums\PaymentMethodType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdatePaymentMethodRequest;
use App\Services\AccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The user's payment destination (one per account): where requesters transfer to.
 */
class PaymentController extends Controller
{
    public function __construct(private readonly AccountService $accounts) {}

    public function edit(Request $request): View
    {
        return view('settings.payment', [
            'user' => $request->user(),
            'types' => PaymentMethodType::cases(),
        ]);
    }

    public function update(UpdatePaymentMethodRequest $request): RedirectResponse
    {
        $this->accounts->updatePaymentMethod($request->user(), $request->validated(), $request->file('qris_image'));

        return back()->with('success', 'Metode pembayaran disimpan. Penitip akan mentransfer ke sini.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->accounts->clearPaymentMethod($request->user());

        return back()->with('success', 'Metode pembayaran dihapus.');
    }
}
