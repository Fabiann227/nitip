@props(['order', 'perspective' => 'requester', 'href' => null])
@php
    $href ??= route('orders.show', $order);
    $counterpart = $perspective === 'requester' ? $order->fulfiller : $order->requester;
@endphp
<a href="{{ $href }}" {{ $attributes->merge(['class' => 'flex items-center gap-3 p-3 sm:p-4 rounded-2xl bg-white border border-border hover:shadow-float hover:-translate-y-px transition-all']) }}>
    <span class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 {{ $order->status->badgeClass() }}">
        <span class="material-symbols-outlined text-[22px]">{{ $order->category->icon }}</span>
    </span>
    <div class="min-w-0 flex-1">
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-[11px] font-semibold text-muted tabular">{{ $order->code }}</span>
            <x-ui.status-badge :status="$order->status" class="h-5 px-2 text-[10px]" :icon="false" />
            @if ($order->needs_refund)<x-ui.badge variant="danger" class="h-5 px-2 text-[10px]">Perlu refund</x-ui.badge>@endif
        </div>
        <p class="text-sm font-semibold text-on-surface truncate mt-0.5">{{ $order->title }}</p>
        <p class="text-xs text-on-surface-variant truncate">
            {{ $order->category->name }}
            @if ($counterpart) &bull; {{ $perspective === 'requester' ? 'Relawan' : 'Penitip' }}: {{ $counterpart->shortName() }} @elseif ($perspective === 'requester') &bull; Menunggu relawan @endif
            &bull; {{ $order->updated_at->diffForHumans() }}
        </p>
    </div>
    <div class="text-right shrink-0">
        <span class="font-sans font-bold text-sm text-primary tabular block">{{ rupiah($order->estimatedTotal()) }}</span>
        <span class="text-[11px] text-muted">jasa {{ rupiah($order->service_fee) }}</span>
    </div>
    <span class="material-symbols-outlined text-outline hidden sm:block">chevron_right</span>
</a>
