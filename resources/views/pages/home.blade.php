<x-layouts.app plain>
    {{-- Hero --}}
    <section class="relative w-full min-h-[calc(100vh-4rem)] flex flex-col justify-between overflow-hidden bg-gradient-to-b from-white via-surface-bright to-white pt-6 pb-6 lg:pt-8 lg:pb-6 border-b border-border/50">
        <!-- Subtle campus grid background texture -->
        <div class="absolute inset-0 bg-[radial-gradient(#cbd5e1_1px,transparent_1px)] [background-size:24px_24px] opacity-40 pointer-events-none"></div>

        <!-- Ambient organic green and mint glow -->
        <div class="absolute -top-24 -left-20 w-96 h-96 rounded-full bg-primary-fixed/35 blur-3xl pointer-events-none"></div>
        <div class="absolute top-1/3 -right-20 w-[480px] h-[480px] rounded-full bg-secondary-fixed/25 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-16 left-1/3 w-72 h-72 rounded-full bg-accent/15 blur-3xl pointer-events-none"></div>

        <!-- Main Hero Content (Vertically Centered) -->
        <div class="max-w-[1280px] mx-auto px-4 lg:px-8 relative z-10 my-auto w-full py-4 lg:py-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-center">
                <!-- Left: Text, Quick Badges & Call to Action -->
                <div class="lg:col-span-5 flex flex-col justify-center z-10">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-amber-200/90 bg-amber-50/90 text-amber-900 mb-5 w-fit shadow-xs transition-transform hover:scale-105 duration-200">
                        <span class="material-symbols-outlined text-[18px] text-amber-600 animate-pulse">waving_hand</span>
                        <span class="text-xs sm:text-[13px] font-bold">Haiiii kamuu, iyaa kamuu!!</span>
                    </div>

                    <h1 class="font-sans font-extrabold text-4xl sm:text-5xl lg:text-[58px] leading-[1.08] tracking-[-0.035em] text-on-surface mb-7">
                        Magerrr Kannn?<br>
                        <span class="relative inline-block text-primary mt-1">
                            Nitip aja.
                            <svg class="absolute left-0 -bottom-2.5 w-full h-3.5 overflow-visible" fill="none" preserveAspectRatio="none" viewBox="0 0 160 12" aria-hidden="true">
                                <path d="M2 8C25 2 45 11 75 6C105 1 130 9 158 5" stroke="#FF6B55" stroke-linecap="round" stroke-width="3.5"/>
                            </svg>
                        </span>
                    </h1>

                    <!-- Campus Services Micro-Pills -->
                    <div class="flex flex-wrap items-center gap-2 mb-7">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-surface-low border border-border/90 text-xs font-semibold text-on-surface shadow-xs">
                            <span class="material-symbols-outlined text-[16px] text-primary">lunch_dining</span>
                            Makanan Kantin
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-surface-low border border-border/90 text-xs font-semibold text-on-surface shadow-xs">
                            <span class="material-symbols-outlined text-[16px] text-secondary">local_cafe</span>
                            Kopi Luar Kampus
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-surface-low border border-border/90 text-xs font-semibold text-on-surface shadow-xs">
                            <span class="material-symbols-outlined text-[16px] text-info">print</span>
                            Print & Fotokopi Tugas
                        </span>
                    </div>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 mb-8">
                        <x-ui.button :href="auth()->check() ? route('explore') : route('register')" icon-right="arrow_forward" class="px-8 h-12 sm:h-13 text-[15px] shadow-primary hover:shadow-lg transition-all group">
                            Cari titipan
                        </x-ui.button>
                        <x-ui.button :href="auth()->check() ? route('trips.create') : '#cara-kerja'" variant="outline" icon="directions_walk" class="px-8 h-12 sm:h-13 text-[15px] bg-white/95 border-slate-200 hover:border-primary/40 shadow-xs">
                            Jadi relawan
                        </x-ui.button>
                    </div>

                    <!-- Social Proof & Verification -->
                    <div class="flex items-center gap-3.5 pt-1">
                        <div class="flex items-center -space-x-2.5">
                            <span class="w-9 h-9 rounded-full ring-2 ring-white bg-primary-fixed text-on-primary-fixed font-bold text-xs flex items-center justify-center shadow-xs">AN</span>
                            <span class="w-9 h-9 rounded-full ring-2 ring-white bg-secondary-fixed text-on-secondary-container font-bold text-xs flex items-center justify-center shadow-xs">RP</span>
                            <span class="w-9 h-9 rounded-full ring-2 ring-white bg-surface-high text-primary font-bold text-xs flex items-center justify-center shadow-xs">DS</span>
                            <span class="w-9 h-9 rounded-full ring-2 ring-white bg-secondary text-white font-bold text-xs flex items-center justify-center shadow-xs">+{{ $stats['students'] }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
                            <span class="text-xs sm:text-[13px] text-on-surface-variant font-medium">
                                <strong class="text-on-surface font-semibold">{{ $stats['students'] }} mahasiswa terverifikasi</strong> &bull; {{ $stats['completed'] }} titipan selesai
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Right: Visual Campus Illustration Showcase -->
                <div class="lg:col-span-7 relative flex items-center justify-center pt-2 lg:pt-0">
                    <!-- Ambient Glow underneath visual -->
                    <div class="absolute -inset-4 rounded-[36px] bg-gradient-to-tr from-primary-fixed/45 via-secondary-fixed/35 to-accent/20 blur-2xl -z-10 opacity-80"></div>

                    <!-- Main Showcase Image (Edge-to-Edge, No White Border) -->
                    <div class="relative w-full aspect-[16/9] rounded-3xl overflow-hidden shadow-[0_20px_50px_rgba(0,96,65,0.14)] group">
                        <img src="{{ asset('images/campus_hero.jpg') }}" alt="Ilustrasi Nitip Mikro-Logistik Kampus" class="w-full h-full object-cover object-center transform group-hover:scale-[1.02] transition-transform duration-700 ease-out" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Scroll Hint Down Indicator -->
        <div class="pt-2 pb-2 text-center flex flex-col items-center justify-center relative z-10">
            <a href="#cerita-nitip" class="group inline-flex flex-col items-center gap-1 text-xs font-semibold text-slate-400 hover:text-primary transition-all duration-200" aria-label="Scroll ke Cerita Nitip">
                <span class="text-[11px] sm:text-xs font-semibold text-slate-400 group-hover:text-primary transition-colors">Cerita Nitip</span>
                <span class="material-symbols-outlined text-[20px] text-slate-400 group-hover:text-primary animate-bounce">keyboard_arrow_down</span>
            </a>
        </div>
    </section>

    {{-- Cerita Nitip --}}
    <section id="cerita-nitip" class="w-full min-h-auto lg:min-h-[calc(100vh-4rem)] flex flex-col justify-start pt-8 sm:pt-12 lg:pt-16 pb-14 sm:pb-20 lg:pb-28 bg-white scroll-mt-16 border-b border-border/50">
        <div class="max-w-[1280px] mx-auto px-4 lg:px-8 w-full">
            <!-- Header (Dinaikkan sedikit & responsif) -->
            <div class="mb-6 sm:mb-8 lg:mb-12">
                <h2 class="text-2xl sm:text-3xl lg:text-[44px] font-extrabold tracking-tight text-primary font-sans">
                    Cerita Nitip
                </h2>
            </div>

            <!-- Content Grid: Image Left (Diperbesar), Story Right (Sejajar) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 lg:gap-12 items-center">
                <!-- Left: Illustration Image (Diperbesar ke col-span-7) -->
                <div class="lg:col-span-7 relative">
                    <div class="relative w-full aspect-[16/10] rounded-2xl sm:rounded-3xl overflow-hidden shadow-2xl border border-slate-100 group">
                        <img src="{{ asset('images/cerita_nitip.jpg') }}" alt="Cerita Nitip - Penitip dan Jastiper" class="w-full h-full object-cover object-center transform group-hover:scale-[1.02] transition-transform duration-700 ease-out" />
                    </div>
                </div>

                <!-- Right: Story Paragraphs (Sejajar col-span-5) -->
                <div class="lg:col-span-5 flex flex-col justify-center space-y-4 sm:space-y-6 text-slate-600 font-sans text-sm sm:text-base lg:text-lg leading-relaxed sm:leading-[1.85]">
                    <p>
                        <strong class="text-on-surface font-bold">Nitip</strong> adalah platform saling titip khusus lingkungan kampus yang dibuat dari mahasiswa, untuk mahasiswa. Berangkat dari padatnya jadwal kuliah dan rasa mager jalan jauh antar-gedung, Nitip hadir agar urusan perut, jajan kopi, sampai print tugas kuliah bisa beres cepat tanpa harus meninggalkan aktivitas belajar.
                    </p>
                    <p>
                        Cukup titipkan ke teman kampus yang kebetulan lagi jalan ke arah sana. Tanpa kurir luar dan tanpa potongan komisi aplikasi, semua transaksi di Nitip berjalan aman sesama mahasiswa terverifikasi, dengan harga barang riil sesuai struk kasir dan uang jasa yang 100% diterima penuh oleh teman yang membantumu.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Dual posting (Cara kerja) --}}
    <section id="cara-kerja" class="w-full min-h-auto lg:min-h-[calc(100vh-4rem)] flex flex-col justify-start pt-8 sm:pt-10 lg:pt-12 pb-16 sm:pb-24 lg:pb-36 bg-surface-low scroll-mt-16">
        <div class="max-w-[1200px] mx-auto px-4 lg:px-8 w-full">
            <div class="text-center max-w-2xl mx-auto mb-6 lg:mb-8">
                <h2 class="font-sans font-bold text-2xl lg:text-[30px] tracking-tight text-on-surface">Sistem Dua Arah Penitip Bertemu Jastiper</h2>
                <p class="text-sm sm:text-base text-on-surface-variant mt-1.5">Nitip mencocokkan titipan di sepanjang rute kampus kamu.</p>
            </div>

            <div class="relative grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 items-stretch">
                <!-- Left Card: Jalur Penitip -->
                <div class="bg-white rounded-3xl p-6 shadow-card hover:shadow-float transition-shadow flex flex-col justify-between border border-border/50">
                    <div>
                        <div class="flex items-center justify-between mb-3.5">
                            <x-ui.badge variant="secondary" icon="shopping_bag">Jalur Penitip</x-ui.badge>
                            <span class="text-xs font-bold tracking-wider text-slate-400 uppercase">REQUEST</span>
                        </div>
                        <h3 class="font-sans font-bold text-xl text-on-surface mb-2">Posting Kebutuhanmu</h3>
                        <p class="text-[14px] leading-[22px] text-on-surface-variant mb-4 min-h-[44px]">
                            Lagi sibuk belajar atau mager keluar? Minta tolong titipin makanan, minuman, atau print tugas. Relawan yang searah akan mengambil pesananmu.
                        </p>
                        <div class="p-4 rounded-2xl bg-surface-low border border-slate-200/60 mb-5 space-y-3">
                            @php $sample = $openRequests->first(); @endphp
                            <div class="flex items-start justify-between gap-3 min-h-[42px]">
                                <div class="min-w-0">
                                    <h4 class="font-bold text-on-surface text-[14px] truncate">{{ $sample?->title ?? 'Kopi Kenangan & Roti Bakar' }}</h4>
                                    <p class="text-[12px] text-on-surface-variant mt-0.5 truncate">Ambil di {{ $sample?->pickup_location ?? 'Kantin Gedung C' }}</p>
                                </div>
                                <div class="h-7 inline-flex items-center gap-1.5 px-2.5 rounded-lg bg-white text-primary font-bold text-[12px] shadow-xs border border-slate-200/60 shrink-0">
                                    <span class="material-symbols-outlined text-[15px]">payments</span>
                                    Jasa {{ rupiah($sample?->service_fee ?? 3500) }}
                                </div>
                            </div>
                            <div class="h-9 px-3 rounded-xl bg-white border border-slate-200/60 flex items-center gap-2 text-[12px] font-medium text-on-surface shadow-xs">
                                <span class="w-2 h-2 rounded-full bg-secondary shrink-0"></span>
                                <span class="truncate font-semibold">{{ $sample?->pickup_location ?? 'Kantin C' }}</span>
                                <span class="h-0.5 flex-1 bg-surface-highest"></span>
                                <span class="material-symbols-outlined text-[15px] text-primary shrink-0">pin_drop</span>
                                <span class="truncate font-semibold">{{ $sample?->dropoff_location ?? 'Perpus Lt. 2' }}</span>
                            </div>
                        </div>
                    </div>
                    <x-ui.button :href="auth()->check() ? route('requests.create') : route('register')" icon-right="check_circle" class="h-12 font-bold text-sm shadow-sm" full>Posting kebutuhan sekarang</x-ui.button>
                </div>

                <!-- Center Floating Match Indicator -->
                <div class="hidden lg:flex absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 z-20 flex-col items-center pointer-events-none">
                    <div class="w-14 h-14 rounded-full bg-white shadow-xl flex items-center justify-center text-primary ring-4 ring-secondary-fixed/50"><span class="material-symbols-outlined text-[28px] animate-spin" style="animation-duration: 9s;">sync_alt</span></div>
                    <span class="mt-2 px-3 py-1 rounded-full bg-on-surface text-white text-[11px] font-bold shadow-md">Cocok Instan</span>
                </div>

                <!-- Right Card: Jalur Jastiper -->
                <div class="bg-white rounded-3xl p-6 shadow-card hover:shadow-float transition-shadow flex flex-col justify-between border border-border/50">
                    <div>
                        <div class="flex items-center justify-between mb-3.5">
                            <x-ui.badge variant="primary" icon="directions_walk">Jalur Jastiper</x-ui.badge>
                            <span class="text-xs font-bold tracking-wider text-slate-400 uppercase">OFFER</span>
                        </div>
                        <h3 class="font-sans font-bold text-xl text-on-surface mb-2">Posting Rute Kamu</h3>
                        <p class="text-[14px] leading-[22px] text-on-surface-variant mb-4 min-h-[44px]">
                            Udah mau jalan ke kantin atau keluar gerbang? Buka rute jalanmu dan tentukan kuota titipan. Dapetin uang saku tambahan sambil bantu bawa pesanan teman.
                        </p>
                        <div class="p-4 rounded-2xl bg-surface-low border border-slate-200/60 mb-5 space-y-3">
                            @php $sampleTrip = $openTrips->first(); @endphp
                            <div class="flex items-start justify-between gap-3 min-h-[42px]">
                                <div class="min-w-0">
                                    <h4 class="font-bold text-on-surface text-[14px] truncate">Menuju {{ $sampleTrip?->destination ?? 'Kantin Vokasi & Kober' }}</h4>
                                    <p class="text-[12px] text-on-surface-variant mt-0.5 truncate">Berangkat {{ $sampleTrip?->departure_at->diffForHumans() ?? '10 menit lagi' }}</p>
                                </div>
                                <div class="h-7 inline-flex items-center gap-1.5 px-2.5 rounded-lg bg-white text-secondary font-bold text-[12px] shadow-xs border border-slate-200/60 shrink-0">
                                    <span class="material-symbols-outlined text-[15px]">backpack</span>
                                    {{ $sampleTrip?->remainingSlots() ?? 5 }} slot
                                </div>
                            </div>
                            <div class="h-9 px-3 rounded-xl bg-white border border-slate-200/60 flex items-center justify-between gap-2 text-[12px] font-medium text-on-surface shadow-xs">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="material-symbols-outlined text-[15px] text-primary shrink-0">lunch_dining</span>
                                    <span class="text-on-surface-variant">Terima:</span>
                                    <span class="truncate font-semibold text-on-surface">{{ $sampleTrip?->category->name ?? 'Makanan Kantin Dalam' }}</span>
                                </div>
                                <span class="text-[11px] font-semibold text-secondary shrink-0 flex items-center gap-1 bg-secondary-fixed/30 px-2 py-0.5 rounded-md">
                                    <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span> Searah
                                </span>
                            </div>
                        </div>
                    </div>
                    <x-ui.button :href="auth()->check() ? route('trips.create') : route('register')" variant="soft" icon-right="add_road" class="h-12 font-bold text-sm border border-slate-200/80 hover:border-primary/40" full>Buka rute sekarang</x-ui.button>
                </div>
            </div>
        </div>
    </section>

    {{-- Siklus transaksi --}}
    <section id="siklus-transaksi" class="w-full min-h-auto lg:min-h-[calc(100vh-4rem)] flex flex-col justify-start pt-8 sm:pt-10 lg:pt-12 pb-14 sm:pb-20 lg:pb-32 bg-white scroll-mt-16 border-b border-slate-200/60">
        <div class="max-w-[1200px] mx-auto px-4 lg:px-8 w-full">
            <div class="text-center max-w-2xl mx-auto mb-6 sm:mb-8">
                <h2 class="font-sans font-extrabold text-2xl sm:text-3xl lg:text-[34px] tracking-tight text-on-surface">Alur Transaksi</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 lg:gap-5 items-stretch">
                @foreach ([
                    ['post_add', 'Post & Match', 'Buat posting titipan atau buka rute kampus sampai bertemu partner yang cocok.'],
                    ['payments', 'Pay & Verify', 'Penitip transfer langsung ke relawan (GoPay/BCA/QRIS) dan bukti diverifikasi.'],
                    ['shopping_cart_checkout', 'In Progress', 'Relawan berbelanja menu kantin atau mencetak berkas tugas yang dititipkan.'],
                    ['directions_walk', 'Delivering', 'Relawan menginput harga riil dari struk kasir, lalu mengantar pesanan ke titik temu.'],
                    ['task_alt', 'Completed', 'Serah terima pesanan, selisih nota diselesaikan, lalu konfirmasi selesai via PIN 4 digit.'],
                ] as $i => [$icon, $title, $desc])
                    <div class="group relative flex flex-col justify-between p-5 lg:p-6 rounded-[24px] bg-gradient-to-b from-slate-50/90 to-surface-low/50 border border-slate-200/80 shadow-xs hover:bg-white hover:border-primary/40 hover:shadow-float hover:-translate-y-1.5 transition-all duration-300">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200/70 shadow-xs flex items-center justify-center text-primary group-hover:scale-105 group-hover:bg-primary group-hover:text-white group-hover:border-primary transition-all duration-300">
                                    <span class="material-symbols-outlined text-[24px]">{{ $icon }}</span>
                                </div>
                                <span class="font-sans font-black text-2xl text-slate-300/80 group-hover:text-primary/30 transition-colors">0{{ $i + 1 }}</span>
                            </div>
                            <h3 class="font-sans font-bold text-on-surface text-base lg:text-[17px] mb-2 group-hover:text-primary transition-colors">{{ $title }}</h3>
                            <p class="text-xs sm:text-[13px] text-on-surface-variant leading-relaxed min-h-[58px]">{{ $desc }}</p>
                        </div>
                        <div class="mt-4 pt-3.5 border-t border-slate-200/70 flex items-center justify-between text-[11px] font-semibold text-slate-500 group-hover:text-primary transition-colors">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                Tahap {{ $i + 1 }}
                            </span>
                            <span class="material-symbols-outlined text-[16px] text-slate-400 group-hover:text-primary group-hover:translate-x-0.5 transition-all">
                                {{ $i < 4 ? 'east' : 'check_circle' }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Quick Education / FAQ Cards -->
            <div class="mt-8 lg:mt-10 pt-6 border-t border-slate-100">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Card 1: Selisih Harga -->
                    <div class="p-4 sm:p-4.5 rounded-2xl bg-surface-low/70 border border-slate-200/70 hover:bg-white hover:shadow-card hover:border-primary/30 transition-all duration-200 flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-white text-primary border border-slate-200/70 shadow-xs flex items-center justify-center shrink-0 mt-0.5">
                            <span class="material-symbols-outlined text-[20px]">receipt_long</span>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <h4 class="text-xs sm:text-[13px] font-bold text-on-surface truncate">Harga Riil Berbeda?</h4>
                                <span class="text-[10px] font-semibold text-secondary px-1.5 py-0.5 rounded bg-secondary-fixed/30 shrink-0">Struk Asli</span>
                            </div>
                            <p class="text-xs text-on-surface-variant leading-relaxed">
                                Penitip mengisi estimasi. Relawan wajib upload foto struk kasir toko. Selisih lebih/kurang dihitung otomatis dan diselesaikan saat serah terima.
                            </p>
                        </div>
                    </div>

                    <!-- Card 2: Metode Bayar -->
                    <div class="p-4 sm:p-4.5 rounded-2xl bg-surface-low/70 border border-slate-200/70 hover:bg-white hover:shadow-card hover:border-primary/30 transition-all duration-200 flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-white text-secondary border border-slate-200/70 shadow-xs flex items-center justify-center shrink-0 mt-0.5">
                            <span class="material-symbols-outlined text-[20px]">account_balance_wallet</span>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <h4 class="text-xs sm:text-[13px] font-bold text-on-surface truncate">Pembayaran Langsung P2P</h4>
                                <span class="text-[10px] font-semibold text-secondary px-1.5 py-0.5 rounded bg-secondary-fixed/30 shrink-0">Bebas Biaya</span>
                            </div>
                            <p class="text-xs text-on-surface-variant leading-relaxed">
                                Transfer langsung tanpa saldo mengendap. Mendukung GoPay, DANA, OVO, ShopeePay, transfer bank, hingga scan QRIS milik relawan.
                            </p>
                        </div>
                    </div>

                    <!-- Card 3: Keamanan PIN -->
                    <div class="p-4 sm:p-4.5 rounded-2xl bg-surface-low/70 border border-slate-200/70 hover:bg-white hover:shadow-card hover:border-primary/30 transition-all duration-200 flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-white text-info border border-slate-200/70 shadow-xs flex items-center justify-center shrink-0 mt-0.5">
                            <span class="material-symbols-outlined text-[20px]">pin</span>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <h4 class="text-xs sm:text-[13px] font-bold text-on-surface truncate">Kapan Transaksi Selesai?</h4>
                                <span class="text-[10px] font-semibold text-info px-1.5 py-0.5 rounded bg-info-container shrink-0">PIN 4 Digit</span>
                            </div>
                            <p class="text-xs text-on-surface-variant leading-relaxed">
                                Pesanan terkunci tuntas hanya jika penitip memberikan PIN 4 digit miliknya saat barang sudah diterima. Aman dan tercatat di audit trail.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Layanan & Tarif --}}
    <section id="layanan" class="w-full min-h-auto lg:min-h-[calc(100vh-4rem)] flex flex-col justify-start pt-8 sm:pt-10 lg:pt-12 pb-16 sm:pb-20 lg:pb-32 bg-surface-low scroll-mt-16 border-b border-slate-200/60">
        <div class="max-w-[1200px] mx-auto px-4 lg:px-8 w-full">
            <div class="text-center max-w-2xl mx-auto mb-6 sm:mb-8">
                <h2 class="font-sans font-extrabold text-2xl sm:text-3xl lg:text-[34px] tracking-tight text-on-surface">Layanan &amp; Skema Biaya Nitip</h2>
                <p class="text-sm sm:text-base text-on-surface-variant mt-1.5 max-w-xl mx-auto">Tarif flat yang transparan tanpa potongan komisi. Biaya barang dibayar sesuai struk kasir asli, dan relawan menerima 100 % biaya jasa.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                @foreach ($categories as $category)
                    <div class="bg-white rounded-[24px] p-5 sm:p-6 shadow-xs hover:shadow-float hover:border-primary/40 hover:-translate-y-1.5 transition-all duration-300 border border-slate-200/80 flex flex-col justify-between group">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="w-12 h-12 rounded-2xl bg-primary-fixed/40 text-primary flex items-center justify-center shadow-xs group-hover:bg-primary group-hover:text-white transition-all duration-300">
                                    <span class="material-symbols-outlined text-[26px]">{{ $category->icon }}</span>
                                </span>
                                <x-ui.badge variant="{{ $category->requires_document ? 'info' : 'secondary' }}">
                                    {{ $category->requires_document ? 'Jasa + biaya kertas' : 'Flat per order' }}
                                </x-ui.badge>
                            </div>
                            <h3 class="font-sans font-bold text-lg sm:text-xl text-on-surface mb-1.5 group-hover:text-primary transition-colors">{{ $category->name }}</h3>
                            <p class="text-xs sm:text-sm text-on-surface-variant mb-4 leading-relaxed min-h-[42px]">{{ $category->description }}</p>

                            <dl class="text-xs sm:text-[13px] space-y-2 py-3 border-t border-slate-100">
                                <div class="flex justify-between items-center py-0.5">
                                    <dt class="text-muted">Struktur biaya</dt>
                                    <dd class="font-semibold text-right text-on-surface">{{ $category->requires_document ? 'Flat jasa + riil kertas' : 'Flat per order' }}</dd>
                                </div>
                                <div class="flex justify-between items-center py-0.5">
                                    <dt class="text-muted">Tarif rekomendasi</dt>
                                    <dd class="font-bold text-primary tabular text-sm">{{ $category->feeRange() }}</dd>
                                </div>
                                <div class="flex justify-between items-center py-0.5">
                                    <dt class="text-muted">Tarif default</dt>
                                    <dd class="font-semibold tabular text-on-surface">{{ rupiah($category->fee_default) }}</dd>
                                </div>
                                <div class="flex justify-between items-center py-0.5">
                                    <dt class="text-muted">Berkas cetak</dt>
                                    <dd class="font-semibold {{ $category->requires_document ? 'text-info font-bold' : 'text-on-surface-variant' }}">
                                        {{ $category->requires_document ? 'Wajib (PDF/DOC)' : 'Tidak perlu' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <div class="pt-3.5 border-t border-slate-100">
                            @auth
                                <x-ui.button :href="route('requests.create', ['category' => $category->id])" variant="soft" size="sm" icon-right="arrow_forward" class="group-hover:bg-primary group-hover:text-white transition-all duration-200" full>
                                    Titip {{ strtolower($category->name) }}
                                </x-ui.button>
                            @else
                                <x-ui.button :href="route('register')" variant="soft" size="sm" icon-right="arrow_forward" class="group-hover:bg-primary group-hover:text-white transition-all duration-200" full>
                                    Mulai nitip sekarang
                                </x-ui.button>
                            @endauth
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Transparency Features Strip -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                <div class="bg-white/90 rounded-2xl p-4 border border-slate-200/70 shadow-xs flex items-start gap-3.5">
                    <span class="w-10 h-10 rounded-xl bg-primary-fixed/40 text-primary flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[20px]">money_off</span>
                    </span>
                    <div>
                        <h4 class="text-xs sm:text-[13px] font-bold text-on-surface">100% Untuk Relawan</h4>
                        <p class="text-xs text-on-surface-variant mt-0.5 leading-relaxed">Nol potongan komisi platform. Biaya jasa seutuhnya milik mahasiswa yang mengantar.</p>
                    </div>
                </div>

                <div class="bg-white/90 rounded-2xl p-4 border border-slate-200/70 shadow-xs flex items-start gap-3.5">
                    <span class="w-10 h-10 rounded-xl bg-secondary-fixed/50 text-secondary flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[20px]">receipt_long</span>
                    </span>
                    <div>
                        <h4 class="text-xs sm:text-[13px] font-bold text-on-surface">Sesuai Struk Kasir</h4>
                        <p class="text-xs text-on-surface-variant mt-0.5 leading-relaxed">Relawan wajib unggah foto nota asli. Tidak ada mark-up harga menu kantin atau cetak.</p>
                    </div>
                </div>

                <div class="bg-white/90 rounded-2xl p-4 border border-slate-200/70 shadow-xs flex items-start gap-3.5">
                    <span class="w-10 h-10 rounded-xl bg-info-container text-info flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[20px]">balance</span>
                    </span>
                    <div>
                        <h4 class="text-xs sm:text-[13px] font-bold text-on-surface">Rekonsiliasi Otomatis</h4>
                        <p class="text-xs text-on-surface-variant mt-0.5 leading-relaxed">Sistem menghitung selisih harga perkiraan vs struk untuk diselesaikan saat serah terima.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @auth
        <x-partials.footer />
    @endauth
</x-layouts.app>
