@php
    use App\Enums\OrderStatus;
    $user = auth()->user();
    $status = $order->status;
    $isRequester = $role === 'requester';
    $isFulfiller = $role === 'fulfiller';
@endphp
<x-layouts.app :title="'Pesanan '.$order->code" wide>
    <x-ui.page-header :back="$role ? route('orders.index', ['role' => $role]) : route('explore')" :back-label="$role ? 'Pesanan saya' : 'Nitip'" :eyebrow="'#'.$order->code.' · '.$order->category->name.' · '.($order->isFromTrip() ? 'Lewat rute relawan' : 'Permintaan')" :title="$order->title" :subtitle="'Dibuat '.$order->created_at->diffForHumans().' oleh '.$order->requester->shortName()">
        <x-ui.status-badge :status="$status" class="h-8 px-3 text-sm" />
    </x-ui.page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-ui.card class="p-5">
                <x-ui.stepper :status="$status" class="mb-5" />
                @if ($status === OrderStatus::Cancelled)
                    <x-ui.alert type="error" icon="cancel" title="Pesanan dibatalkan">
                        {{ $order->cancel_reason }} <span class="block text-xs mt-1">Oleh {{ $order->cancelledBy?->shortName() ?? 'sistem' }} &bull; {{ $order->cancelled_at?->translatedFormat('d M Y H:i') }}</span>
                        @if ($order->needs_refund)<span class="block mt-2 font-semibold">Relawan wajib mengembalikan dana yang sudah diterima ke penitip.</span>@endif
                    </x-ui.alert>
                @elseif ($status === OrderStatus::Disputed)
                    <x-ui.alert type="warning" icon="gavel" title="Sengketa sedang ditinjau admin">
                        {{ $order->dispute?->reason->label() }} &mdash; dibuka oleh {{ $order->dispute?->openedBy?->shortName() }}. <a href="{{ route('disputes.show', $order->dispute) }}" class="font-semibold underline">Lihat detail sengketa</a>
                    </x-ui.alert>
                @elseif ($order->isExpired())
                    <x-ui.alert type="warning" icon="timer_off" title="Batas waktu terlewat">Permintaan ini sudah melewati waktu yang dibutuhkan dan tidak lagi tampil di feed.</x-ui.alert>
                @else
                    <x-ui.alert :type="$status === OrderStatus::Completed ? 'success' : 'info'" :icon="$status->icon()" :title="$status->label()">{{ $status->description() }}</x-ui.alert>
                @endif
            </x-ui.card>

            @include('orders.partials.actions')

            <x-ui.card title="Detail titipan" icon="inventory_2">
                @include('orders.partials.details')
            </x-ui.card>

            <x-ui.card title="Pembayaran" subtitle="Direct P2P: penitip transfer langsung ke relawan" icon="payments">
                @include('orders.partials.payment-summary')
                @can('pay', $order)
                    @include('orders.partials.payment-form')
                @endcan
            </x-ui.card>

            <x-ui.card title="Riwayat aktivitas" subtitle="Audit trail permanen" icon="history">
                <x-ui.timeline :events="$order->events" />
            </x-ui.card>
        </div>

        <aside class="space-y-6">
            <x-ui.card title="Pihak terkait" icon="group">
                @include('orders.partials.parties')
            </x-ui.card>

            @can('viewPin', $order)
                @if (in_array($status, [OrderStatus::Paid, OrderStatus::InProgress, OrderStatus::Delivering, OrderStatus::Delivered], true))
                    <x-ui.card class="p-5 text-center bg-gradient-to-b from-primary-fixed/30 to-white">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-primary mb-1">PIN serah terima</p>
                        <p class="font-sans font-extrabold text-4xl tracking-[0.3em] text-on-surface tabular">{{ $order->completion_pin }}</p>
                        <p class="text-xs text-on-surface-variant mt-2">Sebutkan PIN ini ke relawan <strong>hanya saat barang sudah di tanganmu</strong>. Relawan memasukkan PIN untuk menutup transaksi.</p>
                    </x-ui.card>
                @endif
            @endcan

            @if ($status === OrderStatus::Completed && $role)
                <x-ui.card title="Ulasan" icon="star">
                    @if ($myReview)
                        <div class="mb-3">
                            <p class="text-xs text-muted mb-1">Ulasanmu untuk {{ $counterpart?->shortName() }}</p>
                            <x-ui.rating :value="$myReview->rating" size="md" />
                            @if ($myReview->comment)<p class="text-sm text-on-surface-variant mt-1">"{{ $myReview->comment }}"</p>@endif
                        </div>
                    @elseif ($counterpart)
                        @can('review', $order)
                            <form method="POST" action="{{ route('orders.reviews.store', $order) }}" class="space-y-3">
                                @csrf
                                <p class="text-sm text-on-surface-variant">Bagaimana pengalamanmu dengan {{ $counterpart->shortName() }}?</p>
                                <x-ui.star-picker name="rating" :value="5" />
                                <x-ui.field-error name="rating" />
                                <x-ui.textarea name="comment" rows="2" placeholder="Ceritakan singkat (opsional)" maxlength="500" />
                                <x-ui.button type="submit" size="sm" icon="send" full>Kirim ulasan</x-ui.button>
                            </form>
                        @endcan
                    @endif
                    @if ($theirReview)
                        <div class="pt-3 mt-3 border-t border-border">
                            <p class="text-xs text-muted mb-1">Ulasan {{ $counterpart?->shortName() }} untukmu</p>
                            <x-ui.rating :value="$theirReview->rating" size="md" />
                            @if ($theirReview->comment)<p class="text-sm text-on-surface-variant mt-1">"{{ $theirReview->comment }}"</p>@endif
                        </div>
                    @endif
                </x-ui.card>
            @endif

            @if ($order->dispute)
                <x-ui.card title="Sengketa" icon="gavel">
                    <div class="flex items-center justify-between mb-2"><span class="text-sm font-semibold">{{ $order->dispute->reason->label() }}</span><x-ui.status-badge :status="$order->dispute->status" /></div>
                    <p class="text-xs text-on-surface-variant">Dibuka oleh {{ $order->dispute->openedBy?->shortName() }} {{ $order->dispute->created_at->diffForHumans() }}</p>
                    @if ($order->dispute->resolution)<p class="text-xs text-on-surface mt-2"><span class="font-semibold">Keputusan:</span> {{ $order->dispute->resolution->shortLabel() }}</p>@endif
                    <a href="{{ route('disputes.show', $order->dispute) }}" class="btn btn-sm btn-outline w-full mt-3">Lihat sengketa</a>
                </x-ui.card>
            @elseif ($role)
                @can('dispute', $order)
                    <x-ui.card class="p-4">
                        <p class="text-sm font-semibold text-on-surface mb-1">Ada masalah?</p>
                        <p class="text-xs text-on-surface-variant mb-3">Selesaikan dulu lewat WhatsApp. Jika buntu, buka sengketa agar admin Nitip menengahi berdasarkan audit trail.</p>
                        <button type="button" class="btn btn-sm btn-danger-soft w-full" x-on:click="$dispatch('open-modal', 'dispute')"><span class="material-symbols-outlined text-[18px]">gavel</span>Buka sengketa</button>
                    </x-ui.card>
                    <x-ui.modal name="dispute" title="Buka sengketa" :open-on-error="$errors->hasAny(['reason', 'description', 'evidence'])">
                        <form method="POST" action="{{ route('orders.disputes.store', $order) }}" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <x-ui.select name="reason" label="Jenis masalah" required>
                                @foreach (\App\Enums\DisputeReason::cases() as $reason)<option value="{{ $reason->value }}" @selected(old('reason') === $reason->value)>{{ $reason->label() }}</option>@endforeach
                            </x-ui.select>
                            <x-ui.textarea name="description" label="Kronologi" rows="4" required placeholder="Jelaskan apa yang terjadi, minimal 20 karakter" />
                            <x-ui.file-input name="evidence" label="Bukti (foto)" optional accept="image/*" :max-mb="4" icon="add_a_photo" />
                            <div class="flex justify-end gap-2"><button type="button" class="btn btn-outline" x-on:click="$dispatch('close-modal', 'dispute')">Batal</button><button type="submit" class="btn btn-danger">Kirim sengketa</button></div>
                        </form>
                    </x-ui.modal>
                @endcan
            @endif
        </aside>
    </div>
</x-layouts.app>
