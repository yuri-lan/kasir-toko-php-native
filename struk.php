<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$id_transaksi = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$file_json = 'pengaturan.json';
$pengaturan = file_exists($file_json) ? json_decode(file_get_contents($file_json), true) : [];
$nama_toko = $pengaturan['nama_toko'] ?? 'TOKO KITA';
$alamat = $pengaturan['alamat'] ?? 'Jl. Contoh No. 123';
$telepon = $pengaturan['telepon'] ?? '0812-xxxx';
$footer = $pengaturan['footer'] ?? 'Terima Kasih!';
$nama_kasir = $pengaturan['nama_kasir'] ?? 'Kasir';
$logo = $pengaturan['logo'] ?? '';

$query_header = "SELECT t.*, p.nama_pelanggan FROM tbl_transaksi t JOIN tbl_pelanggan p ON t.id_pelanggan = p.id_pelanggan WHERE t.id_transaksi = ?";
$stmt = mysqli_prepare($koneksi, $query_header);
mysqli_stmt_bind_param($stmt, "i", $id_transaksi);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$header = mysqli_fetch_assoc($result);

if (!$header) { echo "Transaksi tidak ditemukan."; exit; }

$query_detail = "SELECT d.*, pr.nama_produk, pr.harga FROM tbl_detail_transaksi d JOIN tbl_produk pr ON d.id_produk = pr.id_produk WHERE d.id_transaksi = ?";
$stmt_detail = mysqli_prepare($koneksi, $query_detail);
mysqli_stmt_bind_param($stmt_detail, "i", $id_transaksi);
mysqli_stmt_execute($stmt_detail);
$detail = mysqli_stmt_get_result($stmt_detail);
$jumlah_baris_item = mysqli_num_rows($detail);

// ---------- Status pembayaran ----------
$metode = $header['metode_pembayaran'] ?? 'Tunai';
$pernah_utang = in_array($metode, ['Utang', 'Utang (Lunas)']);

$cicilan = [];
$total_cicilan = 0;
if ($pernah_utang) {
    $stmt_c = mysqli_prepare($koneksi, "SELECT nominal, waktu FROM tbl_pelunasan WHERE id_transaksi = ? ORDER BY id_pelanggan ASC");
    mysqli_stmt_bind_param($stmt_c, "i", $id_transaksi);
    mysqli_stmt_execute($stmt_c);
    $res_c = mysqli_stmt_get_result($stmt_c);
    while ($c = mysqli_fetch_assoc($res_c)) {
        $cicilan[] = $c;
        $total_cicilan += $c['nominal'];
    }
}

$dp = $header['uang_bayar'] ?? 0;
$sisa_awal = $pernah_utang ? $header['total_belanja'] - $dp : 0;
$sisa_sekarang = $pernah_utang ? $sisa_awal - $total_cicilan : 0;
$is_lunas = (isset($header['status_bayar']) && $header['status_bayar'] == 'Lunas') || (!$pernah_utang) || ($sisa_sekarang <= 0.001);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Pembayaran #<?= $header['id_transaksi'] ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', Courier, monospace; background: #f4f7f6; display: flex; flex-direction: column; align-items: center; padding: 20px; }

        .struk-wrapper {
            background: #fff; width: 80mm; padding: 15px 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); margin-bottom: 20px;
            border: 1px dashed #ccc; font-size: 13px;
            position: relative; overflow: hidden;
        }

        /* Style Stempel */
        .stempel {
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%) rotate(-15deg);
            border: 4px solid; font-size: 38px; font-weight: bold;
            text-transform: uppercase; padding: 5px 15px; border-radius: 8px;
            opacity: 0.3; pointer-events: none; z-index: 10;
            font-family: 'Arial Black', sans-serif; letter-spacing: 2px;
        }
        .stempel.lunas { border-color: #27ae60; color: #27ae60; }
        .stempel.utang { border-color: #e74c3c; color: #e74c3c; }

        .header-struk { text-align: center; margin-bottom: 10px; }
        .header-struk img { max-width: 55mm; max-height: 25mm; object-fit: contain; margin-bottom: 5px; display: block; margin-left: auto; margin-right: auto;}
        .header-struk h2 { font-size: 20px; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; letter-spacing: 1px;}
        .header-struk p { font-size: 12px; line-height: 1.4; color: #333; }

        .info-struk { font-size: 13px; margin-bottom: 12px; line-height: 1.5;}
        .info-struk .row { display: flex; justify-content: space-between; }
        .info-struk .row span:last-child { text-align: right;}

        .garis { border-top: 1px dashed #000; margin: 12px 0; }

        .item-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .item-name { font-weight: bold; }
        .item-detail { display: flex; justify-content: space-between; font-size: 12px; color: #444; margin-bottom: 10px; }

        .total-row { display: flex; justify-content: space-between; margin-bottom: 6px;}
        .total-bold { font-size: 16px; font-weight: bold; margin-top: 5px; margin-bottom: 10px;}
        .text-utang { color: #e74c3c; font-weight: bold; }
        .text-lunas { color: #27ae60; font-weight: bold; }

        .judul-pelunasan { text-align: center; font-weight: bold; font-size: 14px; letter-spacing: 1px; margin-bottom: 8px; }
        .catatan-pelunasan { text-align: center; font-size: 11px; color: #555; margin-top: 8px; }

        .footer-struk { text-align: center; font-size: 12px; margin-top: 15px; white-space: pre-wrap; font-style: italic;}

        .btn-group { display: flex; gap: 10px; width: 80mm;}
        .btn { flex: 1; padding: 12px; text-align: center; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: bold; cursor: pointer; border: none; font-family: 'Poppins', sans-serif;}
        .btn-print { background: #6B1D2F; color: #fff; }
        .btn-kembali { background: #eee; color: #333; }

        @media print {
            html, body { width: 80mm !important; height: auto !important; margin: 0 !important; padding: 0 !important; background: #fff; }
            body * { visibility: hidden; }
            .btn-group { display: none !important; }
            .struk-wrapper, .struk-wrapper * { visibility: visible; }
            .struk-wrapper {
                position: absolute; left: 0; top: 0; width: 80mm; padding: 0 10px; margin: 0; box-shadow: none; border: none;
                page-break-inside: avoid; page-break-after: avoid;
            }
            @page { size: 80mm auto; margin: 0; }
        }
    </style>
</head>
<body>

    <div class="struk-wrapper">

        <?php if($is_lunas): ?>
            <div class="stempel lunas">LUNAS</div>
        <?php else: ?>
            <div class="stempel utang">BELUM LUNAS</div>
        <?php endif; ?>

        <div class="header-struk">
            <?php if($logo && file_exists($logo)): ?>
                <img src="<?= htmlspecialchars($logo) ?>" alt="Logo">
            <?php endif; ?>

            <h2><?= htmlspecialchars($nama_toko) ?></h2>
            <p><?= htmlspecialchars($alamat) ?><br>Telp: <?= htmlspecialchars($telepon) ?></p>
        </div>

        <div class="garis"></div>

        <div class="info-struk">
            <div class="row"><span>No. #<?= $header['id_transaksi'] ?></span><span><?= date('d/m/y H:i', strtotime($header['tanggal'])) ?></span></div>
            <div class="row"><span>Pelanggan</span><span><?= htmlspecialchars(substr($header['nama_pelanggan'], 0, 20)) ?></span></div>
            <div class="row"><span>Kasir</span><span><?= htmlspecialchars($nama_kasir) ?></span></div>
        </div>

        <div class="garis"></div>

        <?php while($d = mysqli_fetch_assoc($detail)): ?>
            <div class="item-row">
                <span class="item-name"><?= htmlspecialchars($d['nama_produk']) ?></span>
            </div>
            <div class="item-detail">
                <span><?= $d['jumlah'] ?> x <?= number_format($d['harga'], 0, '', '') ?></span>
                <span>Rp <?= number_format($d['subtotal'], 0, '', '') ?></span>
            </div>
        <?php endwhile; ?>

        <div class="garis"></div>

        <div class="total-row">
            <span>Total Item</span>
            <span><?= $jumlah_baris_item ?> Pcs</span>
        </div>
        <div class="total-bold total-row">
            <span>TOTAL</span>
            <span>Rp <?= number_format($header['total_belanja'], 0, ',', '.') ?></span>
        </div>
        <div class="garis" style="margin: 5px 0;"></div>

        <div class="total-row">
            <span>Metode</span>
            <span style="font-weight:bold;"><?= $pernah_utang ? 'UTANG' : strtoupper($metode) ?></span>
        </div>

        <?php if($pernah_utang): ?>
            <div class="total-row text-utang">
                <span>DP / Bayar</span>
                <span>Rp <?= number_format($dp, 0, ',', '.') ?></span>
            </div>
            <div class="total-row text-utang">
                <span>Sisa Utang</span>
                <span>Rp <?= number_format($sisa_awal, 0, ',', '.') ?></span>
            </div>
        <?php else: ?>
            <div class="total-row">
                <span>Bayar</span>
                <span>Rp <?= number_format($header['uang_bayar'] ?? 0, 0, ',', '.') ?></span>
            </div>
            <div class="total-row">
                <span>Kembali</span>
                <span>Rp <?= number_format($header['kembalian'] ?? 0, 0, ',', '.') ?></span>
            </div>
        <?php endif; ?>

        <div class="garis"></div>

        <div class="footer-struk">
            <p><?= htmlspecialchars($footer) ?></p>
            <br>
            <p style="font-size: 11px; color: #666;">Struk ini adalah bukti pembayaran yang sah.</p>
        </div>
    </div>

    <div class="btn-group">
        <?php 
        // Otomatis mengarahkan tombol kembali sesuai role yang sedang login
        $link_kembali = (isset($_SESSION['role']) && $_SESSION['role'] == 'pelanggan') ? 'riwayat_pelanggan.php' : 'dashboard.php';
        ?>
        <a href="<?= $link_kembali ?>" class="btn btn-kembali">Kembali</a>
        <button onclick="window.print()" class="btn btn-print">Cetak Struk</button>
    </div>

</body>
</html>