@props(['title' => null])
@php
    $user = auth()->user();
    $nav = $nav ?? ['unread' => 0];
    $links = [
        ['route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'icon' => 'space_dashboard', 'label' => 'Dashboard'],
        ['route' => 'admin.orders.index', 'match' => 'admin.orders.*', 'icon' => 'receipt_long', 'label' => 'Pesanan'],
        ['route' => 'admin.trips.index', 'match' => 'admin.trips.*', 'icon' => 'route', 'label' => 'Rute'],
        ['route' => 'admin.disputes.index', 'match' => 'admin.disputes.*', 'icon' => 'gavel', 'label' => 'Sengketa'],
        ['route' => 'admin.users.index', 'match' => 'admin.users.*', 'icon' => 'group', 'label' => 'Pengguna'],
        ['route' => 'admin.categories.index', 'match' => 'admin.categories.*', 'icon' => 'sell', 'label' => 'Kategori & tarif'],
        ['route' => 'admin.audit-logs.index', 'match' => 'admin.audit-logs.*', 'icon' => 'history', 'label' => 'Audit trail'],
    ];
@endphp
<x-layouts.base :title="$title ? $title.' · Admin' : 'Admin'" body-class="bg-surface">
    <div x-data="{ sidebar: false }" class="min-h-screen flex">
        <aside class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-border flex flex-col transition-transform duration-200 lg:translate-x-0" :class="sidebar ? 'translate-x-0' : '-translate-x-full'">
            <div class="h-16 px-5 flex items-center justify-between border-b border-border">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2"><x-logo /><span class="badge badge-primary text-[10px] h-5 px-2">ADMIN</span></a>
                <button type="button" class="btn-icon lg:hidden" x-on:click="sidebar = false" aria-label="Tutup menu"><span class="material-symbols-outlined">close</span></button>
            </div>
            <nav class="flex-1 overflow-y-auto p-3 space-y-1" aria-label="Menu admin">
                @foreach ($links as $link)
                    <a href="{{ route($link['route']) }}" class="side-link {{ request()->routeIs($link['match']) ? 'side-link-active' : '' }}">
                        <span class="material-symbols-outlined text-[22px]">{{ $link['icon'] }}</span>{{ $link['label'] }}
                    </a>
                @endforeach
                <div class="pt-3 mt-3 border-t border-border">
                    <a href="{{ route('home') }}" class="side-link"><span class="material-symbols-outlined text-[22px]">public</span>Lihat situs</a>
                    <a href="{{ route('notifications.index') }}" class="side-link"><span class="material-symbols-outlined text-[22px]">notifications</span>Notifikasi @if($nav['unread'] > 0)<span class="ml-auto badge badge-secondary h-5 px-2 text-[10px]">{{ $nav['unread'] }}</span>@endif</a>
                </div>
            </nav>
            <div class="p-4 border-t border-border">
                <div class="flex items-center gap-3">
                    <x-ui.avatar :user="$user" size="sm" />
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold truncate">{{ $user->name }}</p>
                        <p class="text-xs text-muted truncate">{{ $user->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="btn-icon text-error" aria-label="Keluar"><span class="material-symbols-outlined">logout</span></button></form>
                </div>
            </div>
        </aside>
        <div x-show="sidebar" x-cloak x-transition.opacity class="fixed inset-0 z-40 bg-on-surface/40 lg:hidden" x-on:click="sidebar = false"></div>

        <div class="flex-1 lg:pl-72 flex flex-col min-w-0">
            <header class="h-16 sticky top-0 z-30 bg-white/95 backdrop-blur border-b border-border flex items-center gap-3 px-4 lg:px-8">
                <button type="button" class="btn-icon lg:hidden" x-on:click="sidebar = true" aria-label="Buka menu"><span class="material-symbols-outlined">menu</span></button>
                <h1 class="font-sans font-bold text-lg truncate">{{ $title ?? 'Admin' }}</h1>
                <div class="ml-auto flex items-center gap-2">
                    {{ $actions ?? '' }}
                </div>
            </header>
            <main class="flex-1 p-4 lg:p-8 max-w-[1400px] w-full">
                {{ $slot }}
            </main>
        </div>
    </div>
</x-layouts.base>
