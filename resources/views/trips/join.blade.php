<x-layouts.app :title="'Titip ke '.$trip->fulfiller->shortName()">
    <x-ui.page-header :back="route('trips.show', $trip)" back-label="Detail rute" :eyebrow="'Rute #'.$trip->code.' · '.$trip->routeLabel()" :title="'Titip ke '.$trip->fulfiller->shortName()" :subtitle="'Berangkat '.$trip->departure_at->translatedFormat('l, d M H:i').' · '.$trip->remainingSlots().' slot tersisa'" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <form method="POST" action="{{ route('trips.join.store', $trip) }}" enctype="multipart/form-data" class="lg:col-span-2 card p-5 sm:p-6" novalidate>
            @csrf
            @include('orders.partials.item-form')
            <div class="mt-8 pt-5 border-t border-border flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-2">
                <x-ui.button :href="route('trips.show', $trip)" variant="outline">Batal</x-ui.button>
                <x-ui.button type="submit" icon-right="add_shopping_cart">Kirim titipan ke {{ $trip->fulfiller->shortName() }}</x-ui.button>
            </div>
        </form>
        <aside class="space-y-4">
            <x-ui.card title="Relawan" icon="directions_walk">
                <x-ui.user-chip :user="$trip->fulfiller" size="md" :subtitle="$trip->fulfiller->campusName()" />
                <x-ui.rating :value="$trip->fulfiller->ratingAverage()" :count="$trip->fulfiller->ratingCount()" class="mt-2" />
                <dl class="text-sm space-y-1.5 mt-3 pt-3 border-t border-border">
                    <div class="flex justify-between"><dt class="text-muted">Biaya jasa</dt><dd class="font-bold text-primary tabular">{{ rupiah($trip->service_fee) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-muted">Tujuan</dt><dd class="font-semibold text-right">{{ $trip->destination }}</dd></div>
                    <div class="flex justify-between"><dt class="text-muted">Berangkat</dt><dd class="font-semibold">{{ $trip->departure_at->translatedFormat('H:i') }}</dd></div>
                </dl>
            </x-ui.card>
            <x-ui.alert type="info" icon="payments">Setelah bergabung, kamu langsung diminta transfer biaya jasa + perkiraan barang ke relawan dan mengunggah bukti.</x-ui.alert>
        </aside>
    </div>
</x-layouts.app>
