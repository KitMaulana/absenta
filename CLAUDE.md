# CLAUDE.md — Petunjuk Pengembangan Aplikasi "AbsensiKelas"

> **Instruksi untuk Claude Code:** Kerjakan proyek ini tahap demi tahap sesuai dokumen ini. Setelah setiap tahap selesai, jalankan migrasi/seeder dan pastikan tidak ada error sebelum lanjut ke tahap berikutnya. Jika ada keputusan teknis yang ambigu, pilih solusi paling sederhana yang memenuhi kebutuhan, lalu catat keputusannya di bagian `## CATATAN IMPLEMENTASI` di akhir file ini.

---

## 1. GAMBARAN UMUM

Aplikasi web **mandiri** untuk absensi **satu kelas** (bukan sistem sekolah). Pemiliknya adalah **wali kelas**. Aplikasi ini:

1. **Publik (tanpa login):** orang tua siswa dan siswa dapat melihat **infografis absensi** (harian umum & per mata pelajaran) secara real-time.
2. **Admin (login):** wali kelas, ketua kelas, dan sekretaris kelas menginput absensi, mengelola data, serta mencetak laporan **PDF A4**.
3. Dua jenis absensi:
   - **Absensi Umum Harian** — status kehadiran siswa untuk satu hari (Hadir / Sakit / Izin / Alpa / Dispensasi).
   - **Absensi per Mata Pelajaran (per JP)** — kehadiran siswa dicek per jam pelajaran sesuai jadwal (misal JP 1 Bahasa Indonesia, JP 2 Matematika), input berbentuk **ceklis**.

---

## 2. TEKNOLOGI

| Komponen | Pilihan |
|---|---|
| Framework | **Laravel 11+** (PHP 8.2+) |
| Database | MySQL/MariaDB (XAMPP) — nama DB: `absensi_kelas` |
| Frontend | Blade + **Tailwind CSS** (via Vite) + Alpine.js |
| Grafik | **Chart.js** (CDN atau npm) |
| PDF | `barryvdh/laravel-dompdf` (kertas **A4**, portrait/landscape sesuai kebutuhan) |
| Auth | Laravel Breeze (Blade stack) — hanya untuk admin |
| Icon | Heroicons / Lucide (opsional) |

Jalankan di lingkungan Windows + XAMPP (`C:\xampp\htdocs\ABSENSIKELAS`). Gunakan `php artisan serve` atau virtual host XAMPP.

---

## 3. PERAN & HAK AKSES

| Peran | Login | Hak |
|---|---|---|
| **Publik** (ortu/siswa) | ❌ | Lihat infografis & rekap ringkas. Tidak bisa lihat data sensitif (NISN, no. HP). |
| **Wali Kelas** (super admin) | ✅ | Semua fitur + **menu tambah/edit/hapus admin** + pengaturan kelas. |
| **Ketua Kelas** | ✅ | Input absensi umum & per mapel, lihat rekap, cetak PDF. |
| **Sekretaris Kelas** | ✅ | Sama dengan ketua kelas. |

- Gunakan kolom `role` (enum: `wali_kelas`, `ketua_kelas`, `sekretaris`) di tabel `users`. Tidak perlu paket permission eksternal — cukup middleware `role:wali_kelas` dsb.
- **Menu Manajemen Admin** (khusus wali kelas): tambah admin baru (nama, email, password, peran), nonaktifkan/aktifkan, reset password.
- Minimal harus selalu ada 1 akun wali kelas aktif (cegah penghapusan akun wali kelas terakhir).

---

## 4. STRUKTUR DATABASE

### 4.1 `users`
`id, name, email, password, role (enum), is_active (bool), timestamps`

### 4.2 `settings` (key-value, single row juga boleh)
Identitas kelas untuk header web & kop laporan:
`nama_kelas` (mis. "XII IPA 3"), `nama_sekolah` (opsional, bebas diisi), `nama_wali_kelas`, `tahun_ajaran` (mis. "2026/2027"), `semester` (Ganjil/Genap), `logo` (upload opsional), `hari_aktif` (JSON, default Senin–Jumat/Sabtu).

### 4.3 `students`
`id, no_absen (urutan), nama, nisn (nullable), jenis_kelamin (L/P), no_hp_ortu (nullable), foto (nullable), is_active, timestamps`
- Fitur: CRUD + **import CSV** (kolom: no_absen, nama, nisn, jenis_kelamin) + export CSV.
- Siswa pindah/keluar → nonaktifkan, jangan hapus (riwayat absensi tetap ada).

### 4.4 `subjects` (mata pelajaran)
`id, nama, singkatan, warna (hex, untuk grafik), is_active, timestamps`

### 4.5 `schedules` (jadwal pelajaran kelas)
`id, hari (enum senin..sabtu), jam_ke (int), subject_id, guru_pengampu (string, nullable), jam_mulai (time, nullable), jam_selesai (time, nullable), timestamps`
- Satu hari bisa banyak JP; satu mapel boleh muncul beberapa JP berurutan.
- Jadwal ini menjadi dasar form absensi per mapel: saat admin memilih tanggal, sistem otomatis menampilkan daftar JP + mapel hari itu.

### 4.6 `daily_attendances` (absensi umum harian)
`id, student_id, tanggal (date), status (enum: hadir, sakit, izin, alpa, dispensasi), keterangan (nullable), created_by (user_id), timestamps`
- Unik: `(student_id, tanggal)`.

### 4.7 `subject_attendances` (absensi per mapel/JP)
`id, student_id, tanggal (date), schedule_id, status (enum: hadir, sakit, izin, alpa, dispensasi), keterangan (nullable), created_by (user_id), timestamps`
- Unik: `(student_id, tanggal, schedule_id)`.

### 4.8 `holidays` (opsional tapi disarankan)
`id, tanggal, keterangan` — hari libur tidak dihitung dalam persentase kehadiran.

---

## 5. FITUR SISI ADMIN

### 5.1 Dashboard
- Kartu ringkasan hari ini: jumlah hadir/sakit/izin/alpa, % kehadiran hari ini, % kehadiran bulan berjalan.
- Grafik mini tren kehadiran 30 hari terakhir.
- Peringatan: siswa dengan alpa ≥ 3 dalam sebulan (daftar merah).
- Status input: "Absensi umum hari ini: ✅ sudah / ⚠️ belum", "Absensi mapel JP x: belum diinput".

### 5.2 Input Absensi Umum Harian
- Pilih tanggal (default hari ini) → tabel semua siswa aktif.
- Default semua siswa **Hadir**; admin tinggal mengubah yang tidak hadir (radio/tombol pilihan S/I/A/D) + kolom keterangan.
- Tombol "Tandai semua hadir". Simpan = upsert.
- Bisa edit hari-hari sebelumnya (untuk koreksi), dengan catatan `updated_at`.

### 5.3 Input Absensi per Mapel (ceklis per JP)
- Pilih tanggal → sistem tampilkan **tab/accordion per JP** sesuai jadwal hari itu (mis. JP1–2 Bahasa Indonesia, JP3–4 Matematika).
- Di tiap JP: tabel siswa dengan **checkbox "Hadir"** (dicentang = hadir). Yang tidak dicentang wajib dipilih statusnya (S/I/A/D) — default mengikuti status absensi umum hari itu jika sudah diinput (mis. siswa sakit di absensi umum otomatis terisi "Sakit" di semua JP, tetap bisa diubah).
- Tombol cepat: "Salin dari absensi umum", "Salin dari JP sebelumnya", "Centang semua".
- Simpan per JP (upsert).

### 5.4 Rekapitulasi & Laporan (semuanya bisa **cetak PDF A4**)
Filter periode: harian / mingguan / bulanan / semester / rentang tanggal custom.
1. **Rekap Absensi Umum** — tabel siswa × tanggal (kode H/S/I/A/D) + total & persentase per siswa. Bulanan: landscape A4.
2. **Rekap per Mapel** — pilih mapel → tabel kehadiran siswa di mapel tsb + persentase.
3. **Rekap per Siswa** — profil kehadiran satu siswa (umum + semua mapel), cocok untuk laporan ke orang tua.
4. **Surat/Laporan Bulanan Wali Kelas** — ringkasan + tanda tangan wali kelas (kop dari `settings`).
5. **Infografis versi cetak** — halaman print-friendly berisi grafik yang sama dengan halaman publik (render Chart.js → gambar via `toBase64Image()` lalu masukkan ke PDF, ATAU sediakan tombol "Print halaman ini" dengan CSS `@media print` bila lebih sederhana — pilih salah satu, utamakan hasil rapi di A4).

### 5.5 Master Data
- CRUD Siswa (+ import/export CSV), CRUD Mapel, CRUD Jadwal (form per hari), CRUD Hari Libur, Pengaturan Kelas (identitas + logo), Manajemen Admin (khusus wali kelas).

---

## 6. SISI PUBLIK (TANPA LOGIN)

Halaman: `/` (landing infografis). Desain bersih, mobile-first (mayoritas ortu buka via HP).

Konten:
1. **Header**: nama kelas, wali kelas, tahun ajaran/semester, tanggal hari ini.
2. **Ringkasan hari ini**: kartu jumlah Hadir/Sakit/Izin/Alpa + % kehadiran; daftar **nama siswa yang tidak hadir hari ini beserta status** (tanpa data sensitif lain).
3. **Grafik**:
   - Donut: komposisi kehadiran bulan berjalan.
   - Line: tren % kehadiran harian 30 hari terakhir.
   - Bar: % kehadiran per mata pelajaran (bulan berjalan).
4. **Pencarian siswa**: ketik nama → tampil ringkasan kehadiran siswa tsb (jumlah H/S/I/A dan % per periode). Cukup nama & no absen, tanpa NISN/no HP.
5. Pemilih periode sederhana (bulan ini / bulan lalu / semester).
6. Footer: "Data diperbarui otomatis dari input admin kelas".

Endpoint data grafik boleh via route JSON internal (tanpa auth, read-only, hanya agregat).

---

## 7. STRUKTUR ROUTE (RINGKAS)

```
/                       → publik: infografis
/siswa/{id}/ringkasan   → publik: ringkasan per siswa (read-only)
/login                  → auth admin
/admin                  → dashboard
/admin/absensi-umum
/admin/absensi-mapel
/admin/rekap/{jenis}    → umum|mapel|siswa|bulanan
/admin/rekap/{jenis}/pdf
/admin/siswa, /admin/mapel, /admin/jadwal, /admin/libur
/admin/pengaturan
/admin/admins           → khusus wali_kelas
```

---

## 8. SEEDER & DATA CONTOH

Buat seeder agar aplikasi langsung bisa didemokan:
- 1 akun wali kelas: `walikelas@kelas.test` / `password` (role `wali_kelas`).
- 1 ketua kelas & 1 sekretaris.
- 36 siswa contoh (18 L, 18 P).
- 8 mapel contoh (Bahasa Indonesia, Matematika, Fisika, Kimia, Biologi, Bahasa Inggris, PJOK, PAI) dengan warna berbeda.
- Jadwal Senin–Jumat, 8–10 JP per hari.
- Absensi dummy 30 hari terakhir (acak realistis: ±92% hadir).

---

## 9. STANDAR KUALITAS

- Validasi semua input (Form Request), pesan error bahasa Indonesia (`lang/id`).
- Semua tanggal ditampilkan format Indonesia (Senin, 1 September 2026) — gunakan Carbon locale `id`.
- Upsert absensi memakai `updateOrCreate` dalam transaction.
- Halaman publik hanya query agregat + nama; **jangan pernah** mengekspos NISN/no HP di publik.
- PDF: header kop (logo + identitas kelas), footer "Dicetak {tanggal} oleh {nama admin}".
- Responsif: admin nyaman di laptop, publik nyaman di HP.
- Tulis README singkat: cara install (composer, npm, .env, migrate --seed), akun demo.

---

## 10. URUTAN PENGERJAAN

1. Init Laravel + Breeze + Tailwind + dompdf; konfigurasi `.env`, locale `id`, timezone `Asia/Jakarta`.
2. Migrasi semua tabel + model + relasi + seeder.
3. Middleware role + Manajemen Admin.
4. Master data (siswa, mapel, jadwal, libur, pengaturan).
5. Input absensi umum harian.
6. Input absensi per mapel (ceklis per JP).
7. Halaman publik + infografis Chart.js.
8. Rekap + export PDF A4 (semua jenis).
9. Dashboard admin + peringatan alpa.
10. Polish UI, uji alur lengkap, tulis README.

---

## CATATAN IMPLEMENTASI

Keputusan teknis yang diambil selama pengerjaan, beserta alasannya.

### 1. Laravel 12, bukan Laravel 11

Dokumen meminta "Laravel 11+". Seluruh rilis Laravel 11 (v11.31.0–v11.56.1) diblokir oleh
Composer karena enam advisory keamanan aktif, sehingga `composer create-project` gagal.
Dipakai **Laravel 12.69.1**, yang tetap memenuhi "11+" dan masih mendukung PHP 8.2.
Tidak ada perbedaan API yang relevan untuk aplikasi ini.

### 2. Tanpa Node/npm — Tailwind, Alpine, dan Chart.js lewat CDN

Node.js tidak terpasang di mesin ini, jadi Vite tidak bisa membangun aset. Ketiga pustaka
frontend dimuat dari CDN di `resources/views/layouts/admin.blade.php` dan `layouts/publik.blade.php`:

- Tailwind Play CDN (`cdn.tailwindcss.com`, dengan plugin `forms`)
- Alpine.js 3.14
- Chart.js 4.4

Konsekuensi: **butuh koneksi internet** untuk tampilan penuh. Bila nanti perlu offline,
unduh ketiga berkas ke `public/vendor/` lalu ganti `<script src>` di kedua layout — tidak ada
kode lain yang perlu diubah. Berkas `vite.config.js` dan `package.json` dibiarkan apa adanya
agar mudah beralih ke build Vite di kemudian hari.

### 3. Tanpa Laravel Breeze — auth ditulis manual

Breeze menerbitkan aset yang harus dibangun dengan npm, yang tidak tersedia. Autentikasi
admin dibuat langsung di `LoginController` (~60 baris): `Auth::attempt()` dengan syarat
`is_active = true`, rate limit 5 percobaan per menit per email+IP, dan regenerasi sesi.
Fitur Breeze yang tidak dipakai aplikasi ini (registrasi mandiri, verifikasi email, reset
sandi via email) memang tidak dibutuhkan — penambahan admin dilakukan wali kelas lewat
menu Manajemen Admin, dan reset sandi dilakukan wali kelas dengan sandi acak sekali tampil.

### 4. Tabel `settings` berbentuk key-value

Dokumen membolehkan key-value atau baris tunggal. Dipilih **key-value** agar penambahan
pengaturan baru tidak perlu migrasi. `Setting::map()` menyatukan nilai tersimpan dengan
`Setting::DEFAULTS` dan menyimpannya di cache selamanya; cache dibersihkan otomatis lewat
model event `saved`/`deleted`. `hari_aktif` disimpan sebagai JSON dan selalu dikembalikan
sebagai array oleh `Setting::hariAktif()`.

Ditambahkan satu kunci di luar dokumen: **`nip_wali_kelas`** (opsional), karena blok tanda
tangan pada laporan resmi lazim mencantumkan NIP.

### 5. Dispensasi dihitung sebagai hadir

Dokumen tidak menyebut cara memperlakukan status Dispensasi dalam persentase. Diputuskan
**dispensasi + hadir dihitung sebagai kehadiran** (siswa sedang mewakili sekolah, bukan
membolos). Rumus tunggal ada di `RekapService::persen()` dan dipakai seragam oleh dashboard,
halaman publik, dan seluruh PDF. Keterangan ini dicantumkan di catatan kaki laporan.

### 6. Persentase dihitung terhadap entri tercatat, bukan hari efektif

Pembaginya adalah jumlah entri absensi milik siswa tersebut, bukan jumlah hari efektif.
Dengan begitu hari yang belum diinput tidak menurunkan persentase siswa secara keliru.
Jumlah hari efektif tetap ditampilkan terpisah sebagai konteks.

### 7. Hari efektif = hari aktif dikurangi hari libur

`RekapService::hariEfektif()` menyaring rentang tanggal dengan dua syarat: hari tersebut
termasuk `hari_aktif` di pengaturan, dan tidak terdaftar di tabel `holidays`. Akhir pekan
tidak perlu didaftarkan sebagai libur — cukup tidak dicentang di Pengaturan Kelas.

### 8. Absensi per mapel: checkbox + radio tersembunyi

Dokumen meminta input berbentuk ceklis, tetapi status yang disimpan ada lima. Solusinya:
tiap baris punya satu checkbox "Hadir" dan satu grup radio S/I/A/D yang berbagi `name`
yang sama (`status[siswa_id]`) dengan sebuah radio `value="hadir"` tersembunyi. Alpine
menjaga keduanya sinkron, sehingga tepat satu nilai selalu terkirim tanpa perlu logika
penggabungan di sisi server. Saat checkbox dilepas dan belum ada status lain terpilih,
sistem otomatis memilih **Alpa** sebagai nilai paling aman.

Urutan nilai awal tiap JP: data JP yang sudah tersimpan → status absensi umum hari itu →
Hadir. Ini memenuhi permintaan "siswa sakit di absensi umum otomatis terisi Sakit di semua JP".

### 9. Tombol salin lewat JSON, bukan reload

"Salin dari absensi umum" dan "Salin dari JP sebelumnya" memanggil
`POST /admin/absensi-mapel/salin` yang mengembalikan JSON, lalu Alpine mengisi form di
tempat. Alternatifnya adalah reload halaman dengan query string, tetapi itu akan membuang
perubahan yang belum disimpan pada JP lain yang sedang dibuka.

### 10. Kolom DATE disimpan eksplisit sebagai `Y-m-d`

Cast bawaan `'date'` menulis nilai ke database dalam format `Y-m-d H:i:s`. MySQL memotong
bagian jam secara diam-diam pada kolom DATE, tetapi mesin lain (mis. SQLite yang dipakai
test) tidak — akibatnya `updateOrCreate` gagal menemukan baris yang sudah ada, lalu menabrak
unique index. `DailyAttendance`, `SubjectAttendance`, dan `Holiday` karena itu memakai
mutator `Attribute` yang selalu menyimpan `toDateString()` dan mengembalikan `CarbonImmutable`
saat dibaca. Bug ini ditemukan oleh test dan diperbaiki di kodenya, bukan di testnya.

### 11. Pemformat tanggal Indonesia sendiri, tanpa `intl`

Ekstensi `intl` sering tidak aktif di XAMPP bawaan, dan `setlocale` untuk bahasa Indonesia
tidak tersedia di Windows. Kelas `App\Support\Tanggal` memetakan nama hari dan bulan secara
manual, sehingga format "Senin, 1 September 2026" selalu benar tanpa dependensi tambahan.
Locale Carbon tetap diset ke `id` di `AppServiceProvider` untuk fungsi lain seperti `diffForHumans()`.

### 12. Lebar kolom PDF memakai persen, bukan piksel

Awalnya lebar kolom tabel PDF ditulis dalam piksel. dompdf menangani konflik lebar tetap
dengan melebarkan tinggi baris secara berlebihan — rekap per mapel membengkak jadi 4 halaman
untuk 36 siswa. Setelah diganti ke persen, jumlah halaman turun ke 2. Pada rekap umum,
lebar kolom tanggal dihitung dinamis di template mengikuti panjang periode.

### 13. Infografis cetak menyediakan dua jalur

Dokumen mempersilakan memilih salah satu; di sini keduanya disediakan karena masing-masing
punya kelebihan:

- **Cetak PDF A4** — dompdf tidak menjalankan JavaScript, sehingga grafik digambar sebagai
  batang CSS statis. Hasilnya konsisten dan bisa diarsipkan.
- **Print halaman ini** — memakai `@media print` di browser dengan grafik Chart.js asli
  (donat, garis, batang berwarna). Hasilnya lebih kaya tetapi bergantung pada browser.

Pendekatan `toBase64Image()` sengaja tidak dipakai karena menuntut alur bolak-balik
browser→server yang jauh lebih rumit untuk keuntungan yang kecil.

### 14. Orientasi PDF ditentukan otomatis

Rekap umum berisi matriks siswa × tanggal yang melebar seiring panjang periode, jadi
orientasinya beralih ke **landscape** ketika periode melebihi 7 hari. Laporan lain selalu
portrait. Semua memakai kertas A4.

### 15. Session dan cache memakai driver `file`

Lebih sederhana untuk instalasi XAMPP satu mesin dan menghilangkan ketergantungan pada
tabel database saat sesi dibuat. Migrasi tabel `sessions` tetap disertakan (bawaan Laravel)
bila nanti ingin beralih ke `SESSION_DRIVER=database`. Migrasi tabel `jobs` dihapus karena
aplikasi ini tidak memakai antrean.

### 16. Penjagaan "minimal satu wali kelas aktif"

Aturan ini berlaku di tiga jalur: `update`, `toggle`, dan `destroy` pada `AdminUserController`.
Yang paling penting adalah **`update`**, karena form edit adalah satu-satunya tempat seorang
wali kelas bisa mencabut status dirinya sendiri (mengganti peran atau membuang centang aktif).
Jalur `toggle` dan `destroy` sudah lebih dulu menolak aksi terhadap akun sendiri. Akun yang
dinonaktifkan saat sesinya masih berjalan langsung dikeluarkan oleh middleware `EnsureActive`.

### 17. Hal kecil lainnya

- **Import CSV** menyediakan dua mode: *tambah/perbarui* (cocokkan berdasarkan nama) dan
  *ganti daftar kelas* (nonaktifkan semua lebih dulu). Export menyertakan BOM UTF-8 agar
  Excel membaca nama bahasa Indonesia dengan benar.
- **Penghapusan** ditolak bila data masih dirujuk: siswa yang punya riwayat absensi, mapel
  yang dipakai jadwal, dan jadwal yang punya riwayat absensi. Pesan penolakan mengarahkan
  ke tindakan yang benar (nonaktifkan).
- **Pencarian publik** membutuhkan minimal 2 huruf dan dibatasi 10 hasil, mengembalikan
  hanya `id`, `nama`, `no_absen`, dan URL — NISN serta nomor HP tidak pernah ikut terkirim.
  Ada test khusus yang memastikan hal ini.
- **`orderByRaw`** memakai `CASE`, bukan `FIELD()`, agar query tetap jalan di luar MySQL.
- **60 test** (`php artisan test`) mencakup upsert absensi, tombol salin, hak akses per peran,
  penjagaan wali kelas terakhir, privasi halaman publik, import/export CSV, perhitungan hari
  efektif, dan validitas kelima berkas PDF.

### Yang belum dikerjakan

- Kolom `foto` pada tabel `students` sudah ada di skema, tetapi belum ada antarmuka unggah.
  Aplikasi berjalan normal tanpanya; tambahkan bila nanti dibutuhkan.
- Aset frontend masih dari CDN (lihat poin 2).
