<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Services\EmailVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    public function __construct(private readonly EmailVerificationService $verification) {}

    public function notice(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route($user->isAdmin() ? 'admin.dashboard' : 'dashboard');
        }

        if (! $this->verification->hasPendingCode($user) && ! $this->verification->bypassEnabled()) {
            $this->verification->send($user);
            $user->refresh();
        }

        return view('auth.verify-email', [
            'user' => $user,
            'cooldown' => $this->verification->secondsUntilResend($user),
            'devCode' => $this->verification->devCode($user),
            'bypass' => $this->verification->bypassEnabled(),
        ]);
    }

    public function verify(VerifyEmailRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('verification.verified');
        }

        if ($this->verification->verify($user, $request->validated('code'))) {
            return redirect()->route('verification.verified');
        }

        return back()->withErrors(['code' => 'Kode OTP salah. Periksa kembali email kampusmu.']);
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $this->verification->resend($user);

        return back()->with('success', 'Kode OTP baru sudah dikirim ke '.$user->email.'.');
    }

    public function verified(Request $request): View
    {
        return view('auth.verified', ['user' => $request->user()]);
    }
}
