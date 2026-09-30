@php use App\Enums\TripStatus; $user = auth()->user(); @endphp
<x-layouts.app :title="'Rute '.$trip->code" wide>
    <x-ui.page-header :back="$isOwner ? route('trips.index') : route('explore', ['tab' => 'trips'])" :back-label="$isOwner ? 'Rute saya' : 'Nitip'" :eyebrow="'Rute #'.$trip->code.' · '.$trip->campus" :title="'Menuju '.$trip->destination" :subtitle="'Berangkat '.$trip->departure_at->translatedFormat('l, d M Y H:i').' ('.$trip->departure_at->diffForHumans().')'">
        <x-ui.status-badge :status="$trip->status" class="h-8 px-3 text-sm" />
        @if ($trip->status === TripStatus::Open && ! $trip->isOpen())<x-ui.badge variant="neutral" class="h-8 px-3 text-sm">Batas terima lewat</x-ui.badge>@endif
    </x-ui.page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-ui.card class="p-5">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
                    <div class="p-3 rounded-xl bg-surface-low"><p class="text-[11px] text-muted mb-0.5">Slot tersisa</p><p class="font-sans font-extrabold text-xl text-primary tabular">{{ $trip->remainingSlots() }}<span class="text-sm text-muted font-normal">/{{ $trip->max_slots }}</span></p></div>
                    <div class="p-3 rounded-xl bg-surface-low"><p class="text-[11px] text-muted mb-0.5">Biaya jasa</p><p class="font-sans font-extrabold text-xl text-primary tabular">{{ rupiah($trip->service_fee) }}</p></div>
                    <div class="p-3 rounded-xl bg-surface-low"><p class="text-[11px] text-muted mb-0.5">Titipan ditutup</p><p class="font-semibold text-sm">{{ $trip->closes_at->translatedFormat('d M H:i') }}</p></div>
                    <div class="p-3 rounded-xl bg-surface-low"><p class="text-[11px] text-muted mb-0.5">Transportasi</p><p class="font-semibold text-sm flex items-center gap-1"><span class="material-symbols-outlined text-[18px] text-primary">{{ $trip->transport_mode->icon() }}</span>{{ $trip->transport_mode->label() }}</p></div>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center gap-2"><span class="text-primary font-bold w-16 shrink-0">Tujuan</span><span>{{ $trip->destination }}</span></div>
                    @if ($trip->waypoints)<div class="flex items-center gap-2"><span class="text-primary font-bold w-16 shrink-0">Lewat</span><span>{{ $trip->waypoints }}</span></div>@endif
                    <div class="flex items-center gap-2"><span class="text-primary font-bold w-16 shrink-0">Terima</span><x-ui.badge variant="secondary" :icon="$trip->category->icon">{{ $trip->category->name }}</x-ui.badge></div>
                </div>
                @if ($trip->notes)<p class="mt-4 p-3 rounded-xl bg-warning-container/60 border border-warning-border text-sm text-warning"><span class="font-semibold">Catatan relawan:</span> {{ $trip->notes }}</p>@endif
                @if ($trip->status === TripStatus::Cancelled)<x-ui.alert type="error" class="mt-4" title="Rute dibatalkan">{{ $trip->cancel_reason }}</x-ui.alert>@endif
            </x-ui.card>

            @if ($isOwner)
                <x-ui.card title="Titipan di rute ini" :subtitle="$orders->count().' titipan'" icon="inventory_2" :padding="false">
                    <div class="p-3 space-y-2">
                        @forelse ($orders as $order)
                            <x-cards.order-row :order="$order" perspective="fulfiller" />
                        @empty
                            <x-ui.empty-state compact icon="hourglass_empty" title="Belum ada yang menitip" description="Bagikan rutemu ke grup kelas, atau tunggu penitip dari feed kampus." />
                        @endforelse
                    </div>
                </x-ui.card>
            @elseif ($orders->isNotEmpty())
                <x-ui.card title="Titipanmu di rute ini" icon="inventory_2" :padding="false">
                    <div class="p-3 space-y-2">@foreach ($orders as $order)<x-cards.order-row :order="$order" perspective="requester" />@endforeach</div>
                </x-ui.card>
            @endif
        </div>

        <aside class="space-y-6">
            <x-ui.card title="Relawan" icon="directions_walk">
                <div class="flex items-center gap-3 mb-3">
                    <x-ui.avatar :user="$trip->fulfiller" size="lg" />
                    <div class="min-w-0">
                        <a href="{{ route('users.show', $trip->fulfiller) }}" class="font-sans font-bold text-on-surface hover:text-primary">{{ $trip->fulfiller->name }}</a>
                        <p class="text-xs text-on-surface-variant">{{ $trip->fulfiller->campusName() }}</p>
                        <x-ui.rating :value="$fulfillerRating" :count="$fulfillerRatingCount" class="mt-1" />
                    </div>
                </div>
                <p class="text-xs text-muted">{{ $fulfillerTrips }} pengantaran selesai &bull; bergabung {{ $trip->fulfiller->created_at->translatedFormat('M Y') }}</p>
                @if ($trip->fulfiller->bio)<p class="text-sm text-on-surface-variant mt-2">"{{ $trip->fulfiller->bio }}"</p>@endif
            </x-ui.card>

            @if ($isOwner)
                <x-ui.card title="Kelola rute" icon="settings">
                    <div class="flex flex-col gap-2">
                        @can('update', $trip)<x-ui.button :href="route('trips.edit', $trip)" variant="outline" icon="edit" full>Ubah rute</x-ui.button>@endcan
                        @can('close', $trip)<x-ui.confirm-form :action="route('trips.close', $trip)" title="Tutup rute?" message="Rute tidak lagi menerima titipan baru. Titipan yang sudah masuk tetap berjalan." confirm="Tutup rute" button-class="btn btn-soft w-full" icon="lock">Tutup dari titipan baru</x-ui.confirm-form>@endcan
                        @can('cancel', $trip)<x-ui.confirm-form :action="route('trips.cancel', $trip)" title="Batalkan rute?" message="Semua titipan yang belum dibayar akan ikut dibatalkan. Titipan yang sudah dibayar harus kamu selesaikan atau batalkan satu per satu dengan refund." confirm="Batalkan rute" variant="danger" reason reason-label="Alasan (dikirim ke penitip)" button-class="btn btn-ghost text-error w-full" icon="cancel">Batalkan rute</x-ui.confirm-form>@endcan
                    </div>
                </x-ui.card>
            @else
                @can('join', $trip)
                    <x-ui.card class="p-5 bg-gradient-to-b from-primary-fixed/30 to-white text-center">
                        <p class="font-sans font-bold text-lg mb-1">Titip ke {{ $trip->fulfiller->shortName() }}</p>
                        <p class="text-sm text-on-surface-variant mb-4">Biaya jasa {{ rupiah($trip->service_fee) }} per titipan. Bayar langsung ke relawan setelah bergabung.</p>
                        <x-ui.button :href="route('trips.join', $trip)" icon="add_shopping_cart" full>Ikut rute ini</x-ui.button>
                    </x-ui.card>
                @else
                    <x-ui.alert type="info" icon="info">
                        @if ($trip->isFull()) Slot rute ini sudah penuh.
                        @elseif (! $trip->isOpen()) Rute ini sudah tidak menerima titipan.
                        @elseif ($user->isSuspended()) Akunmu ditangguhkan.
                        @else Kamu tidak bisa bergabung ke rute ini. @endif
                    </x-ui.alert>
                @endcan
            @endif
        </aside>
    </div>
</x-layouts.app>
