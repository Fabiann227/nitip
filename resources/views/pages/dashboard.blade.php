<x-layouts.app title="Beranda" wide>
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6">
        <div>
            <p class="text-sm text-on-surface-variant">{{ now()->translatedFormat('l, d F Y') }}</p>
            <h1 class="font-sans font-bold text-2xl sm:text-3xl tracking-tight text-on-surface">Halo, {{ $user->shortName() }} 👋</h1>
            <p class="text-sm text-on-surface-variant mt-1">{{ $user->campusName() }} &bull; Siap nitip &amp; dianterin hari ini?</p>
        </div>
        <div class="flex gap-2">
            <x-ui.button :href="route('requests.create')" icon="shopping_bag" size="sm">Posting kebutuhan</x-ui.button>
            <x-ui.button :href="route('trips.create')" variant="secondary" icon="directions_walk" size="sm">Posting rute</x-ui.button>
        </div>
    </div>

    @if (! $hasPaymentMethod)
        <x-ui.alert type="warning" icon="account_balance_wallet" title="Lengkapi metode pembayaranmu" class="mb-6">
            Atur GoPay/BCA/QRIS agar kamu bisa menerima pembayaran saat menjadi relawan. <a href="{{ route('settings.payment') }}" class="font-semibold underline">Atur sekarang</a>.
        </x-ui.alert>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-8">
        <x-ui.stat-card label="Titipanku aktif" :value="$stats['active_requests']" icon="shopping_bag" :href="route('orders.index')" />
        <x-ui.stat-card label="Jadi relawan aktif" :value="$stats['active_jobs']" icon="directions_walk" tone="secondary" :href="route('orders.index', ['role' => 'fulfiller'])" />
        <x-ui.stat-card label="Uang saku bulan ini" :value="rupiah($stats['earnings_month'])" icon="payments" tone="info" :hint="$stats['completed_jobs'].' pengantaran selesai'" :href="route('earnings')" />
        <x-ui.stat-card label="Rating" :value="$stats['rating'] ? number_format($stats['rating'], 1).' / 5' : '-'" icon="star" tone="warning" :hint="$stats['rating_count'].' ulasan'" :href="route('users.show', $user)" />
    </div>

    @if ($needsAction->isNotEmpty())
        <section class="mb-8">
            <div class="flex items-center gap-2 mb-3">
                <span class="w-2 h-2 rounded-full bg-warning animate-pulse"></span>
                <h2 class="font-sans font-bold text-lg">Perlu tindakanmu</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach ($needsAction as $order)
                    <a href="{{ route('orders.show', $order) }}" class="card card-hover p-4 flex items-center gap-3 border-l-4 border-l-warning">
                        <span class="material-symbols-outlined text-warning text-[28px]">{{ $order->status->icon() }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-on-surface truncate">{{ $order->title }}</p>
                            <p class="text-xs text-on-surface-variant">
                                @switch($order->status)
                                    @case(\App\Enums\OrderStatus::AwaitingPayment) Transfer {{ rupiah($order->estimatedTotal()) }} ke relawan dan unggah bukti @break
                                    @case(\App\Enums\OrderStatus::Delivered) Konfirmasi bahwa pesanan sudah kamu terima @break
                                    @case(\App\Enums\OrderStatus::PaymentSubmitted) Verifikasi bukti transfer dari penitip @break
                                    @case(\App\Enums\OrderStatus::Paid) Pembayaran masuk, mulai proses pesanan @break
                                @endswitch
                            </p>
                        </div>
                        <span class="material-symbols-outlined text-outline">chevron_right</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-ui.card title="Titipanku" subtitle="Pesanan yang kamu buat sebagai penitip" icon="shopping_bag" :padding="false">
                <x-slot:actions><a href="{{ route('orders.index') }}" class="btn btn-xs btn-ghost">Semua</a></x-slot:actions>
                <div class="p-3 space-y-2">
                    @forelse ($requesterActive as $order)
                        <x-cards.order-row :order="$order" perspective="requester" />
                    @empty
                        <x-ui.empty-state compact icon="shopping_bag" title="Belum ada titipan aktif" description="Posting kebutuhanmu, relawan yang searah akan mengambilnya.">
                            <x-ui.button :href="route('requests.create')" size="sm">Posting kebutuhan</x-ui.button>
                        </x-ui.empty-state>
                    @endforelse
                </div>
            </x-ui.card>

            <x-ui.card title="Jadi relawan" subtitle="Titipan yang sedang kamu bantu" icon="directions_walk" :padding="false">
                <x-slot:actions><a href="{{ route('orders.index', ['role' => 'fulfiller']) }}" class="btn btn-xs btn-ghost">Semua</a></x-slot:actions>
                <div class="p-3 space-y-2">
                    @forelse ($fulfillerActive as $order)
                        <x-cards.order-row :order="$order" perspective="fulfiller" />
                    @empty
                        <x-ui.empty-state compact icon="directions_walk" title="Belum ada titipan yang kamu bantu" description="Cek permintaan di sekitar rutemu atau buka rute sendiri.">
                            <x-ui.button :href="route('explore')" size="sm" variant="soft">Cari titipan</x-ui.button>
                            <x-ui.button :href="route('trips.create')" size="sm" variant="outline">Buka rute</x-ui.button>
                        </x-ui.empty-state>
                    @endforelse
                </div>
            </x-ui.card>

            @if ($myOpenTrips->isNotEmpty())
                <x-ui.card title="Rute aktifku" icon="route" :padding="false">
                    <x-slot:actions><a href="{{ route('trips.index') }}" class="btn btn-xs btn-ghost">Semua</a></x-slot:actions>
                    <div class="divide-y divide-border">
                        @foreach ($myOpenTrips as $trip)
                            <a href="{{ route('trips.show', $trip) }}" class="flex items-center gap-3 p-4 hover:bg-canvas">
                                <span class="material-symbols-outlined text-primary">{{ $trip->transport_mode->icon() }}</span>
                                <div class="min-w-0 flex-1"><p class="text-sm font-semibold truncate">{{ $trip->routeLabel() }}</p><p class="text-xs text-on-surface-variant">Berangkat {{ $trip->departure_at->diffForHumans() }} &bull; {{ $trip->remainingSlots() }} slot tersisa</p></div>
                                <x-ui.badge variant="success" dot>{{ $trip->activeOrdersCount() }} titipan</x-ui.badge>
                            </a>
                        @endforeach
                    </div>
                </x-ui.card>
            @endif
        </div>

        <div class="space-y-6">
            <x-ui.card title="Di sekitar kampusmu" subtitle="Permintaan terbuka" icon="explore" :padding="false">
                <x-slot:actions><a href="{{ route('explore') }}" class="btn btn-xs btn-ghost">Semua</a></x-slot:actions>
                <div class="divide-y divide-border">
                    @forelse ($feedRequests as $order)
                        <a href="{{ route('orders.show', $order) }}" class="flex items-start gap-3 p-4 hover:bg-canvas">
                            <x-ui.avatar :user="$order->requester" size="sm" />
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-on-surface truncate">{{ $order->title }}</p>
                                <p class="text-xs text-on-surface-variant truncate">{{ $order->pickup_location }} &rarr; {{ $order->dropoff_location }}</p>
                                <p class="text-xs text-muted">{{ $order->needed_by?->diffForHumans() }}</p>
                            </div>
                            <span class="text-sm font-bold text-primary tabular shrink-0">{{ rupiah($order->service_fee) }}</span>
                        </a>
                    @empty
                        <p class="p-4 text-sm text-muted text-center">Belum ada permintaan terbuka di kampusmu.</p>
                    @endforelse
                </div>
            </x-ui.card>

            <x-ui.card title="Rute yang bisa kamu tumpangi" icon="route" :padding="false">
                <x-slot:actions><a href="{{ route('explore', ['tab' => 'trips']) }}" class="btn btn-xs btn-ghost">Semua</a></x-slot:actions>
                <div class="divide-y divide-border">
                    @forelse ($feedTrips as $trip)
                        <a href="{{ route('trips.show', $trip) }}" class="flex items-start gap-3 p-4 hover:bg-canvas">
                            <x-ui.avatar :user="$trip->fulfiller" size="sm" />
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-on-surface truncate">{{ $trip->fulfiller->shortName() }} &rarr; {{ $trip->destination }}</p>
                                <p class="text-xs text-on-surface-variant">Berangkat {{ $trip->departure_at->translatedFormat('H:i') }} &bull; {{ $trip->remainingSlots() }} slot</p>
                            </div>
                            <span class="text-sm font-bold text-primary tabular shrink-0">{{ rupiah($trip->service_fee) }}</span>
                        </a>
                    @empty
                        <p class="p-4 text-sm text-muted text-center">Belum ada rute terbuka. <a href="{{ route('trips.create') }}" class="text-primary font-semibold">Buka rute?</a></p>
                    @endforelse
                </div>
            </x-ui.card>

            <x-ui.card title="Notifikasi terbaru" icon="notifications" :padding="false">
                <x-slot:actions><a href="{{ route('notifications.index') }}" class="btn btn-xs btn-ghost">Semua</a></x-slot:actions>
                <div class="divide-y divide-border">
                    @forelse ($recentNotifications as $notification)
                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                            @csrf
                            <button type="submit" class="w-full text-left flex items-start gap-3 p-3 hover:bg-canvas {{ $notification->read_at ? 'opacity-70' : '' }}">
                                <span class="material-symbols-outlined text-[20px] {{ $notification->read_at ? 'text-outline' : 'text-primary' }}">{{ $notification->data['icon'] ?? 'notifications' }}</span>
                                <span class="min-w-0"><span class="block text-xs font-semibold text-on-surface truncate">{{ $notification->data['title'] ?? '' }}</span><span class="block text-[11px] text-on-surface-variant line-clamp-2">{{ $notification->data['message'] ?? '' }}</span><span class="block text-[10px] text-muted">{{ $notification->created_at->diffForHumans() }}</span></span>
                            </button>
                        </form>
                    @empty
                        <p class="p-4 text-sm text-muted text-center">Belum ada notifikasi.</p>
                    @endforelse
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
