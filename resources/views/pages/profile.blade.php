<x-layouts.app :title="$profile->name" wide>
    <x-ui.card class="p-5 sm:p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <x-ui.avatar :user="$profile" size="xl" />
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="font-sans font-bold text-2xl text-on-surface">{{ $profile->name }}</h1>
                    @if ($profile->hasVerifiedEmail())<x-ui.badge variant="success" icon="verified">Terverifikasi</x-ui.badge>@endif
                    @if ($profile->isSuspended())<x-ui.badge variant="danger" icon="block">Ditangguhkan</x-ui.badge>@endif
                </div>
                <p class="text-sm text-on-surface-variant">{{ $profile->campusName() }} &bull; bergabung {{ $stats['member_since']->translatedFormat('M Y') }}</p>
                @if ($profile->bio)<p class="text-sm text-on-surface mt-2">"{{ $profile->bio }}"</p>@endif
            </div>
            <div class="flex gap-2">
                @if ($isMe)
                    <x-ui.button :href="route('settings.profile')" variant="outline" size="sm" icon="edit">Ubah profil</x-ui.button>
                @elseif ($profile->whatsapp_number)
                    <x-ui.button :href="$profile->whatsappUrl('Halo '.$profile->shortName().', saya '.auth()->user()->shortName().' dari Nitip.')" target="_blank" variant="accent" size="sm" icon="chat">WhatsApp</x-ui.button>
                @endif
            </div>
        </div>
        <div class="grid grid-cols-3 gap-3 mt-5 pt-5 border-t border-border">
            <div class="text-center"><p class="font-sans font-extrabold text-2xl text-primary tabular">{{ $stats['deliveries'] }}</p><p class="text-xs text-muted">Pengantaran selesai</p></div>
            <div class="text-center"><p class="font-sans font-extrabold text-2xl text-on-surface tabular">{{ $stats['requests'] }}</p><p class="text-xs text-muted">Titipan selesai</p></div>
            <div class="text-center"><p class="font-sans font-extrabold text-2xl text-on-surface tabular flex items-center justify-center gap-1"><span class="material-symbols-outlined filled text-amber-400 text-[22px]">star</span>{{ $stats['rating'] ? number_format($stats['rating'], 1) : '-' }}</p><p class="text-xs text-muted">{{ $stats['rating_count'] }} ulasan</p></div>
        </div>
    </x-ui.card>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-ui.card title="Ulasan" icon="reviews">
                @forelse ($reviews as $review)
                    <div class="py-3 first:pt-0 last:pb-0 border-b last:border-b-0 border-border">
                        <div class="flex items-center justify-between gap-2"><x-ui.user-chip :user="$review->reviewer" size="sm" /><x-ui.rating :value="$review->rating" size="md" /></div>
                        @if ($review->comment)<p class="text-sm text-on-surface-variant mt-2">"{{ $review->comment }}"</p>@endif
                        <p class="text-[11px] text-muted mt-1">{{ $review->order?->title }} &bull; {{ $review->created_at->translatedFormat('d M Y') }}</p>
                    </div>
                @empty
                    <x-ui.empty-state compact icon="star" title="Belum ada ulasan" description="Ulasan muncul setelah transaksi selesai." />
                @endforelse
            </x-ui.card>
        </div>
        <aside class="space-y-6">
            <x-ui.card title="Rute terbuka" icon="route" :padding="false">
                <div class="divide-y divide-border">
                    @forelse ($openTrips as $trip)
                        <a href="{{ route('trips.show', $trip) }}" class="flex items-center gap-3 p-4 hover:bg-canvas"><span class="material-symbols-outlined text-primary">{{ $trip->transport_mode->icon() }}</span><span class="min-w-0 flex-1"><span class="block text-sm font-semibold truncate">{{ $trip->routeLabel() }}</span><span class="block text-xs text-muted">{{ $trip->departure_at->translatedFormat('d M H:i') }} &bull; {{ $trip->remainingSlots() }} slot</span></span></a>
                    @empty
                        <p class="p-4 text-sm text-muted">Tidak ada rute terbuka.</p>
                    @endforelse
                </div>
            </x-ui.card>
            <x-ui.card title="Permintaan terbuka" icon="shopping_bag" :padding="false">
                <div class="divide-y divide-border">
                    @forelse ($openRequests as $order)
                        <a href="{{ route('orders.show', $order) }}" class="flex items-center gap-3 p-4 hover:bg-canvas"><span class="material-symbols-outlined text-primary">{{ $order->category->icon }}</span><span class="min-w-0 flex-1"><span class="block text-sm font-semibold truncate">{{ $order->title }}</span><span class="block text-xs text-muted">{{ rupiah($order->service_fee) }} &bull; {{ $order->needed_by?->diffForHumans() }}</span></span></a>
                    @empty
                        <p class="p-4 text-sm text-muted">Tidak ada permintaan terbuka.</p>
                    @endforelse
                </div>
            </x-ui.card>
        </aside>
    </div>
</x-layouts.app>
