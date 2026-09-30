<x-layouts.admin :title="'Sengketa #'.$order->code">
    <a href="{{ route('admin.disputes.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-secondary hover:text-primary mb-4"><span class="material-symbols-outlined text-[16px]">arrow_back</span>Daftar sengketa</a>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div><span class="eyebrow">Pesanan #{{ $order->code }} · {{ $order->category->name }}</span><h2 class="font-sans font-bold text-2xl">{{ $dispute->reason->label() }}</h2><p class="text-sm text-muted">Dibuka oleh {{ $dispute->openedBy->name }} ({{ $order->isRequester($dispute->openedBy) ? 'penitip' : 'relawan' }}) &bull; {{ $dispute->created_at->translatedFormat('d M Y H:i') }}</p></div>
        <x-ui.status-badge :status="$dispute->status" class="h-8 px-3 text-sm" />
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 space-y-6">
            <x-ui.card title="Kronologi pelapor" icon="description">
                <p class="text-sm leading-relaxed whitespace-pre-line">{{ $dispute->description }}</p>
                @if ($dispute->hasEvidence())
                    <a href="{{ route('disputes.evidence', $dispute) }}" target="_blank" class="block w-full sm:w-64 mt-4 rounded-xl overflow-hidden border border-border"><img src="{{ route('disputes.evidence', $dispute) }}" class="w-full h-40 object-cover" alt="Bukti"></a>
                @endif
            </x-ui.card>

            @if ($dispute->isOpen())
                <x-ui.card title="Keputusan admin" subtitle="Tinjau audit trail, bukti transfer, dan struk sebelum memutuskan." icon="balance" class="border-primary/40">
                    <form method="POST" action="{{ route('admin.disputes.resolve', $dispute) }}" class="space-y-4">
                        @csrf
                        <div class="space-y-2">
                            @foreach ($resolutions as $resolution)
                                <x-ui.checkbox type="radio" name="resolution" :value="$resolution->value" :checked="old('resolution') === $resolution->value" class="p-3 rounded-xl border border-border hover:bg-surface-low">{{ $resolution->label() }}</x-ui.checkbox>
                            @endforeach
                            <x-ui.field-error name="resolution" />
                        </div>
                        <x-ui.textarea name="note" label="Catatan resolusi (dikirim ke kedua pihak)" rows="4" required placeholder="Jelaskan dasar keputusan, nominal refund jika ada, dan langkah selanjutnya." />
                        <div class="flex justify-end"><x-ui.button type="submit" icon="gavel">Simpan keputusan</x-ui.button></div>
                    </form>
                </x-ui.card>
            @else
                <x-ui.alert type="success" icon="balance" title="Keputusan: {{ $dispute->resolution?->shortLabel() }}">{{ $dispute->resolution_note }}<span class="block text-xs mt-1">{{ $dispute->resolvedBy?->name }} &bull; {{ $dispute->resolved_at?->translatedFormat('d M Y H:i') }}</span></x-ui.alert>
            @endif

            <x-ui.card title="Detail titipan" icon="inventory_2">@include('orders.partials.details')</x-ui.card>
            <x-ui.card title="Pembayaran" icon="payments">@include('orders.partials.payment-summary')</x-ui.card>
            <x-ui.card title="Audit trail" icon="history"><x-ui.timeline :events="$order->events" /></x-ui.card>
        </div>
        <aside class="space-y-6">
            <x-ui.card title="Pihak" icon="group">@include('orders.partials.parties', ['whatsappUrl' => null])
                <div class="mt-3 pt-3 border-t border-border text-xs text-muted space-y-1">
                    <p>WA penitip: <a href="{{ $order->requester->whatsappUrl() }}" target="_blank" class="text-primary font-semibold">{{ $order->requester->whatsappPretty() }}</a></p>
                    @if ($order->fulfiller)<p>WA relawan: <a href="{{ $order->fulfiller->whatsappUrl() }}" target="_blank" class="text-primary font-semibold">{{ $order->fulfiller->whatsappPretty() }}</a></p>@endif
                </div>
            </x-ui.card>
            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline w-full"><span class="material-symbols-outlined text-[18px]">receipt_long</span>Buka halaman pesanan</a>
        </aside>
    </div>
</x-layouts.admin>
