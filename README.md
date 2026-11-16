```markdown
# Sistem Kasir Toko (PHP Native)

Aplikasi Point of Sale (POS) sederhana berbasis web yang dibangun menggunakan PHP Native dan MySQL. Aplikasi ini dirancang untuk mempermudah proses transaksi penjualan toko, mulai dari pemilihan produk, pembayaran, hingga pencetakan struk thermal.

## Fitur Utama

- **Login dan Register**: Sistem autentikasi multi-user dengan password yang di-hash.
- **Dashboard**: Menampilkan ringkasan pendapatan, transaksi, dan ketersediaan stok.
- **Katalog Produk**: Pencarian produk dengan filter real-time dan keranjang belanja interaktif.
- **Sistem Pembayaran**: Mendukung metode Tunai, QRIS, dan Utang (Bon).
- **Cetak Struk**: Format struk thermal 80mm dengan stempel LUNAS atau BELUM LUNAS otomatis.
- **Pengaturan Struk**: Kustomisasi nama toko, alamat, logo, dan ucapan di struk dengan fitur Live Preview.
- **Manajemen Produk**: Tambah dan hapus data produk beserta kategorinya.
- **Riwayat Transaksi**: Laporan transaksi harian, bulanan, dan keseluruhan.
- **Manajemen Stok Otomatis**: Stok berkurang secara otomatis setiap kali transaksi berhasil.

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

1. Clone repository ini ke dalam folder `htdocs` XAMPP Anda atau download sebagai ZIP.
   ```bash
   git clone https://github.com/yuri-lan/kasir-toko-php-native.git
   ```
2. Buka phpMyAdmin dan buat database baru dengan nama `db_toko`.
3. Import file `db_toko.sql` (ada di dalam folder project) ke dalam database `db_toko`.
4. Pastikan folder `uploads` sudah ada di dalam direktori project (jika belum, buat folder baru bernama `uploads`).
5. Buka browser dan akses `http://localhost/kasir_toko`.
6. Login menggunakan akun default:
   - Username: `admin`
   - Password: `12345`

## Struktur Folder

Berikut adalah struktur direktori dari aplikasi kasir ini:

```text
kasir_toko/
│
├── uploads/              # Folder penyimpanan logo toko
│
├── dashboard.php         # Halaman utama (ringkasan statistik)
├── index.php             # Halaman kasir (transaksi & keranjang)
├── pembayaran.php        # Halaman proses pembayaran & kembalian
├── proses.php            # Logika pemrosesan transaksi ke database
├── struk.php             # Halaman cetak struk (thermal 80mm)
├── riwayat.php           # Halaman riwayat transaksi
├── produk.php            # Halaman manajemen produk (tambah/hapus)
├── pengaturan.php        # Halaman pengaturan struk (live preview)
├── sidebar.php           # Komponen sidebar navigasi
│
├── koneksi.php           # Konfigurasi koneksi database
├── login.php             # Halaman login
├── register.php          # Halaman registrasi akun baru
├── logout.php            # Logika logout dan destroy session
│
├── pengaturan.json       # File JSON penyimpanan konfigurasi struk
├── db_toko.sql           # File database untuk di-import
└── README.md             # Dokumentasi proyek
```

## Catatan Penggunaan

- Pastikan printer thermal sudah terhubung dan driver terinstal dengan benar sebelum mencetak.
- Pada jendela cetak browser (Print Preview), pastikan pengaturan ukuran kertas diatur ke `80mm` (atau Custom) dan margin diatur ke `None` agar hasil cetak tidak terpotong.
- Aplikasi ini dibuat untuk keperluan pembelajaran dan tugas akademik.

## Lisensi

Proyek ini dibuat untuk keperluan tugas akademik. Silakan digunakan dan dikembangkan sesuai kebutuhan.