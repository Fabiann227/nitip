@props(['title' => null, 'wide' => false, 'plain' => false])
@php
    $user = auth()->user();
    $nav = $nav ?? ['unread' => 0, 'open_feed' => 0, 'active_orders' => 0];
    $isActive = fn (string|array $patterns) => request()->routeIs($patterns);
@endphp
<x-layouts.base :title="$title" body-class="bg-surface">
    <header class="fixed top-0 left-0 w-full z-50 bg-white/95 backdrop-blur-md border-b border-surface-highest/60 shadow-[0_1px_4px_rgba(0,0,0,0.03)]">
        <div class="h-16 max-w-[1200px] mx-auto px-4 lg:px-8 flex items-center justify-between gap-4">
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
                <nav class="hidden md:flex items-center gap-1">
                    <a href="{{ route('home') }}#cara-kerja" class="nav-link">Cara kerja</a>
                    <a href="{{ route('services') }}" class="nav-link {{ $isActive('services') ? 'nav-link-active' : '' }}">Layanan & tarif</a>
                    <a href="{{ route('home') }}#feed" class="nav-link">Titipan aktif</a>
                </nav>
                <div class="flex items-center gap-2">
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Masuk</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Daftar</a>
                </div>
            @endauth
        </div>
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
