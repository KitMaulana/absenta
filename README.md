# AbsensiKelas

Aplikasi web mandiri untuk absensi **satu kelas**, dimiliki oleh wali kelas.

- **Publik (tanpa login)** — orang tua dan siswa melihat infografis kehadiran harian dan per mata pelajaran secara real-time.
- **Admin (login)** — wali kelas, ketua kelas, dan sekretaris menginput absensi, mengelola data, dan mencetak laporan PDF A4.

Dua jenis absensi didukung: **absensi umum harian** (satu status per siswa per hari) dan **absensi per mata pelajaran** (ceklis kehadiran per jam pelajaran mengikuti jadwal).

---

## Kebutuhan

| Komponen | Versi |
|---|---|
| PHP | 8.2+ (XAMPP bawaan sudah cukup) |
| Composer | 2.x |
| MySQL / MariaDB | 10.4+ |
| Node.js | **tidak diperlukan** — aset memakai CDN |

Ekstensi PHP yang harus aktif: `pdo_mysql`, `mbstring`, `gd`, `zip`, `fileinfo`, `openssl`, `dom`.

---

## Instalasi

```bash
# 1. Masuk ke folder proyek
cd C:\xampp\htdocs\absensi-siswa

# 2. Pasang dependensi PHP
composer install

# 3. Siapkan konfigurasi
copy .env.example .env
php artisan key:generate
```

Nyalakan **Apache** dan **MySQL** dari XAMPP Control Panel, lalu buat databasenya:

```bash
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE absensi_kelas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
```

Sesuaikan `.env` bila kredensial MySQL Anda berbeda:

```dotenv
DB_DATABASE=absensi_kelas
DB_USERNAME=root
DB_PASSWORD=
```

Jalankan migrasi beserta data contoh:

```bash
php artisan migrate --seed
php artisan storage:link   # agar logo sekolah tampil
```

Jalankan aplikasi:

```bash
php artisan serve
```

Buka <http://127.0.0.1:8000>.

> Ingin memakai virtual host XAMPP? Arahkan `DocumentRoot` ke folder `public/` proyek ini.

---

## Akun demo

Semua akun memakai kata sandi **`password`**.

| Peran | Email | Hak akses |
|---|---|---|
| Wali Kelas | `walikelas@kelas.test` | Semua fitur + Pengaturan Kelas + Manajemen Admin |
| Ketua Kelas | `ketua@kelas.test` | Input absensi, rekap, cetak PDF |
| Sekretaris | `sekretaris@kelas.test` | Sama dengan ketua kelas |

Seeder mengisi 36 siswa, 8 mata pelajaran, jadwal Senin–Jumat, dan absensi dummy 30 hari terakhir (±92% hadir) supaya semua grafik dan laporan langsung berisi.

Ganti kata sandi demo sebelum dipakai sungguhan: **Manajemen Admin → Reset Sandi**.

---

## Peta halaman

### Publik (tanpa login)

| URL | Isi |
|---|---|
| `/` | Ringkasan hari ini, daftar siswa tidak hadir, pencarian siswa, grafik donat/garis/batang |
| `/siswa/{id}/ringkasan` | Rekap kehadiran satu siswa (umum + per mapel) |
| `/data/grafik` | Endpoint JSON agregat untuk Chart.js |

Halaman publik **tidak pernah** mengirim NISN atau nomor HP orang tua — hanya nama, nomor absen, dan angka agregat.

### Admin

| URL | Isi |
|---|---|
| `/admin` | Dashboard: kartu hari ini, tren 30 hari, status input per JP, daftar merah alpa ≥ 3 |
| `/admin/absensi-umum` | Input absensi harian (default semua hadir) |
| `/admin/absensi-mapel` | Input per JP dengan ceklis + tombol salin |
| `/admin/rekap/{jenis}` | `umum`, `mapel`, `siswa`, `bulanan`, `infografis` |
| `/admin/rekap/{jenis}/pdf` | Versi cetak A4 |
| `/admin/siswa`, `/mapel`, `/jadwal`, `/libur` | Master data |
| `/admin/pengaturan` | Identitas kelas + logo (khusus wali kelas) |
| `/admin/admins` | Manajemen akun admin (khusus wali kelas) |

---

## Cara pakai singkat

**Menyiapkan kelas baru**

1. **Pengaturan Kelas** — isi nama kelas, sekolah, wali kelas, tahun ajaran, dan centang hari aktif.
2. **Siswa** — tambah manual atau **Import CSV** (kolom wajib: `no_absen`, `nama`, `jenis_kelamin`).
3. **Mata Pelajaran** — daftarkan mapel beserta warna grafiknya.
4. **Jadwal** — susun jam pelajaran per hari. Jadwal inilah yang membentuk form absensi per mapel.
5. **Hari Libur** — catat tanggal libur agar tidak dihitung sebagai hari efektif.

**Rutinitas harian**

1. Buka **Absensi Umum**, semua siswa sudah default *Hadir*. Ubah yang tidak hadir, isi keterangan, simpan.
2. Buka **Absensi per Mapel**, pilih tab JP. Status mengikuti absensi umum secara otomatis. Gunakan **Salin dari absensi umum** atau **Salin dari JP sebelumnya** untuk mempercepat, lalu simpan per JP.

**Akhir bulan**

Buka **Laporan Bulanan**, pilih periode, klik **Cetak PDF A4**.

---

## Import CSV siswa

Baris pertama harus header. Contoh:

```csv
no_absen,nama,nisn,jenis_kelamin
1,Ahmad Fauzan,0012345678,L
2,Alya Rahmawati,0012345679,P
```

- Kolom wajib: `no_absen`, `nama`, `jenis_kelamin` (`L` atau `P`).
- Kolom opsional: `nisn`, `no_hp_ortu`.
- Baris yang tidak lengkap dilewati dan dilaporkan setelah impor.
- Mode **Tambah/perbarui** mencocokkan siswa berdasarkan nama. Mode **Ganti daftar kelas** menonaktifkan semua siswa lama lebih dulu.

Gunakan **Export CSV** untuk mengunduh templat berisi data saat ini.

---

## Menjalankan test

```bash
php artisan test
```

60 test mencakup penyimpanan absensi (termasuk upsert), tombol salin, hak akses per peran, penjagaan "minimal satu wali kelas aktif", privasi halaman publik, import/export CSV, perhitungan hari efektif, dan validitas kelima berkas PDF.

Test memakai SQLite in-memory sehingga tidak menyentuh database `absensi_kelas`.

---

## Catatan operasional

- **Backup.** Seluruh data ada di database `absensi_kelas`. Cadangkan berkala:
  `C:\xampp\mysql\bin\mysqldump.exe -u root absensi_kelas > backup.sql`
- **Siswa pindah/keluar** — nonaktifkan, jangan hapus, agar laporan periode lama tetap akurat. Aplikasi menolak penghapusan siswa yang sudah punya riwayat absensi.
- **Koreksi absensi** — pilih tanggal lampau pada form input; penyimpanan bersifat *upsert* sehingga data lama diperbarui, bukan digandakan.
- **Internet** — Tailwind, Alpine.js, dan Chart.js dimuat dari CDN, jadi tampilan penuh butuh koneksi internet. Lihat `CATATAN IMPLEMENTASI` di `CLAUDE (2).md` untuk cara beralih ke aset lokal.
