@php
    use App\Enums\OrderStatus;
    $actions = [];
@endphp
@can('claim', $order) @php $actions[] = 'claim'; @endphp @endcan
@can('release', $order) @php $actions[] = 'release'; @endphp @endcan
@can('verifyPayment', $order) @php $actions[] = 'verify'; @endphp @endcan
@can('start', $order) @php $actions[] = 'start'; @endphp @endcan
@can('deliver', $order) @php $actions[] = 'deliver'; @endphp @endcan
@can('markDelivered', $order) @php $actions[] = 'delivered'; @endphp @endcan
@can('complete', $order) @php $actions[] = 'complete'; @endphp @endcan
@can('cancel', $order) @php $actions[] = 'cancel'; @endphp @endcan

@if (count($actions) > 0)
    <x-ui.card class="p-5 border-primary/30 bg-gradient-to-br from-white to-surface-low">
        <div class="flex items-center gap-2 mb-3">
            <span class="material-symbols-outlined text-primary">bolt</span>
            <h2 class="font-sans font-bold text-base">
                @if ($isRequester) Aksi penitip @elseif ($isFulfiller) Aksi relawan @else Ambil titipan ini @endif
            </h2>
        </div>

        @if (in_array('claim', $actions))
            <p class="text-sm text-on-surface-variant mb-3">Kamu akan menjadi relawan untuk titipan ini. Penitip akan mentransfer <strong>{{ rupiah($order->estimatedTotal()) }}</strong> (jasa + perkiraan barang) ke metode pembayaranmu, lalu kamu membeli/mencetak dan mengantar ke <strong>{{ $order->dropoff_location }}</strong>. Harga riil mengikuti struk.</p>
            @if (! $user->hasPaymentMethod())
                <x-ui.alert type="warning" class="mb-3">Atur metode pembayaran dulu di <a href="{{ route('settings.payment') }}" class="font-semibold underline">pengaturan</a> agar penitip bisa membayar.</x-ui.alert>
            @endif
            <x-ui.confirm-form :action="route('orders.claim', $order)" title="Ambil titipan ini?" :message="'Pastikan kamu memang searah ke '.$order->pickup_location.' dan bisa mengantar ke '.$order->dropoff_location.($order->needed_by ? ' sebelum '.$order->needed_by->translatedFormat('H:i') : '').'.'" confirm="Ya, saya ambil" button-class="btn btn-primary w-full sm:w-auto" icon="handshake">Ambil titipan (+{{ rupiah($order->service_fee) }})</x-ui.confirm-form>
        @endif

        <div class="flex flex-wrap gap-2">
            @if (in_array('verify', $actions))
                <div class="w-full mb-2 p-3 rounded-xl bg-white border border-border flex items-center gap-3">
                    <a href="{{ $order->fileUrl('proof') }}" target="_blank"><img src="{{ $order->fileUrl('proof') }}" alt="Bukti" class="w-16 h-16 rounded-lg object-cover border border-border"></a>
                    <div class="text-sm min-w-0 flex-1"><p class="font-semibold">Cek mutasi masuk {{ rupiah($order->estimatedTotal()) }}</p><p class="text-xs text-on-surface-variant">ke {{ $order->payment_method }} &bull; {{ $order->payment_submitted_at?->diffForHumans() }}</p>@if($order->payment_note)<p class="text-xs text-muted">Catatan: {{ $order->payment_note }}</p>@endif</div>
                    <a href="{{ $order->fileUrl('proof') }}" target="_blank" class="btn btn-xs btn-outline">Lihat</a>
                </div>
                <x-ui.confirm-form :action="route('orders.payment.verify', $order)" title="Konfirmasi dana sudah masuk?" message="Verifikasi hanya jika saldo/mutasi kamu benar-benar bertambah sesuai jumlah. Setelah ini pesanan berstatus dibayar." confirm="Ya, dana masuk" button-class="btn btn-primary" icon="verified">Verifikasi pembayaran</x-ui.confirm-form>
                <x-ui.confirm-form :action="route('orders.payment.reject', $order)" title="Tolak bukti pembayaran" message="Penitip akan diminta mengunggah ulang bukti yang benar." confirm="Tolak bukti" variant="danger" reason reason-label="Alasan penolakan" reason-placeholder="Contoh: nominal tidak sesuai / mutasi belum masuk" button-class="btn btn-danger-soft" icon="block">Tolak</x-ui.confirm-form>
            @endif

            @if (in_array('start', $actions))
                <form method="POST" action="{{ route('orders.start', $order) }}">@csrf<x-ui.button type="submit" icon="shopping_cart_checkout">{{ $order->category->isPrint() ? 'Mulai mencetak' : 'Mulai membeli' }}</x-ui.button></form>
            @endif

            @if (in_array('deliver', $actions))
                <button type="button" class="btn btn-primary" x-on:click="$dispatch('open-modal', 'deliver')"><span class="material-symbols-outlined text-[18px]">directions_walk</span>Sudah dibeli, antar sekarang</button>
                <x-ui.modal name="deliver" title="Catat harga riil & antar" :open-on-error="$errors->hasAny(['actual_item_cost', 'receipt'])">
                    <form method="POST" action="{{ route('orders.deliver', $order) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <p class="text-sm text-on-surface-variant">Masukkan total sesuai struk kasir dan fotonya. Penitip langsung melihat selisih dari perkiraan <strong>{{ rupiah($order->estimated_item_cost) }}</strong>, lalu diselesaikan saat serah terima.</p>
                        @if ($order->category->has_item_cost)
                            <x-ui.input name="actual_item_cost" type="number" label="Total sesuai struk" prefix="Rp" :value="old('actual_item_cost', $order->estimated_item_cost)" min="0" step="500" required />
                            <x-ui.file-input name="receipt" label="Foto struk kasir" accept="image/*" :max-mb="4" :required="! $order->hasReceipt()" icon="receipt_long" hint="JPG/PNG maksimal 4 MB" />
                        @endif
                        <div class="flex justify-end gap-2"><button type="button" class="btn btn-outline" x-on:click="$dispatch('close-modal', 'deliver')">Batal</button><button type="submit" class="btn btn-primary">Simpan &amp; mulai antar</button></div>
                    </form>
                </x-ui.modal>
            @endif

            @if (in_array('delivered', $actions))
                <button type="button" class="btn btn-primary" x-on:click="$dispatch('open-modal', 'delivered')"><span class="material-symbols-outlined text-[18px]">handshake</span>Tandai sudah diserahkan</button>
                <x-ui.modal name="delivered" title="Serah terima" :open-on-error="$errors->hasAny(['note']) || ($errors->has('receipt') && $order->status === OrderStatus::Delivering)">
                    <form method="POST" action="{{ route('orders.delivered', $order) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        @php $diff = $order->settlementDifference(); @endphp
                        <p class="text-sm text-on-surface-variant">
                            @if ($diff !== null && $diff > 0) Ingatkan penitip menambah <strong>{{ rupiah($diff) }}</strong> (selisih nota).
                            @elseif ($diff !== null && $diff < 0) Kembalikan <strong>{{ rupiah(abs($diff)) }}</strong> ke penitip (selisih nota).
                            @else Biaya barang sesuai perkiraan, tidak ada selisih. @endif
                            Setelah itu tandai serah terima dan minta penitip mengonfirmasi atau menyebutkan PIN.
                        </p>
                        @if ($order->category->has_item_cost)
                            <x-ui.file-input name="receipt" :label="$order->hasReceipt() ? 'Ganti foto struk (opsional)' : 'Foto struk kasir'" accept="image/*" :max-mb="4" :required="! $order->hasReceipt()" icon="receipt_long" />
                        @endif
                        <x-ui.input name="note" label="Catatan" optional placeholder="Contoh: diserahkan ke teman sekelas" maxlength="255" />
                        <div class="flex justify-end gap-2"><button type="button" class="btn btn-outline" x-on:click="$dispatch('close-modal', 'delivered')">Batal</button><button type="submit" class="btn btn-primary">Simpan serah terima</button></div>
                    </form>
                </x-ui.modal>
            @endif

            @if (in_array('complete', $actions))
                @if ($isRequester)
                    <x-ui.confirm-form :action="route('orders.complete', $order)" title="Konfirmasi pesanan diterima?" message="Pastikan barang/hasil cetak sudah di tanganmu dan selisih nota sudah diselesaikan. Setelah ini transaksi ditutup." confirm="Ya, sudah saya terima" button-class="btn btn-primary" icon="task_alt">Konfirmasi diterima &amp; selesaikan</x-ui.confirm-form>
                @else
                    <button type="button" class="btn btn-secondary" x-on:click="$dispatch('open-modal', 'complete-pin')"><span class="material-symbols-outlined text-[18px]">pin</span>Selesaikan dengan PIN</button>
                    <x-ui.modal name="complete-pin" title="Masukkan PIN penitip" max-width="sm" :open-on-error="$errors->has('pin')">
                        <form method="POST" action="{{ route('orders.complete', $order) }}" class="space-y-5">
                            @csrf
                            <p class="text-sm text-on-surface-variant text-center">Minta penitip menyebutkan PIN 4 digit saat barang diserahkan.</p>
                            <x-ui.code-input :length="4" name="digits" box-class="w-[52px] h-14 text-2xl" :error="$errors->first('pin')" :autofocus="false" />
                            <button type="submit" class="btn btn-primary w-full">Tutup transaksi</button>
                        </form>
                    </x-ui.modal>
                @endif
            @endif

            @if (in_array('release', $actions))
                <x-ui.confirm-form :action="route('orders.release', $order)" title="Lepas titipan ini?" message="Permintaan akan kembali tayang untuk relawan lain. Gunakan hanya jika kamu benar-benar tidak bisa membantu." confirm="Lepas titipan" variant="danger" reason reason-label="Alasan (dikirim ke penitip)" button-class="btn btn-outline" icon="undo">Lepas titipan</x-ui.confirm-form>
            @endif

            @if (in_array('cancel', $actions))
                <x-ui.confirm-form :action="route('orders.cancel', $order)" title="Batalkan pesanan?" :message="in_array($order->status, [OrderStatus::Paid, OrderStatus::InProgress], true) ? 'Pembayaran sudah diverifikasi. Membatalkan sekarang berarti kamu WAJIB mengembalikan dana ke penitip dan tercatat di audit trail.' : 'Pesanan akan dibatalkan dan pihak lain diberi tahu.'" confirm="Batalkan pesanan" variant="danger" reason reason-label="Alasan pembatalan" button-class="btn btn-ghost text-error" icon="cancel">Batalkan</x-ui.confirm-form>
            @endif
        </div>
    </x-ui.card>
@elseif ($isRequester && $order->status === OrderStatus::PaymentSubmitted)
    <x-ui.alert type="info" icon="hourglass_top">Bukti transfermu sedang menunggu verifikasi relawan. Kamu bisa mengingatkan lewat WhatsApp.</x-ui.alert>
@elseif ($isRequester && in_array($order->status, [OrderStatus::Paid, OrderStatus::InProgress, OrderStatus::Delivering], true))
    <x-ui.alert type="info" icon="hourglass_top">Relawan sedang bekerja. Siapkan PIN serah terima di panel samping saat pesanan tiba.</x-ui.alert>
@endif
