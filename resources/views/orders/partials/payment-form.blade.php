{{-- Requester payment form: transfer to the fulfiller's account, upload proof. Expects $order (fulfiller loaded). --}}
@php $fulfiller = $order->fulfiller; @endphp
<div class="mt-5 pt-5 border-t border-border">
    <h3 class="font-sans font-bold text-base mb-1">Bayar sekarang</h3>
    <p class="text-sm text-on-surface-variant mb-4">Transfer <strong class="text-primary">{{ rupiah($order->estimatedTotal()) }}</strong> ke {{ $fulfiller->shortName() }}, lalu unggah bukti transfer.</p>

    @if ($order->payment_rejection_reason)
        <x-ui.alert type="error" icon="error" class="mb-4" title="Bukti sebelumnya ditolak">{{ $order->payment_rejection_reason }}. Unggah ulang bukti yang benar.</x-ui.alert>
    @endif

    @if (! $fulfiller->hasPaymentMethod())
        <x-ui.alert type="warning">Relawan belum mengatur metode pembayaran. Hubungi via WhatsApp.</x-ui.alert>
    @else
        <div class="flex items-start gap-3 p-4 rounded-xl border-2 border-primary bg-primary-fixed/20 mb-4" x-data="{ copied: false }">
            <span class="material-symbols-outlined text-primary mt-0.5">{{ $fulfiller->payment_type->icon() }}</span>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-on-surface">{{ $fulfiller->paymentTitle() }}</p>
                @if ($fulfiller->payment_account_number)
                    <p class="flex items-center gap-2 text-base text-on-surface tabular font-mono">
                        {{ $fulfiller->payment_account_number }}
                        <button type="button" class="btn btn-xs btn-outline" x-on:click="navigator.clipboard.writeText(@js($fulfiller->payment_account_number)); copied = true; setTimeout(() => copied = false, 1500)"><span x-text="copied ? 'Tersalin' : 'Salin'">Salin</span></button>
                    </p>
                @endif
                <p class="text-xs text-on-surface-variant">a.n. {{ $fulfiller->payment_account_name }}</p>
                @if ($fulfiller->hasQris())
                    <a href="{{ route('users.qris', $fulfiller) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-xs font-semibold text-primary mt-1"><span class="material-symbols-outlined text-[16px]">qr_code_2</span>Lihat kode QRIS</a>
                @endif
            </div>
        </div>

        <form method="POST" action="{{ route('orders.payment.store', $order) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <x-ui.file-input name="proof" label="Bukti transfer" accept="image/*" :max-mb="4" required icon="receipt_long" hint="Screenshot m-banking / e-wallet, JPG/PNG maks 4 MB" />
            <x-ui.input name="note" label="Catatan untuk relawan" optional placeholder="Contoh: sudah transfer 12:10 via BCA" maxlength="255" />
            <x-ui.button type="submit" icon="upload" full>Kirim bukti pembayaran</x-ui.button>
        </form>
    @endif
</div>
