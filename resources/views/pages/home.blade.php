<x-layouts.app title="Platform Mikro-Logistik Kampus" plain>
    {{-- Hero --}}
    <section class="relative w-full overflow-hidden bg-white pt-10 pb-16 lg:pt-16 lg:pb-24">
        <div class="absolute -top-24 -left-20 w-96 h-96 rounded-full bg-primary-fixed/30 blur-3xl pointer-events-none"></div>
        <div class="absolute top-1/2 right-0 w-[480px] h-[480px] rounded-full bg-secondary-fixed/20 blur-3xl pointer-events-none"></div>
        <div class="max-w-[1200px] mx-auto px-4 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                <div class="lg:col-span-6 flex flex-col z-10">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full border border-warning-border bg-warning-container text-warning mb-4 w-fit shadow-sm">
                        <span class="material-symbols-outlined text-[18px] animate-pulse">waving_hand</span>
                        <span class="text-xs font-bold">Haiiii kamuu, iyaa kamuu!!</span>
                    </div>
                    <h1 class="font-sans font-extrabold text-4xl sm:text-5xl lg:text-[56px] leading-[1.05] tracking-[-0.03em] text-on-surface mb-4">
                        Magerrr Kannn?<br>
                        <span class="relative inline-block text-primary">Nitip aja.
                            <svg class="absolute left-0 -bottom-2 w-full h-3 overflow-visible" fill="none" preserveAspectRatio="none" viewBox="0 0 160 12" aria-hidden="true"><path d="M2 8C25 2 45 11 75 6C105 1 130 9 158 5" stroke="#FF6B55" stroke-linecap="round" stroke-width="3.5"/></svg>
                        </span>
                    </h1>
                    <p class="text-lg text-on-surface-variant max-w-xl mb-6 leading-relaxed">Mau Nitip makanan bisa, Nitip fotokopi juga bisa kok! Platform micro-errand khusus mahasiswa: penitip bertemu relawan yang searah jalan.</p>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 mb-6">
                        <x-ui.button :href="auth()->check() ? route('explore') : route('register')" icon-right="arrow_forward" class="px-7 shadow-md">Cari titipan</x-ui.button>
                        <x-ui.button :href="auth()->check() ? route('trips.create') : '#dual-posting'" variant="outline" icon="directions_walk" class="px-7">Jadi relawan</x-ui.button>
                    </div>
                    <div class="flex items-center gap-3 pt-1">
                        <div class="flex items-center -space-x-2.5">
                            <span class="w-8 h-8 rounded-full ring-2 ring-white bg-primary-fixed text-on-primary-fixed font-bold text-[11px] flex items-center justify-center">AN</span>
                            <span class="w-8 h-8 rounded-full ring-2 ring-white bg-secondary-fixed text-on-secondary-container font-bold text-[11px] flex items-center justify-center">RP</span>
                            <span class="w-8 h-8 rounded-full ring-2 ring-white bg-surface-high text-primary font-bold text-[11px] flex items-center justify-center">DS</span>
                            <span class="w-8 h-8 rounded-full ring-2 ring-white bg-secondary text-white font-bold text-[11px] flex items-center justify-center">+{{ $stats['students'] }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
                            <span class="text-xs text-on-surface-variant font-medium"><strong class="text-on-surface font-semibold">{{ $stats['students'] }} mahasiswa terverifikasi</strong> &bull; {{ $stats['completed'] }} titipan selesai</span>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-6 relative flex items-center justify-center">
                    <div class="relative w-full aspect-[4/3] rounded-3xl overflow-hidden shadow-xl bg-gradient-to-br from-primary via-primary-hover to-secondary">
                        <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 30%, #5bf674 0, transparent 35%), radial-gradient(circle at 80% 70%, #a4f3ca 0, transparent 40%);"></div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="material-symbols-outlined text-white/20" style="font-size: 220px;">directions_walk</span>
                        </div>
                        <div class="absolute top-4 left-4 right-4 sm:right-auto sm:max-w-xs p-3 rounded-2xl bg-white/95 backdrop-blur-md shadow-lg flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-secondary-fixed flex items-center justify-center text-on-secondary-container shrink-0"><span class="material-symbols-outlined text-[22px]">route</span></div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5 mb-0.5"><span class="w-2 h-2 rounded-full bg-accent animate-pulse"></span><span class="text-[11px] font-bold text-primary uppercase tracking-wider">Rute aktif</span></div>
                                @if ($openTrips->isNotEmpty())
                                    <p class="text-xs text-on-surface truncate font-semibold">Menuju {{ $openTrips->first()->destination }}</p>
                                    <p class="text-[11px] text-on-surface-variant">{{ $openTrips->first()->remainingSlots() }} slot titipan tersedia</p>
                                @else
                                    <p class="text-xs text-on-surface truncate font-semibold">Menuju Kantin Gedung D</p>
                                    <p class="text-[11px] text-on-surface-variant">Buka rute pertamamu hari ini</p>
                                @endif
                            </div>
                        </div>
                        <div class="absolute bottom-4 right-4 left-4 sm:left-auto sm:max-w-xs p-3 rounded-2xl bg-white/95 backdrop-blur-md shadow-lg flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center text-white shrink-0"><span class="material-symbols-outlined text-[20px]">local_cafe</span></div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-1 mb-0.5"><span class="text-[11px] font-bold text-secondary">Rating komunitas</span><span class="flex items-center text-secondary font-bold text-[11px]"><span class="material-symbols-outlined filled text-[14px]">star</span>{{ number_format($stats['avg_rating'], 1) }}</span></div>
                                <p class="text-xs text-on-surface truncate font-semibold">{{ rupiah($stats['fees_paid']) }} uang saku relawan</p>
                                <p class="text-[11px] text-on-surface-variant">tanpa potongan komisi pihak ketiga</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Dual posting --}}
    <section id="dual-posting" class="w-full py-16 lg:py-20 bg-surface-low">
        <div class="max-w-[1200px] mx-auto px-4 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <span class="eyebrow block mb-2">Cara kerja</span>
                <h2 class="font-sans font-bold text-2xl lg:text-[32px] tracking-tight text-on-surface">Sistem Dua Arah: Penitip Bertemu Jastiper</h2>
                <p class="text-on-surface-variant mt-2">Lagi duduk belajar di perpus atau lagi jalan ke kelas berikutnya, Nitip mencocokkan titipan di sepanjang rute kampus kamu.</p>
            </div>

            <div class="relative grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 items-stretch">
                <div class="bg-white rounded-3xl p-6 shadow-card hover:shadow-float transition-shadow flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <x-ui.badge variant="secondary" icon="shopping_bag">Jalur Penitip</x-ui.badge>
                            <span class="text-xs text-on-surface-variant">REQUEST</span>
                        </div>
                        <h3 class="font-sans font-bold text-xl text-on-surface mb-1">Posting Kebutuhanmu</h3>
                        <p class="text-sm text-on-surface-variant mb-4">Lagi sibuk belajar buat UTS? Minta tolong titipin makanan kantin, kopi luar kampus, atau print tugas. Relawan yang searah akan meng-<em>claim</em> permintaanmu.</p>
                        <div class="p-4 rounded-2xl bg-surface-low mb-4 space-y-3">
                            @php $sample = $openRequests->first(); @endphp
                            <div class="flex items-start justify-between gap-3">
                                <div><h4 class="font-semibold text-on-surface">{{ $sample?->title ?? 'Kopi Kenangan & Roti Bakar' }}</h4><p class="text-sm text-on-surface-variant">Ambil di {{ $sample?->pickup_location ?? 'Kantin Gedung C' }}</p></div>
                                <div class="px-2.5 py-1 rounded-lg bg-white text-primary font-bold text-xs shadow-sm shrink-0">Jasa {{ rupiah($sample?->service_fee ?? 3500) }}</div>
                            </div>
                            <div class="flex items-center gap-2 pt-1 text-xs font-medium text-on-surface">
                                <span class="w-2 h-2 rounded-full bg-secondary"></span><span class="truncate">{{ $sample?->pickup_location ?? 'Kantin C' }}</span>
                                <span class="h-0.5 flex-1 bg-surface-highest"></span>
                                <span class="material-symbols-outlined text-[16px] text-primary">pin_drop</span><span class="truncate">{{ $sample?->dropoff_location ?? 'Perpus Lt. 2' }}</span>
                            </div>
                        </div>
                    </div>
                    <x-ui.button :href="auth()->check() ? route('requests.create') : route('register')" icon-right="check_circle" full>Posting kebutuhan sekarang</x-ui.button>
                </div>

                <div class="hidden lg:flex absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 z-20 flex-col items-center pointer-events-none">
                    <div class="w-14 h-14 rounded-full bg-white shadow-xl flex items-center justify-center text-primary ring-4 ring-secondary-fixed/50"><span class="material-symbols-outlined text-[28px] animate-spin" style="animation-duration: 9s;">sync_alt</span></div>
                    <span class="mt-2 px-3 py-1 rounded-full bg-on-surface text-white text-[11px] font-bold shadow-md">Cocok Instan</span>
                </div>

                <div class="bg-white rounded-3xl p-6 shadow-card hover:shadow-float transition-shadow flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <x-ui.badge variant="primary" icon="directions_walk">Jalur Jastiper</x-ui.badge>
                            <span class="text-xs text-on-surface-variant">OFFER</span>
                        </div>
                        <h3 class="font-sans font-bold text-xl text-on-surface mb-1">Posting Rute Kamu</h3>
                        <p class="text-sm text-on-surface-variant mb-4">Udah mau jalan ke kantin atau keluar gerbang? Posting rute dan kuota maksimal. Dapetin uang saku tambahan sambil bawa titipan teman.</p>
                        <div class="p-4 rounded-2xl bg-surface-low mb-4 space-y-3">
                            @php $sampleTrip = $openTrips->first(); @endphp
                            <div class="flex items-start justify-between gap-3">
                                <div><h4 class="font-semibold text-on-surface">Menuju {{ $sampleTrip?->destination ?? 'Kantin Vokasi & Kober' }}</h4><p class="text-sm text-on-surface-variant">Berangkat {{ $sampleTrip?->departure_at->diffForHumans() ?? '10 menit lagi' }}</p></div>
                                <div class="px-2.5 py-1 rounded-lg bg-white text-secondary font-bold text-xs shadow-sm shrink-0 flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">backpack</span>{{ $sampleTrip?->remainingSlots() ?? 5 }} slot</div>
                            </div>
                            <div class="text-xs font-medium text-on-surface bg-white p-2 rounded-xl flex items-center gap-2"><span class="text-primary font-bold">Terima:</span><span class="truncate">{{ $sampleTrip?->category->name ?? 'Makanan Kantin Dalam' }}</span></div>
                        </div>
                    </div>
                    <x-ui.button :href="auth()->check() ? route('trips.create') : route('register')" variant="soft" icon-right="add_road" full>Buka rute sekarang</x-ui.button>
                </div>
            </div>
        </div>
    </section>

    {{-- How it works --}}
    <section id="cara-kerja" class="w-full py-16 lg:py-20 bg-white">
        <div class="max-w-[1200px] mx-auto px-4 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <span class="eyebrow block mb-2">Siklus transaksi</span>
                <h2 class="font-sans font-bold text-2xl lg:text-[32px] tracking-tight text-on-surface">Lima tahap, semuanya tercatat</h2>
                <p class="text-on-surface-variant mt-2">Setiap langkah punya bukti: resi transfer, foto struk, PIN serah terima, dan audit trail permanen di sistem.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                @foreach ([
                    ['post_add', 'Post & Match', 'Buat posting Request atau Offer sampai terjadi kecocokan penitip dan relawan.'],
                    ['payments', 'Pay & Verify', 'Penitip transfer langsung ke relawan (GoPay/BCA/QRIS) dan unggah resi. Relawan memverifikasi.'],
                    ['shopping_cart_checkout', 'In Progress', 'Relawan membeli menu kantin atau mencetak berkas yang dititipkan.'],
                    ['directions_walk', 'Delivering', 'Relawan mencatat harga riil dari struk, lalu mengantar ke titik serah terima.'],
                    ['task_alt', 'Completed', 'Serah terima barang, selisih nota diselesaikan, konfirmasi PIN 4 digit.'],
                ] as $i => [$icon, $title, $desc])
                    <div class="p-5 rounded-2xl bg-surface-low flex flex-col hover:bg-surface-container transition-colors">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl bg-white shadow-sm flex items-center justify-center text-primary"><span class="material-symbols-outlined text-[24px]">{{ $icon }}</span></div>
                            <span class="font-sans font-extrabold text-xl text-outline-variant">0{{ $i + 1 }}</span>
                        </div>
                        <h3 class="font-sans font-bold text-on-surface mb-1">{{ $title }}</h3>
                        <p class="text-sm text-on-surface-variant">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Fees --}}
    <section class="w-full py-16 bg-surface-low">
        <div class="max-w-[1200px] mx-auto px-4 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                <div>
                    <span class="eyebrow block mb-2">Skema biaya transparan</span>
                    <h2 class="font-sans font-bold text-2xl lg:text-[32px] tracking-tight text-on-surface">Flat fee, tanpa komisi</h2>
                    <p class="text-on-surface-variant mt-1">Biaya jasa dihitung otomatis per kategori. Harga barang mengikuti struk asli, relawan menerima 100 % biaya jasa.</p>
                </div>
                <x-ui.button :href="route('services')" variant="outline" icon-right="arrow_forward">Detail layanan</x-ui.button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach ($categories as $category)
                    <div class="card p-5 flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <span class="w-11 h-11 rounded-xl bg-primary-fixed/40 text-primary flex items-center justify-center"><span class="material-symbols-outlined">{{ $category->icon }}</span></span>
                            <x-ui.badge variant="neutral">{{ $category->requires_document ? 'Jasa + biaya kertas' : 'Flat per order' }}</x-ui.badge>
                        </div>
                        <h3 class="font-sans font-bold text-on-surface">{{ $category->name }}</h3>
                        <p class="text-sm text-on-surface-variant flex-1">{{ $category->description }}</p>
                        <p class="font-sans font-extrabold text-xl text-primary tabular">{{ $category->feeRange() }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Live feed --}}
    <section id="feed" class="w-full py-16 lg:py-20 bg-white">
        <div class="max-w-[1200px] mx-auto px-4 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                <div>
                    <div class="flex items-center gap-2 mb-2"><span class="w-2.5 h-2.5 rounded-full bg-secondary-fixed-dim animate-ping"></span><span class="eyebrow">Live campus activity</span></div>
                    <h2 class="font-sans font-bold text-2xl lg:text-[32px] tracking-tight text-on-surface">Titipan &amp; rute yang sedang aktif</h2>
                    <p class="text-on-surface-variant mt-1">{{ $stats['open_requests'] }} permintaan terbuka dan {{ $stats['open_trips'] }} rute berjalan saat ini.</p>
                </div>
                <x-ui.button :href="auth()->check() ? route('explore') : route('login')" icon-right="arrow_forward">Lihat semua</x-ui.button>
            </div>

            @if ($openRequests->isEmpty() && $openTrips->isEmpty())
                <x-ui.empty-state icon="explore" title="Belum ada titipan aktif" description="Jadilah yang pertama memposting kebutuhan atau rute di kampusmu hari ini.">
                    <x-ui.button :href="auth()->check() ? route('requests.create') : route('register')">Posting kebutuhan</x-ui.button>
                </x-ui.empty-state>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($openRequests as $order)
                        <x-cards.order-card :order="$order" />
                    @endforeach
                    @foreach ($openTrips as $trip)
                        <x-cards.trip-card :trip="$trip" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Trust banner --}}
    <section class="w-full py-16 bg-white">
        <div class="max-w-[1200px] mx-auto px-4 lg:px-8">
            <div class="relative rounded-3xl bg-primary text-white p-8 lg:p-12 overflow-hidden shadow-xl">
                <div class="absolute -right-20 -bottom-20 w-80 h-80 rounded-full bg-secondary/30 blur-2xl pointer-events-none"></div>
                <div class="absolute -left-10 -top-10 w-60 h-60 rounded-full bg-primary-hover/40 blur-xl pointer-events-none"></div>
                <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    <div class="lg:col-span-8">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-secondary-container text-on-secondary-container text-xs font-bold mb-4"><span class="material-symbols-outlined text-[16px]">domain_verification</span>Ekosistem tertutup kampus</div>
                        <h2 class="font-sans font-bold text-2xl lg:text-[32px] tracking-tight mb-3">Khusus email kampus terverifikasi</h2>
                        <p class="text-primary-fixed-dim max-w-2xl mb-6">Tidak ada orang asing atau kurir eksternal. Nitip berjalan di atas kredensial akademik (email kampus + NIM sebagai <em>social collateral</em>), kode kehormatan bersama, dan nol komisi.</p>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            @foreach ([['receipt_long', 'Bukti di setiap tahap', 'Resi transfer penitip dan foto struk kasir relawan tersimpan permanen.'], ['badge', 'Verifikasi NIM', 'Rekam jejak akademik menjadi jaminan kepatuhan moral.'], ['balance', 'Resolusi sengketa', 'Audit trail menjadi rekaman legal bila terjadi sanggahan.']] as [$icon, $t, $d])
                                <div class="flex items-start gap-2">
                                    <span class="material-symbols-outlined text-secondary-fixed text-[20px] shrink-0 mt-0.5">{{ $icon }}</span>
                                    <div><h4 class="text-sm font-bold">{{ $t }}</h4><p class="text-xs text-primary-fixed-dim/90">{{ $d }}</p></div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="lg:col-span-4 flex flex-col items-center lg:items-end">
                        <div class="w-full max-w-sm bg-white/10 backdrop-blur-md p-6 rounded-2xl flex flex-col items-center text-center">
                            <div class="w-12 h-12 rounded-full bg-secondary-fixed text-on-secondary-container flex items-center justify-center mb-3 shadow-md"><span class="material-symbols-outlined text-[24px]">school</span></div>
                            <h3 class="font-sans font-bold mb-1">Gabung Hub Kampusmu</h3>
                            <p class="text-sm text-primary-fixed-dim mb-4">{{ implode(', ', array_keys($campuses)) }} sudah terdaftar.</p>
                            <x-ui.button :href="auth()->check() ? route('dashboard') : route('register')" variant="accent" full>{{ auth()->check() ? 'Ke dashboard' : 'Daftar dengan email kampus' }}</x-ui.button>
                            <span class="text-xs text-primary-fixed-dim/80 mt-2">Kurang dari 60 detik</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @auth
        <x-partials.footer />
    @endauth
</x-layouts.app>
