<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_pelanggan = $_POST['id_pelanggan'];
    $keranjang_json = $_POST['keranjang'];
    $uang_bayar = $_POST['uang_bayar'] ?? 0;
    $kembalian = $_POST['kembalian'] ?? 0;
    $metode_pembayaran = $_POST['metode_pembayaran'] ?? 'Tunai';
    
    $keranjang = json_decode($keranjang_json, true);

    if (empty($id_pelanggan) || empty($keranjang)) {
        echo "<script>alert('Data tidak valid! Pastikan pelanggan dan barang sudah dipilih.'); window.location='index.php';</script>";
        exit;
    }

    mysqli_begin_transaction($koneksi);

    try {
        $total_belanja = 0;
        foreach ($keranjang as $item) {
            $total_belanja += ($item['harga'] * $item['jumlah']);
        }

        // Insert Header
        $query_header = "INSERT INTO tbl_transaksi (tanggal, id_pelanggan, total_belanja, uang_bayar, kembalian, metode_pembayaran) VALUES (CURDATE(), ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($koneksi, $query_header);
        mysqli_stmt_bind_param($stmt, "iddds", $id_pelanggan, $total_belanja, $uang_bayar, $kembalian, $metode_pembayaran);
        mysqli_stmt_execute($stmt);
        
        $id_transaksi = mysqli_insert_id($koneksi);

        // Insert Detail
        foreach ($keranjang as $item) {
            $subtotal = $item['harga'] * $item['jumlah'];
            $id_produk = $item['id_produk'];
            $jumlah = $item['jumlah'];

            $query_detail = "INSERT INTO tbl_detail_transaksi (id_transaksi, id_produk, jumlah, subtotal) VALUES (?, ?, ?, ?)";
            $stmt_detail = mysqli_prepare($koneksi, $query_detail);
            mysqli_stmt_bind_param($stmt_detail, "iiid", $id_transaksi, $id_produk, $jumlah, $subtotal);
            mysqli_stmt_execute($stmt_detail);

            // Potong Stok
            $query_stok = "UPDATE tbl_produk SET stok = stok - ? WHERE id_produk = ?";
            $stmt_stok = mysqli_prepare($koneksi, $query_stok);
            mysqli_stmt_bind_param($stmt_stok, "ii", $jumlah, $id_produk);
            mysqli_stmt_execute($stmt_stok);
        }

        mysqli_commit($koneksi);
        header("Location: struk.php?id=" . $id_transaksi);
        exit;

    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        echo "<script>alert('Gagal: " . $e->getMessage() . "'); window.location='index.php';</script>";
        exit;
    }
} else {
    header("Location: index.php");
}
?>