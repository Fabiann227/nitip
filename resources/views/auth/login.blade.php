<x-layouts.guest title="Masuk" width="max-w-xl">
    <section class="w-full bg-white border border-slate-200/80 rounded-2xl shadow-sm p-6 sm:p-8 flex flex-col animate-fade-in-up">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold font-sans text-slate-900 mb-2">Masuk ke Akun Nitip</h1>
            <p class="text-sm text-slate-600 max-w-md mx-auto leading-relaxed">Masuk dengan email kampus terverifikasi untuk mulai titip dan antar pesanan.</p>
        </div>

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5 w-full" novalidate>
            @csrf
            <x-ui.input name="email" type="email" label="Email Kampus (.ac.id)" icon="mail" placeholder="nama.mahasiswa@student.kampus.ac.id" :value="old('email')" required autofocus autocomplete="email" />

            <x-ui.password name="password" label="Kata Sandi" placeholder="Masukkan kata sandi akun">
                <x-slot:labelSlot>
                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-[#006041] hover:underline">Lupa kata sandi?</a>
                </x-slot:labelSlot>
            </x-ui.password>

            <div class="flex items-center justify-between pt-0.5">
                <x-ui.checkbox name="remember" :checked="(bool) old('remember')">Ingat saya di perangkat ini</x-ui.checkbox>
            </div>

            <x-ui.button type="submit" icon-right="arrow_forward" full class="mt-2 shadow-md hover:shadow-lg">Masuk ke Nitip</x-ui.button>

            <p class="text-center text-xs sm:text-sm text-slate-600 pt-2">Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-[#006041] hover:underline">Daftar di sini</a></p>
        </form>
    </section>

    @if (app()->environment('local'))
        <details class="w-full mt-4 text-xs text-slate-500">
            <summary class="cursor-pointer font-medium text-center">Akun demo (development)</summary>
            <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
                @foreach ([['admin@nitip.test', 'Admin'], ['willy@student.uph.edu', 'Mahasiswa UPH (relawan aktif)'], ['nadia@student.uph.edu', 'Mahasiswa UPH (penitip aktif)'], ['belumverif@student.uph.edu', 'Belum verifikasi OTP']] as [$email, $label])
                    <div class="p-2 rounded-lg bg-white border border-slate-200"><span class="font-semibold text-slate-700">{{ $label }}</span><br><code>{{ $email }}</code> / <code>password</code></div>
                @endforeach
            </div>
        </details>
    @endif
</x-layouts.guest>
