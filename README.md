<div align="center">
  <img src="public/images/sipkl-mark.svg" alt="Logo SIPKL" width="96" height="96">

  <h1>SIPKL — Sistem Informasi Praktik Kerja Lapangan</h1>
  <p><strong>Satu sistem untuk lima peran</strong> &mdash; admin, dosen pembimbing, koordinator, pimpinan, dan mahasiswa</p>

  <p>
    <a href="https://www.php.net/"><img src="https://img.shields.io/badge/PHP-8.2-777BB4?logo=php&logoColor=white" alt="PHP 8.2"></a>
    <a href="https://laravel.com/"><img src="https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel&logoColor=white" alt="Laravel 11"></a>
    <a href="https://www.mysql.com/"><img src="https://img.shields.io/badge/MySQL-8-4479A1?logo=mysql&logoColor=white" alt="MySQL 8"></a>
    <a href="https://tailwindcss.com/"><img src="https://img.shields.io/badge/Tailwind_CSS-3-06B6D4?logo=tailwindcss&logoColor=white" alt="Tailwind CSS"></a>
    <a href="https://vitejs.dev/"><img src="https://img.shields.io/badge/Vite-6-646CFF?logo=vite&logoColor=white" alt="Vite"></a>
    <img src="https://img.shields.io/badge/Lisensi-MIT-green" alt="Lisensi MIT">
  </p>
</div>

<p align="center">
  <img src="public/template/mahasiswa.png" alt="Dashboard mahasiswa SIPKL" width="820">
</p>

---

## Daftar Isi

- [Tentang Project](#tentang-project)
- [Masalah yang Diselesaikan](#masalah-yang-diseselesaikan)
- [Fitur Utama](#fitur-utama)
- [Modul per Peran](#modul-per-peran)
- [Tampilan](#tampilan)
- [Teknologi](#teknologi)
- [Cara Instalasi](#cara-instalasi)
- [Akun Demo](#akun-demo)
- [Struktur Project](#struktur-project)
- [Keamanan](#keamanan)
- [Pengujian](#pengujian)
- [Kontributor](#kontributor)
- [Lisensi](#lisensi)

---

## Tentang Project

**SIPKL** adalah aplikasi web berbasis Laravel untuk mengelola seluruh siklus **Praktik Kerja Lapangan (PKL)** di lingkungan Jurusan &mdash; mulai dari pendaftaran, penempatan perusahaan mitra, pengajuan bimbingan, monitoring harian, sampai penilaian akhir dan penerbitan sertifikat.

Sebelum sistem ini, proses PKL tersebar di berkas, grup chat, dan catatan pribadi. Data mahasiswa, absensi, dan nilai sulit dilacak, tidak ada satu tempat yang bisa dijadikan rujukan bersama, dan setiap role bekerja dengan format yang berbeda-beda.

> Dibuat sebagai proyek pribadi.

### Masalah yang Diselesaikan

| Sebelum (manual) | Sekarang (SIPKL) |
| :--- | :--- |
| Pendaftaran lewat formulir kertas dan berkas WhatsApp | Pendaftaran daring, diverifikasi admin, dan statusnya bisa dipantau mahasiswa |
| Jadwal bimbingan dicatat di buku atau chat | Mahasiswa mengajukan, dosen menyetujui, seluruh riwayat tersimpan di sistem |
| Absensi dan jurnal berupa berkas yang menumpuk | Absensi check-in/out, jurnal harian, dan review langsung dari dosen |
| Nilai PKL dihitung manual dari berkas | Nilai dihitung sistem setelah jumlah sesi bimbingan memenuhi syarat |
| Sertifikat diminta keTU dan butuh waktu lama | Sertifikat terbit di sistem, bisa diunduh, dan bisa diverifikasi publik lewat kode |
| Tidak ada jejak audit | Audit log untuk setiap aksi penting, dilengkapi halaman _System Health_ |

---

## Fitur Utama

- **Lima peran, satu basis data** &mdash; `admin`, `dosen`, `koordinator`, `pimpinan`, dan `mahasiswa`, masing-masing punya dashboard dan menu sesuai kewenangannya.
- **Satu kolom login untuk semua** &mdash; NIM, NIP, atau email bisa dipakai pada kolom yang sama; sistem menentukan peran secara otomatis.
- **Login lewat QR Code** &mdash; buat tautan QR dari perangkat yang sudah login, pindai di perangkat baru, lalu setujui dari dashboard. Praktis untuk perangkat bersama atau saat berada di lokasi PKL.
- **Monitoring PKL** &mdash; absensi, jurnal, izin, dan progres bimbingan terpantau dari dashboard dosen, koordinator, dan admin.
- **Penilaian yang bisa dikonfigurasi** &mdash; kategori dan komponen penilaian diatur admin, sehingga bobot penilaian tidak ditulis mati di kode.
- **Manajemen dokumen** &mdash; format laporan resmi diunggah admin, lalu dipakai mahasiswa dan dosen dari berkas yang sama.
- **Sidang dan presentasi** &mdash; penjadwalan sidang, peserta, penguji, serta input nilai final sudah tertata rapi.
- **Sertifikat dengan verifikasi publik** &mdash; setiap sertifikat punya kode unik yang bisa dicek siapa pun tanpa login.
- **Impor data massal** &mdash; data mahasiswa dan dosen bisa diimpor dari Excel, lengkap dengan laporan baris yang gagal.
- **Audit log dan system health** &mdash; aksi sensitif tercatat dan bisa diekspor; halaman kesehatan sistem menampilkan status komponen penting.
- **Landing page publik** &mdash; halaman depan tanpa login yang menjelaskan alur kerja setiap peran.

---

## Modul per Peran

| Peran | Modul yang diakses |
| :--- | :--- |
| **Admin** | Dashboard statistik, kelola mahasiswa dan dosen (manual + impor Excel), perusahaan mitra, verifikasi pendaftaran dan laporan PKL, upload format laporan, periode PKL, sidang dan presentasi, sertifikat (termasuk pencabutan), konfigurasi penilaian, pengumuman, manajemen pengguna, monitoring, audit log, system health, pengaturan sistem |
| **Dosen Pembimbing** | Daftar mahasiswa bimbingan, verifikasi jadwal bimbingan, konfirmasi absensi, review jurnal harian, proses izin, tugas PKL dan review pengumpulan, penilaian per kategori, input dan finalisasi nilai PKL, monitoring mahasiswa bimbingan |
| **Mahasiswa** | Dashboard PKL, pendaftaran PKL, daftar perusahaan mitra, pengajuan jadwal bimbingan, absensi check-in/out dan rekap, jurnal harian, pengajuan izin, tugas PKL, unggah laporan sesuai format resmi, unduh sertifikat |
| **Koordinator** | Dashboard PKL, monitoring seluruh mahasiswa, detail monitoring per mahasiswa, verifikasi pendaftaran PKL |
| **Pimpinan** | Dashboard eksekutif, rekap dan laporan PKL, ekspor laporan |
| **Semua peran** | Notifikasi, pengumuman, profil dan ganti kata sandi, daftar perangkat terhubung, login QR |

---

## Tampilan

### Login

<table>
  <tr>
    <td><img src="public/screenshots/Login-page.png" alt="Halaman login SIPKL" width="260"></td>
  </tr>
</table>

### Admin

<table>
  <tr>
    <td><img src="public/screenshots/Admin-1.png" alt="Dashboard admin" width="260"></td>
    <td><img src="public/screenshots/Admin-2.png" alt="Kelola data mahasiswa" width="260"></td>
  </tr>
  <tr>
    <td><img src="public/screenshots/Admin-3.png" alt="Verifikasi pendaftaran PKL" width="260"></td>
    <td><img src="public/screenshots/Admin-4.png" alt="Verifikasi laporan PKL" width="260"></td>
  </tr>
  <tr>
    <td><img src="public/screenshots/Admin-5.png" alt="Perusahaan mitra" width="260"></td>
    <td><img src="public/screenshots/Admin-6.png" alt="Format laporan" width="260"></td>
  </tr>
  <tr>
    <td><img src="public/screenshots/Admin-7.png" alt="Sertifikat" width="260"></td>
    <td><img src="public/screenshots/Admin-8.png" alt="Audit log" width="260"></td>
  </tr>
</table>

### Mahasiswa

<table>
  <tr>
    <td><img src="public/screenshots/Mahasiswa-1.png" alt="Dashboard mahasiswa" width="260"></td>
    <td><img src="public/screenshots/Mahasiswa-2.png" alt="Pendaftaran PKL" width="260"></td>
  </tr>
  <tr>
    <td><img src="public/screenshots/Mahasiswa-3.png" alt="Jadwal bimbingan" width="260"></td>
    <td><img src="public/screenshots/Mahasiswa-4.png" alt="Absensi" width="260"></td>
  </tr>
  <tr>
    <td><img src="public/screenshots/Mahasiswa-5.png" alt="Jurnal harian" width="260"></td>
    <td><img src="public/screenshots/Mahasiswa-6.png" alt="Unggah laporan" width="260"></td>
  </tr>
  <tr>
    <td><img src="public/screenshots/Mahasiswa-7.png" alt="Sertifikat" width="260"></td>
    <td></td>
  </tr>
</table>

### Dosen

<table>
  <tr>
    <td><img src="public/screenshots/Dosen-1.png" alt="Dashboard dosen" width="260"></td>
    <td><img src="public/screenshots/Dosen-2.png" alt="Mahasiswa bimbingan" width="260"></td>
  </tr>
  <tr>
    <td><img src="public/screenshots/Dosen-3.png" alt="Verifikasi jadwal bimbingan" width="260"></td>
    <td><img src="public/screenshots/Dosen-4.png" alt="Monitoring" width="260"></td>
  </tr>
  <tr>
    <td><img src="public/screenshots/Dosen-5.png" alt="Input nilai PKL" width="260"></td>
    <td></td>
  </tr>
</table>

---

## Teknologi

| Lapisan | Teknologi |
| :--- | :--- |
| Backend | Laravel 11, PHP 8.2, Eloquent ORM, Laravel Breeze |
| Database | MySQL 8, 20 migrasi, 41 model |
| Frontend | Blade, Tailwind CSS 3 (token Material 3), Alpine.js, Bootstrap 5, Vanilla JavaScript |
| Asset dan build | Vite 6, PostCSS, Autoprefixer |
| Fitur unggulan | QR Code (`qrcode` dan `jsQR`), Maatwebsite Excel, SweetAlert2, Chart.js |
| Kualitas | PHPUnit 11, Laravel Pint, feature test untuk auth, QR login, flash message, dan profil |

---

## Cara Instalasi

### Prasyarat

| Kebutuhan | Versi |
| :--- | :--- |
| PHP | 8.2 atau lebih baru, dengan ekstensi `pdo_mysql`, `mbstring`, `openssl`, dan `fileinfo` |
| Composer | 2.x |
| Node.js | 18 atau lebih baru |
| Database | MySQL 8 atau MariaDB 10.4 ke atas |

### 1. Clone repository

```bash
git clone https://github.com/muizfrhan/Sistem-Informasi-Praktik-Kerja-Lapangan.git
cd Sistem-Informasi-Praktik-Kerja-Lapangan
```

### 2. Install dependency

```bash
composer install
npm install
```

### 3. Konfigurasi environment

```bash
cp .env.example .env      # Windows: copy .env.example .env
php artisan key:generate
```

Lalu sesuaikan bagian database di `.env`. Untuk XAMPP, biasanya user `root` tanpa kata sandi:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sipkl
DB_USERNAME=root
DB_PASSWORD=
```

Buat database `sipkl` di phpMyAdmin, lalu jalankan migrasi:

```bash
php artisan migrate --seed
```

> `--seed` mengisi role, permission, data master, dan lima akun demo.
> Lewati `--seed` bila ingin database kosong.

### 4. Link penyimpanan dan build aset

```bash
php artisan storage:link
npm run build            # atau `npm run dev` saat pengembangan
```

### 5. Jalankan aplikasi

```bash
php artisan serve        # http://127.0.0.1:8000
```

Alternatif tanpa `artisan serve` (XAMPP/Apache): letakkan project di `htdocs`, lalu buka
**http://localhost/Sistem-Informasi-Praktik-Kerja-Lapangan/public/**. File `.htaccess` di root
project mengalihkan URL akar ke folder `public/` supaya aplikasi tidak bisa diakses dari luar folder tersebut.

### Rute utama

| Halaman | URL |
| :--- | :--- |
| Landing page (publik) | `/` |
| Login | `/login` |
| Verifikasi sertifikat (publik) | `/certificate/verify` |
| Pengalihan sesuai peran | `/redirect` |
| Dashboard admin | `/admin/dashboard` |
| Dashboard dosen | `/dosen/home` |
| Dashboard mahasiswa | `/mahasiswa/home` |

---

## Akun Demo

Seluruh akun demo memakai kata sandi **`password`**. Kolom login menerima **NIM**, **NIP**, atau **email**.

| Peran | Email | NIM / NIP |
| :--- | :--- | :--- |
| Admin | `admin@sipkl.test` | &mdash; |
| Dosen Pembimbing | `dosen@sipkl.test` | `198304122011011001` |
| Mahasiswa | `mhs@sipkl.test` | `2310631145` |
| Koordinator | `koordinator@sipkl.test` | `198502021991032002` |
| Pimpinan | `pimpinan@sipkl.test` | `198001011990031001` |

---

## Struktur Project

```
app/
├── Http/
│   ├── Controllers/            # dikelompokkan per peran (Admin, Dosen, Mahasiswa, Auth, Public)
│   └── Middleware/RoleMiddleware.php
├── Models/                     # 41 model: User, Mahasiswa, Dosen, Absensi, Jurnal, NilaiPkl, ...
├── View/Components/            # AppLayout, GuestLayout, LandingLayout
resources/
├── views/
│   ├── components/landing/     # navbar, hero, about, features, flow, roles, faq, cta, footer
│   ├── admin/                  # view khusus admin
│   ├── dosen/                  # view khusus dosen
│   ├── mahasiswa/              # view khusus mahasiswa
│   └── layouts/
├── css/app.css                 # design system: token Material 3 dan komponen
├── css/landing.css             # gaya khusus landing page
└── js/landing.js               # tab peran, accordion FAQ, menu mobile
routes/web.php                  # seluruh rute aplikasi, dikelompokkan per peran
database/migrations/            # 20 migrasi
database/seeders/               # role, permission, dan data demo
tests/Feature/                  # feature test
tools/build_brand_assets.py     # pembuat aset brand (logo, favicon, OG image)
```

---

## Keamanan

- **Otorisasi di backend** &mdash; setiap route per peran dilindungi middleware `role:{namaPeran}`. Menu di antarmuka hanya untuk kenyamanan, bukan pengaman.
- **Password di-hash** dengan bcrypt, dan sesi diregenerate setiap kali login berhasil.
- **Rate limiting** pada login, pembuatan QR, dan seluruh endpoint QR (dikonfigurasi di `config/qr-login.php`).
- **CSRF token** pada setiap form dan endpoint AJAX.
- **Upload berkas** divalidasi berdasarkan ekstensi, ukuran, dan MIME type sebelum disimpan.
- **Token QR sekali pakai** &mdash; token langsung dihapus begitu diklaim, sehingga tautannya tidak bisa dipakai ulang.
- **Audit log** untuk setiap aksi destruktif dan perubahan data sensitif.

---

## Pengujian

```bash
php artisan test
```

| Test | Cakupan |
| :--- | :--- |
| `Auth/QrLoginTest` | Pembuatan, persetujuan, penolakan, dan pengklaiman QR; kedaluwarsa; token sekali pakai; pembatasan peran |
| `Auth/AuthenticationTest` | Login, logout, dan pembatasan percobaan login |
| `Auth/PasswordUpdateTest` | Ganti kata sandi |
| `Auth/PasswordConfirmationTest` | Konfirmasi kata sandi untuk aksi sensitif |
| `ProfileTest` | Ubah profil, ganti email, hapus akun |
| `ProfilePhotoTest` | Unggah dan hapus foto profil |
| `FlashMessageTest` | Pesan sukses dan error pada tiap alur |

---

## Kontributor

**Muhamad Farhan Muizaddin** &mdash; Software Engineer

- GitHub: <https://github.com/muizfrhan>
- LinkedIn: <https://www.linkedin.com/in/mfarhan-703753268/>
- Email: <mfarhanmuizaddin@gmail.com>

---

## Lisensi

Proyek ini berada di bawah lisensi **MIT**. Bebas digunakan, dimodifikasi, dan dikembangkan
dengan tetap mencantumkan nama pembuat aslinya.
