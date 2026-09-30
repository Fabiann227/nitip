# Analisis Requirement & Arsitektur — Nitip

Dokumen ini memetakan requirement dari **Presentasi Proyek Nitip.pdf** dan referensi UI/UX
**stitch_nitip_campus_logistics_platform** ke implementasi Laravel.

## 1. Ringkasan Produk (dari PDF)

Nitip adalah platform *micro-errand* berbasis web (Laravel + MySQL) untuk ekosistem tertutup kampus.
Menghubungkan mahasiswa yang membutuhkan barang (**Penitip / Requester**) dengan mahasiswa yang sedang
berada di lokasi pembelian atau searah jalan (**Relawan / Fulfiller / Jastiper**).

Dua layanan utama:

| Kode kategori     | Layanan                               | Struktur biaya                | Tarif rekomendasi   |
|-------------------|---------------------------------------|-------------------------------|---------------------|
| `food_in_campus`  | Makanan & minuman kantin dalam kampus | Flat per order                | Rp 3.000 - Rp 4.000 |
| `food_off_campus` | Makanan luar kampus (sekitar gerbang) | Flat per order                | Rp 5.000 - Rp 7.000 |
| `print_copy`      | Print & fotokopi tugas                | Flat jasa + biaya riil kertas | Rp 3.000 - Rp 5.000 |

Fitur inti:

1. **Dual-Posting Stream** - jalur OFFER (relawan posting rute + kuota; penitip langsung ikut) dan jalur
   REQUEST (penitip posting kebutuhan; relawan *claim job*).
2. **Skema biaya transparan (flat fee)** - kalkulasi otomatis, dapat disesuaikan dalam rentang kategori.
3. **Social collateral & direct pay** - registrasi wajib email kampus + NIM; dana ditransfer langsung
   penitip ke relawan (GoPay/BCA/QRIS, tanpa saldo tertahan); penitip wajib unggah resi transfer,
   relawan wajib unggah foto struk kasir.
4. **Komunikasi cepat & log sistem** - WhatsApp deep-link `wa.me/{nomor}` dengan template berisi ID order;
   audit trail permanen di MySQL sebagai rekaman legal untuk sanggahan, pembatalan, dan rekonsiliasi selisih nota.
5. **Siklus hidup transaksi** - Post & Match, Pay & Verify, In Progress, Delivering, Completed.

Kriteria sukses: completion rate 85 %, CSAT 4.0/5 (butuh fitur rating), 0 biaya admin pihak ketiga.

## 2. Role Pengguna

| Role      | Deskripsi | Sumber |
|-----------|-----------|--------|
| `student` | Mahasiswa terverifikasi. **Satu akun dapat berperan sebagai Penitip sekaligus Relawan** (PDF: "Siap Nitip & Dianterin"). | PDF, Stitch |
| `admin`   | Operator platform: memantau order, menyelesaikan sengketa, mengelola user, kategori/tarif, audit trail. | PDF (implisit), Stitch footer "Dispute Resolution" |

Asumsi terdokumentasi: PDF tidak menyebut admin secara eksplisit, tetapi proses sengketa dan rekonsiliasi
membutuhkan pihak penengah.

## 3. Skema Database (disederhanakan: 8 tabel aplikasi)

```
users 1--n trips (fulfiller)          users 1--n orders (requester)
users 1--n orders (fulfiller, null)   service_categories 1--n trips
service_categories 1--n orders        trips 1--n orders (nullable)
orders 1--n order_events              orders 1--n reviews (maks 2)
orders 1--1 disputes                  users 1--n notifications (Laravel)
```

| Tabel | Kolom penting |
|---|---|
| `users` | role, name, email, nim, campus (kode dari config), whatsapp_number, `payment_type/provider/account_number/account_name/qris_path` (tujuan transfer, satu per akun), `otp_code_hash/expires_at/attempts/sent_at`, is_suspended |
| `service_categories` | code, name, fee_min/default/max, requires_document, has_item_cost, icon |
| `trips` | code, fulfiller_id, service_category_id, campus, destination, waypoints, departure_at, closes_at (otomatis), transport_mode, max_slots, service_fee, status |
| `orders` | code, requester_id, fulfiller_id, trip_id, service_category_id, campus, title, pickup/dropoff, needed_by, `items` (JSON), `print_spec` (JSON), document_path, service_fee, estimated_item_cost, actual_item_cost, receipt_path, `payment_method/proof_path/note/submitted_at/verified_at/rejection_reason`, status, completion_pin, needs_refund, stempel waktu tiap tahap |
| `order_events` | order_id, actor_id, type, from_status, to_status, description, meta |
| `reviews` | order_id, reviewer_id, reviewee_id, rating, comment |
| `disputes` | order_id, opened_by, reason, description, evidence_path, status, resolution, resolution_note, resolved_by |
| `notifications` | bawaan Laravel |

Keputusan desain:

- **Request = `orders` berstatus `open` dengan `fulfiller_id = NULL`.** Saat relawan meng-*claim*, `fulfiller_id`
  diisi dan status menjadi `awaiting_payment`. Saat penitip ikut rute, order dibuat langsung dengan `trip_id`.
- Item pesanan dan spesifikasi cetak disimpan sebagai JSON di `orders` (tidak perlu tabel `order_items`).
- Pembayaran disimpan sebagai kolom di `orders` (satu bukti aktif; penolakan menyimpan alasan dan penitip
  mengunggah ulang), bukan tabel terpisah.
- Metode pembayaran relawan (satu per akun) disimpan di `users`.
- Kampus, domain email kampus, dan saran lokasi berada di `config/nitip.php` (`App\Support\Campuses`).
- Sesi dan cache memakai driver file agar tidak menambah tabel.

## 4. Status & Business Rules

### Order (`orders.status`)

```
open --claim--> awaiting_payment --upload bukti--> payment_submitted --verifikasi--> paid
 ^                    ^                                   |
 | release            +------------ tolak bukti ----------+
paid --mulai beli--> in_progress --harga riil + struk--> delivering --serah terima--> delivered --konfirmasi/PIN--> completed
cancelled : dibatalkan (penitip sebelum pembayaran diverifikasi; relawan sebelum mengantar; admin kapan saja)
disputed  : sengketa dibuka (paid...delivered), diselesaikan admin menjadi completed / cancelled
expired   : (turunan) open dan needed_by sudah lewat
```

Transisi hanya boleh dilakukan lewat `OrderWorkflowService`; controller tidak pernah mengubah status
secara langsung. Setiap transisi menulis `order_events` (audit trail) dan mengirim notifikasi.

| Aksi                  | Pelaku    | Dari status                              | Ke status             | Syarat |
|-----------------------|-----------|------------------------------------------|-----------------------|--------|
| Post request          | requester | -                                        | open                  | kategori aktif; berkas wajib untuk print |
| Claim                 | fulfiller | open                                     | awaiting_payment      | bukan requester sendiri, tidak disuspend, punya tujuan pembayaran |
| Join trip             | requester | -                                        | awaiting_payment      | trip open, slot tersedia, bukan pemilik trip |
| Release (lepas)       | fulfiller | awaiting_payment, payment_submitted      | open                  | order berasal dari request |
| Submit bukti transfer | requester | awaiting_payment                         | payment_submitted     | file gambar maks 4 MB |
| Verifikasi pembayaran | fulfiller | payment_submitted                        | paid                  | - |
| Tolak bukti           | fulfiller | payment_submitted                        | awaiting_payment      | alasan wajib |
| Mulai beli / cetak    | fulfiller | paid                                     | in_progress           | - |
| Sudah dibeli, antar   | fulfiller | in_progress                              | delivering            | **total sesuai struk + foto struk** (jika ada biaya barang) |
| Serah terima          | fulfiller | delivering                               | delivered             | struk boleh diganti |
| Konfirmasi diterima   | requester | delivered                                | completed             | - |
| Selesaikan dengan PIN | fulfiller | delivering, delivered                    | completed             | PIN 4 digit milik penitip |
| Batalkan              | requester | open, awaiting_payment, payment_submitted| cancelled             | alasan |
| Batalkan              | fulfiller | awaiting_payment...in_progress           | cancelled             | alasan; jika sudah paid, ditandai perlu refund |
| Buka sengketa         | keduanya  | paid, in_progress, delivering, delivered | disputed              | alasan + bukti opsional |
| Selesaikan sengketa   | admin     | disputed                                 | completed / cancelled | catatan resolusi |

### Selisih harga (harga di resto tidak selalu diketahui)

Penitip hanya mengisi **perkiraan** harga barang (boleh total kira-kira jika harga per item tidak tahu).
Penitip mentransfer biaya jasa + perkiraan. Setelah membeli, relawan memasukkan total sesuai struk dan
memfotonya (tahap *Delivering*). Sistem menghitung `actual_item_cost - estimated_item_cost`:
positif berarti penitip menambah, negatif berarti relawan mengembalikan, diselesaikan tunai/transfer saat
serah terima. Semua tercatat di `order_events` dan bisa jadi dasar sengketa "selisih nota".

### Trip (`trips.status`): `open` -> `closed` (ditutup manual / lewat waktu) / `cancelled`.
`closes_at` dihitung otomatis = `departure_at - 15 menit` (konfigurasi `nitip.trips.close_before_minutes`).
Slot tersisa = `max_slots - jumlah order aktif`. Trip yang dibatalkan membatalkan order yang belum dibayar.

### Dispute (`disputes.status`): `open` -> `resolved`.

## 5. Pemetaan Halaman

| Area    | Halaman | Route |
|---------|---------|-------|
| Publik  | Landing | `/` |
| Publik  | Info layanan & tarif | `/layanan` |
| Auth    | Register / verifikasi OTP / sukses | `/register`, `/verify-email`, `/verified` |
| Auth    | Login / lupa & reset password | `/login`, `/forgot-password`, `/reset-password/{token}` |
| Student | Dashboard | `/dashboard` |
| Student | **Nitip** (tab Permintaan & Rute) | `/nitip` |
| Student | Buat permintaan | `/requests/create` |
| Student | Rute saya / buat / detail / ikut | `/trips`, `/trips/create`, `/trips/{trip}`, `/trips/{trip}/join` |
| Student | Pesanan saya / detail order | `/orders`, `/orders/{order}` |
| Student | File privat order | `/orders/{order}/files/{document|proof|receipt}` |
| Student | Penghasilan, notifikasi, profil publik | `/earnings`, `/notifications`, `/u/{user}` |
| Student | Pengaturan profil / metode pembayaran / password | `/settings/profile`, `/settings/payment`, `/settings/password` |
| Student | Sengketa | `/disputes/{dispute}` |
| Admin   | Dashboard, pengguna, pesanan, rute, sengketa, kategori & tarif, audit trail | `/admin/...` |

## 6. Keamanan

- CSRF (form Blade), mass-assignment (`#[Fillable]`), validasi via Form Request, Policy untuk setiap
  resource, middleware `auth`, `verified`, `active` (tidak disuspend), `admin`.
- Rate limiting: login, kirim ulang OTP, lupa password.
- File pribadi (bukti transfer, struk, berkas cetak, bukti sengketa, QRIS) disimpan di disk `local` (privat)
  dan disajikan lewat route yang diotorisasi; hanya avatar di disk `public`.
- Validasi MIME/ukuran/ekstensi untuk semua upload; nama file diacak.
- Kredensial hanya di `.env`; akun dummy didokumentasikan di README.

## 7. Asumsi yang Didokumentasikan

1. Email kampus dianggap valid jika berakhiran `.ac.id` **atau** cocok dengan domain kampus terdaftar
   (mis. `student.uph.edu`), karena beberapa kampus tidak memakai `.ac.id`.
2. Verifikasi email memakai kode OTP 6 digit (sesuai Stitch), bukan tautan bertanda tangan. Saat development
   tersedia mode bypass (`NITIP_OTP_BYPASS=true`) agar kode apa pun diterima.
3. Tarif layanan bisa disesuaikan pengguna dalam rentang min-max kategori (PDF menyebut "tarif rekomendasi").
4. Refund setelah pembatalan pasca-pembayaran dilakukan di luar platform (P2P), sistem hanya mencatat
   flag `needs_refund` dan admin memantaunya lewat audit trail/sengketa.
5. PIN 4 digit serah terima (Stitch "4-digit completion PIN") menjadi opsi konfirmasi cepat oleh relawan;
   penitip tetap dapat konfirmasi lewat tombol.
6. Satu rute menerima satu kategori; satu akun punya satu tujuan pembayaran; satu order punya satu berkas
   cetak dan satu bukti transfer aktif (penyederhanaan skema untuk proyek kampus).

## 8. Status Implementasi (audit akhir terhadap PDF)

| Requirement PDF | Status | Lokasi utama |
|---|---|---|
| Dual-Posting: jalur OFFER (rute + kuota) | Selesai | `TripService`, `TripController`, `TripJoinController`, `trips/*` |
| Dual-Posting: jalur REQUEST (posting kebutuhan, claim job) | Selesai | `OrderService::createRequest`, `OrderWorkflowService::claim`, `orders/create` |
| Layanan makanan kantin dalam / luar kampus | Selesai | `ServiceCategorySeeder`, form item dinamis |
| Layanan print & fotokopi (upload berkas, spesifikasi, penjilidan) | Selesai | `orders.print_spec`, `orders.document_path`, `FileStorageService` |
| Flat fee per kategori + kalkulasi otomatis | Selesai | `service_categories.fee_*`, validasi `StoreRequestRequest` / `StoreTripRequest`, admin `/admin/categories` |
| Registrasi email kampus + NIM (social collateral) | Selesai | `RegisterRequest`, `Campuses::allowsEmail`, unique NIM per kampus |
| Verifikasi email (OTP 6 digit, sesuai Stitch) | Selesai | `EmailVerificationService`, `VerifyEmailOtp`, `auth/verify-email` |
| Direct P2P payment (GoPay/BCA/QRIS), tanpa holding balance | Selesai | `users.payment_*`, `PaymentService`, `orders/partials/payment-form` |
| Penitip unggah resi transfer sebelum diproses | Selesai | `PaymentService::submitProof`, status `payment_submitted` |
| Relawan lampirkan foto struk riil + harga riil | Selesai | `OrderWorkflowService::startDelivering`, selisih nota otomatis |
| WhatsApp deep-link `wa.me/{nomor}` + template ID order | Selesai | `Phone::whatsappUrl`, `Order::whatsappText` |
| Audit trail permanen | Selesai | `order_events`, `/admin/audit-logs` |
| Siklus: Post & Match → Pay & Verify → In Progress → Delivering → Completed | Selesai | `OrderStatus`, `OrderWorkflowService`, komponen stepper |
| Pembatalan sepihak & rekonsiliasi selisih nota (sengketa) | Selesai | `DisputeService`, `/admin/disputes`, flag `needs_refund` |
| Kriteria sukses: completion rate & CSAT | Selesai | rating dua arah (`reviews`), dashboard admin |
| Tanpa komisi pihak ketiga | Selesai | relawan menerima 100 % biaya jasa |
