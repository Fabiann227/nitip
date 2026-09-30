@props(['for' => null, 'required' => false, 'optional' => false, 'icon' => null, 'hint' => null])
<label @if($for) for="{{ $for }}" @endif {{ $attributes->merge(['class' => 'label flex items-center justify-between gap-2']) }}>
    <span class="inline-flex items-center gap-1.5">
        @if ($icon)<span class="material-symbols-outlined text-slate-400 text-[18px]">{{ $icon }}</span>@endif
        {{ $slot }}
        @if ($required)<span class="text-error" aria-hidden="true">*</span>@endif
    </span>
    @if ($optional)<span class="text-xs font-normal text-muted">Opsional</span>@endif
    @if ($hint)<span class="text-xs font-normal text-emerald-700">{{ $hint }}</span>@endif
</label>
