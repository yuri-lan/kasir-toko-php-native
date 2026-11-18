<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] == 'pelanggan') {
    header("Location: login.php");
    exit;
}

if (isset($_POST['update_status'])) {
    $id_trx = $_POST['id_transaksi'];
    $status_baru = $_POST['status_pengiriman'];
    mysqli_query($koneksi, "UPDATE tbl_transaksi SET status_pengiriman = '$status_baru' WHERE id_transaksi = '$id_trx'");
    header("Location: pesanan_online.php?pesan=updated");
    exit;
}

$pesanan = mysqli_query($koneksi, "SELECT t.*, p.nama_pelanggan, p.kota FROM tbl_transaksi t JOIN tbl_pelanggan p ON t.id_pelanggan = p.id_pelanggan WHERE t.metode_pembayaran = 'Transfer' ORDER BY t.id_transaksi DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pesanan Online Pelanggan - Admin/Kasir</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --maroon: #6B1D2F; --maroon-dark: #4A1320; --cream: #FDFBF7;
            --cream-card: #F4EFE6; --gold: #C5A059; --gold-hover: #B08D48; --text-dark: #2C2C2C;
            --sidebar-bg: #2A0A10;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: var(--cream); color: var(--text-dark); display: flex; min-height: 100vh; }

        /* --- SIDEBAR ADMIN/KASIR --- */
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
        .sidebar-brand svg { width: 24px; height: 24px; min-width: 24px; fill: currentColor; }
        .sidebar-menu { padding: 20px 15px; flex: 1; }
        .menu-label { font-size: 11px; text-transform: uppercase; color: #886; margin-bottom: 10px; padding-left: 15px; letter-spacing: 1px;}
        .menu-item {
            display: flex; align-items: center; gap: 15px; padding: 12px 15px;
            color: #C9B9B9; text-decoration: none; border-radius: 8px; margin-bottom: 5px;
            font-size: 14px; font-weight: 500; transition: 0.3s;
        }
        .menu-item svg { width: 18px; height: 18px; min-width: 18px; fill: currentColor; }
        .menu-item:hover { background: rgba(197, 160, 89, 0.1); color: var(--gold); }
        .menu-item.active { background: var(--maroon); color: var(--gold); border-left: 4px solid var(--gold); }
        .menu-item.logout:hover { background: #ff4757; color: #fff; }

        /* --- MAIN CONTENT --- */
        .main-content { margin-left: 250px; padding: 30px; width: calc(100% - 250px); }
        .header { margin-bottom: 25px; }
        .header h1 { font-family: 'Playfair Display', serif; font-size: 28px; color: var(--maroon); margin-bottom: 5px;}
        
        .card { background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #EBE2D3; box-shadow: 0 4px 15px rgba(107, 29, 47, 0.03); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px 10px; text-align: left; border-bottom: 1px solid var(--cream-card); font-size: 14px; }
        th { color: var(--maroon); font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        select { padding: 8px 12px; border-radius: 6px; border: 1px solid #DCD3C6; font-size: 13px; background: var(--cream); outline: none; }
        .btn-save { background: var(--maroon); color: #fff; border: none; padding: 8px 14px; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600; transition: 0.2s; }
        .btn-save:hover { background: var(--maroon-dark); }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="header">
            <h1>Notifikasi Pesanan Online</h1>
            <p style="color: #888; font-size: 14px;">Kelola dan perbarui status pengiriman pesanan online pelanggan.</p>
        </div>
        
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>ID Order</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Status Bayar</th>
                        <th>Status Barang</th>
                        <th>Ubah Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($pesanan) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($pesanan)): ?>
                        <tr>
                            <td><strong>#<?= $row['id_transaksi'] ?></strong><br><small style="color:#888;"><?= date('d/m/Y H:i', strtotime($row['tanggal'])) ?></small></td>
                            <td><?= htmlspecialchars($row['nama_pelanggan']) ?><br><small style="color:#888;"><?= htmlspecialchars($row['kota']) ?></small></td>
                            <td style="font-weight:bold; color:var(--maroon);">Rp <?= number_format($row['total_belanja'], 0, ',', '.') ?></td>
                            <td>
                                <span style="padding:4px 10px; border-radius:15px; font-size:12px; font-weight:600; background:<?= ($row['status_bayar'] == 'Lunas') ? '#DCFCE7; color:#166534;' : '#FEE2E2; color:#991B1B;' ?>">
                                    <?= $row['status_bayar'] ?>
                                </span>
                            </td>
                            <td><strong><?= $row['status_pengiriman'] ?></strong></td>
                            <td>
                                <?php if($row['status_bayar'] == 'Lunas'): ?>
                                <form method="POST" style="display:flex; gap:8px; align-items:center;">
                                    <input type="hidden" name="id_transaksi" value="<?= $row['id_transaksi'] ?>">
                                    <select name="status_pengiriman">
                                        <option value="Proses Packing" <?= ($row['status_pengiriman'] == 'Proses Packing') ? 'selected' : '' ?>>Proses Packing</option>
                                        <option value="Sudah Diantar" <?= ($row['status_pengiriman'] == 'Sudah Diantar') ? 'selected' : '' ?>>Sudah Diantar</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn-save">Simpan</button>
                                </form>
                                <?php else: ?>
                                    <span style="font-size:12px; color:#888;">Menunggu transfer</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center; padding:20px; color:#888;">Belum ada pesanan online.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>