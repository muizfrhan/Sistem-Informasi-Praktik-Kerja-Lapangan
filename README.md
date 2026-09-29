# 🎓 SIPKL (Sistem Informasi Praktik Kerja Lapangan)

**SIPKL** adalah aplikasi web berbasis Laravel untuk mempermudah pengelolaan proses **Praktik Kerja Lapangan (PKL)** di lingkungan **Jurusan Kampus**. Sistem ini menyediakan fitur lengkap mulai dari pendaftaran PKL, pengajuan bimbingan, unggah laporan, hingga penilaian akhir oleh dosen pembimbing.

> Dibuat sebagai proyek pribadi.

## 🚀 Menjalankan Project

```bash
composer install
npm install

# konfigurasi .env (MySQL XAMPP), lalu:
php artisan key:generate
php artisan migrate
php artisan storage:link

npm run build          # atau: npm run dev
php artisan serve      # http://127.0.0.1:8000
```

Alternatif tanpa `artisan serve` (XAMPP/Apache): buka
**http://localhost/SIPKL/public/** — file `.htaccess` di root project
mengalihkan URL akar `http://localhost/SIPKL/` ke folder `public/`.

| Halaman | URL |
| --- | --- |
| Landing page (publik) | `/` |
| Login | `/login` |
| Dashboard | `/redirect` → dashboard sesuai role |

## 🖼️ Brand Aset

| Aset | Lokasi |
| --- | --- |
| Logo utama (SVG) | `public/images/sipkl-mark.svg` |
| Logo raster | `public/images/sipkl-mark.png` |
| Favicon | `public/favicon.png`, `public/favicon.ico` |
| Apple touch icon | `public/images/apple-touch-icon.png` |
| Social preview (OG) | `public/images/og-image.jpg` |
| Ilustrasi mockup | `public/images/mockup-*.svg` |

Logo/favicon raster dibuat ulang dengan:

```bash
python tools/build_brand_assets.py
```

## 📁 Struktur Landing Page

```
app/Http/Controllers/LandingController.php        controller halaman publik
app/View/Components/LandingLayout.php            wrapper layout (mengikuti pola app/guest)
resources/views/layouts/landing.blade.php         shell HTML, meta, font, @vite
resources/views/landing/index.blade.php           halaman utama
resources/views/components/landing/*.blade.php    navbar, hero, about, features, flow,
                                                   roles, faq, cta, contact, footer
resources/views/components/landing/brand.blade.php
resources/views/components/icon.blade.php         pembungkus ikon Material Symbols
resources/css/landing.css                         gaya khusus landing page
resources/js/landing.js                           tab peran, accordion FAQ, menu mobile
tailwind.config.js                                token warna & tipografi Material 3
```

## 📌 Fitur Utama

### 🔐 Otentikasi Role

-   Admin
-   Dosen Pembimbing
-   Mahasiswa

### 🎛️ Admin

-   Dashboard statistik
-   Kelola data mahasiswa dan dosen (manual & import Excel)
-   Kelola perusahaan mitra
-   Verifikasi pendaftaran dan laporan PKL
-   Upload format laporan resmi

### 🧑‍🏫 Dosen

-   Lihat mahasiswa bimbingan
-   Konfirmasi jadwal bimbingan dan lihat jadwal
-   Input nilai PKL (hanya jika mahasiswa minimal 4x bimbingan disetujui)

### 🎓 Mahasiswa

-   Pendaftaran PKL dan pantau status pendaftaran
-   Lihat list perusahaan dan ajukan referensi perusahaan
-   Ajukan bimbingan PKL
-   Unggah laporan PKL
-   Unduh format laporan

## 🧩 Teknologi

-   Laravel 11
-   Laravel Breeze (auth)
-   Tailwind CSS
-   Vite
-   MySQL

## Tampilan User Interface

### 🔑 Login

<table>
  <tr>
    <td><img src="public/screenshots/Login-page.png" alt="Login" width="220" /></td>
  </tr>
</table>

### 🎛️ Admin

<table>
  <tr>
    <td><img src="public/screenshots/Admin-1.png" alt="Admin 1" width="220" /></td>
    <td><img src="public/screenshots/Admin-2.png" alt="Admin 2" width="220" /></td>
  </tr>
  <tr>
    <td><img src="public/screenshots/Admin-3.png" alt="Admin 3" width="220" /></td>
    <td><img src="public/screenshots/Admin-4.png" alt="Admin 4" width="220" /></td>
  </tr>
  <tr>
    <td><img src="public/screenshots/Admin-5.png" alt="Admin 5" width="220" /></td>
    <td><img src="public/screenshots/Admin-6.png" alt="Admin 6" width="220" /></td>
  </tr>
  <tr>
    <td><img src="public/screenshots/Admin-7.png" alt="Admin 7" width="220" /></td>
    <td><img src="public/screenshots/Admin-8.png" alt="Admin 8" width="220" /></td>
  </tr>
</table>

### 🎓 Mahasiswa

<table>
  <tr>
    <td><img src="public/screenshots/Mahasiswa-1.png" alt="Mahasiswa 1" width="220" /></td>
    <td><img src="public/screenshots/Mahasiswa-2.png" alt="Mahasiswa 2" width="220" /></td>
  </tr>
  <tr>
    <td><img src="public/screenshots/Mahasiswa-3.png" alt="Mahasiswa 3" width="220" /></td>
    <td><img src="public/screenshots/Mahasiswa-4.png" alt="Mahasiswa 4" width="220" /></td>
  </tr>
  <tr>
    <td><img src="public/screenshots/Mahasiswa-5.png" alt="Mahasiswa 5" width="220" /></td>
    <td><img src="public/screenshots/Mahasiswa-6.png" alt="Mahasiswa 6" width="220" /></td>
  </tr>
  <tr>
    <td><img src="public/screenshots/Mahasiswa-7.png" alt="Mahasiswa 7" width="220" /></td>
    <td></td>
  </tr>
</table>

### 🧑‍🏫 Dosen

<table>
  <tr>
    <td><img src="public/screenshots/Dosen-1.png" alt="Dosen 1" width="220" /></td>
    <td><img src="public/screenshots/Dosen-2.png" alt="Dosen 2" width="220" /></td>
  </tr>
  <tr>
    <td><img src="public/screenshots/Dosen-3.png" alt="Dosen 3" width="220" /></td>
    <td><img src="public/screenshots/Dosen-4.png" alt="Dosen 4" width="220" /></td>
  </tr>
  <tr>
    <td><img src="public/screenshots/Dosen-5.png" alt="Dosen 5" width="220" /></td>
    <td></td>
  </tr>
</table>
