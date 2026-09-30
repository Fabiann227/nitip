{{-- Amount breakdown + payment proof state. Expects $order. --}}
@php $diff = $order->settlementDifference(); @endphp
<div class="space-y-4">
    <dl class="text-sm space-y-2">
        <div class="flex justify-between"><dt class="text-on-surface-variant">Biaya jasa relawan</dt><dd class="font-semibold tabular">{{ rupiah($order->service_fee) }}</dd></div>
        @if ($order->category->has_item_cost)
            <div class="flex justify-between"><dt class="text-on-surface-variant">Perkiraan biaya {{ $order->category->isPrint() ? 'cetak' : 'barang' }}</dt><dd class="font-semibold tabular">{{ rupiah($order->estimated_item_cost) }}</dd></div>
        @endif
        <div class="flex justify-between pt-2 border-t border-border"><dt class="font-semibold text-on-surface">Total ditransfer di awal</dt><dd class="font-sans font-extrabold text-lg text-primary tabular">{{ rupiah($order->estimatedTotal()) }}</dd></div>
        @if ($order->actual_item_cost !== null)
            <div class="flex justify-between"><dt class="text-on-surface-variant">Biaya riil sesuai struk</dt><dd class="font-semibold tabular">{{ rupiah($order->actual_item_cost) }}</dd></div>
            <div class="flex justify-between items-center">
                <dt class="text-on-surface-variant">Selisih nota</dt>
                <dd>
                    @if ($diff === 0)<x-ui.badge variant="success" icon="check">Pas, tidak ada selisih</x-ui.badge>
                    @elseif ($diff > 0)<x-ui.badge variant="warning" icon="north_east">Penitip menambah {{ rupiah($diff) }}</x-ui.badge>
                    @else<x-ui.badge variant="info" icon="south_west">Relawan kembalikan {{ rupiah(abs($diff)) }}</x-ui.badge>@endif
                </dd>
            </div>
            <div class="flex justify-between pt-2 border-t border-border"><dt class="font-semibold text-on-surface">Total akhir</dt><dd class="font-sans font-extrabold text-lg text-on-surface tabular">{{ rupiah($order->finalTotal()) }}</dd></div>
        @endif
    </dl>

    @if ($order->hasPaymentProof())
        <div>
            <h3 class="text-sm font-semibold text-on-surface mb-2">Bukti transfer</h3>
            <div class="flex items-start gap-3 p-3 rounded-xl border border-border">
                <a href="{{ $order->fileUrl('proof') }}" target="_blank" rel="noopener" class="shrink-0"><img src="{{ $order->fileUrl('proof') }}" alt="Bukti transfer" class="w-16 h-16 rounded-lg object-cover border border-border" loading="lazy"></a>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm font-semibold tabular">{{ rupiah($order->estimatedTotal()) }}</span>
                        @if ($order->payment_verified_at)<x-ui.badge variant="success" class="h-5 px-2 text-[10px]">Terverifikasi</x-ui.badge>
                        @elseif ($order->payment_rejection_reason)<x-ui.badge variant="danger" class="h-5 px-2 text-[10px]">Ditolak</x-ui.badge>
                        @else<x-ui.badge variant="warning" class="h-5 px-2 text-[10px]">Menunggu verifikasi</x-ui.badge>@endif
                    </div>
                    <p class="text-xs text-on-surface-variant">Ke {{ $order->payment_method }} &bull; {{ $order->payment_submitted_at?->translatedFormat('d M H:i') }}</p>
                    @if ($order->payment_note)<p class="text-xs text-muted">Catatan: {{ $order->payment_note }}</p>@endif
                    @if ($order->payment_rejection_reason)<p class="text-xs text-error">Ditolak: {{ $order->payment_rejection_reason }}</p>@endif
                </div>
                <a href="{{ $order->fileUrl('proof') }}" target="_blank" rel="noopener" class="btn-icon" aria-label="Lihat bukti"><span class="material-symbols-outlined">open_in_new</span></a>
            </div>
        </div>
    @endif
</div>
