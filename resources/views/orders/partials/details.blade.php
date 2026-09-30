{{-- Order details: locations, items, print spec, notes, document, receipt. Expects $order. --}}
<div class="space-y-5">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="p-3 rounded-xl bg-surface-low">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-muted mb-1">{{ $order->category->isPrint() ? 'Cetak di' : 'Beli di' }}</p>
            <p class="text-sm font-semibold text-on-surface flex items-start gap-1.5"><span class="material-symbols-outlined text-[18px] text-primary shrink-0">storefront</span>{{ $order->pickup_location }}</p>
        </div>
        <div class="p-3 rounded-xl bg-surface-low">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-muted mb-1">Antar ke</p>
            <p class="text-sm font-semibold text-on-surface flex items-start gap-1.5"><span class="material-symbols-outlined text-[18px] text-primary shrink-0">pin_drop</span>{{ $order->dropoff_location }}</p>
        </div>
        <div class="p-3 rounded-xl bg-surface-low">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-muted mb-1">Dibutuhkan</p>
            <p class="text-sm font-semibold text-on-surface flex items-start gap-1.5"><span class="material-symbols-outlined text-[18px] text-primary shrink-0">schedule</span>
                @if ($order->needed_by){{ $order->needed_by->translatedFormat('d M, H:i') }} <span class="text-xs font-normal text-muted">({{ $order->needed_by->diffForHumans() }})</span>@else Fleksibel @endif
            </p>
        </div>
    </div>

    @if ($order->hasItems())
        <div>
            <h3 class="text-sm font-semibold text-on-surface mb-2">Daftar item <span class="text-xs font-normal text-muted">(harga perkiraan penitip)</span></h3>
            <div class="overflow-x-auto rounded-xl border border-border">
                <table class="table">
                    <thead><tr><th>Item</th><th class="text-center">Qty</th><th class="text-right">Perkiraan</th><th class="text-right">Subtotal</th></tr></thead>
                    <tbody>
                        @foreach ($order->itemList() as $item)
                            <tr>
                                <td><span class="font-medium">{{ $item['name'] }}</span>@if (! empty($item['note']))<span class="block text-xs text-muted">{{ $item['note'] }}</span>@endif</td>
                                <td class="text-center tabular">{{ $item['quantity'] }}</td>
                                <td class="text-right tabular">{{ isset($item['estimated_price']) ? rupiah($item['estimated_price']) : '-' }}</td>
                                <td class="text-right tabular font-semibold">{{ isset($item['estimated_price']) ? rupiah($item['quantity'] * $item['estimated_price']) : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($order->hasPrintSpec())
        <div>
            <h3 class="text-sm font-semibold text-on-surface mb-2">Spesifikasi cetak</h3>
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 text-sm">
                @foreach ([['Halaman', $order->print_spec['pages'] ?? 1], ['Rangkap', $order->print_spec['copies'] ?? 1], ['Warna', ! empty($order->print_spec['is_color']) ? 'Berwarna' : 'Hitam putih'], ['Kertas', $order->print_spec['paper_size'] ?? 'A4'], ['Jilid', $order->printBinding()?->label() ?? 'Tanpa jilid']] as [$label, $value])
                    <div class="p-2.5 rounded-xl bg-surface-low"><p class="text-[11px] text-muted">{{ $label }}</p><p class="font-semibold">{{ $value }}</p></div>
                @endforeach
            </div>
            @if (! empty($order->print_spec['instructions']))<p class="text-sm text-on-surface-variant mt-2"><span class="font-semibold text-on-surface">Instruksi:</span> {{ $order->print_spec['instructions'] }}</p>@endif
        </div>
    @endif

    @if ($order->notes)
        <div class="p-3 rounded-xl bg-warning-container/60 border border-warning-border text-sm text-warning"><span class="font-semibold">Catatan penitip:</span> {{ $order->notes }}</div>
    @endif

    @if ($order->hasDocument() || $order->hasReceipt())
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @if ($order->hasDocument())
                <div>
                    <h3 class="text-sm font-semibold text-on-surface mb-2">Berkas cetak</h3>
                    <a href="{{ $order->fileUrl('document') }}" target="_blank" rel="noopener" class="flex items-center gap-3 p-2.5 rounded-xl border border-border hover:bg-surface-low"><span class="material-symbols-outlined text-primary">description</span><span class="min-w-0 flex-1"><span class="block text-sm font-medium truncate">{{ $order->document_name }}</span><span class="block text-xs text-muted">Buka / unduh</span></span><span class="material-symbols-outlined text-outline">open_in_new</span></a>
                </div>
            @endif
            @if ($order->hasReceipt())
                <div>
                    <h3 class="text-sm font-semibold text-on-surface mb-2">Foto struk kasir</h3>
                    <a href="{{ $order->fileUrl('receipt') }}" target="_blank" rel="noopener" class="block rounded-xl overflow-hidden border border-border hover:shadow-float"><img src="{{ $order->fileUrl('receipt') }}" alt="Struk" class="w-full h-40 object-cover" loading="lazy"><span class="block p-2 text-xs text-muted">Biaya riil {{ rupiah($order->actual_item_cost ?? 0) }} &bull; klik untuk memperbesar</span></a>
                </div>
            @endif
        </div>
    @endif
</div>
