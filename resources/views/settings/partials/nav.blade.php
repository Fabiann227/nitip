@php
    $items = [
        ['route' => 'settings.profile', 'icon' => 'person', 'label' => 'Profil'],
        ['route' => 'settings.payment', 'icon' => 'account_balance_wallet', 'label' => 'Metode pembayaran'],
        ['route' => 'settings.password', 'icon' => 'lock', 'label' => 'Kata sandi'],
    ];
@endphp
<nav class="flex lg:flex-col gap-1 overflow-x-auto no-scrollbar pb-1 lg:pb-0" aria-label="Pengaturan">
    @foreach ($items as $item)
        <a href="{{ route($item['route']) }}" class="side-link whitespace-nowrap {{ request()->routeIs($item['route']) ? 'side-link-active' : 'bg-white border border-border lg:border-0 lg:bg-transparent' }}"><span class="material-symbols-outlined text-[20px]">{{ $item['icon'] }}</span>{{ $item['label'] }}</a>
    @endforeach
    <a href="{{ route('users.show', auth()->user()) }}" class="side-link whitespace-nowrap bg-white border border-border lg:border-0 lg:bg-transparent"><span class="material-symbols-outlined text-[20px]">badge</span>Lihat profil publik</a>
</nav>
