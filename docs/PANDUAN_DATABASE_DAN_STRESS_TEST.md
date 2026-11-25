# Panduan Belajar: Database & Stress Test Nitip

Dokumen ini untuk dipakai sendiri sebagai bahan belajar sebelum presentasi. Isinya dua bagian
yang bisa langsung dipraktikkan: **(A) membedah database** lewat phpMyAdmin, dan
**(B) menjalankan stress test** lalu membaca hasilnya.

Semua angka dan query di bawah sudah diuji pada database lokal `nitip` (MySQL 8.4.3,
25 November 2026), jadi bisa langsung disalin tanpa takut error.

---

## 0. Persiapan: buka phpMyAdmin

1. Nyalakan Laragon, klik **Start All** (Apache + MySQL harus hijau).
2. Buka `http://localhost/phpmyadmin/`.
3. Login: user `root`, password dikosongkan saja (`AllowNoPassword` aktif di konfigurasi lokal).
4. Klik database **`nitip`** di panel kiri.

> Kalau phpMyAdmin menampilkan *Fatal error ... autoload_real.php*, lihat
> [Lampiran 1](#lampiran-1-kalau-phpmyadmin-error-lagi) di bagian akhir.

---

# BAGIAN A - MEMBEDAH DATABASE

## A1. Peta isi database

Jalankan di tab **SQL** phpMyAdmin:

```sql
SELECT TABLE_NAME AS tabel, TABLE_ROWS AS perkiraan_baris, ENGINE AS mesin
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'nitip'
ORDER BY TABLE_NAME;
```

Hasilnya 10 tabel: delapan tabel aplikasi (hasil rancangan kita) ditambah dua tabel bawaan Laravel.

| # | Tabel | Jenis | Isi | Jumlah baris (data demo lokal) |
|---|---|---|---|---|
| 1 | `users` | Master | Akun mahasiswa & admin | 14 |
| 2 | `service_categories` | Look-up | 3 kategori layanan + rentang tarif | 3 |
| 3 | `trips` | Transaksi | Rute jalur OFFER | 6 |
| 4 | `orders` | Transaksi | Inti transaksi titipan | 30 |
| 5 | `order_events` | Log | Audit trail tiap perubahan status | 174 |
| 6 | `reviews` | Transaksi | Rating dua arah | 22 |
| 7 | `disputes` | Transaksi | Komplain + resolusi admin | 2 |
| 8 | `notifications` | Log | Notifikasi in-app | 152 |
| 9 | `password_reset_tokens` | Infrastruktur | Token reset password | 0 |
| 10 | `migrations` | Infrastruktur | Catatan versi skema | 8 |

**Hal yang layak diperhatikan:** `order_events` (174 baris) hampir 6x lebih banyak daripada
`orders` (30 baris). Itu memang disengaja: satu order yang selesai meninggalkan 8 jejak event.
Inilah wujud nyata "audit trail" yang kita klaim di laporan.

> **Untuk presentasi:** kalau ditanya "databasenya apa dan tabelnya berapa?", jawabannya
> "satu database MySQL 8, sepuluh tabel: delapan tabel aplikasi plus dua bawaan framework".

## A2. Kenapa hanya 8 tabel, bukan 15?

Tiga keputusan desain yang perlu kamu bisa jelaskan:

| Keputusan | Alternatif yang TIDAK dipakai | Alasan |
|---|---|---|
| Item titipan disimpan sebagai kolom JSON di `orders` | Tabel `order_items` terpisah | Item bersifat heterogen (makanan vs spesifikasi cetak) dan tidak pernah dicari per item. Tabel terpisah hanya menambah JOIN tanpa manfaat query. |
| Pembayaran jadi kolom `payment_*` di `orders` | Tabel `payments` terpisah | Satu order hanya punya satu bukti bayar aktif. Kalau ditolak, bukti diganti, bukan ditambah. |
| Kampus & lokasi di file konfigurasi | Tabel `campuses` + `locations` | Data statis per deployment, tidak pernah diubah pengguna. Menaruhnya di tabel berarti menambah JOIN untuk data yang tidak pernah berubah. |

Sesi dan cache juga sengaja memakai driver file, bukan tabel database. Itu sebabnya skemanya
tetap ramping.

## A3. Anatomi tabel `orders` (tabel paling penting)

```sql
SHOW COLUMNS FROM orders;
```

40 kolom, tapi bisa dikelompokkan jadi 6 blok supaya mudah dijelaskan:

| Blok | Kolom | Fungsi |
|---|---|---|
| Identitas | `id`, `code`, `campus`, `title`, `notes` | `code` (mis. `NT-6PTCZY`) dipakai di URL & chat supaya id asli tidak bocor |
| Relasi | `requester_id`, `fulfiller_id`, `trip_id`, `service_category_id` | `fulfiller_id` NULL = belum ada relawan (status `open`) |
| Isi titipan | `items` (JSON), `print_spec` (JSON), `document_path`, `document_name` | Fleksibel untuk makanan maupun cetak |
| Uang | `service_fee`, `estimated_item_cost`, `actual_item_cost`, `payment_*`, `needs_refund` | Selisih nota dihitung dari `actual - estimated` |
| Status | `status`, `completion_pin` | 10 nilai status, PIN 4 digit untuk serah terima |
| Jejak waktu | `matched_at`, `paid_at`, `started_at`, `delivering_at`, `delivered_at`, `completed_at`, `cancelled_at` | Satu kolom waktu per tahap, bukan satu kolom yang ditimpa |

**Kenapa jejak waktu dipisah per tahap?** Supaya bisa menghitung durasi tiap fase
(misalnya rata-rata berapa lama order menunggu pembayaran) tanpa membongkar tabel log.

## A4. Praktik query: 6 latihan yang sudah teruji

Salin satu per satu ke tab **SQL**. Setiap query punya tujuan belajar yang berbeda.

### Latihan 1: GROUP BY, melihat sebaran status

```sql
SELECT status, COUNT(*) AS jumlah
FROM orders
GROUP BY status
ORDER BY jumlah DESC;
```

**Yang dipelajari:** agregasi dasar. **Yang dilihat:** kesepuluh status benar-benar terpakai di
data demo, bukan sekadar teori di enum.

### Latihan 2: query kolom JSON

```sql
SELECT code,
       JSON_UNQUOTE(JSON_EXTRACT(items, '$[0].name')) AS item_pertama,
       JSON_LENGTH(items) AS jumlah_item
FROM orders
WHERE items IS NOT NULL
LIMIT 5;
```

**Yang dipelajari:** MySQL bisa membaca isi JSON tanpa tabel terpisah.
**Untuk presentasi:** ini jawaban kalau dosen bertanya "kalau JSON, nanti susah dicari dong?"
Jawabnya: bisa dicari, hanya saja tidak diindeks, dan memang tidak perlu karena item
tidak pernah jadi kriteria pencarian.

### Latihan 3: audit trail satu order

```sql
SELECT o.code, e.type, e.from_status, e.to_status, e.description, e.created_at
FROM order_events e
JOIN orders o ON o.id = e.order_id
WHERE o.status = 'completed'
ORDER BY e.order_id, e.created_at
LIMIT 10;
```

**Yang dipelajari:** JOIN + membaca log. **Yang dilihat:** rantai `created` lalu `claimed`,
`payment_submitted`, `payment_verified`, `started`, `delivering`, `delivered`, `completed`.
Setiap baris punya `from_status` dan `to_status`, jadi tidak ada lompatan status.

### Latihan 4: JOIN tiga tabel sekaligus

```sql
SELECT o.code,
       r.name  AS penitip,
       f.name  AS relawan,
       c.name  AS kategori,
       o.status
FROM orders o
JOIN users r              ON r.id = o.requester_id
LEFT JOIN users f         ON f.id = o.fulfiller_id
JOIN service_categories c ON c.id = o.service_category_id
LIMIT 10;
```

**Yang dipelajari:** beda `JOIN` dan `LEFT JOIN`. Relawan memakai LEFT JOIN karena order
berstatus `open` belum punya relawan (NULL). Kalau dipaksa JOIN biasa, order yang masih
mencari relawan akan hilang dari hasil.

### Latihan 5: agregasi rating

```sql
SELECT u.name,
       ROUND(AVG(rv.rating), 2) AS rata_rata,
       COUNT(*) AS jumlah_ulasan
FROM reviews rv
JOIN users u ON u.id = rv.reviewee_id
GROUP BY u.id, u.name
ORDER BY rata_rata DESC;
```

**Yang dipelajari:** AVG + GROUP BY. Ini query yang sama dengan yang dipakai halaman profil
publik dan halaman penghasilan di aplikasi.

### Latihan 6: uji integritas data

```sql
SELECT COUNT(*) AS order_tanpa_jejak
FROM orders o
LEFT JOIN order_events e ON e.order_id = o.id
WHERE e.id IS NULL;
```

**Yang dipelajari:** cara membuktikan klaim lewat data. Hasilnya harus **0**. Artinya tidak ada
satu pun order yang lahir tanpa tercatat di audit trail. Ini bukti langsung dari aturan
"semua transisi lewat satu pintu (OrderWorkflowService)".

## A5. Indeks: kenapa ada dan bagaimana membuktikannya

Lihat semua indeks:

```sql
SELECT TABLE_NAME AS tabel, INDEX_NAME AS nama_indeks,
       GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS kolom,
       IF(NON_UNIQUE = 0, 'UNIQUE', '-') AS sifat
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = 'nitip' AND INDEX_NAME <> 'PRIMARY'
GROUP BY tabel, nama_indeks, NON_UNIQUE
ORDER BY tabel;
```

Indeks yang penting untuk dijelaskan:

| Indeks | Kolom | Dipakai untuk |
|---|---|---|
| `orders_status_campus_needed_by_index` | status, campus, needed_by | Halaman feed `/nitip`: cari order `open`, satu kampus, urut batas waktu. Ini **composite index**, urutan kolomnya mengikuti urutan filter di query. |
| `orders_requester_id_status_index` | requester_id, status | Halaman "Pesanan Saya" milik penitip |
| `orders_fulfiller_id_status_index` | fulfiller_id, status | Halaman pekerjaan milik relawan + perhitungan penghasilan |
| `order_events_order_id_created_at_index` | order_id, created_at | Timeline di halaman detail order (ambil semua event satu order, urut waktu) |
| `users_campus_nim_unique` | campus, nim | **Aturan bisnis jadi batasan database**: NIM unik per kampus, bukan unik global, karena kampus berbeda bisa punya format NIM yang sama |
| `reviews_order_id_reviewer_id_unique` | order_id, reviewer_id | Mencegah satu orang memberi dua ulasan pada order yang sama |
| `disputes_order_id_unique` | order_id | Satu order maksimal satu komplain |

**Praktik: buktikan indeksnya benar-benar dipakai.**

```sql
EXPLAIN SELECT * FROM orders
WHERE status = 'open' AND campus = 'UPH'
ORDER BY needed_by;
```

Lihat kolom `key` pada hasilnya. Kalau berisi nama indeks (bukan NULL), berarti MySQL memakai
indeks dan tidak memindai seluruh tabel. Bandingkan dengan query tanpa indeks:

```sql
EXPLAIN SELECT * FROM orders WHERE title LIKE '%kopi%';
```

Yang ini `key`-nya NULL dan `type`-nya `ALL` (full table scan), karena `title` memang tidak
diindeks. Dua perintah ini bagus untuk ditunjukkan berdampingan saat presentasi.

## A6. Foreign key dan aturan penghapusan

```sql
SELECT TABLE_NAME AS tabel, COLUMN_NAME AS kolom,
       REFERENCED_TABLE_NAME AS merujuk_ke, CONSTRAINT_NAME AS nama_constraint
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'nitip' AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY tabel;
```

Tiga aturan hapus yang dipakai, masing-masing dengan alasan berbeda:

| Aturan | Contoh | Alasan |
|---|---|---|
| `CASCADE` (ikut terhapus) | `orders.requester_id` ke `users` | Kalau akun penitip dihapus, pesanannya ikut hilang karena tidak bermakna tanpa pemiliknya |
| `SET NULL` (dikosongkan) | `orders.fulfiller_id` ke `users` | Kalau akun relawan dihapus, ordernya tetap ada sebagai catatan, hanya relawannya jadi kosong |
| `RESTRICT` (ditolak) | `orders.service_category_id` ke `service_categories` | Kategori yang masih dipakai order tidak boleh dihapus, supaya data historis tidak rusak |

**Praktik aman:** coba hapus kategori yang sedang dipakai, database akan menolak.

```sql
-- jalankan, lalu baca pesan errornya. JANGAN panik: ini memang harus gagal
DELETE FROM service_categories WHERE id = 1;
```

Pesan error `Cannot delete or update a parent row` itulah buktinya integritas referensial bekerja.

---

# BAGIAN B - STRESS TEST

## B1. Apa dan kenapa

Stress test = menembak aplikasi dengan banyak permintaan sekaligus, lalu mengukur apakah dia
tetap benar dan tetap cepat. Untuk Nitip ada dua pertanyaan yang ingin dijawab:

1. **Kinerja:** berapa lama pengguna menunggu saat sistem ramai?
2. **Kebenaran (yang paling penting):** kalau 10 relawan berebut 1 titipan pada detik yang sama,
   apakah tetap hanya 1 yang menang? Ini menguji `lockForUpdate()` di dalam transaksi database.

Skripnya ada di `qa/stress_test/stress.py`.

## B2. Empat skenario di dalam skrip

| Kode | Nama | Yang dilakukan | Yang diukur |
|---|---|---|---|
| S1 | Landing | 300 GET halaman depan lewat 30 koneksi anonim | Ketahanan halaman publik (query statistik paling berat ada di sini) |
| S2 | Browsing | 40 user login, masing-masing buka dashboard + feed + daftar pesanan | Kecepatan halaman setelah login |
| S3 | Posting | 30 user x 2 kali POST titipan secara paralel | Apakah penulisan data tahan beban |
| S4 | Race klaim | 10 relawan merebut 1 titipan, diulang 5 ronde | **Kebenaran lock database: pemenang harus tepat 1** |

## B3. Cara menjalankan

### Persiapan sekali saja

```bash
pip install requests
```

Akun uji (`loadtest01..40@student.uph.edu`) harus ada di target. Kalau menembak server:

```bash
ssh -i ~/hendra root@76.13.22.7
cd /var/www/nitip
php artisan tinker --execute="require 'qa/stress_test/setup_loadtest_users.php';"
```

### Tiga cara menjalankan

Skrip sekarang menerima target dan skala beban, jadi tidak perlu mengedit file.

```bash
cd qa/stress_test

# 1. Beban penuh ke server live (seperti pengujian 10 November)
python stress.py

# 2. Target lain, misalnya aplikasi yang jalan di laptop
python stress.py http://127.0.0.1:8000

# 3. Latihan ringan (20 persen beban) supaya cepat selesai
NITIP_SCALE=0.2 python stress.py http://127.0.0.1:8000
```

Skrip mencetak target dan skalanya di baris pertama, jadi kamu selalu tahu sedang menembak apa:

```
Target : http://127.0.0.1:8000
Skala  : 0.2x (landing 60/6 koneksi, 8 user, 6 poster, race 2x1)
```

### Kalau mau mencoba di laptop

Project ini tidak terpasang di folder Laragon, jadi jalankan server bawaan Laravel.
Penting: beri beberapa worker, karena secara bawaan server itu hanya melayani satu permintaan
dalam satu waktu sehingga hasil pengukurannya tidak mencerminkan apa pun.

```bash
# terminal 1
cd c:/Han/UPH/WEB/nitip
PHP_CLI_SERVER_WORKERS=8 php artisan serve

# terminal 2: siapkan akun uji di database lokal, lalu tembak
cd c:/Han/UPH/WEB/nitip
php artisan tinker --execute="require 'qa/stress_test/setup_loadtest_users.php';"
cd qa/stress_test
NITIP_SCALE=0.2 python stress.py http://127.0.0.1:8000
```

> **Peringatan:** stress test membuat puluhan order sampah. **Jangan menjalankannya di server
> live menjelang demo**, karena akan mengotori data demo. Kalau sudah terlanjur, rapikan dengan
> `php artisan migrate:fresh --seed --force` di server, lalu buat ulang akun demo.

## B4. Cara membaca hasilnya

Hasil tersimpan di `qa/stress_test/stress_results.json`. Isinya seperti ini:

```json
"browse": { "n": 120, "err": 0, "p50": 0.533, "p95": 2.49, "p99": 3.312, "max": 4.181 }
```

| Istilah | Arti | Kenapa penting |
|---|---|---|
| `n` | Jumlah permintaan | Ukuran sampel |
| `err` | Jumlah permintaan gagal | Harus 0 atau sangat kecil |
| `p50` (median) | Separuh pengguna merasakan lebih cepat dari ini | Pengalaman "rata-rata" yang sesungguhnya |
| `p95` | 95 persen pengguna lebih cepat dari ini | **Angka yang dipakai untuk menilai**, karena mewakili pengguna yang apes |
| `p99`, `max` | Kasus paling buruk | Menunjukkan ada lonjakan atau tidak |

**Kenapa pakai p95, bukan rata-rata?** Karena rata-rata bisa menyamarkan masalah. Kalau 99 orang
dilayani dalam 0,2 detik dan 1 orang menunggu 60 detik, rata-ratanya terlihat bagus (0,8 detik)
padahal ada satu orang yang jelas kecewa. Persentil tidak bisa ditipu seperti itu.

## B5. Hasil pengujian nyata (10 November 2026, server live)

| Skenario | n | Gagal | p50 | p95 | Kesimpulan |
|---|---|---|---|---|---|
| S1 Landing | 300 | 1 | 0,62 s | 4,96 s | Titik terberat. Halaman depan menghitung statistik setiap kali dibuka |
| S2 Browsing | 120 | 0 | 0,53 s | 2,49 s | Sehat |
| S3 Posting | 60 | 0 | 0,27 s | 1,12 s | **60 dari 60 order berhasil dibuat** |
| S4 Race klaim | 50 | 7 | 0,68 s | 2,06 s | **5 ronde, selalu tepat 1 pemenang** |

Total 530 permintaan, 8 gagal (1,5 persen).

**Dua hal yang wajib kamu pahami sebelum ditanya:**

1. **"Kenapa S4 ada 7 error tapi kalian bilang berhasil?"**
   Justru 7 error itu hasil yang benar. Dari 10 relawan yang berebut, hanya 1 boleh menang;
   9 sisanya harus ditolak. Penolakan itu terhitung sebagai "error HTTP" oleh skrip, padahal
   secara bisnis itu perilaku yang diharapkan. Yang kami periksa adalah baris `winners: 1`
   pada setiap ronde di file hasil, dan kelima ronde menunjukkan angka 1.

2. **"Kenapa landing p99 sampai 31 detik?"**
   Itu temuan jujur kami, tercatat sebagai defect D-01. Penyebabnya query statistik di halaman
   depan yang dihitung ulang setiap permintaan. Solusinya menyimpan hasil hitungan itu di cache
   selama beberapa menit. Kami catat sebagai minor karena tidak menghalangi fungsi apa pun,
   dan beban 30 koneksi serentak ke halaman publik jauh di atas pola pemakaian nyata di kampus.

## B6. Mendemokan race condition tanpa skrip

Cara paling meyakinkan saat presentasi, cukup dua browser:

1. Login sebagai dua relawan berbeda (browser normal + incognito).
2. Buka titipan yang sama, berstatus **Mencari Relawan**, di kedua browser.
3. Hitung mundur, klik **Ambil** bersamaan.
4. Satu browser masuk ke halaman pesanan, satunya lagi mendapat pesan sopan
   *"Permintaan ini sudah diambil relawan lain."*

Kalau ditanya mekanismenya: pengambilan dibungkus transaksi database, dan baris order dikunci
dengan `lockForUpdate()` sebelum statusnya diperiksa. Pemroses kedua menunggu kunci terlepas,
lalu melihat status sudah bukan `open` sehingga ditolak. Kodenya ada di
`app/Services/OrderWorkflowService.php` method `claim()`.

Bisa juga dibuktikan dari database setelah demo:

```sql
SELECT code, status, fulfiller_id, matched_at
FROM orders
WHERE code = 'ISI_KODE_ORDER_TADI';
```

Hanya ada satu `fulfiller_id`, dan `order_events` hanya mencatat satu peristiwa `claimed`.

---

# BAGIAN C - Kerangka presentasi

Alur 8 sampai 10 menit kalau kamu diminta menjelaskan bagian ini:

| Menit | Bagian | Yang ditunjukkan |
|---|---|---|
| 0-1 | Pembuka | Buka phpMyAdmin, tunjukkan 10 tabel. "Satu database, sepuluh tabel." |
| 1-3 | Rancangan | Jelaskan 3 keputusan desain di A2 (JSON, payment, config). Tunjukkan Latihan 2 (query JSON). |
| 3-5 | Integritas | Jalankan Latihan 6 (hasilnya 0), lalu coba DELETE kategori yang gagal (A6). |
| 5-6 | Kinerja | Tunjukkan EXPLAIN dengan indeks vs tanpa indeks (A5). |
| 6-8 | Stress test | Buka `stress_results.json`, jelaskan tabel B5, tekankan `winners: 1`. |
| 8-10 | Demo race | Dua browser berebut satu titipan (B6). |

**Pertanyaan yang mungkin muncul dan inti jawabannya:**

| Pertanyaan | Inti jawaban |
|---|---|
| Kenapa pakai JSON, bukan tabel `order_items`? | Item heterogen dan tidak pernah jadi kriteria pencarian. Masih bisa di-query (Latihan 2). Ini pengecualian 3NF yang sadar dan terdokumentasi, bukan kelalaian. |
| Databasenya sudah normalisasi? | 3NF dengan dua pengecualian yang disengaja: kolom JSON dan snapshot `payment_method` (supaya riwayat order tidak berubah ketika relawan mengganti rekening). |
| Indeksnya atas dasar apa? | Mengikuti query yang benar-benar dipakai aplikasi. Composite index urutannya mengikuti urutan filter, contohnya status + campus + needed_by untuk halaman feed. |
| Bagaimana kalau dua orang mengklaim bersamaan? | Transaksi + `lockForUpdate()`. Sudah diuji 5 ronde x 10 relawan, selalu tepat satu pemenang. Bisa didemokan sekarang juga. |
| Stress testnya pakai alat apa? | Skrip Python dengan `requests` + `ThreadPoolExecutor`, bukan JMeter, supaya skenarionya bisa mengikuti alur bisnis kami sendiri (login, posting, lalu rebutan klaim). |
| Berapa pengguna yang sanggup ditangani? | Pada 40 pengguna aktif serentak, p95 masih di bawah 3 detik. Titik lemah yang diketahui ada di query statistik halaman depan, dan solusinya sudah direncanakan. |

---

## Lampiran 1: Kalau phpMyAdmin error lagi

Gejala: `Fatal error: Failed opening required .../symfony/polyfill-php80/bootstrap.php`
di `vendor/composer/autoload_real.php`.

**Artinya:** folder `vendor/` phpMyAdmin tidak lengkap. Biasanya karena proses penyalinan atau
ekstraksi berhenti di tengah jalan. Pada kasus 25 November 2026, `vendor/` hanya berisi 11 paket
dari seharusnya 27 (berhenti di huruf "l", sehingga folder `symfony` tidak ikut tersalin).

**Cara memperbaiki:** Laragon menyimpan salinan utuh di subfolder `phpMyAdmin-5.2.3-english`.
Jalankan di Git Bash:

```bash
PMA=/c/laragon/etc/apps/phpmyadmin
mv "$PMA/vendor" "$PMA/vendor_rusak_backup"
cp -r "$PMA/phpMyAdmin-5.2.3-english/vendor" "$PMA/vendor"

# verifikasi: harus 27 paket dan 1905 file
ls "$PMA/vendor" | wc -l
find "$PMA/vendor" -type f | wc -l
```

Lalu muat ulang `http://localhost/phpmyadmin/`. Tidak perlu me-restart Laragon, dan
`config.inc.php` milikmu tidak tersentuh.

Kalau salinan cadangan itu juga rusak, unduh phpMyAdmin versi yang sama dari
`https://www.phpmyadmin.net/downloads/`, ekstrak, lalu ganti seluruh folder `vendor`.

## Lampiran 2: Perintah yang sering dipakai

```bash
# lihat info koneksi database yang sedang dipakai aplikasi
php artisan db:show

# masuk ke shell database interaktif
php artisan db

# reset database lokal + isi ulang data demo
php artisan migrate:fresh --seed

# lihat daftar migrasi dan statusnya
php artisan migrate:status

# jalankan seluruh automated test (45 test)
php artisan test
```
