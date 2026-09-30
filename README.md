# Nitip — Platform Mikro-Logistik Kampus

Nitip adalah aplikasi web *micro-errand* untuk ekosistem tertutup kampus. Mahasiswa yang butuh sesuatu
(**Penitip / Requester**) dipertemukan dengan mahasiswa yang sedang berada di lokasi pembelian atau searah
jalan (**Relawan / Fulfiller / Jastiper**). Dibangun dengan **Laravel 13, Blade, Tailwind CSS v4, Alpine.js,
dan MySQL** sesuai *Presentasi Proyek Nitip.pdf* dan referensi UI/UX Stitch *Campus Courier*.

Analisis requirement, pemetaan fitur, entitas, dan asumsi ada di
[`docs/ANALISIS_REQUIREMENT.md`](docs/ANALISIS_REQUIREMENT.md).

## Fitur

| Fitur dari PDF | Implementasi |
|---|---|
| Dual-Posting Stream (OFFER & REQUEST) | Penitip memposting **Permintaan** (`/requests/create`), relawan meng-*claim*. Relawan memposting **Rute** dengan kuota (`/trips/create`), penitip langsung ikut. Feed gabungan di menu **Nitip** (`/nitip`) dengan tab Permintaan / Rute. |
| Dua layanan utama | Kategori *Makanan Kantin Dalam*, *Makanan Luar Kampus*, *Print & Fotokopi Tugas* (dengan unggah berkas + spesifikasi cetak). |
| Skema biaya transparan (flat fee) | Rentang tarif per kategori dikelola admin, biaya jasa divalidasi server-side, relawan menerima 100 %. |
| Social collateral & direct pay | Registrasi wajib email kampus + NIM + OTP 6 digit. Pembayaran P2P ke GoPay/OVO/DANA/ShopeePay/bank/QRIS milik relawan, unggah resi transfer, relawan verifikasi. |
| Harga barang tidak pasti | Penitip mengisi **perkiraan** harga. Setelah membeli, relawan memasukkan **total sesuai struk** + foto struk saat mulai mengantar; sistem menghitung selisih (penitip menambah / relawan mengembalikan) yang diselesaikan saat serah terima. |
| Validasi bukti & nota | Foto struk kasir wajib, PIN 4 digit untuk menutup transaksi. |
| WhatsApp deep-link | Tombol `wa.me/{nomor}` dengan template berisi kode order dan nama. |
| Audit trail | Tabel `order_events` mencatat setiap transisi status secara permanen, dilihat admin di `/admin/audit-logs`. |
| Siklus hidup 5 tahap | Post & Match → Pay & Verify → In Progress → Delivering → Completed, dipaksa oleh `OrderWorkflowService` (status tidak bisa dilompati). |
| Kriteria sukses (completion rate, CSAT) | Rating dua arah setelah selesai, dashboard admin menampilkan completion rate dan CSAT. |
| Sengketa | Penitip/relawan membuka sengketa (dengan bukti), admin menyelesaikan (selesai / batal / batal + refund). |
| Notifikasi | Notifikasi in-app (database) untuk setiap perubahan status, pembayaran, ulasan, sengketa. |

## Role

- **Mahasiswa** (`student`) — satu akun bisa menjadi penitip dan relawan.
- **Admin** (`admin`) — dashboard statistik, manajemen pengguna (tangguhkan/verifikasi), pesanan, rute,
  sengketa, kategori & tarif, audit trail.

## Skema database (8 tabel aplikasi)

| Tabel | Isi |
|---|---|
| `users` | Akun mahasiswa/admin, kampus (kode), NIM, WhatsApp, **tujuan pembayaran** (`payment_type`, `payment_provider`, `payment_account_number`, `payment_account_name`, `payment_qris_path`), kolom OTP, status penangguhan. |
| `service_categories` | Tiga layanan PDF dengan rentang tarif (`fee_min`, `fee_default`, `fee_max`), flag berkas & biaya barang. |
| `trips` | Rute relawan: tujuan, waktu berangkat, batas terima titipan (otomatis 15 menit sebelum berangkat), kuota, biaya jasa, kategori yang diterima. |
| `orders` | Titipan (permintaan maupun dari rute): item (JSON), spesifikasi cetak (JSON), berkas, biaya jasa, perkiraan & biaya riil barang, struk, bukti transfer & status pembayaran, status siklus, PIN, stempel waktu tiap tahap. |
| `order_events` | Audit trail: siapa, kapan, dari status apa ke apa, keterangan. |
| `reviews` | Rating 1–5 dua arah per pesanan. |
| `disputes` | Sengketa per pesanan: alasan, kronologi, bukti, keputusan admin. |
| `notifications` | Notifikasi in-app bawaan Laravel. |

Tabel framework tambahan: `migrations`, `password_reset_tokens`. Daftar kampus, domain email kampus, dan
saran lokasi disimpan di `config/nitip.php` (bukan tabel) agar skema tetap ringkas.

## Requirements

- PHP ^8.3 dengan ekstensi `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd` (dipakai seeder untuk gambar demo)
- Composer 2
- MySQL 8 (atau MariaDB 10.6+)
- **Opsional:** Node.js 20+ dan npm. Hanya diperlukan jika kamu mengubah `resources/css/app.css` atau
  `resources/js/app.js`. Hasil build (Tailwind CSS + Alpine.js) sudah disertakan di `public/build`, jadi
  aplikasi bisa langsung dijalankan tanpa Node.

## Instalasi

```bash
git clone <repo> nitip && cd nitip

composer install
cp .env.example .env
php artisan key:generate
```

Buat database kosong, lalu sesuaikan `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nitip
DB_USERNAME=root
DB_PASSWORD=
```

```sql
CREATE DATABASE nitip CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Migrasi + seed data demo, tautkan storage, jalankan server:

```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve    # http://localhost:8000
```

Jika mengubah CSS/JS (butuh Node.js):

```bash
npm install
npm run build        # atau `npm run dev` untuk hot reload saat pengembangan
```

Kenapa ada Node/Vite? Tailwind v4 mengompilasi hanya class yang dipakai di Blade menjadi satu file CSS kecil,
dan Alpine.js (interaksi modal, toast, input OTP, baris item dinamis) dibundel ke satu file JS. Prosesnya
dilakukan sekali oleh Vite; browser hanya memuat hasil build di `public/build`.

### Konfigurasi environment penting

| Variabel | Keterangan |
|---|---|
| `APP_LOCALE=id`, `APP_TIMEZONE=Asia/Jakarta` | Bahasa dan zona waktu aplikasi. |
| `SESSION_DRIVER=file`, `CACHE_STORE=file` | Sesi dan cache di file, sehingga tidak butuh tabel tambahan. |
| `MAIL_MAILER=log` | Email OTP & reset password ditulis ke `storage/logs/laravel.log`. Ganti ke `smtp` + Mailpit (`127.0.0.1:1025`) untuk melihat email sungguhan. |
| `NITIP_OTP_BYPASS=true` | **Verifikasi dummy** (default saat development): 6 digit angka apa saja diterima di halaman verifikasi, jadi registrasi bisa langsung masuk tanpa email. Hanya berlaku saat `APP_ENV=local`. Set `false` saat email sudah dikonfigurasi. |
| `NITIP_DEV_SHOW_OTP=true` | Jika bypass `false`: kode OTP asli ditampilkan di halaman verifikasi agar alur bisa diuji tanpa mailbox. Hanya berlaku saat `APP_ENV=local`. |

### Storage

- `storage/app/private/orders/{id}/` — berkas cetak, bukti transfer, struk (privat, lewat `/orders/{kode}/files/{jenis}`)
- `storage/app/private/orders/{id}/dispute/` — bukti sengketa (privat)
- `storage/app/private/qris/{user}/` — gambar QRIS (privat)
- `storage/app/public/avatars/` — foto profil (publik, lewat `public/storage`)

## Akun demo (hasil `php artisan migrate --seed`)

Semua kata sandi: **`password`**

| Peran | Email | Catatan |
|---|---|---|
| Admin | `admin@nitip.test` | Panel admin `/admin` |
| Mahasiswa UPH | `willy@student.uph.edu` | Relawan aktif: punya rute terbuka, titipan berjalan, riwayat & rating |
| Mahasiswa UPH | `nadia@student.uph.edu` | Penitip aktif: ada order menunggu pembayaran & verifikasi |
| Mahasiswa UPH | `andhika@student.uph.edu`, `farhan@student.uph.edu`, `sisila@student.uph.edu`, `raka@student.uph.edu`, `maya@student.uph.edu`, `faris@student.uph.edu` | Berbagai status order |
| Mahasiswa UI | `sarah@ui.ac.id`, `dimas@ui.ac.id` | Kampus berbeda, satu sengketa terbuka |
| Belum verifikasi | `belumverif@student.uph.edu` | Untuk mencoba alur OTP |
| Ditangguhkan | `suspended@student.uph.edu` | Untuk melihat halaman akun ditangguhkan |

Seeder `DemoDataSeeder` mem-*replay* alur nyata lewat service layer, sehingga setiap order demo punya
event audit trail, bukti pembayaran, struk, notifikasi, dan ulasan yang konsisten (30 order di semua status,
6 rute, 2 sengketa).

## Testing

Test memakai SQLite in-memory (lihat `phpunit.xml`) dan `Storage::fake()`, jadi tidak menyentuh database MySQL.

```bash
php artisan test
```

Cakupan: registrasi + OTP (asli dan mode bypass) + login/logout + reset password, siklus order end-to-end
(request → claim → bayar → verifikasi → proses → harga riil & struk → antar → serah terima → PIN/konfirmasi →
ulasan), join rute & kuota & penutupan otomatis, pembatalan/pelepasan, sengketa & resolusi admin, otorisasi
lintas pengguna/role, akun ditangguhkan, metode pembayaran, serta smoke test yang merender setiap halaman
untuk guest, mahasiswa, dan admin.

Cek gaya kode: `vendor/bin/pint --test`.

## Struktur kode

```
app/
├── Enums/            OrderStatus, TripStatus, PaymentMethodType, DisputeReason, ...
├── Http/
│   ├── Controllers/  Auth, student area, Settings, Admin, OrderFileController (file privat)
│   ├── Middleware/   EnsureUserIsAdmin, EnsureUserIsActive, TouchLastSeen
│   └── Requests/     Form Request per aksi (validasi + otorisasi)
├── Models/           User, ServiceCategory, Trip, Order, OrderEvent, Review, Dispute
├── Policies/         OrderPolicy, TripPolicy, DisputePolicy
├── Services/         OrderService (posting/join), OrderWorkflowService (transisi status), PaymentService,
│                     TripService, DisputeService, ReviewService, AccountService, RegistrationService,
│                     EmailVerificationService (OTP), FileStorageService
└── Support/          Campuses (config), Money, Phone (wa.me), Codes
resources/views/
├── components/       layouts (base/guest/app/admin), ui/* (button, input, modal, confirm-form, stepper, ...), cards/*
├── auth/ pages/ orders/ trips/ settings/ admin/ errors/
lang/id/              Pesan validasi & auth berbahasa Indonesia
database/             migrations (8), factories, seeders (ServiceCategory, User, DemoData)
docs/                 Analisis requirement & arsitektur
```

## Keamanan

CSRF di semua form, mass-assignment protection (`#[Fillable]`), validasi Form Request, Policy per resource,
middleware `auth` / `verified` / `active` / `admin`, rate limiting (login 5x/menit per email+IP, OTP, reset
password), file privat disajikan lewat controller yang diotorisasi, nama file diacak (UUID), validasi
MIME/ukuran, password di-hash (bcrypt), tidak ada kredensial di kode.
