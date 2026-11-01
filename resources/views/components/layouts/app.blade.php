@props(['title' => null, 'wide' => false, 'plain' => false])
@php
    $user = auth()->user();
    $nav = $nav ?? ['unread' => 0, 'open_feed' => 0, 'active_orders' => 0];
    $isActive = fn (string|array $patterns) => request()->routeIs($patterns);
@endphp
<x-layouts.base :title="$title" body-class="bg-surface">
    <header class="fixed top-0 left-0 w-full z-50 bg-white/95 backdrop-blur-md border-b border-surface-highest/60 shadow-[0_1px_4px_rgba(0,0,0,0.03)]"
            x-data="{ mobileMenuOpen: false }">
        <div class="h-16 max-w-[1280px] mx-auto px-4 lg:px-8 flex items-center justify-between gap-3 sm:gap-4">
            <a href="{{ $user ? route('dashboard') : route('home') }}" class="flex items-center shrink-0" aria-label="Beranda Nitip">
                <x-logo />
            </a>

            @auth
                <nav class="hidden lg:flex items-center gap-1" aria-label="Navigasi utama">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ $isActive('dashboard') ? 'nav-link-active' : '' }}">Beranda</a>
                    <a href="{{ route('explore') }}" class="nav-link {{ $isActive('explore') ? 'nav-link-active' : '' }}">
                        Nitip
                        @if ($nav['open_feed'] > 0)<span class="text-[11px] px-1.5 py-0.5 rounded-full bg-secondary-fixed text-on-secondary-container font-semibold">{{ $nav['open_feed'] }}</span>@endif
                    </a>
                    <a href="{{ route('orders.index') }}" class="nav-link {{ $isActive(['orders.*']) ? 'nav-link-active' : '' }}">
                        Pesanan Saya
                        @if ($nav['active_orders'] > 0)<span class="text-[11px] px-1.5 py-0.5 rounded-full bg-surface-high text-secondary font-semibold">{{ $nav['active_orders'] }}</span>@endif
                    </a>
                    <a href="{{ route('trips.index') }}" class="nav-link {{ $isActive(['trips.index', 'trips.create', 'trips.edit']) ? 'nav-link-active' : '' }}">Rute Saya</a>
                </nav>

                <div class="flex items-center gap-2 sm:gap-3">
                    <x-ui.dropdown align="right" width="w-64">
                        <x-slot:trigger>
                            <button type="button" class="hidden sm:inline-flex btn btn-primary btn-sm">
                                <span class="material-symbols-outlined text-[18px]">add_circle</span>
                                Buat Titipan
                            </button>
                        </x-slot:trigger>
                        <a href="{{ route('requests.create') }}" class="flex items-start gap-3 px-3 py-2.5 rounded-xl hover:bg-surface-low transition-colors">
                            <span class="material-symbols-outlined text-primary text-[22px] mt-0.5">shopping_bag</span>
                            <span><span class="block text-sm font-semibold text-on-surface">Posting kebutuhan</span><span class="block text-xs text-muted">Minta tolong dibelikan / dicetak</span></span>
                        </a>
                        <a href="{{ route('trips.create') }}" class="flex items-start gap-3 px-3 py-2.5 rounded-xl hover:bg-surface-low transition-colors">
                            <span class="material-symbols-outlined text-primary text-[22px] mt-0.5">directions_walk</span>
                            <span><span class="block text-sm font-semibold text-on-surface">Posting rute</span><span class="block text-xs text-muted">Bawa titipan searah jalanmu</span></span>
                        </a>
                    </x-ui.dropdown>

                    <a href="{{ route('notifications.index') }}" class="btn-icon relative" aria-label="Notifikasi{{ $nav['unread'] ? ', '.$nav['unread'].' belum dibaca' : '' }}">
                        <span class="material-symbols-outlined text-[22px]">notifications</span>
                        @if ($nav['unread'] > 0)
                            <span class="absolute top-1.5 right-1.5 min-w-[18px] h-[18px] px-1 rounded-full bg-secondary-fixed-dim text-on-secondary-container text-[10px] font-bold flex items-center justify-center ring-2 ring-white">{{ $nav['unread'] > 9 ? '9+' : $nav['unread'] }}</span>
                        @endif
                    </a>

                    <div class="h-6 w-px bg-surface-high hidden sm:block"></div>

                    <x-ui.dropdown align="right" width="w-60">
                        <x-slot:trigger>
                            <button type="button" class="flex items-center gap-2 cursor-pointer pl-1 py-1 pr-2 rounded-xl hover:bg-surface-low transition-colors" aria-label="Menu akun">
                                <x-ui.avatar :user="$user" size="sm" />
                                <span class="hidden md:flex flex-col text-left leading-tight">
                                    <span class="text-xs font-semibold text-on-surface">{{ $user->shortName() }}</span>
                                    <span class="text-[11px] text-secondary font-medium">{{ $user->campus ?? $user->role->label() }}</span>
                                </span>
                                <span class="material-symbols-outlined text-outline text-[18px] hidden md:inline-block">expand_more</span>
                            </button>
                        </x-slot:trigger>
                        <div class="px-3 py-2 border-b border-border mb-1">
                            <p class="text-sm font-semibold text-on-surface truncate">{{ $user->name }}</p>
                            <p class="text-xs text-muted truncate">{{ $user->email }}</p>
                        </div>
                        <a href="{{ route('users.show', $user) }}" class="side-link"><span class="material-symbols-outlined text-[20px]">person</span>Profil publik</a>
                        <a href="{{ route('earnings') }}" class="side-link"><span class="material-symbols-outlined text-[20px]">payments</span>Penghasilan</a>
                        <a href="{{ route('settings.profile') }}" class="side-link"><span class="material-symbols-outlined text-[20px]">settings</span>Pengaturan</a>
                        <a href="{{ route('settings.payment') }}" class="side-link"><span class="material-symbols-outlined text-[20px]">account_balance_wallet</span>Metode pembayaran</a>
                        @if ($user->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="side-link"><span class="material-symbols-outlined text-[20px]">admin_panel_settings</span>Panel admin</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="border-t border-border mt-1 pt-1">
                            @csrf
                            <button type="submit" class="side-link w-full text-error hover:text-error"><span class="material-symbols-outlined text-[20px]">logout</span>Keluar</button>
                        </form>
                    </x-ui.dropdown>
                </div>
            @else
                <nav class="hidden md:flex items-center gap-5 lg:gap-6">
                    <a href="{{ route('home') }}#cerita-nitip" class="relative py-1.5 px-0.5 text-sm font-semibold text-on-surface-variant hover:text-primary transition-colors group">
                        <span>Cerita Nitip</span>
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-primary rounded-full transition-all duration-300 ease-out group-hover:w-full"></span>
                    </a>
                    <a href="{{ route('home') }}#cara-kerja" class="relative py-1.5 px-0.5 text-sm font-semibold text-on-surface-variant hover:text-primary transition-colors group">
                        <span>Cara Kerja</span>
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-primary rounded-full transition-all duration-300 ease-out group-hover:w-full"></span>
                    </a>
                    <a href="{{ route('home') }}#layanan" class="relative py-1.5 px-0.5 text-sm font-semibold text-on-surface-variant hover:text-primary transition-colors group">
                        <span>Layanan & Tarif</span>
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-primary rounded-full transition-all duration-300 ease-out group-hover:w-full"></span>
                    </a>
                </nav>
                <div class="flex items-center gap-2">
                    <!-- Desktop & Tablet Auth Buttons (Hidden on mobile) -->
                    <a href="{{ route('login') }}" class="hidden md:inline-flex btn btn-sm bg-slate-100 hover:bg-slate-200/80 text-on-surface font-semibold border border-slate-200/80 shadow-2xs px-4">Masuk</a>
                    <a href="{{ route('register') }}" class="hidden md:inline-flex btn btn-primary btn-sm px-4">Daftar</a>

                    <!-- Hamburger Button on Mobile (< md) -->
                    <button type="button" 
                            @click="mobileMenuOpen = !mobileMenuOpen" 
                            class="md:hidden inline-flex items-center justify-center w-10 h-10 rounded-xl text-on-surface hover:text-primary hover:bg-slate-100 transition-colors focus:outline-none" 
                            aria-label="Toggle navigation menu"
                            :aria-expanded="mobileMenuOpen">
                        <span class="material-symbols-outlined text-[26px]" x-show="!mobileMenuOpen">menu</span>
                        <span class="material-symbols-outlined text-[26px]" x-show="mobileMenuOpen" style="display: none;">close</span>
                    </button>
                </div>
            @endauth
        </div>

        @guest
            <!-- Mobile Menu Dropdown Panel -->
            <div x-show="mobileMenuOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 @click.outside="mobileMenuOpen = false"
                 @keydown.escape.window="mobileMenuOpen = false"
                 class="md:hidden bg-white/98 backdrop-blur-lg border-b border-slate-200 shadow-xl px-4 pt-3 pb-5"
                 style="display: none;">
                <nav class="flex flex-col space-y-1 mb-4">
                    <a href="{{ route('home') }}#cerita-nitip" 
                       @click="mobileMenuOpen = false"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-on-surface hover:text-primary hover:bg-emerald-50/70 transition-colors">
                        <span class="material-symbols-outlined text-[20px] text-primary">auto_stories</span>
                        <span>Cerita Nitip</span>
                    </a>
                    <a href="{{ route('home') }}#cara-kerja" 
                       @click="mobileMenuOpen = false"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-on-surface hover:text-primary hover:bg-emerald-50/70 transition-colors">
                        <span class="material-symbols-outlined text-[20px] text-primary">sync_alt</span>
                        <span>Cara Kerja</span>
                    </a>
                    <a href="{{ route('home') }}#layanan" 
                       @click="mobileMenuOpen = false"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-on-surface hover:text-primary hover:bg-emerald-50/70 transition-colors">
                        <span class="material-symbols-outlined text-[20px] text-primary">receipt_long</span>
                        <span>Layanan & Tarif</span>
                    </a>
                </nav>

                <!-- Auth Buttons inside Mobile Menu -->
                <div class="pt-3 border-t border-slate-100 grid grid-cols-2 gap-2.5">
                    <a href="{{ route('login') }}" class="btn btn-sm bg-slate-100 hover:bg-slate-200/80 text-on-surface font-semibold border border-slate-200/80 shadow-2xs justify-center">Masuk</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm justify-center">Daftar</a>
                </div>
            </div>
        @endguest
    </header>

    <main class="flex-1 w-full pt-16 {{ auth()->check() ? 'pb-20 lg:pb-0' : '' }}">
        @if ($plain)
            {{ $slot }}
        @else
            <div class="{{ $wide ? 'max-w-[1200px]' : 'max-w-5xl' }} mx-auto px-4 lg:px-8 py-6 sm:py-8">
                {{ $slot }}
            </div>
        @endif
    </main>

    @auth
        {{-- Mobile bottom navigation --}}
        <nav class="lg:hidden fixed bottom-0 left-0 w-full z-40 bg-white/95 backdrop-blur-md border-t border-border pb-[env(safe-area-inset-bottom)]" aria-label="Navigasi bawah">
            <div class="flex items-stretch h-16">
                <a href="{{ route('dashboard') }}" class="bottom-nav-item {{ $isActive('dashboard') ? 'bottom-nav-item-active' : '' }}">
                    <span class="material-symbols-outlined text-[24px] {{ $isActive('dashboard') ? 'filled' : '' }}">home</span>Beranda
                </a>
                <a href="{{ route('explore') }}" class="bottom-nav-item {{ $isActive('explore') ? 'bottom-nav-item-active' : '' }}">
                    <span class="material-symbols-outlined text-[24px] {{ $isActive('explore') ? 'filled' : '' }}">explore</span>Nitip
                </a>
                <a href="{{ route('requests.create') }}" class="bottom-nav-item -mt-5" aria-label="Buat titipan">
                    <span class="w-12 h-12 rounded-full bg-primary text-white shadow-primary flex items-center justify-center"><span class="material-symbols-outlined text-[28px]">add</span></span>
                </a>
                <a href="{{ route('orders.index') }}" class="bottom-nav-item relative {{ $isActive(['orders.*']) ? 'bottom-nav-item-active' : '' }}">
                    <span class="material-symbols-outlined text-[24px] {{ $isActive(['orders.*']) ? 'filled' : '' }}">receipt_long</span>Pesanan
                    @if ($nav['active_orders'] > 0)<span class="absolute top-1.5 right-[calc(50%-18px)] w-2 h-2 rounded-full bg-secondary-fixed-dim"></span>@endif
                </a>
                <a href="{{ route('settings.profile') }}" class="bottom-nav-item {{ $isActive(['settings.*', 'users.show', 'earnings', 'trips.index']) ? 'bottom-nav-item-active' : '' }}">
                    <span class="material-symbols-outlined text-[24px]">account_circle</span>Akun
                </a>
            </div>
        </nav>
    @endauth

    @guest
        <x-partials.footer />
    @endguest
</x-layouts.base>
