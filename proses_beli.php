<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'pelanggan') {
    header("Location: login.php");
    exit;
}

if (isset($_POST['checkout_sekarang']) && !empty($_SESSION['keranjang'])) {
    $id_pelanggan = $_SESSION['id_pelanggan'];
    $tanggal = date('Y-m-d H:i:s');
    $total_belanja = 0;

    foreach ($_SESSION['keranjang'] as $id_produk => $qty) {
        $p = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT harga, stok FROM tbl_produk WHERE id_produk = '$id_produk'"));
        if ($p && $p['stok'] >= $qty) {
            $total_belanja += ($p['harga'] * $qty);
        }
    }

    $q_trx = "INSERT INTO tbl_transaksi (tanggal, id_pelanggan, total_belanja, uang_bayar, kembalian, metode_pembayaran, status_bayar, status_pengiriman) 
              VALUES ('$tanggal', '$id_pelanggan', '$total_belanja', 0, 0, 'Transfer', 'Belum Lunas', 'Menunggu Pembayaran')";
    
    if (mysqli_query($koneksi, $q_trx)) {
        $id_transaksi_baru = mysqli_insert_id($koneksi);

        foreach ($_SESSION['keranjang'] as $id_produk => $qty) {
            $p = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT harga, stok FROM tbl_produk WHERE id_produk = '$id_produk'"));
            if ($p && $p['stok'] >= $qty) {
                $subtotal = $p['harga'] * $qty;
                mysqli_query($koneksi, "INSERT INTO tbl_detail_transaksi (id_transaksi, id_produk, jumlah, subtotal) VALUES ('$id_transaksi_baru', '$id_produk', '$qty', '$subtotal')");
                
                $stok_baru = $p['stok'] - $qty;
                mysqli_query($koneksi, "UPDATE tbl_produk SET stok = '$stok_baru' WHERE id_produk = '$id_produk'");
            }
        }

        unset($_SESSION['keranjang']);
        header("Location: bayar_transfer.php?id=$id_transaksi_baru");
        exit;
    }
}
header("Location: katalog_pelanggan.php");
?>