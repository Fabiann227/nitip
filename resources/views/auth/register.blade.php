<x-layouts.guest title="Daftar" width="max-w-[480px]">
    <x-auth.stepper :step="1" />

    <section class="w-full bg-white border border-slate-200/80 rounded-2xl p-6 sm:p-7 shadow-sm flex flex-col animate-fade-in-up">
        <div class="text-center flex flex-col items-center mb-6">
            <h1 class="text-slate-900 font-sans font-bold text-[22px] tracking-tight leading-tight">Daftar Akun Nitip</h1>
            <p class="text-slate-500 text-[13px] mt-1">Khusus mahasiswa terverifikasi (email kampus)</p>
        </div>

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col" novalidate>
            @csrf
            <span class="text-[12px] font-semibold tracking-wider text-slate-500 uppercase mb-3">Data diri</span>
            <div class="flex flex-col gap-4">
                <x-ui.input name="name" label="Nama Lengkap" label-icon="person" label-hint="(Sesuai KTM)" placeholder="Nama lengkap sesuai KTM" required autocomplete="name" autofocus />
                <x-ui.input name="nim" label="NIM / NPM" label-icon="badge" placeholder="Nomor Induk Mahasiswa" required maxlength="30" />
                <x-ui.select name="campus" label="Pilih Universitas" label-icon="school" icon="search" required>
                    <option value="" disabled @selected(! old('campus'))>Cari &amp; pilih universitas...</option>
                    @foreach ($campuses as $code => $campus)
                        <option value="{{ $code }}" @selected(old('campus') === $code)>{{ $campus['name'] }} ({{ $code }})</option>
                    @endforeach
                </x-ui.select>
            </div>

            <span class="text-[12px] font-semibold tracking-wider text-slate-500 uppercase mb-3 mt-6">Kontak &amp; keamanan</span>
            <div class="flex flex-col gap-4">
                <x-ui.input name="email" type="email" label="Email Kampus (.ac.id)" label-icon="mail" placeholder="nama@student.kampus.ac.id" required autocomplete="email" />
                <x-ui.input name="whatsapp_number" type="tel" label="Nomor WhatsApp" label-icon="chat" placeholder="08xxxxxxxxxx" required autocomplete="tel" hint="Dipakai untuk koordinasi cepat via wa.me saat pesanan berjalan." />
                <x-ui.password name="password" label="Kata Sandi" placeholder="Minimal 8 karakter, huruf & angka" autocomplete="new-password" />
                <x-ui.password name="password_confirmation" label="Konfirmasi Kata Sandi" placeholder="Ulangi kata sandi" icon="lock_reset" autocomplete="new-password" />
            </div>

            <div class="pt-4">
                <x-ui.checkbox name="terms" :checked="(bool) old('terms')">
                    Saya menyetujui <a href="#" class="text-emerald-800 font-semibold hover:underline">Ketentuan Layanan</a> &amp; <a href="#" class="text-emerald-800 font-semibold hover:underline">Kebijakan Privasi</a>, serta bersedia data NIM saya dijadikan jaminan sosial (social collateral).
                </x-ui.checkbox>
                <x-ui.field-error name="terms" />
            </div>

            <x-ui.button type="submit" icon-right="arrow_forward" full class="mt-4">Daftar &amp; Lanjut Verifikasi</x-ui.button>

            <p class="flex items-center justify-center gap-1 text-[13px] text-slate-500 pt-4">Sudah punya akun? <a href="{{ route('login') }}" class="text-emerald-800 font-semibold hover:underline">Masuk di sini</a></p>
        </form>
    </section>
</x-layouts.guest>
