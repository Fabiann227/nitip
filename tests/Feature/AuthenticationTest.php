<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailOtp;
use App\Services\EmailVerificationService;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function registrationPayload(array $overrides = []): array
    {
        return array_replace([
            'name' => 'Budi Santoso',
            'nim' => '01082230011',
            'campus' => 'UPH',
            'email' => 'budi.santoso@student.uph.edu',
            'whatsapp_number' => '081234567890',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'terms' => '1',
        ], $overrides);
    }

    #[Test]
    public function registration_creates_an_unverified_student_and_sends_an_otp(): void
    {
        Notification::fake();

        $response = $this->post(route('register.store'), $this->registrationPayload());

        $response->assertRedirect(route('verification.notice'));

        $user = User::query()->where('email', 'budi.santoso@student.uph.edu')->first();

        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);
        $this->assertSame('UPH', $user->campus);
        $this->assertSame('6281234567890', $user->whatsapp_number);
        $this->assertTrue($user->isStudent());
        $this->assertAuthenticatedAs($user);

        Notification::assertSentTo($user, VerifyEmailOtp::class);
    }

    #[Test]
    public function registration_rejects_non_campus_emails(): void
    {
        $this->from(route('register'))
            ->post(route('register.store'), $this->registrationPayload(['email' => 'budi@gmail.com']))
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('users', ['email' => 'budi@gmail.com']);
    }

    #[Test]
    public function registration_accepts_generic_ac_id_emails(): void
    {
        Notification::fake();

        $this->post(route('register.store'), $this->registrationPayload(['email' => 'budi@mahasiswa.kampuslain.ac.id']))
            ->assertRedirect(route('verification.notice'));

        $this->assertDatabaseHas('users', ['email' => 'budi@mahasiswa.kampuslain.ac.id']);
    }

    #[Test]
    public function registration_requires_a_known_campus_and_unique_nim_per_campus(): void
    {
        $this->from(route('register'))
            ->post(route('register.store'), $this->registrationPayload(['campus' => 'XYZ']))
            ->assertSessionHasErrors('campus');

        $this->student(['nim' => '01082230011', 'campus' => 'UPH'], false);

        $this->from(route('register'))
            ->post(route('register.store'), $this->registrationPayload())
            ->assertSessionHasErrors('nim');
    }

    #[Test]
    public function otp_verification_marks_the_email_as_verified(): void
    {
        Notification::fake();
        config(['nitip.otp.dev_show' => true]);

        $user = $this->student(['email_verified_at' => null], false);
        $service = app(EmailVerificationService::class);
        $service->send($user);
        $code = $service->devCode($user);

        $this->assertNotNull($code);
        $this->assertNotNull($user->fresh()->otp_code_hash);

        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('verification.notice'));

        $this->actingAs($user)
            ->post(route('verification.verify'), ['digits' => str_split($code)])
            ->assertRedirect(route('verification.verified'));

        $fresh = $user->fresh();
        $this->assertNotNull($fresh->email_verified_at);
        $this->assertNull($fresh->otp_code_hash);

        $this->actingAs($fresh)->get(route('dashboard'))->assertOk();
    }

    #[Test]
    public function otp_bypass_accepts_any_code_in_development(): void
    {
        Notification::fake();
        config(['nitip.otp.bypass' => true]);

        $user = $this->student(['email_verified_at' => null], false);

        $this->actingAs($user)
            ->post(route('verification.verify'), ['digits' => ['1', '2', '3', '4', '5', '6']])
            ->assertRedirect(route('verification.verified'));

        $this->assertNotNull($user->fresh()->email_verified_at);

        $other = $this->student(['email_verified_at' => null], false);
        $this->actingAs($other)
            ->from(route('verification.notice'))
            ->post(route('verification.verify'), ['code' => '12'])
            ->assertSessionHasErrors('code');
        $this->assertNull($other->fresh()->email_verified_at);
    }

    #[Test]
    public function wrong_otp_is_rejected_and_counted(): void
    {
        Notification::fake();

        $user = $this->student(['email_verified_at' => null], false);
        app(EmailVerificationService::class)->send($user);

        $this->actingAs($user)
            ->from(route('verification.notice'))
            ->post(route('verification.verify'), ['code' => '000000'])
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHasErrors('code');

        $fresh = $user->fresh();
        $this->assertNull($fresh->email_verified_at);
        $this->assertSame(1, $fresh->otp_attempts);
    }

    #[Test]
    public function users_can_login_with_valid_credentials(): void
    {
        $user = $this->student([], false);

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function admins_are_redirected_to_the_admin_dashboard_after_login(): void
    {
        $admin = $this->admin();

        $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
    }

    #[Test]
    public function login_fails_with_invalid_credentials(): void
    {
        $user = $this->student([], false);

        $this->from(route('login'))
            ->post(route('login.store'), ['email' => $user->email, 'password' => 'salah'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    #[Test]
    public function users_can_logout(): void
    {
        $user = $this->student([], false);

        $this->actingAs($user)->post(route('logout'))->assertRedirect(route('home'));

        $this->assertGuest();
    }

    #[Test]
    public function guests_are_redirected_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('orders.index'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    #[Test]
    public function password_reset_link_can_be_requested(): void
    {
        Notification::fake();

        $user = $this->student([], false);

        $this->post(route('password.email'), ['email' => $user->email])->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }
}
