<x-layouts.admin :title="'Pesanan '.$order->code">
    <x-slot:actions>
        @if (! $order->status->isTerminal())
            <x-ui.confirm-form :action="route('admin.orders.cancel', $order)" title="Batalkan pesanan sebagai admin?" message="Gunakan untuk pembatalan darurat. Jika pembayaran sudah diverifikasi, pesanan ditandai perlu refund. Tindakan dicatat di audit trail." confirm="Batalkan pesanan" variant="danger" reason reason-label="Alasan" button-class="btn btn-sm btn-danger-soft" icon="cancel">Batalkan</x-ui.confirm-form>
        @endif
        @if ($order->dispute)<a href="{{ route('admin.disputes.show', $order->dispute) }}" class="btn btn-sm btn-outline"><span class="material-symbols-outlined text-[18px]">gavel</span>Sengketa</a>@endif
    </x-slot:actions>

    <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-secondary hover:text-primary mb-4"><span class="material-symbols-outlined text-[16px]">arrow_back</span>Daftar pesanan</a>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div><span class="eyebrow">{{ $order->category->name }} · {{ $order->campus }} · {{ $order->isFromTrip() ? 'Lewat rute relawan' : 'Permintaan' }}</span><h2 class="font-sans font-bold text-2xl">{{ $order->title }}</h2><p class="text-sm text-muted">Dibuat {{ $order->created_at->translatedFormat('d M Y H:i') }}</p></div>
        <x-ui.status-badge :status="$order->status" class="h-8 px-3 text-sm" />
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 space-y-6">
            <x-ui.card class="p-5"><x-ui.stepper :status="$order->status" class="mb-4" />
                @if ($order->status === \App\Enums\OrderStatus::Cancelled)<x-ui.alert type="error" title="Dibatalkan">{{ $order->cancel_reason }} &mdash; {{ $order->cancelledBy?->name }} @if ($order->needs_refund)<strong>(perlu refund)</strong>@endif</x-ui.alert>@endif
            </x-ui.card>
            <x-ui.card title="Detail titipan" icon="inventory_2">@include('orders.partials.details')</x-ui.card>
            <x-ui.card title="Pembayaran" icon="payments">@include('orders.partials.payment-summary')</x-ui.card>
            <x-ui.card title="Audit trail" icon="history"><x-ui.timeline :events="$order->events" /></x-ui.card>
        </div>
        <aside class="space-y-6">
            <x-ui.card title="Pihak" icon="group">@include('orders.partials.parties', ['whatsappUrl' => null])
                <div class="mt-3 pt-3 border-t border-border text-xs text-muted space-y-1">
                    <p>WA penitip: <a href="{{ $order->requester->whatsappUrl() }}" target="_blank" class="text-primary font-semibold">{{ $order->requester->whatsappPretty() }}</a></p>
                    @if ($order->fulfiller)<p>WA relawan: <a href="{{ $order->fulfiller->whatsappUrl() }}" target="_blank" class="text-primary font-semibold">{{ $order->fulfiller->whatsappPretty() }}</a></p>@endif
                    <p>PIN serah terima: <span class="font-mono font-bold text-on-surface">{{ $order->completion_pin }}</span></p>
                </div>
            </x-ui.card>
            @if ($order->reviews->isNotEmpty())
                <x-ui.card title="Ulasan" icon="star">@foreach ($order->reviews as $review)<div class="py-2 border-b last:border-b-0 border-border"><div class="flex items-center justify-between"><span class="text-sm font-semibold">{{ $review->reviewer->shortName() }}</span><x-ui.rating :value="$review->rating" /></div>@if ($review->comment)<p class="text-sm text-on-surface-variant">"{{ $review->comment }}"</p>@endif</div>@endforeach</x-ui.card>
            @endif
        </aside>
    </div>
</x-layouts.admin>
