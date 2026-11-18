<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'pelanggan') {
    header("Location: login.php");
    exit;
}

$id_trx = $_GET['id'];
$trx = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM tbl_transaksi WHERE id_transaksi = '$id_trx'"));

if (isset($_POST['konfirmasi_transfer'])) {
    $total = $trx['total_belanja'];
    mysqli_query($koneksi, "UPDATE tbl_transaksi SET uang_bayar = '$total', status_bayar = 'Lunas', status_pengiriman = 'Proses Packing' WHERE id_transaksi = '$id_trx'");
    
    // Langsung arahkan ke struk.php bawaan toko
    header("Location: struk.php?id=$id_trx");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pembayaran Transfer - Toko Kita</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --maroon: #6B1D2F; --cream: #FDFBF7; --gold: #C5A059; }
        body { background: var(--cream); font-family: 'Poppins', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
        .card { background: #fff; padding: 40px; border-radius: 15px; max-width: 450px; width: 100%; border-top: 5px solid var(--gold); box-shadow: 0 10px 30px rgba(0,0,0,0.08); text-align: center; }
        h2 { font-family: 'Playfair Display', serif; color: var(--maroon); margin-bottom: 10px; }
        .rekening-box { background: #F4EFE6; padding: 20px; border-radius: 10px; margin: 20px 0; border: 1px dashed var(--maroon); }
        .nominal { font-size: 26px; font-weight: 700; color: var(--maroon); margin: 10px 0; }
        .btn { width: 100%; padding: 14px; background: var(--maroon); color: #fff; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; margin-top: 10px; }
        .btn:hover { background: #4A1320; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Selesaikan Pembayaran</h2>
        <p style="font-size:13px; color:#666;">Order ID: <strong>#<?= $trx['id_transaksi'] ?></strong></p>
        
        <div class="rekening-box">
            <p style="font-size:13px; color:#555;">Silakan Transfer ke Rekening Toko:</p>
            <h3 style="color:var(--maroon); margin:5px 0;">BCA - 1234567890</h3>
            <p style="font-size:12px; color:#777;">a.n. Toko Kita Official</p>
            <hr style="border:0; border-top:1px solid #ddd; margin:15px 0;">
            <p style="font-size:12px; color:#555;">Total yang harus dibayar:</p>
            <div class="nominal">Rp <?= number_format($trx['total_belanja'], 0, ',', '.') ?></div>
        </div>

        <form method="POST">
            <button type="submit" name="konfirmasi_transfer" class="btn">Saya Sudah Transfer (Cetak Struk)</button>
        </form>
        <a href="riwayat_pelanggan.php" style="display:block; margin-top:15px; font-size:13px; color:#888; text-decoration:none;">Bayar Nanti (Kembali ke Riwayat)</a>
    </div>
</body>
</html>