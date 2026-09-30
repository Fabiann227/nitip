<x-layouts.guest title="Verifikasi Email" width="max-w-[440px]">
    <x-auth.stepper :step="2" />

    <section class="w-full bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm flex flex-col gap-5 animate-fade-in-up">
        <div class="flex flex-col items-center text-center gap-2.5">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 text-[#006041] flex items-center justify-center shadow-sm"><span class="material-symbols-outlined text-2xl">mark_email_unread</span></div>
            <div class="flex flex-col items-center gap-1.5 w-full">
                <h1 class="text-slate-900 font-sans font-semibold text-xl tracking-tight">Verifikasi Email Kampus</h1>
                <p class="text-slate-600 text-xs leading-relaxed max-w-xs">Kami telah mengirimkan 6-digit kode OTP ke email kampus Anda:</p>
                <div class="inline-flex items-center gap-2 bg-slate-50 border border-slate-200/80 px-3 py-1.5 rounded-lg mt-1 max-w-full">
                    <span class="material-symbols-outlined text-emerald-700 text-base">mail</span>
                    <span class="font-semibold text-slate-800 text-xs truncate max-w-[220px]">{{ $user->email }}</span>
                </div>
            </div>
        </div>

        @if ($bypass)
            <x-ui.alert type="warning" icon="bug_report" title="Mode development (verifikasi dummy)">
                Email belum dikonfigurasi, jadi <strong>6 digit angka apa saja</strong> akan diterima. Matikan lewat <code>NITIP_OTP_BYPASS=false</code> sebelum produksi.
            </x-ui.alert>
        @elseif ($devCode)
            <x-ui.alert type="warning" icon="bug_report" title="Mode development">
                Kode OTP untuk pengujian: <strong class="font-mono text-base tracking-widest">{{ $devCode }}</strong>. Matikan lewat <code>NITIP_DEV_SHOW_OTP=false</code>.
            </x-ui.alert>
        @endif

        <form method="POST" action="{{ route('verification.verify') }}" class="flex flex-col gap-5 mt-1">
            @csrf
            <div class="flex flex-col items-center gap-2.5">
                <label class="text-xs font-semibold text-slate-700">Masukkan 6 Digit Kode OTP</label>
                <x-ui.code-input :length="6" name="digits" :error="$errors->first('code')" />
            </div>

            <x-ui.button type="submit" icon-right="arrow_forward" full class="shadow-md hover:shadow-lg">Verifikasi &amp; Lanjutkan</x-ui.button>
        </form>

        <div x-data="countdown({{ (int) $cooldown }})" class="flex flex-col items-center gap-1 text-center">
            <div class="flex items-center gap-1.5 text-xs text-slate-500">
                <span>Tidak menerima kode?</span>
                <span x-show="remaining > 0" class="font-medium text-slate-500">Kirim ulang dalam <strong class="text-slate-700 font-semibold tabular" x-text="label"></strong></span>
            </div>
            <form method="POST" action="{{ route('verification.send') }}" x-show="remaining <= 0" x-cloak>
                @csrf
                <button type="submit" class="text-xs text-[#006041] hover:underline font-semibold mt-0.5">Kirim Ulang Kode</button>
            </form>
        </div>

        <div class="w-full flex items-start gap-2.5 p-3.5 bg-slate-50 border border-slate-200/80 rounded-xl text-left">
            <span class="material-symbols-outlined text-slate-400 text-lg shrink-0 mt-0.5">info</span>
            <p class="text-xs text-slate-600 leading-relaxed">Cek juga folder <strong>Spam</strong> atau <strong>Promosi</strong> jika email belum masuk dalam 1&ndash;2 menit. Kode berlaku {{ config('nitip.otp.ttl_minutes') }} menit.</p>
        </div>

        <div class="flex items-center justify-center pt-2 border-t border-slate-100">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium text-xs transition-all shadow-sm"><span class="material-symbols-outlined text-sm">logout</span>Keluar &amp; pakai akun lain</button>
            </form>
        </div>
    </section>
</x-layouts.guest>
