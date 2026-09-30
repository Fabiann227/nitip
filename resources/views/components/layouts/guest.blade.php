@props(['title' => null, 'width' => 'max-w-md'])
<x-layouts.base :title="$title" body-class="bg-[#f8fafc]">
    <div class="w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10 flex flex-col min-h-screen items-center">
        <header class="w-full flex items-center justify-center py-2 mb-6">
            <a href="{{ route('home') }}" class="inline-flex items-center" aria-label="Beranda Nitip">
                <x-logo class="h-11 sm:h-12" />
            </a>
        </header>

        <main class="w-full {{ $width }} mx-auto flex flex-col items-center my-auto">
            {{ $slot }}
        </main>

        <footer class="w-full text-center py-6 mt-6">
            <p class="text-xs text-slate-400 font-medium">&copy; {{ date('Y') }} Nitip &bull; Platform mikro-logistik kampus</p>
        </footer>
    </div>
</x-layouts.base>
