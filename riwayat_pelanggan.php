<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'pelanggan') {
    header("Location: login.php");
    exit;
}

$id_pelanggan = $_SESSION['id_pelanggan'];
$nama_lengkap = $_SESSION['nama_lengkap'];

$query_trx = mysqli_query($koneksi, "SELECT * FROM tbl_transaksi WHERE id_pelanggan = '$id_pelanggan' ORDER BY tanggal DESC");
$total_transaksi = mysqli_num_rows($query_trx);

$q_total = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(total_belanja) as total FROM tbl_transaksi WHERE id_pelanggan = '$id_pelanggan'"));
$jumlah_belanja = $q_total['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Belanja - Pelanggan</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --maroon: #6B1D2F; --maroon-dark: #4A1320; --cream: #FDFBF7;
            --cream-card: #F4EFE6; --gold: #C5A059; --gold-hover: #B08D48; --text-dark: #2C2C2C;
            --sidebar-bg: #2A0A10; --green-light: #F0FDF4; --green-text: #166534;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: var(--cream); color: var(--text-dark); display: flex; min-height: 100vh; }

        .sidebar {
            width: 250px; background: var(--sidebar-bg); color: #fff;
            display: flex; flex-direction: column; border-right: 3px solid var(--gold);
            position: fixed; height: 100vh; z-index: 100;
        }
        .sidebar-brand {
            padding: 25px; font-family: 'Playfair Display', serif; font-size: 22px; font-weight: 700;
            color: var(--gold); display: flex; align-items: center; gap: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .sidebar-brand svg { width: 28px; height: 28px; fill: currentColor; }
        .sidebar-menu { padding: 20px 15px; flex: 1; }
        .menu-label { font-size: 11px; text-transform: uppercase; color: #886; margin-bottom: 10px; padding-left: 15px; letter-spacing: 1px;}
        .menu-item {
            display: flex; align-items: center; gap: 15px; padding: 12px 15px;
            color: #C9B9B9; text-decoration: none; border-radius: 8px; margin-bottom: 5px;
            font-size: 14px; font-weight: 500; transition: 0.3s;
        }
        .menu-item:hover { background: rgba(197, 160, 89, 0.1); color: var(--gold); }
        .menu-item.active { background: var(--maroon); color: var(--gold); border-left: 4px solid var(--gold); }
        .menu-item.logout:hover { background: #ff4757; color: #fff; }

        .main-content { margin-left: 250px; padding: 30px; width: calc(100% - 250px); }
        .header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; }
        .header h1 { font-family: 'Playfair Display', serif; font-size: 28px; color: var(--maroon); margin-bottom: 5px;}
        .header p { color: #888; font-size: 14px; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 25px; }
        .stat-box { padding: 20px; border-radius: 12px; color: #fff; box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .bg-1 { background: linear-gradient(135deg, #C5A059 0%, #B08D48 100%); }
        .bg-2 { background: linear-gradient(135deg, #4A1320 0%, #2A0A10 100%); }
        .stat-box h4 { font-size: 13px; font-weight: 400; opacity: 0.9; margin-bottom: 5px; }
        .stat-box .val { font-size: 22px; font-weight: 700; }

        .card { background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #EBE2D3; box-shadow: 0 4px 15px rgba(107, 29, 47, 0.03); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .card-header h3 { font-family: 'Playfair Display', serif; font-size: 18px; color: var(--maroon); }
        .btn-gold { background: var(--gold); color: #fff; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 600; }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px 10px; text-align: left; border-bottom: 1px solid var(--cream-card); font-size: 14px;}
        th { color: var(--maroon); font-weight: 600; font-size: 13px; text-transform: uppercase; }
        .td-harga { font-weight: 700; color: var(--maroon); }
        .badge { background: var(--cream-card); color: var(--gold-hover); padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;}
    </style>
</head>
<body>
    <?php include 'sidebar_pelanggan.php'; ?>

    <div class="main-content">
        <div class="header">
            <div>
                <h1>Welcome, <?= htmlspecialchars($nama_lengkap) ?></h1>
                <p>Pantau riwayat pesanan dan transaksi belanjaan Anda di sini.</p>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-box bg-1">
                <h4>Total Keseluruhan Belanja</h4>
                <div class="val">Rp <?= number_format($jumlah_belanja, 0, ',', '.') ?></div>
            </div>
            <div class="stat-box bg-2">
                <h4>Total Riwayat Transaksi</h4>
                <div class="val"><?= $total_transaksi ?> Pesanan</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Daftar Riwayat Belanja</h3>
                <a href="katalog_pelanggan.php" class="btn-gold">+ Belanja Produk Baru</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID Order</th>
                        <th>Tanggal</th>
                        <th>Total Belanja</th>
                        <th>Status Pembayaran</th>
                        <th>Status Belanja / Pengiriman</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($total_transaksi > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($query_trx)): ?>
                        <?php $is_lunas = (strtolower(trim($row['status_bayar'] ?? '')) === 'lunas'); ?>
                        <tr>
                            <td><span class="badge">#<?= $row['id_transaksi'] ?></span></td>
                            <td><?= date('d-m-Y H:i', strtotime($row['tanggal'])) ?></td>
                            <td class="td-harga">Rp <?= number_format($row['total_belanja'], 0, ',', '.') ?></td>
                            
                            <!-- Status Bayar -->
                            <td>
                                <span style="padding:4px 10px; border-radius:20px; font-size:12px; font-weight:600; background:<?= $is_lunas ? '#DCFCE7; color:#166534;' : '#FEE2E2; color:#991B1B;' ?>">
                                    <?= htmlspecialchars($row['status_bayar']) ?>
                                </span>
                            </td>

                            <!-- Status Pengiriman / Belanja -->
                            <td>
                                <?php 
                                    $status = $row['status_pengiriman'];
                                    $bg = '#F3F4F6'; $color = '#374151';
                                    if ($status == 'Proses Packing') { $bg = '#FEF3C7'; $color = '#92400E'; }
                                    else if ($status == 'Sudah Diantar' || $status == 'Selesai') { $bg = '#DCFCE7'; $color = '#166534'; }
                                    else if ($status == 'Menunggu Pembayaran') { $bg = '#FEE2E2'; $color = '#991B1B'; }
                                ?>
                                <span style="padding:4px 10px; border-radius:20px; font-size:12px; font-weight:600; background:<?= $bg ?>; color:<?= $color ?>;">
                                    <?= htmlspecialchars($status) ?>
                                </span>
                            </td>

                            <!-- Aksi -->
                            <td>
                                <?php if (!$is_lunas): ?>
                                    <a href="bayar_duitku.php?id=<?= $row['id_transaksi'] ?>" style="background:var(--maroon); color:#fff; padding:5px 10px; border-radius:6px; text-decoration:none; font-size:12px;">Bayar Sekarang</a>
                                <?php else: ?>
                                    <a href="struk.php?id=<?= $row['id_transaksi'] ?>" style="background:var(--gold); color:#fff; padding:5px 10px; border-radius:6px; text-decoration:none; font-size:12px;">Lihat Struk</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center; padding:20px; color:#888;">Belum ada riwayat transaksi.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>