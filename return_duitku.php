<?php
session_start();
require 'koneksi.php';

$id_trx = isset($_GET['id_trx']) ? (int)$_GET['id_trx'] : 0;
$resultCode = $_GET['resultCode'] ?? '';

if ($id_trx > 0 && $resultCode == '00') {
    $q = mysqli_query($koneksi, "SELECT total_belanja FROM tbl_transaksi WHERE id_transaksi = '$id_trx'");
    $trx = mysqli_fetch_assoc($q);
    $total = $trx['total_belanja'] ?? 0;

    mysqli_query($koneksi, "UPDATE tbl_transaksi 
        SET uang_bayar = '$total', 
            kembalian = 0, 
            status_bayar = 'Lunas', 
            status_pengiriman = 'Proses Packing' 
        WHERE id_transaksi = '$id_trx'");

    header("Location: struk.php?id=$id_trx");
    exit;
}

header("Location: riwayat_pelanggan.php");
exit;
?>