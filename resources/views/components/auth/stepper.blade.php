@props(['step' => 1])
@php $labels = ['Registrasi', 'Verifikasi', 'Terverifikasi']; @endphp
<nav class="w-full max-w-[352px] mx-auto bg-white border border-slate-200/80 rounded-2xl px-6 py-4 mb-6 shadow-sm" aria-label="Langkah pendaftaran">
    <div class="relative w-full">
        <div class="absolute top-[9px] left-0 right-0 h-[2px] w-full flex pointer-events-none">
            <div class="w-1/2 h-full {{ $step >= 2 ? 'bg-[#006041]' : 'bg-slate-200' }}"></div>
            <div class="w-1/2 h-full {{ $step >= 3 ? 'bg-[#006041]' : 'bg-slate-200' }}"></div>
        </div>
        <ol class="relative flex justify-between items-start w-full">
            @foreach ($labels as $index => $label)
                @php $n = $index + 1; @endphp
                <li class="relative flex flex-col items-center">
                    @if ($n < $step || ($step === 3 && $n === 3))
                        <div class="w-5 h-5 rounded-full bg-[#006041] border-2 border-[#006041] text-white flex items-center justify-center shadow-sm z-10 {{ $n === 3 ? 'ring-2 ring-emerald-600/20' : '' }}"><span class="material-symbols-outlined text-[12px] font-bold">check</span></div>
                        <span class="absolute top-6 text-[11px] font-bold {{ $n === 3 ? 'text-[#006041]' : 'text-slate-900' }} font-sans whitespace-nowrap">{{ $label }}</span>
                    @elseif ($n === $step)
                        <div class="w-5 h-5 rounded-full border-2 border-[#006041] bg-white text-[#006041] flex items-center justify-center font-bold text-[11px] shadow-sm z-10">{{ $n }}</div>
                        <span class="absolute top-6 text-[11px] font-bold text-slate-900 font-sans whitespace-nowrap">{{ $label }}</span>
                    @else
                        <div class="w-5 h-5 rounded-full border border-slate-300 bg-slate-100 text-slate-400 flex items-center justify-center font-medium text-[11px] z-10">{{ $n }}</div>
                        <span class="absolute top-6 text-[11px] font-medium text-slate-400 font-sans whitespace-nowrap">{{ $label }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
        <div class="h-4"></div>
    </div>
</nav>
