<?php
session_start();
require 'koneksi.php';

$id_trx = $_GET['id'];
$trx = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT t.*, p.nama_pelanggan, p.kota FROM tbl_transaksi t JOIN tbl_pelanggan p ON t.id_pelanggan = p.id_pelanggan WHERE t.id_transaksi = '$id_trx'"));
$items = mysqli_query($koneksi, "SELECT d.*, pr.nama_produk, pr.harga FROM tbl_detail_transaksi d JOIN tbl_produk pr ON d.id_produk = pr.id_produk WHERE d.id_transaksi = '$id_trx'");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Belanja - #<?= $id_trx ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Courier New', Courier, monospace; background: #f4f4f4; padding: 30px; display: flex; justify-content: center; }
        .struk { background: #fff; width: 380px; padding: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); border-radius: 8px; }
        .center { text-align: center; }
        .line { border-top: 1px dashed #333; margin: 12px 0; }
        table { width: 100%; font-size: 13px; border-collapse: collapse; }
        td { padding: 3px 0; }
        .stamp-lunas {
            border: 3px solid #166534; color: #166534; font-size: 18px; font-weight: bold;
            padding: 4px 12px; display: inline-block; transform: rotate(-5deg);
            margin: 15px 0; letter-spacing: 2px; font-family: 'Poppins', sans-serif;
        }
        .no-print { margin-top: 20px; text-align: center; }
        .btn { padding: 8px 15px; background: #6B1D2F; color: #fff; text-decoration: none; border-radius: 5px; font-family: 'Poppins', sans-serif; font-size: 13px; border: none; cursor: pointer; margin: 0 3px;}
        .btn:hover { background: #4A1320; }
        @media print { .no-print { display: none; } body { background: #fff; padding: 0; } .struk { box-shadow: none; width: 100%; } }
    </style>
</head>
<body>
    <div class="struk">
        <div class="center">
            <h3 style="margin:0; font-family:'Poppins', sans-serif; font-size:18px; color:#6B1D2F;">TOKO KITA</h3>
            <p style="font-size:11px; margin:3px 0; color:#555;">Pusat Belanja & Kebutuhan Harian</p>
            <p style="font-size:11px; margin:0; color:#777;">Nota Transaksi Online (Transfer)</p>
        </div>
        <div class="line"></div>
        
        <table>
            <tr>
                <td>No. Nota</td>
                <td>: #<?= $trx['id_transaksi'] ?></td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>: <?= date('d-m-Y H:i', strtotime($trx['tanggal'])) ?></td>
            </tr>
            <tr>
                <td>Pelanggan</td>
                <td>: <?= htmlspecialchars($trx['nama_pelanggan']) ?> (<?= htmlspecialchars($trx['kota']) ?>)</td>
            </tr>
            <tr>
                <td>Metode</td>
                <td>: <?= $trx['metode_pembayaran'] ?></td>
            </tr>
            <tr>
                <td>Pengiriman</td>
                <td>: <strong><?= $trx['status_pengiriman'] ?></strong></td>
            </tr>
        </table>
        
        <div class="line"></div>
        
        <table>
            <thead>
                <tr style="border-bottom: 1px solid #333;">
                    <th style="text-align:left; font-size:12px;">Item</th>
                    <th style="text-align:center; font-size:12px;">Qty</th>
                    <th style="text-align:right; font-size:12px;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php while($item = mysqli_fetch_assoc($items)): ?>
                <tr>
                    <td colspan="3" style="padding-top:6px; font-weight:bold;"><?= htmlspecialchars($item['nama_produk']) ?></td>
                </tr>
                <tr>
                    <td style="color:#555; font-size:12px;">@ <?= number_format($item['harga'], 0, ',', '.') ?></td>
                    <td style="text-align:center; font-size:12px;"><?= $item['jumlah'] ?></td>
                    <td style="text-align:right; font-size:12px;">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <div class="line"></div>
        
        <table>
            <tr>
                <td><strong>Total Belanja</strong></td>
                <td style="text-align:right; font-weight:bold; font-size:15px; color:#6B1D2F;">Rp <?= number_format($trx['total_belanja'], 0, ',', '.') ?></td>
            </tr>
            <tr>
                <td>Jumlah Bayar (Transfer)</td>
                <td style="text-align:right;">Rp <?= number_format($trx['uang_bayar'], 0, ',', '.') ?></td>
            </tr>
        </table>

        <div class="center">
            <?php if($trx['status_bayar'] == 'Lunas'): ?>
                <div class="stamp-lunas">LUNAS</div>
            <?php else: ?>
                <div style="color:red; font-weight:bold; margin:15px 0; font-family:'Poppins',sans-serif;">BELUM LUNAS</div>
            <?php endif; ?>
            <p style="font-size:11px; margin:5px 0; color:#555;">Terima kasih, barang akan segera diproses/diantar!</p>
        </div>

        <div class="no-print">
            <button onclick="window.print()" class="btn">Cetak Struk</button>
            <a href="riwayat_pelanggan.php" class="btn" style="background:#555; text-decoration:none; display:inline-block; padding:8px 15px;">Kembali</a>
        </div>
    </div>
</body>
</html>