<footer class="w-full bg-surface-low border-t border-surface-highest/70 mt-auto">
    <div class="max-w-[1200px] mx-auto px-4 lg:px-8 pt-12 pb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-8 mb-10">
            <div class="lg:col-span-5">
                <x-logo class="h-9 mb-3" />
                <p class="text-sm text-on-surface-variant max-w-sm">Platform mikro-logistik kampus yang menghubungkan mahasiswa untuk saling titip makanan, minuman, dan cetak tugas di sepanjang rute harian mereka.</p>
                <div class="mt-4 p-3 rounded-xl bg-white border border-surface-high inline-flex items-center gap-3">
                    <span class="material-symbols-outlined text-secondary">verified_user</span>
                    <span class="text-xs text-on-surface-variant">Khusus email kampus terverifikasi &bull; Tanpa komisi pihak ketiga</span>
                </div>
            </div>
            <div class="lg:col-span-2">
                <h3 class="font-sans font-semibold text-sm text-on-surface mb-3">Nitip</h3>
                <ul class="space-y-2 text-sm text-on-surface-variant">
                    <li><a href="{{ route('home') }}#cara-kerja" class="hover:text-primary">Cara kerja</a></li>
                    <li><a href="{{ route('services') }}" class="hover:text-primary">Layanan &amp; tarif</a></li>
                    <li><a href="{{ route('home') }}#feed" class="hover:text-primary">Titipan aktif</a></li>
                </ul>
            </div>
            <div class="lg:col-span-2">
                <h3 class="font-sans font-semibold text-sm text-on-surface mb-3">Akun</h3>
                <ul class="space-y-2 text-sm text-on-surface-variant">
                    <li><a href="{{ route('login') }}" class="hover:text-primary">Masuk</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-primary">Daftar</a></li>
                    <li><a href="{{ route('password.request') }}" class="hover:text-primary">Lupa kata sandi</a></li>
                </ul>
            </div>
            <div class="lg:col-span-3">
                <h3 class="font-sans font-semibold text-sm text-on-surface mb-3">Kepercayaan &amp; keamanan</h3>
                <ul class="space-y-2 text-sm text-on-surface-variant">
                    <li class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>Verifikasi email kampus &amp; NIM</li>
                    <li class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>Bukti transfer &amp; foto struk</li>
                    <li class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>PIN serah terima 4 digit</li>
                    <li class="flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>Audit trail &amp; resolusi sengketa</li>
                </ul>
            </div>
        </div>
        <div class="pt-6 border-t border-surface-highest/70 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-on-surface-variant">
            <span>&copy; {{ date('Y') }} Nitip &bull; Tim Pengembang Nitip (UPH)</span>
            <span>Dibangun dengan Laravel &amp; MySQL</span>
        </div>
    </div>
</footer>
