@props(['order', 'cta' => true])
@php
    $isMine = auth()->check() && $order->requester_id === auth()->id();
    $url = auth()->check() ? route('orders.show', $order) : route('login');
@endphp
<article {{ $attributes->merge(['class' => 'card card-hover p-4 flex flex-col justify-between h-full']) }}>
    <div>
        <div class="flex items-center justify-between gap-2 mb-3">
            <x-ui.user-chip :user="$order->requester" :link="auth()->check()" />
            <x-ui.badge variant="{{ $order->category->isPrint() ? 'neutral' : 'secondary' }}" :icon="$order->category->icon" class="shrink-0">{{ $order->category->name }}</x-ui.badge>
        </div>
        <h3 class="font-sans font-bold text-base text-on-surface leading-snug mb-1">
            <a href="{{ $url }}" class="hover:text-primary">{{ $order->title }}</a>
        </h3>
        <p class="text-[13px] text-on-surface-variant mb-3 truncate">Dari: {{ $order->pickup_location }}</p>
        <div class="p-2.5 rounded-xl bg-surface-low mb-3 space-y-1.5">
            <div class="flex items-center gap-1.5 text-xs text-on-surface font-medium">
                <span class="material-symbols-outlined text-[16px] text-primary">pin_drop</span>
                <span class="truncate">Antar ke: {{ $order->dropoff_location }}</span>
            </div>
            @if ($order->needed_by)
                <div class="flex items-center gap-1.5 text-xs {{ $order->needed_by->diffInMinutes(now(), false) > -30 ? 'text-warning' : 'text-tertiary' }} font-medium">
                    <span class="material-symbols-outlined text-[16px]">schedule</span>
                    <span>Dibutuhkan {{ $order->needed_by->diffForHumans() }} ({{ $order->needed_by->translatedFormat('H:i') }})</span>
                </div>
            @endif
        </div>
    </div>
    <div class="pt-1 flex items-center justify-between gap-3">
        <div>
            <span class="text-[11px] text-on-surface-variant block">Biaya jasa</span>
            <span class="font-sans font-extrabold text-base text-primary tabular">{{ rupiah($order->service_fee) }}</span>
            @if ($order->estimated_item_cost > 0)<span class="text-[11px] text-muted block">+ barang ~{{ rupiah($order->estimated_item_cost) }}</span>@endif
        </div>
        @if ($cta)
            @if ($isMine)
                <a href="{{ $url }}" class="btn btn-sm btn-soft">Milikmu</a>
            @else
                <a href="{{ $url }}" class="btn btn-sm btn-primary">Ambil Titipan</a>
            @endif
        @endif
    </div>
</article>
