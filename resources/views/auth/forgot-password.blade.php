<x-layouts.guest title="Lupa Kata Sandi" width="max-w-md">
    <section class="w-full bg-white border border-slate-200/80 rounded-2xl shadow-sm p-6 sm:p-8 flex flex-col gap-5 animate-fade-in-up">
        <div class="text-center">
            <div class="w-12 h-12 mx-auto rounded-xl bg-emerald-50 border border-emerald-100 text-[#006041] flex items-center justify-center mb-3"><span class="material-symbols-outlined text-2xl">lock_reset</span></div>
            <h1 class="text-xl font-bold font-sans text-slate-900 mb-1">Lupa kata sandi?</h1>
            <p class="text-sm text-slate-600 leading-relaxed">Masukkan email kampusmu. Kami kirimkan tautan untuk mengatur ulang kata sandi.</p>
        </div>

        @if (session('status'))
            <x-ui.alert type="success">{{ session('status') }}</x-ui.alert>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-4" novalidate>
            @csrf
            <x-ui.input name="email" type="email" label="Email Kampus" icon="mail" placeholder="nama@student.kampus.ac.id" :value="old('email')" required autofocus autocomplete="email" />
            <x-ui.button type="submit" icon-right="send" full>Kirim Tautan Reset</x-ui.button>
        </form>

        <p class="text-center text-sm text-slate-600"><a href="{{ route('login') }}" class="font-semibold text-[#006041] hover:underline inline-flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">arrow_back</span>Kembali ke halaman masuk</a></p>
    </section>
</x-layouts.guest>
