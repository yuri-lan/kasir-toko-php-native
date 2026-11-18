<?php
session_start();
require 'koneksi.php';

// biar error mysqli kena catch di bawah
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: index.php");
    exit;
}

$id_pelanggan = (int)($_POST['id_pelanggan'] ?? 0);
$keranjang    = json_decode($_POST['keranjang'] ?? '', true);
$uang_bayar   = (float)($_POST['uang_bayar'] ?? 0);
$metode       = $_POST['metode_pembayaran'] ?? 'Tunai';

if (!in_array($metode, ['Tunai', 'QRIS', 'Utang'])) {
    $metode = 'Tunai';
}

if ($id_pelanggan <= 0 || empty($keranjang) || !is_array($keranjang)) {
    echo "<script>alert('Data tidak valid! Pastikan pelanggan dan barang sudah dipilih.'); window.location='index.php';</script>";
    exit;
}

mysqli_begin_transaction($koneksi);

try {
    $total_belanja = 0;
    $item_valid = [];

    // 1. Cek tiap barang: harga dari DB, stok cukup atau nggak
    $stmt_cek = mysqli_prepare($koneksi, "SELECT nama_produk, harga, stok FROM tbl_produk WHERE id_produk = ? FOR UPDATE");

    foreach ($keranjang as $item) {
        $id_produk = (int)($item['id_produk'] ?? 0);
        $jumlah    = (int)($item['jumlah'] ?? 0);

        if ($id_produk <= 0 || $jumlah <= 0) {
            throw new Exception("Data barang tidak valid.");
        }

        mysqli_stmt_bind_param($stmt_cek, "i", $id_produk);
        mysqli_stmt_execute($stmt_cek);
        $p = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_cek));

        if (!$p) {
            throw new Exception("Produk tidak ditemukan.");
        }
        if ($p['stok'] < $jumlah) {
            throw new Exception("Stok {$p['nama_produk']} tidak cukup (sisa {$p['stok']}).");
        }

        $subtotal = $p['harga'] * $jumlah;
        $total_belanja += $subtotal;
        $item_valid[] = [$id_produk, $jumlah, $subtotal];
    }

    // 2. Cek pembayaran
    if ($metode == 'Utang') {
        if ($uang_bayar > $total_belanja) $uang_bayar = $total_belanja;
        $kembalian = 0;
    } else {
        if ($uang_bayar < $total_belanja) {
            throw new Exception("Uang bayar kurang dari total belanja.");
        }
        $kembalian = $uang_bayar - $total_belanja;
    }

    // 3. Simpan header transaksi
    $stmt = mysqli_prepare($koneksi, "INSERT INTO tbl_transaksi (tanggal, id_pelanggan, total_belanja, uang_bayar, kembalian, metode_pembayaran) VALUES (NOW(), ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iddds", $id_pelanggan, $total_belanja, $uang_bayar, $kembalian, $metode);
    mysqli_stmt_execute($stmt);
    $id_transaksi = mysqli_insert_id($koneksi);

    // 4. Simpan detail + potong stok
    $stmt_detail = mysqli_prepare($koneksi, "INSERT INTO tbl_detail_transaksi (id_transaksi, id_produk, jumlah, subtotal) VALUES (?, ?, ?, ?)");
    $stmt_stok   = mysqli_prepare($koneksi, "UPDATE tbl_produk SET stok = stok - ? WHERE id_produk = ? AND stok >= ?");

    foreach ($item_valid as [$id_produk, $jumlah, $subtotal]) {
        mysqli_stmt_bind_param($stmt_detail, "iiid", $id_transaksi, $id_produk, $jumlah, $subtotal);
        mysqli_stmt_execute($stmt_detail);

        mysqli_stmt_bind_param($stmt_stok, "iii", $jumlah, $id_produk, $jumlah);
        mysqli_stmt_execute($stmt_stok);

        if (mysqli_stmt_affected_rows($stmt_stok) < 1) {
            throw new Exception("Stok berubah saat transaksi, coba ulang.");
        }
    }

    mysqli_commit($koneksi);
    header("Location: struk.php?id=" . $id_transaksi);
    exit;

} catch (Exception $e) {
    mysqli_rollback($koneksi);
    $pesan = json_encode($e->getMessage());
    echo "<script>alert('Gagal: ' + $pesan); window.location='index.php';</script>";
    exit;
}
?>