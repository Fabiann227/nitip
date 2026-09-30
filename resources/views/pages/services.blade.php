<x-layouts.app title="Layanan & Tarif">
    <x-ui.page-header eyebrow="Informasi layanan" title="Layanan & skema biaya Nitip" subtitle="Dua layanan utama kampus dengan tarif flat yang transparan. Biaya barang dibayar sesuai struk riil, tanpa markup dan tanpa komisi platform." />

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-10">
        @foreach ($categories as $category)
            <x-ui.card class="p-5 flex flex-col gap-3">
                <span class="w-12 h-12 rounded-xl bg-primary-fixed/40 text-primary flex items-center justify-center"><span class="material-symbols-outlined text-[26px]">{{ $category->icon }}</span></span>
                <h2 class="font-sans font-bold text-lg text-on-surface">{{ $category->name }}</h2>
                <p class="text-sm text-on-surface-variant flex-1">{{ $category->description }}</p>
                <dl class="text-sm space-y-1.5 pt-2 border-t border-border">
                    <div class="flex justify-between"><dt class="text-muted">Struktur biaya</dt><dd class="font-semibold text-right">{{ $category->requires_document ? 'Flat jasa + biaya riil kertas' : 'Flat per order' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-muted">Tarif rekomendasi</dt><dd class="font-bold text-primary tabular">{{ $category->feeRange() }}</dd></div>
                    <div class="flex justify-between"><dt class="text-muted">Tarif default</dt><dd class="font-semibold tabular">{{ rupiah($category->fee_default) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-muted">Berkas wajib</dt><dd class="font-semibold">{{ $category->requires_document ? 'Ya (PDF/DOC/PPT)' : 'Tidak' }}</dd></div>
                </dl>
                @auth
                    <x-ui.button :href="route('requests.create', ['category' => $category->id])" variant="soft" size="sm" icon-right="arrow_forward" full>Titip {{ strtolower($category->name) }}</x-ui.button>
                @endauth
            </x-ui.card>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-ui.card title="Bagaimana pembayaran & harga bekerja?" icon="payments">
            <ol class="space-y-3 text-sm text-on-surface-variant list-decimal list-inside">
                <li><strong class="text-on-surface">Perkiraan di awal.</strong> Penitip mengisi perkiraan harga barang (boleh kira-kira). Penitip mentransfer biaya jasa + perkiraan itu langsung ke e-wallet/rekening relawan.</li>
                <li><strong class="text-on-surface">Bukti transfer wajib.</strong> Penitip mengunggah resi sebelum pesanan diproses; relawan memverifikasi mutasi masuk.</li>
                <li><strong class="text-on-surface">Harga riil dari struk.</strong> Setelah membeli, relawan memasukkan total sesuai struk kasir dan memfotonya. Sistem langsung menghitung selisih: kurang berarti penitip menambah, lebih berarti relawan mengembalikan.</li>
                <li><strong class="text-on-surface">Selesaikan saat serah terima.</strong> Selisih dibayar tunai atau transfer di tempat, lalu penitip menyebutkan PIN 4 digit untuk menutup transaksi.</li>
            </ol>
        </x-ui.card>
        <x-ui.card title="Kampus yang terdaftar" icon="school">
            <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                @foreach ($campuses as $code => $campus)
                    <li class="flex items-center gap-2 p-2 rounded-lg bg-surface-low"><span class="w-1.5 h-1.5 rounded-full bg-secondary"></span><span class="font-semibold text-on-surface">{{ $code }}</span><span class="text-on-surface-variant truncate">{{ $campus['name'] }}</span></li>
                @endforeach
            </ul>
            <p class="text-xs text-muted mt-3">Kampusmu belum ada? Daftar dengan email berakhiran .ac.id dan minta admin menambahkan hub kampus baru.</p>
        </x-ui.card>
    </div>
</x-layouts.app>
