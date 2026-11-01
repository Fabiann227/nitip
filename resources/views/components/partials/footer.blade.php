<footer class="w-full bg-white border-t border-slate-200/80 mt-auto">
    <div class="max-w-[1280px] mx-auto px-4 lg:px-8 py-8 sm:py-10">
        <!-- Main Horizontal Row -->
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            <!-- Brand Info (Logo + Tagline) -->
            <div class="flex flex-col sm:flex-row items-center gap-3 sm:gap-4 text-center sm:text-left">
                <x-logo class="h-8 shrink-0" />
                <span class="hidden sm:inline-block h-4 w-px bg-slate-200"></span>
                <p class="text-xs sm:text-sm text-on-surface-variant font-medium">
                    Platform saling titip khusus mahasiswa di lingkungan kampus.
                </p>
            </div>

            <!-- Horizontal Navigation Links -->
            <nav class="flex flex-wrap items-center justify-center gap-6 text-sm font-semibold text-on-surface-variant">
                <a href="{{ route('home') }}#cerita-nitip" class="relative py-1 hover:text-primary transition-colors group">
                    <span>Cerita Nitip</span>
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-primary rounded-full transition-all duration-300 ease-out group-hover:w-full"></span>
                </a>
                <a href="{{ route('home') }}#cara-kerja" class="relative py-1 hover:text-primary transition-colors group">
                    <span>Cara Kerja</span>
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-primary rounded-full transition-all duration-300 ease-out group-hover:w-full"></span>
                </a>
                <a href="{{ route('home') }}#layanan" class="relative py-1 hover:text-primary transition-colors group">
                    <span>Layanan &amp; Tarif</span>
                    <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-primary rounded-full transition-all duration-300 ease-out group-hover:w-full"></span>
                </a>
            </nav>
        </div>

        <!-- Divider & Bottom Info -->
        <div class="mt-6 pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
            <span>&copy; {{ date('Y') }} Nitip &bull; Tim Pengembang Nitip (UPH)</span>
            <div class="inline-flex items-center gap-2 text-slate-500 font-medium">
                <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>
                <span>Khusus email kampus terverifikasi</span>
            </div>
        </div>
    </div>
</footer>
