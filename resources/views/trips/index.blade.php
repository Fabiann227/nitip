<x-layouts.app title="Rute Saya">
    <x-ui.page-header eyebrow="Jalur Jastiper · OFFER" title="Rute saya" subtitle="Rute yang kamu buka untuk membawa titipan teman sekampus.">
        <x-ui.button :href="route('trips.create')" size="sm" icon="add_road">Buka rute baru</x-ui.button>
    </x-ui.page-header>

    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1 mb-4">
        @foreach (['open' => 'Terbuka', 'closed' => 'Ditutup', 'cancelled' => 'Dibatalkan', 'all' => 'Semua'] as $key => $label)
            <x-ui.chip :href="route('trips.index', ['status' => $key])" :active="$filter === $key">{{ $label }}</x-ui.chip>
        @endforeach
    </div>

    @if ($trips->isEmpty())
        <x-ui.card>
            <x-ui.empty-state icon="route" title="Belum ada rute" description="Mau ke kantin atau keluar gerbang? Buka rute, tentukan kuota, dan dapat uang saku dari titipan yang searah.">
                <x-ui.button :href="route('trips.create')" size="sm">Buka rute</x-ui.button>
            </x-ui.empty-state>
        </x-ui.card>
    @else
        <div class="space-y-2">
            @foreach ($trips as $trip)
                <a href="{{ route('trips.show', $trip) }}" class="flex items-center gap-3 p-4 rounded-2xl bg-white border border-border hover:shadow-float hover:-translate-y-px transition-all">
                    <span class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 {{ $trip->status->badgeClass() }}"><span class="material-symbols-outlined text-[22px]">{{ $trip->transport_mode->icon() }}</span></span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap"><span class="text-[11px] font-semibold text-muted">{{ $trip->code }}</span><x-ui.status-badge :status="$trip->status" class="h-5 px-2 text-[10px]" />@if ($trip->status === \App\Enums\TripStatus::Open && ! $trip->isOpen())<x-ui.badge variant="neutral" class="h-5 px-2 text-[10px]">Lewat batas waktu</x-ui.badge>@endif</div>
                        <p class="text-sm font-semibold text-on-surface truncate mt-0.5">{{ $trip->routeLabel() }}</p>
                        <p class="text-xs text-on-surface-variant">Berangkat {{ $trip->departure_at->translatedFormat('d M H:i') }} &bull; {{ $trip->category->name }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="font-sans font-bold text-sm text-primary tabular block">{{ $trip->active_orders_count }}/{{ $trip->max_slots }} slot</span>
                        <span class="text-[11px] text-muted">{{ rupiah($trip->service_fee) }}/titipan</span>
                    </div>
                    <span class="material-symbols-outlined text-outline hidden sm:block">chevron_right</span>
                </a>
            @endforeach
        </div>
        {{ $trips->links() }}
    @endif
</x-layouts.app>
