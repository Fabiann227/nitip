<x-layouts.app title="Pengaturan Profil">
    <x-ui.page-header title="Pengaturan" subtitle="Kelola identitas, kontak, dan cara penitip membayar kamu." />
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-1">@include('settings.partials.nav')</div>
        <div class="lg:col-span-3 space-y-6">
            <x-ui.card title="Profil" subtitle="Nama sesuai KTM, NIM dan kampus tidak bisa diubah sendiri." icon="person">
                <form method="POST" action="{{ route('settings.profile.update') }}" enctype="multipart/form-data" class="space-y-5" novalidate>
                    @csrf @method('PUT')
                    <div class="flex items-center gap-4">
                        <x-ui.avatar :user="$user" size="xl" />
                        <div class="flex-1 space-y-2">
                            <x-ui.file-input name="avatar" accept="image/*" :max-mb="2" hint="JPG/PNG/WebP maks 2 MB" icon="add_a_photo" />
                            @if ($user->avatar_path)
                                <x-ui.checkbox name="remove_avatar">Hapus foto profil</x-ui.checkbox>
                            @endif
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-ui.input name="name" label="Nama lengkap" :value="$user->name" required label-icon="person" />
                        <x-ui.input name="whatsapp_number" type="tel" label="Nomor WhatsApp" :value="$user->whatsapp_number ? '0'.substr($user->whatsapp_number, 2) : ''" placeholder="08xxxxxxxxxx" label-icon="chat" hint="Dipakai untuk tombol chat wa.me di pesanan." />
                        <x-ui.input name="email_display" label="Email kampus" :value="$user->email" disabled label-icon="mail" />
                        <x-ui.input name="nim_display" label="NIM / Kampus" :value="($user->nim ?? '-').' · '.$user->campusName()" disabled label-icon="badge" />
                    </div>
                    <x-ui.textarea name="bio" label="Bio singkat" optional :value="$user->bio" rows="2" placeholder="Contoh: Sering di perpus, bisa bantu titip kopi!" maxlength="500" />
                    <div class="flex justify-end"><x-ui.button type="submit" icon="save">Simpan perubahan</x-ui.button></div>
                </form>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
