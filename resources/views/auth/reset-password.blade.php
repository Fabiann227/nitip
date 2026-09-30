<x-layouts.guest title="Atur Ulang Kata Sandi" width="max-w-md">
    <section class="w-full bg-white border border-slate-200/80 rounded-2xl shadow-sm p-6 sm:p-8 flex flex-col gap-5 animate-fade-in-up">
        <div class="text-center">
            <h1 class="text-xl font-bold font-sans text-slate-900 mb-1">Atur ulang kata sandi</h1>
            <p class="text-sm text-slate-600">Buat kata sandi baru untuk akun Nitip kamu.</p>
        </div>

        <form method="POST" action="{{ route('password.store') }}" class="flex flex-col gap-4" novalidate>
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <x-ui.input name="email" type="email" label="Email Kampus" icon="mail" :value="old('email', $email)" required autocomplete="email" />
            <x-ui.password name="password" label="Kata Sandi Baru" autocomplete="new-password" />
            <x-ui.password name="password_confirmation" label="Konfirmasi Kata Sandi" icon="lock_reset" placeholder="Ulangi kata sandi" autocomplete="new-password" />
            <x-ui.button type="submit" icon-right="arrow_forward" full>Simpan Kata Sandi</x-ui.button>
        </form>
    </section>
</x-layouts.guest>
