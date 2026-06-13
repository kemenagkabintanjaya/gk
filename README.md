# Sistem Pendataan Gereja Kristen — Kemenag Kabupaten Intan Jaya

Aplikasi web (PHP + SQLite) untuk pendataan gereja Kristen di lingkungan Bimbingan Masyarakat Kristen, Kantor Kementerian Agama Kabupaten Intan Jaya. Dibangun mengikuti struktur **Formulir Pendataan Gereja TA 2026 (Bimas Kristen)**.

## Fitur Utama

- **Dashboard statistik interaktif** — ringkasan jumlah gereja, jemaat, gedung, personil pelayanan, status registrasi & verifikasi; grafik distribusi aras organisasi, kondisi gedung, jemaat per distrik, personil pelayanan, demografi, serta rekap per distrik.
- **Manajemen data gereja** — formulir lengkap 7 bagian sesuai formulir resmi.
- **Verifikasi data** oleh Administrator dan Kepala Seksi.
- **Manajemen pengguna** dan kontrol akses berbasis peran (RBAC).
- **Keamanan**: hashing kata sandi (bcrypt), proteksi CSRF, escaping output (anti-XSS), prepared statement (anti-SQL injection), pembatasan percobaan login, dan timeout sesi.

## Peran Pengguna

| Peran | Hak Akses |
|---|---|
| **Administrator** | Akses penuh: kelola pengguna, semua data gereja, verifikasi, dan hapus data. |
| **Kepala Seksi Bimas Kristen** | Lihat & kelola semua data gereja, lakukan verifikasi. |
| **Penyuluh** | Tambah & kelola data gereja **khusus distriknya sendiri**. |

## Akun Default

| Peran | Username | Kata Sandi |
|---|---|---|
| Administrator | `admin` | `Admin#2026` |
| Kepala Seksi | `kepala` | `Kepala#2026` |
| Penyuluh (Sugapa) | `penyuluh` | `Penyuluh#2026` |

> ⚠️ **Segera ganti seluruh kata sandi default setelah login pertama.**

## Struktur Data Gereja (sesuai formulir Kristen)

1. **Identitas Utama** — nama gereja, singkatan/sinode, aras organisasi (Jemaat / Resort-Klasis / Wilayah / Sinode), nama sinode/induk, alamat, desa, distrik, kontak.
2. **Legalitas** — IMB/PBG, tahun berdiri, SK pendirian, SK pendeta, SK Kemenag RI, status & nomor registrasi.
3. **Fisik & Fasilitas** — jumlah gedung (permanen/semi/darurat), status kepemilikan, daya tampung, perpustakaan.
4. **Demografi Jemaat** — bapak, ibu, pemuda, remaja, anak sekolah minggu, total warga, rekap jenis kelamin.
5. **Pimpinan & Pengurus** — data pendeta/gembala + Badan Pekerja Jemaat (ketua, sekretaris, bendahara, koster).
6. **Personil Pelayanan** (pria/wanita) — Pendeta, Pendeta Muda, Pendeta Pembantu, Majelis/Penatua, Diaken/Syamas, Guru Injil, Guru Sekolah Minggu, Penginjil.
7. **Media & Keterangan** — email, media sosial, jadwal ibadah, catatan.

## Persyaratan

- PHP **8.0+** dengan ekstensi `pdo_sqlite` aktif.
- Server web (Apache dengan `mod_rewrite`, atau Nginx).

## Instalasi

1. Salin seluruh folder ke direktori web server.
2. Pastikan folder `storage/` dapat ditulis (`chmod 775 storage`).
3. Buka `install.php` di browser, lalu klik **Jalankan Instalasi** (membuat database, tabel, akun default, dan data contoh).
4. **Hapus `install.php`** demi keamanan.
5. Buka `login.php` dan masuk, lalu ganti kata sandi default.

## Pindah ke Hosting

- Cukup salin seluruh folder. Pada hosting baru, jalankan `install.php` sekali untuk database baru, **atau** salin juga file `storage/gereja.sqlite` bila ingin memindahkan data yang sudah ada.
- Pastikan PHP 8.0+ dengan `pdo_sqlite`, folder `storage/` writable, aktifkan HTTPS.
- File `.htaccess` hanya berlaku di Apache; untuk Nginx, tambahkan aturan setara untuk memblokir akses ke folder `storage/` dan file `*.sqlite`.

## Struktur Folder

```
gereja-kristen-intanjaya/
├─ index.php            Pengalih ke dashboard / login
├─ install.php          Penyiapan database & data contoh
├─ login.php / logout.php
├─ dashboard.php        Dashboard statistik
├─ data.php             Daftar data gereja
├─ gereja_form.php      Form tambah/edit gereja
├─ gereja_save.php      Penyimpanan data gereja
├─ gereja_delete.php    Hapus data (admin)
├─ verifikasi.php       Detail & verifikasi
├─ verifikasi_save.php
├─ users.php / user_*.php  Manajemen pengguna (admin)
├─ profile.php          Ganti kata sandi sendiri
├─ api.php              Endpoint JSON statistik & data
├─ config.php
├─ lib/                 db.php, auth.php, helpers.php, stats.php
├─ includes/layout.php  Header/nav/footer
├─ assets/              style.css, app.js
└─ storage/             Database SQLite (otomatis dibuat)
```

---
© 2026 Bimbingan Masyarakat Kristen — Kantor Kementerian Agama Kabupaten Intan Jaya, Papua Tengah.
