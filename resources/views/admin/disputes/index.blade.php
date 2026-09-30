<x-layouts.admin title="Sengketa">
    <div class="flex items-center gap-2 mb-4">
        @foreach (['open' => 'Menunggu tinjauan', 'resolved' => 'Selesai', 'all' => 'Semua'] as $key => $label)
            <x-ui.chip :href="route('admin.disputes.index', ['status' => $key])" :active="$filter === $key">{{ $label }}</x-ui.chip>
        @endforeach
    </div>
    <x-ui.card :padding="false">
        @if ($disputes->isEmpty())
            <x-ui.empty-state icon="gavel" title="Tidak ada sengketa" description="Semua transaksi berjalan mulus." />
        @else
            <div class="divide-y divide-border">
                @foreach ($disputes as $dispute)
                    <a href="{{ route('admin.disputes.show', $dispute) }}" class="flex items-start gap-4 p-4 hover:bg-canvas">
                        <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $dispute->status->badgeClass() }}"><span class="material-symbols-outlined">gavel</span></span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap"><span class="text-sm font-semibold">{{ $dispute->reason->label() }}</span><x-ui.status-badge :status="$dispute->status" :icon="false" class="h-5 px-2 text-[10px]" /></div>
                            <p class="text-sm text-on-surface-variant truncate">#{{ $dispute->order->code }} &bull; {{ $dispute->order->title }}</p>
                            <p class="text-xs text-muted">Dibuka {{ $dispute->openedBy->shortName() }} {{ $dispute->created_at->diffForHumans() }} &bull; {{ $dispute->order->requester->shortName() }} vs {{ $dispute->order->fulfiller?->shortName() }}@if ($dispute->resolution) &bull; {{ $dispute->resolution->shortLabel() }}@endif</p>
                        </div>
                        <span class="material-symbols-outlined text-outline">chevron_right</span>
                    </a>
                @endforeach
            </div>
            <div class="px-4 pb-4">{{ $disputes->links() }}</div>
        @endif
    </x-ui.card>
</x-layouts.admin>
