# Sistem Kasir Toko (PHP Native)

Aplikasi Point of Sale (POS) sederhana berbasis web yang dibangun menggunakan PHP Native dan MySQL. Aplikasi ini dirancang untuk mempermudah proses transaksi penjualan toko, mulai dari pemilihan produk, pembayaran, hingga pencetakan struk.

## Fitur Utama

- **Login dan Register**: Sistem autentikasi multi-user dengan pembagian role.
- **Dashboard**: Menampilkan ringkasan pendapatan, transaksi, dan stok.
- **Katalog Produk**: Pencarian produk dan keranjang belanja interaktif.
- **Sistem Pembayaran**: Mendukung metode Tunai, QRIS, dan Utang (Bon).
- **Cetak Struk**: Format struk thermal 80mm dengan stempel LUNAS atau BELUM LUNAS otomatis.
- **Pengaturan Struk**: Kustomisasi nama toko, alamat, logo, dan ucapan di struk.
- **Manajemen Produk**: CRUD (Create, Read, Delete) data produk dan kategori.
- **Riwayat Transaksi**: Laporan transaksi harian dan bulanan.
- **Manajemen Stok Otomatis**: Stok berkurang secara otomatis setiap kali transaksi berhasil.

## Struktur Folder
kasir_toko/
│
├── uploads/              # Folder penyimpanan logo toko
├── dashboard.php         # Halaman utama (ringkasan)
├── index.php             # Halaman kasir (transaksi)
├── pembayaran.php        # Halaman proses pembayaran
├── proses.php            # Logika pemrosesan transaksi ke database
├── struk.php             # Halaman cetak struk
├── riwayat.php           # Halaman riwayat transaksi
├── produk.php            # Halaman manajemen produk
├── pengaturan.php        # Halaman pengaturan struk
├── sidebar.php           # Komponen sidebar navigasi
├── koneksi.php           # Konfigurasi koneksi database
├── login.php             # Halaman login
├── register.php          # Halaman registrasi
├── logout.php            # Logika logout
├── pengaturan.json       # File penyimpanan konfigurasi struk
└── db_toko.sql           # File database untuk di-import


## Teknologi yang Digunakan

- **Bahasa Pemrograman**: PHP (Native)
- **Database**: MySQL / MariaDB
- **Antarmuka**: HTML5, CSS3 Murni (Tanpa Framework), JavaScript
- **Web Server**: XAMPP / Apache

## Prasyarat

Sebelum menjalankan aplikasi ini, pastikan Anda sudah menginstal:
- XAMPP (termasuk Apache dan MySQL)
- Web Browser (Chrome, Edge, Firefox, dll)
- Code Editor (Visual Studio Code)

## Cara Instalasi

1. Clone repository ini ke dalam folder `htdocs` XAMPP Anda.
   ```bash
   git clone https://github.com/yuri-lan/kasir-toko-php-native.git