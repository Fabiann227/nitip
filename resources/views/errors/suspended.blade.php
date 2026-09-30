<x-layouts.guest title="Akun Ditangguhkan" width="max-w-md">
    <section class="w-full bg-white border border-slate-200/80 rounded-2xl shadow-sm p-8 text-center flex flex-col gap-4">
        <div class="w-14 h-14 mx-auto rounded-2xl bg-error-container text-on-error-container flex items-center justify-center"><span class="material-symbols-outlined text-3xl">block</span></div>
        <h1 class="text-xl font-bold font-sans text-slate-900">Akunmu sedang ditangguhkan</h1>
        <p class="text-sm text-slate-600 leading-relaxed">Admin Nitip menangguhkan akun ini sehingga kamu belum bisa membuat atau mengambil titipan.</p>
        @if ($user->suspension_reason)
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-left text-sm">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Alasan</span>
                <p class="text-slate-800 mt-1">{{ $user->suspension_reason }}</p>
                @if ($user->suspended_at)<p class="text-xs text-slate-500 mt-1">Sejak {{ $user->suspended_at->translatedFormat('d M Y H:i') }}</p>@endif
            </div>
        @endif
        <p class="text-xs text-slate-500">Merasa ini keliru? Hubungi admin lewat email kampus untuk banding.</p>
        <form method="POST" action="{{ route('logout') }}">@csrf<x-ui.button type="submit" variant="outline" icon="logout" full>Keluar</x-ui.button></form>
    </section>
</x-layouts.guest>
