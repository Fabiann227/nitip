<x-layouts.guest title="Akun Terverifikasi" width="max-w-[440px]">
    <x-auth.stepper :step="3" />

    <section class="w-full bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm flex flex-col gap-5 animate-fade-in-up">
        <div class="flex flex-col items-center text-center gap-3 pt-1">
            <div class="relative flex items-center justify-center">
                <div class="absolute -inset-3 bg-emerald-400/20 rounded-full blur-md animate-pulse pointer-events-none"></div>
                <div class="absolute -inset-1.5 rounded-full bg-emerald-600/20 animate-ping pointer-events-none"></div>
                <div class="relative w-16 h-16 rounded-full bg-gradient-to-tr from-emerald-100 via-[#E6F4EA] to-teal-50 border-2 border-emerald-200/80 flex items-center justify-center shadow-md shadow-emerald-600/10">
                    <div class="w-11 h-11 rounded-full bg-[#006041] text-white flex items-center justify-center shadow-sm ring-4 ring-emerald-100/80"><span class="material-symbols-outlined text-2xl font-bold">check</span></div>
                </div>
            </div>
            <div class="flex flex-col items-center gap-1.5 w-full">
                <h1 class="text-slate-900 font-sans font-semibold text-[22px] tracking-tight leading-tight">Horeee! Akunmu Terverifikasi 🎉</h1>
                <p class="text-slate-600 text-[13px] leading-relaxed max-w-xs">Selamat datang di Nitip! Akunmu sudah aktif dan siap digunakan.</p>
            </div>
        </div>

        <div class="w-full bg-gradient-to-b from-emerald-50/70 to-slate-50 border border-emerald-200/70 rounded-2xl p-4 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between border-b border-emerald-100 pb-2.5 mb-3">
                <div class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[#006041] text-base">badge</span><span class="text-[11px] font-bold text-slate-800 font-sans uppercase tracking-wider">Pass Nitip Kampus</span></div>
                <span class="inline-flex items-center gap-1 bg-[#006041] text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm"><span class="material-symbols-outlined text-[11px]">verified</span>Verified</span>
            </div>
            <div class="flex items-center gap-3 mb-3.5">
                <x-ui.avatar :user="$user" size="md" tone="bg-emerald-100 text-[#006041] border-2 border-white shadow-sm" />
                <div class="flex flex-col text-left overflow-hidden">
                    <span class="font-sans font-bold text-sm text-slate-900 truncate">{{ $user->name }}</span>
                    <span class="text-xs text-slate-600 truncate">{{ $user->campusName() }}</span>
                    <div class="inline-flex items-center gap-1 text-[11px] text-[#006041] font-semibold mt-0.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>Siap Nitip &amp; Dianterin</div>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-1.5 pt-2 border-t border-emerald-100/80 text-center">
                <div><span class="block text-[10px] text-slate-500">NIM</span><span class="block text-xs font-bold text-slate-800 truncate">{{ $user->nim ?? '-' }}</span></div>
                <div><span class="block text-[10px] text-slate-500">Kampus</span><span class="block text-xs font-bold text-slate-800 truncate">{{ $user->campus ?? '-' }}</span></div>
                <div><span class="block text-[10px] text-slate-500">Bergabung</span><span class="block text-xs font-bold text-slate-800 truncate">{{ $user->created_at->translatedFormat('M Y') }}</span></div>
            </div>
        </div>

        <div class="flex flex-col gap-2.5 w-full pt-1">
            <x-ui.button :href="route('dashboard')" icon-right="arrow_forward" full class="shadow-md hover:shadow-lg">Mulai Jelajahi Nitip</x-ui.button>
            <a href="{{ route('requests.create') }}" class="w-full text-center text-xs text-slate-600 hover:text-[#006041] hover:underline font-medium py-1">Atau mulai Nitip sekarang &rarr;</a>
        </div>
    </section>
</x-layouts.guest>
