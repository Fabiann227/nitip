<x-layouts.app :title="'Sengketa '.$order->code">
    <x-ui.page-header :back="route('orders.show', $order)" back-label="Kembali ke pesanan" :eyebrow="'Sengketa · Pesanan #'.$order->code" :title="$dispute->reason->label()" :subtitle="'Dibuka oleh '.$dispute->openedBy->shortName().' '.$dispute->created_at->diffForHumans()">
        <x-ui.status-badge :status="$dispute->status" class="h-8 px-3 text-sm" />
    </x-ui.page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-ui.card title="Kronologi" icon="description">
                <p class="text-sm text-on-surface leading-relaxed whitespace-pre-line">{{ $dispute->description }}</p>
                @if ($dispute->hasEvidence())
                    <div class="mt-4">
                        <h3 class="text-sm font-semibold mb-2">Bukti</h3>
                        <a href="{{ route('disputes.evidence', $dispute) }}" target="_blank" rel="noopener" class="block w-full sm:w-64 rounded-xl overflow-hidden border border-border"><img src="{{ route('disputes.evidence', $dispute) }}" alt="Bukti" class="w-full h-40 object-cover"></a>
                    </div>
                @endif
            </x-ui.card>

            @if ($dispute->status === \App\Enums\DisputeStatus::Resolved)
                <x-ui.alert type="success" icon="balance" title="Keputusan admin: {{ $dispute->resolution?->shortLabel() }}">
                    {{ $dispute->resolution_note }}
                    <span class="block text-xs mt-1">Oleh {{ $dispute->resolvedBy?->name }} &bull; {{ $dispute->resolved_at?->translatedFormat('d M Y H:i') }}</span>
                </x-ui.alert>
            @else
                <x-ui.alert type="warning" icon="hourglass_top" title="Menunggu tinjauan admin">Admin Nitip akan meninjau audit trail, bukti transfer, dan struk, lalu menghubungi kedua pihak bila perlu. Pesanan dibekukan selama sengketa berjalan.</x-ui.alert>
            @endif
        </div>
        <aside class="space-y-6">
            <x-ui.card title="Ringkasan pesanan" icon="receipt_long">
                <dl class="text-sm space-y-2">
                    <div class="flex justify-between"><dt class="text-muted">Judul</dt><dd class="font-semibold text-right">{{ $order->title }}</dd></div>
                    <div class="flex justify-between"><dt class="text-muted">Kategori</dt><dd class="font-semibold">{{ $order->category->name }}</dd></div>
                    <div class="flex justify-between"><dt class="text-muted">Total dibayar</dt><dd class="font-semibold tabular">{{ rupiah($order->estimatedTotal()) }}</dd></div>
                    @if ($order->actual_item_cost !== null)<div class="flex justify-between"><dt class="text-muted">Biaya riil</dt><dd class="font-semibold tabular">{{ rupiah($order->actual_item_cost) }}</dd></div>@endif
                    <div class="flex justify-between"><dt class="text-muted">Status</dt><dd><x-ui.status-badge :status="$order->status" :icon="false" /></dd></div>
                </dl>
                <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline w-full mt-4">Buka detail pesanan</a>
            </x-ui.card>
            <x-ui.card title="Pihak" icon="group">
                <div class="space-y-3">
                    <x-ui.user-chip :user="$order->requester" subtitle="Penitip" />
                    @if ($order->fulfiller)<x-ui.user-chip :user="$order->fulfiller" subtitle="Relawan" />@endif
                </div>
            </x-ui.card>
        </aside>
    </div>
</x-layouts.app>
