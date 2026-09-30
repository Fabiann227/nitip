@props(['trip', 'cta' => true])
@php
    $isMine = auth()->check() && $trip->fulfiller_id === auth()->id();
    $url = auth()->check() ? route('trips.show', $trip) : route('login');
    $remaining = $trip->remainingSlots();
@endphp
<article {{ $attributes->merge(['class' => 'card card-hover p-4 flex flex-col justify-between h-full']) }}>
    <div>
        <div class="flex items-center justify-between gap-2 mb-3">
            <x-ui.badge variant="primary" icon="directions_walk">Rute relawan</x-ui.badge>
            <span class="text-[11px] text-on-surface-variant">#{{ $trip->code }}</span>
        </div>
        <h3 class="font-sans font-bold text-base text-on-surface leading-snug mb-1">
            <a href="{{ $url }}" class="hover:text-primary">Menuju {{ $trip->destination }}</a>
        </h3>
        <p class="text-[13px] text-on-surface-variant mb-3">
            Berangkat {{ $trip->departure_at->diffForHumans() }} ({{ $trip->departure_at->translatedFormat('H:i') }})
        </p>
        <div class="p-2.5 rounded-xl bg-surface-low mb-3 space-y-1.5">
            @if ($trip->waypoints)
                <div class="flex items-center gap-1.5 text-xs text-on-surface font-medium">
                    <span class="text-primary font-bold shrink-0">Lewat:</span><span class="truncate">{{ $trip->waypoints }}</span>
                </div>
            @endif
            <div class="flex items-center justify-between gap-2 text-xs text-on-surface-variant">
                <span class="flex items-center gap-1 text-secondary font-medium truncate"><span class="material-symbols-outlined text-[16px]">{{ $trip->category->icon }}</span>{{ $trip->category->name }}</span>
                <span class="flex items-center gap-1 shrink-0"><span class="material-symbols-outlined text-[16px]">{{ $trip->transport_mode->icon() }}</span>{{ $trip->transport_mode->label() }}</span>
            </div>
            <div class="flex items-center gap-1.5 text-xs text-muted">
                <span class="material-symbols-outlined text-[16px]">timer</span>Titipan ditutup {{ $trip->closes_at->translatedFormat('H:i') }}
            </div>
        </div>
        <div class="flex items-center justify-between gap-2 mb-3">
            <x-ui.user-chip :user="$trip->fulfiller" :link="auth()->check()" />
            <x-ui.rating :value="$trip->fulfiller->ratingAverage()" :count="$trip->fulfiller->ratingCount()" :show-empty="false" />
        </div>
    </div>
    <div class="pt-1 flex items-center justify-between gap-3">
        <div>
            <span class="text-[11px] text-on-surface-variant block">Biaya jasa / titipan</span>
            <span class="font-sans font-extrabold text-base text-primary tabular">{{ rupiah($trip->service_fee) }}</span>
        </div>
        <div class="flex items-center gap-2">
            <x-ui.badge :variant="$remaining > 0 ? 'secondary' : 'danger'" icon="backpack">{{ $remaining }} slot</x-ui.badge>
            @if ($cta)
                @if ($isMine)
                    <a href="{{ $url }}" class="btn btn-sm btn-soft">Rutemu</a>
                @elseif ($trip->isJoinable())
                    <a href="{{ $url }}" class="btn btn-sm btn-soft">Titip ke {{ $trip->fulfiller->shortName() }}</a>
                @else
                    <a href="{{ $url }}" class="btn btn-sm btn-outline">Lihat</a>
                @endif
            @endif
        </div>
    </div>
</article>
