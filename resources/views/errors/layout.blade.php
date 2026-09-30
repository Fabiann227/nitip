@props(['code', 'title', 'message', 'icon' => 'error'])
<x-layouts.guest :title="$code.' - '.$title" width="max-w-md">
    <section class="w-full bg-white border border-slate-200/80 rounded-2xl shadow-sm p-8 text-center flex flex-col gap-4">
        <div class="w-14 h-14 mx-auto rounded-2xl bg-surface-low text-primary flex items-center justify-center"><span class="material-symbols-outlined text-3xl">{{ $icon }}</span></div>
        <span class="font-sans font-extrabold text-5xl text-outline-variant tracking-tight">{{ $code }}</span>
        <h1 class="text-xl font-bold font-sans text-slate-900">{{ $title }}</h1>
        <p class="text-sm text-slate-600 leading-relaxed">{{ $message }}</p>
        <div class="flex flex-col sm:flex-row gap-2 justify-center pt-2">
            <x-ui.button :href="url()->previous() !== url()->current() ? url()->previous() : route('home')" variant="outline" icon="arrow_back">Kembali</x-ui.button>
            <x-ui.button :href="auth()->check() ? (auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard')) : route('home')" icon="home">Ke beranda</x-ui.button>
        </div>
    </section>
</x-layouts.guest>
