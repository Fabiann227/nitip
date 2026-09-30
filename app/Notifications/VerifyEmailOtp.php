<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailOtp extends Notification
{
    use Queueable;

    public function __construct(public string $code, public int $ttlMinutes) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Kode verifikasi Nitip: {$this->code}")
            ->greeting("Halo {$notifiable->name}!")
            ->line('Gunakan kode berikut untuk memverifikasi email kampus kamu di Nitip:')
            ->line("**{$this->code}**")
            ->line("Kode berlaku selama {$this->ttlMinutes} menit. Jangan bagikan kode ini ke siapa pun.")
            ->line('Jika kamu tidak merasa mendaftar di Nitip, abaikan email ini.')
            ->salutation('Salam hangat, Tim Nitip');
    }
}
