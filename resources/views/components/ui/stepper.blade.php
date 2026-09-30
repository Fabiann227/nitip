@props(['status'])
@php
    $steps = [
        ['label' => 'Post & Match', 'icon' => 'campaign'],
        ['label' => 'Bayar & Verifikasi', 'icon' => 'payments'],
        ['label' => 'Diproses', 'icon' => 'shopping_cart_checkout'],
        ['label' => 'Diantar', 'icon' => 'directions_walk'],
        ['label' => 'Selesai', 'icon' => 'task_alt'],
    ];
    $current = $status->step();
    $terminalBad = $current === null;
    $isCompleted = $status === \App\Enums\OrderStatus::Completed;
@endphp
<ol {{ $attributes->merge(['class' => 'flex items-start w-full']) }} aria-label="Tahapan transaksi">
    @foreach ($steps as $index => $step)
        @php
            $number = $index + 1;
            $done = $current !== null && ($number < $current || ($number === 5 && $isCompleted));
            $active = ! $terminalBad && $number === $current && ! ($number === 5 && $isCompleted);
        @endphp
        <li class="flex-1 flex flex-col items-center relative min-w-0">
            @if ($index > 0)
                <div class="absolute top-4 right-1/2 w-full h-[2px] -z-0 {{ $done || $active ? 'bg-primary' : 'stepper-line' }}"></div>
            @endif
            <div class="relative z-10 w-8 h-8 rounded-full flex items-center justify-center border-2 transition-colors
                {{ $done ? 'bg-primary border-primary text-white' : ($active ? 'bg-white border-primary text-primary ring-4 ring-primary/15' : ($terminalBad ? 'bg-surface-low border-outline-variant text-outline' : 'bg-white border-slate-300 text-slate-400')) }}">
                <span class="material-symbols-outlined text-[16px] {{ $done ? 'font-bold' : '' }}">{{ $done ? 'check' : $step['icon'] }}</span>
            </div>
            <span class="mt-2 text-[10px] sm:text-[11px] font-semibold text-center leading-tight px-0.5 {{ $done || $active ? 'text-on-surface' : 'text-slate-400' }}">{{ $step['label'] }}</span>
        </li>
    @endforeach
</ol>
