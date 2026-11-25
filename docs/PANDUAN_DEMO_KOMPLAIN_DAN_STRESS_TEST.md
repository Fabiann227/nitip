# Panduan Demo: Tombol Aksi, Fitur Komplain, dan Stress Test

Dokumen pendamping untuk [PANDUAN_DATABASE_DAN_STRESS_TEST.md](PANDUAN_DATABASE_DAN_STRESS_TEST.md).
Isinya tiga hal yang sering ditanyakan saat demo: beda tiga tombol yang mirip, cara mendemokan
fitur komplain dari awal sampai diputus admin, dan naskah untuk mempraktikkan stress test
secara langsung di depan kelas.

Semua alur dan data di bawah sudah dicek pada server live per 25 November 2026.

---

# BAGIAN A - Beda "Tolak bukti", "Lepas titipan", dan "Batalkan pesanan"

Ketiganya terlihat mirip karena sama-sama berwarna merah, padahal akibatnya sangat berbeda.
Cara termudah mengingatnya: **satu mundur satu langkah, satu mundur keluar, satu mematikan pesanan.**

| | Tolak bukti | Lepas titipan | Batalkan pesanan |
|---|---|---|---|
| **Inti** | Menolak satu bukti transfer | Relawan mundur dari pesanan | Menutup pesanan selamanya |
| **Siapa yang menekan** | Relawan | Relawan | Penitip, relawan, atau admin |
| **Status sebelum** | Verifikasi Pembayaran | Menunggu Pembayaran | Status aktif, kecuali Verifikasi Pembayaran |
| **Status sesudah** | kembali ke Menunggu Pembayaran | kembali ke Mencari Relawan | Dibatalkan (terminal) |
| **Nasib pesanan** | Tetap hidup, relawan tetap orang yang sama | Tetap hidup, tayang lagi mencari relawan lain | Mati, tidak bisa dilanjutkan |
| **Alasan wajib diisi** | Ya | Ya | Ya |
| **Efek lain** | Penitip diminta upload ulang bukti yang benar | Bukti transfer lama dihapus, data pembayaran dikosongkan | Kalau pembayaran sudah diverifikasi, pesanan ditandai **wajib refund** |

### Analogi supaya gampang dijelaskan

- **Tolak bukti** = "foto bukti transfernya salah/kurang, tolong kirim ulang". Transaksinya jalan terus.
- **Lepas titipan** = driver membatalkan diri, tapi orderannya dilempar lagi ke driver lain.
  Penitip tidak dirugikan, kebutuhannya tetap dicarikan orang.
- **Batalkan pesanan** = orderannya dibatalkan total. Tidak ada yang melanjutkan.

### Detail aturan (kalau dosen menanyakan batasnya)

**Tolak bukti** hanya muncul ketika ada bukti transfer yang menunggu diperiksa. Sistem menyimpan
alasan penolakan, mengembalikan status ke Menunggu Pembayaran, dan memberi tahu penitip.
Siklus ini boleh berulang sampai buktinya benar.

**Lepas titipan** hanya tersedia untuk pesanan **jalur REQUEST** (titipan yang diposting penitip),
dan hanya pada tahap **Menunggu Pembayaran**, yaitu ketika penitip belum mengirim bukti transfer
apa pun. Pesanan jalur OFFER (ikut rute) tidak punya tombol ini, karena relawannya adalah pemilik
rute itu sendiri, jadi tidak masuk akal kalau dia "melepas" sambil tetap memiliki rutenya.

**Aturan penting (berlaku sejak 25 November 2026):** begitu penitip mengunggah bukti transfer
(status **Verifikasi Pembayaran**), tombol **Lepas titipan** dan **Batalkan pesanan** sengaja
dihilangkan untuk kedua pihak. Pada titik itu penitip sudah mengirim uang, jadi relawan tidak boleh
mundur begitu saja sambil meninggalkan dana yang menggantung. Satu-satunya pilihan adalah
memutuskan soal dananya dulu: **Verifikasi** kalau dana masuk, atau **Tolak bukti** kalau tidak.
Setelah ditolak, status kembali ke Menunggu Pembayaran dan opsi melepas atau membatalkan
tersedia lagi. Hanya admin yang tetap bisa membatalkan pada tahap ini, dan pembatalan itu otomatis
ditandai **wajib refund**.

**Batalkan pesanan** punya batas berbeda untuk tiap peran:

| Peran | Boleh membatalkan pada tahap |
|---|---|
| Penitip | Mencari Relawan, Menunggu Pembayaran |
| Relawan | Menunggu Pembayaran, Dibayar, Sedang Diproses |
| Admin | Semua tahap yang belum selesai (termasuk Verifikasi Pembayaran) |

Perhatikan: setelah tahap **Sedang Diantar**, penitip maupun relawan **tidak bisa lagi membatalkan**.
Pada titik itu barang sudah dibeli dengan uang relawan, jadi jalan keluarnya bukan pembatalan
sepihak melainkan **komplain** (Bagian B), supaya admin yang memutuskan berdasarkan bukti.

> **Jawaban singkat kalau ditanya:** "Tolak bukti mengulang satu langkah pembayaran, lepas titipan
> mengganti relawannya tapi pesanan tetap hidup, batalkan menutup pesanan untuk kedua pihak dan
> kalau uang sudah masuk akan ditandai wajib refund. Khusus saat bukti transfer sedang menunggu
> diperiksa, hanya ada dua pilihan: verifikasi atau tolak."

---

# BAGIAN B - Alur demo fitur komplain

## B1. Kapan tombol komplain muncul

Tombol **Buka komplain** hanya tampil kalau ketiga syarat ini terpenuhi:

1. Kamu adalah **pihak dalam pesanan** itu (penitip atau relawannya), bukan penonton.
2. Status pesanan berada di antara **Dibayar sampai Sudah Diserahkan**
   (`paid`, `in_progress`, `delivering`, `delivered`).
3. Pesanan itu **belum pernah** punya komplain (satu pesanan maksimal satu komplain).

Alasannya logis: sebelum dibayar belum ada uang yang dipermasalahkan, cukup batalkan saja.
Setelah pesanan selesai, transaksinya sudah ditutup kedua pihak.

## B2. Langkah demo (sisi pengguna)

**Akun yang dipakai:** akun penitip atau relawan dari pesanan yang sedang berjalan.

1. Login, lalu buka menu **Pesanan Saya** di navigasi atas.
2. Klik salah satu pesanan yang statusnya **Dibayar / Sedang Diproses / Sedang Diantar / Sudah Diserahkan**.
3. Lihat **panel kanan** (di HP: scroll ke bawah). Ada kartu kecil berjudul **"Ada masalah?"**
   dengan tombol merah **Buka komplain**.
4. Klik tombol itu, lalu isi formulirnya:
   - **Jenis masalah** (pilih salah satu dari enam):
     Pesanan tidak diserahkan, Barang / hasil cetak tidak sesuai,
     Selisih nota / biaya tidak sesuai, Masalah pembayaran,
     Pihak lain tidak merespons, atau Lainnya.
   - **Kronologi**: wajib, minimal 20 karakter. Contoh untuk demo:
     *"Struk yang dilampirkan tertulis Rp 25.000, tetapi saya sudah transfer Rp 30.000 dan
     selisihnya belum dikembalikan sampai sekarang."*
   - **Bukti (foto)**: opsional, maksimal 4 MB. Untuk demo, lampirkan gambar apa saja.
5. Klik **Kirim komplain**.

**Yang langsung terjadi dan layak ditunjukkan:**

- Status pesanan berubah menjadi **Komplain**, dan muncul banner kuning
  *"Komplain sedang ditinjau admin"* di halaman pesanan.
- Semua admin menerima notifikasi (lonceng di navbar admin bertambah).
- Satu baris baru masuk ke audit trail (timeline di halaman pesanan).
- Bukti yang kamu lampirkan tersimpan privat: hanya pihak pesanan dan admin yang bisa membukanya.

## B3. Langkah demo (sisi admin)

1. Logout, lalu login sebagai admin (`admin.demo@nitip.test`, password `password`).
2. Di sidebar admin, klik menu **Komplain** (alamatnya `/admin/disputes`).
3. Klik komplain yang baru masuk. Di halaman detail, admin melihat semuanya sekaligus:
   kronologi pengadu, bukti foto, bukti transfer, foto struk, dan seluruh timeline audit trail.
4. Pilih salah satu dari tiga keputusan, isi catatan resolusi, lalu kirim:

| Keputusan | Artinya |
|---|---|
| Tandai pesanan selesai | Relawan dianggap benar dan berhak atas pembayaran. Pesanan jadi Selesai. |
| Batalkan pesanan tanpa refund | Pesanan ditutup, tidak ada kewajiban pengembalian dana. |
| Batalkan pesanan dan wajibkan refund | Pesanan ditutup dan ditandai **wajib refund** ke penitip. |

5. Setelah diputus: kedua pihak menerima notifikasi, status komplain menjadi **Selesai**,
   dan keputusan admin tercatat permanen di audit trail lengkap dengan nama admin dan waktunya.

## B4. Data yang sudah siap di server (per 25 November 2026)

Tidak perlu menyiapkan apa pun, tinggal pakai:

| Untuk mendemokan | Pesanan | Status | Login sebagai |
|---|---|---|---|
| **Membuka komplain** | `NT-FBSHMY` | Dibayar | `farhan@student.uph.edu` (kamu sebagai penitip) |
| Alternatif | `NT-4THJ97` | Sedang Diantar | `farhan@student.uph.edu` (kamu sebagai relawan) |
| Alternatif | `NT-JB47WW` | Sudah Diserahkan | `faris@student.uph.edu` |
| **Memutus komplain** | `NT-4PRZWT` | sudah ada komplain terbuka (Selisih nota) | `admin.demo@nitip.test` |

Semua akun demo memakai password `password`.

> **Tips demo:** mulai dari komplain yang sudah ada (`NT-4PRZWT`) kalau waktu sempit, karena
> bagian admin yang paling menarik. Kalau waktu cukup, buka komplain baru dari `NT-FBSHMY`
> supaya dosen melihat alur utuh dari pengaduan sampai keputusan.

## B5. Poin yang layak ditekankan

- Komplain **tidak bisa diajukan sembarangan**: dibatasi peran, tahap, dan satu per pesanan.
- Admin memutus **berdasarkan bukti**, bukan katanya: bukti transfer, foto struk, dan audit trail
  tersedia dalam satu layar.
- Keputusan apa pun tetap **meninggalkan jejak permanen**, jadi tidak ada perubahan status
  yang tidak bisa dipertanggungjawabkan.
- Refund dilakukan **di luar platform** (transfer antar mahasiswa), sistem hanya menandai
  kewajibannya dan mengawasinya. Ini keputusan desain yang kami dokumentasikan, bukan kelalaian.

---

# BAGIAN C - Naskah praktik stress test

Bagian ini untuk menjawab "gimana cara membuktikannya langsung di depan kelas".
Intinya: **jangan jalankan seluruh skenario saat presentasi** karena memakan lebih dari dua menit
dan skenario paling lambat (membuka halaman depan 300 kali) bukan bagian yang menarik.
Jalankan **mode pembuktian lock** yang selesai dalam sekitar 10 detik.

## C1. Persiapan (lakukan sebelum masuk kelas)

```bash
# 1. pastikan paket requests terpasang
pip install requests

# 2. pastikan 40 akun uji ada di server (sudah dibuatkan 25 Nov 2026, ulangi jika data direset)
ssh -i ~/hendra root@76.13.22.7
cd /var/www/nitip
php artisan tinker --execute="require 'qa/stress_test/setup_loadtest_users.php';"
```

Lalu uji coba sekali di kamar, supaya kamu tahu bentuk keluarannya:

```bash
cd qa/stress_test
NITIP_ONLY=race python stress.py
```

## C2. Naskah saat presentasi

### Babak 1 - Jelaskan dulu apa yang mau dibuktikan (sekitar 30 detik)

> "Satu hal yang paling kami khawatirkan dari aplikasi ini adalah kalau dua relawan menekan
> tombol Ambil pada titipan yang sama di detik yang sama. Kalau sistemnya lemah, dua-duanya bisa
> berhasil, satu titipan punya dua relawan, dan pembayarannya jadi kacau.
>
> Kami sudah menanganinya dengan mengunci baris pesanan di database di dalam satu transaksi.
> Tapi daripada sekadar klaim, izinkan kami membuktikannya sekarang juga."

### Babak 2 - Jalankan (sekitar 10 detik)

Ketik di terminal, sambil dibacakan:

```bash
cd qa/stress_test
NITIP_ONLY=race python stress.py
```

> "Skrip ini melakukan login 40 akun uji, membuat 60 titipan sungguhan di server yang tadi
> kita demokan, lalu untuk lima titipan yang berbeda dia menyuruh sepuluh relawan menekan
> tombol Ambil secara bersamaan. Jadi total ada lima puluh percobaan perebutan."

### Babak 3 - Tunjukkan hasilnya (sekitar 1 menit)

Keluarannya akan seperti ini:

```
  ronde 1: order NT-X4GYVF: 1 pemenang dari 10 (OK)
  ronde 2: order NT-VQYWUD: 1 pemenang dari 10 (OK)
  ronde 3: order NT-FBVVA7: 1 pemenang dari 10 (OK)
  ronde 4: order NT-CPXTQK: 1 pemenang dari 10 (OK)
  ronde 5: order NT-Q2352X: 1 pemenang dari 10 (OK)

==========================================================
PEMBUKTIAN LOCK: 5/5 ronde menghasilkan TEPAT 1 pemenang
VERDIKT: LULUS
==========================================================
```

> "Lima ronde, sepuluh relawan tiap ronde, dan hasilnya selalu tepat satu pemenang.
> Sembilan yang lain mendapat pesan bahwa titipannya sudah diambil orang lain."

**Lalu buktikan sekali lagi dari sisi database** (ini yang membuat dosen percaya), buka
phpMyAdmin dan jalankan dengan salah satu kode order yang muncul di layar:

```sql
SELECT code, status, fulfiller_id, matched_at
FROM orders
WHERE code = 'NT-X4GYVF';
```

> "Kolom fulfiller_id-nya hanya terisi satu nilai. Dan kalau kita lihat audit trailnya:"

```sql
SELECT e.type, e.from_status, e.to_status, e.created_at
FROM order_events e
JOIN orders o ON o.id = e.order_id
WHERE o.code = 'NT-X4GYVF';
```

> "Hanya ada satu peristiwa claimed. Sembilan percobaan yang kalah tidak meninggalkan jejak
> perubahan status sama sekali, karena memang ditolak sebelum sempat mengubah apa pun."

## C3. Kalau diminta menunjukkan angka kinerjanya juga

Jangan jalankan live. Buka saja file hasil pengujian lengkap tanggal 10 November:

```bash
cat qa/stress_test/stress_results.json
```

Lalu jelaskan tabel di
[PANDUAN_DATABASE_DAN_STRESS_TEST.md bagian B5](PANDUAN_DATABASE_DAN_STRESS_TEST.md#b5-hasil-pengujian-nyata-10-november-2026-server-live),
terutama kenapa dipakai p95 dan kenapa 7 error di skenario race justru berarti sistemnya benar.

## C4. Opsi menjalankan (ringkasan)

```bash
cd qa/stress_test

# pembuktian lock saja, +- 10 detik  <- PAKAI INI SAAT PRESENTASI
NITIP_ONLY=race python stress.py

# semua skenario, +- 2 menit (untuk laporan, bukan untuk demo)
python stress.py

# latihan ringan ke target lain
NITIP_ONLY=race NITIP_SCALE=0.3 python stress.py http://127.0.0.1:8000
```

## C5. Rencana cadangan

| Masalah | Yang dilakukan |
|---|---|
| Internet kelas mati | Tunjukkan `stress_results.json` yang sudah ada, plus demo manual dua browser berebut satu titipan |
| Python error / paket belum ada | Siapkan screenshot keluaran skrip dari latihan di rumah |
| Takut mengotori data demo | Jalankan stress test **setelah** demo fitur selesai, bukan sebelumnya |

## C6. Membersihkan setelah latihan

Stress test membuat puluhan titipan berjudul "Stress test titipan ...". Hapus supaya data demo
kembali rapi, tanpa menghapus akun uji:

```bash
ssh -i ~/hendra root@76.13.22.7
cd /var/www/nitip
php artisan tinker --execute="App\Models\Order::where('title','like','Stress test%')->get()->each->delete();"
```

> Jangan memakai `migrate:fresh` untuk membersihkan, karena itu ikut menghapus akun demo
> (`demo1`, `demo2`, `admin.demo`) dan seluruh data presentasimu.
