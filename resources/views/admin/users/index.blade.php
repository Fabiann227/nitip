<x-layouts.admin title="Pengguna">
    <form method="GET" class="card p-4 mb-4 grid grid-cols-1 md:grid-cols-4 gap-3">
        <div class="md:col-span-2 relative"><span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-placeholder text-[20px]">search</span><input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Cari nama, email, NIM" class="input input-sm pl-10"></div>
        <select name="status" class="input input-sm">
            <option value="">Semua status</option>
            @foreach (['active' => 'Aktif & terverifikasi', 'unverified' => 'Belum verifikasi', 'suspended' => 'Ditangguhkan', 'admin' => 'Admin'] as $k => $label)<option value="{{ $k }}" @selected($filters['status'] === $k)>{{ $label }}</option>@endforeach
        </select>
        <div class="flex gap-2">
            <select name="campus" class="input input-sm"><option value="">Semua kampus</option>@foreach ($campuses as $code => $campus)<option value="{{ $code }}" @selected($filters['campus'] === $code)>{{ $code }}</option>@endforeach</select>
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
        </div>
    </form>

    <x-ui.card :padding="false">
        @if ($users->isEmpty())
            <x-ui.empty-state icon="person_search" title="Tidak ada pengguna yang cocok" description="Ubah kata kunci atau filter." />
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>Pengguna</th><th>Kampus / NIM</th><th>Status</th><th>Pengantaran</th><th>Rating</th><th>Bergabung</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($users as $u)
                            <tr>
                                <td><div class="flex items-center gap-2"><x-ui.avatar :user="$u" size="sm" /><div><p class="font-semibold">{{ $u->name }}</p><p class="text-xs text-muted">{{ $u->email }}</p></div></div></td>
                                <td class="text-xs">{{ $u->campus ?? '-' }}<br><span class="text-muted">{{ $u->nim ?? '-' }}</span></td>
                                <td><div class="flex flex-wrap gap-1">@if ($u->isAdmin())<x-ui.badge variant="primary" class="h-5 px-2 text-[10px]">Admin</x-ui.badge>@endif @if ($u->isSuspended())<x-ui.badge variant="danger" class="h-5 px-2 text-[10px]">Ditangguhkan</x-ui.badge>@elseif (! $u->hasVerifiedEmail())<x-ui.badge variant="warning" class="h-5 px-2 text-[10px]">Belum verif</x-ui.badge>@else<x-ui.badge variant="success" class="h-5 px-2 text-[10px]">Aktif</x-ui.badge>@endif</div></td>
                                <td class="tabular">{{ $u->completedDeliveriesCount() }}</td>
                                <td><x-ui.rating :value="$u->ratingAverage()" :show-empty="false" /></td>
                                <td class="text-xs text-muted whitespace-nowrap">{{ $u->created_at->translatedFormat('d M Y') }}</td>
                                <td class="text-right"><a href="{{ route('admin.users.show', $u) }}" class="btn btn-xs btn-outline">Detail</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 pb-4">{{ $users->links() }}</div>
        @endif
    </x-ui.card>
</x-layouts.admin>
