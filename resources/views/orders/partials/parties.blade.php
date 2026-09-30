{{-- Requester & fulfiller cards. Expects $order. Optional $whatsappUrl. --}}
<div class="space-y-3">
    <div class="p-3 rounded-xl bg-surface-low">
        <p class="text-[11px] font-semibold uppercase tracking-wider text-muted mb-2">Penitip</p>
        <div class="flex items-center justify-between gap-2">
            <x-ui.user-chip :user="$order->requester" size="md" :subtitle="($order->requester->campus ?? '-').' · NIM '.$order->requester->nim" />
            <x-ui.rating :value="$order->requester->ratingAverage()" :count="$order->requester->ratingCount()" :show-empty="false" />
        </div>
    </div>
    <div class="p-3 rounded-xl bg-surface-low">
        <p class="text-[11px] font-semibold uppercase tracking-wider text-muted mb-2">Relawan</p>
        @if ($order->fulfiller)
            <div class="flex items-center justify-between gap-2">
                <x-ui.user-chip :user="$order->fulfiller" size="md" :subtitle="($order->fulfiller->campus ?? '-').' · '.$order->fulfiller->completedDeliveriesCount().' pengantaran'" />
                <x-ui.rating :value="$order->fulfiller->ratingAverage()" :count="$order->fulfiller->ratingCount()" :show-empty="false" />
            </div>
        @else
            <p class="text-sm text-muted flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>Sedang mencari relawan yang searah...</p>
        @endif
    </div>
    @if (! empty($whatsappUrl))
        <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="btn btn-accent w-full"><span class="material-symbols-outlined text-[20px]">chat</span>Chat via WhatsApp</a>
        <p class="text-[11px] text-muted text-center">Pesan template berisi kode order otomatis terisi.</p>
    @endif
    @if ($order->trip)
        <a href="{{ route('trips.show', $order->trip) }}" class="flex items-center gap-2 p-3 rounded-xl border border-border hover:bg-surface-low text-sm"><span class="material-symbols-outlined text-primary">route</span><span class="min-w-0 flex-1"><span class="block font-semibold truncate">Rute {{ $order->trip->code }}</span><span class="block text-xs text-muted truncate">{{ $order->trip->routeLabel() }}</span></span><span class="material-symbols-outlined text-outline">chevron_right</span></a>
    @endif
</div>
