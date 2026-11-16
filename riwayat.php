<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

// Logika Filter (Default: Hari Ini)
$filter = $_GET['filter'] ?? 'hari_ini';
$where = "";

if ($filter == 'hari_ini') {
    $tgl = date('Y-m-d');
    $where = "WHERE DATE(t.tanggal) = '$tgl'";
} elseif ($filter == 'bulan_ini') {
    $bln = date('m');
    $thn = date('Y');
    $where = "WHERE MONTH(t.tanggal) = '$bln' AND YEAR(t.tanggal) = '$thn'";
}

// Query Ambil Data
$query = "
    SELECT t.*, p.nama_pelanggan 
    FROM tbl_transaksi t 
    JOIN tbl_pelanggan p ON t.id_pelanggan = p.id_pelanggan 
    $where
    ORDER BY t.id_transaksi DESC
";
$riwayat = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Transaksi - Toko Kita</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --maroon: #6B1D2F; --maroon-dark: #4A1320; --cream: #FDFBF7;
            --cream-card: #F4EFE6; --gold: #C5A059; --gold-hover: #B08D48; --text-dark: #2C2C2C;
            --sidebar-bg: #2A0A10;
            --green: #27ae60; --red: #e74c3c;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: var(--cream); color: var(--text-dark); display: flex; min-height: 100vh; }

        /* Sidebar */
        .sidebar { width: 260px; background: var(--sidebar-bg); color: #fff; display: flex; flex-direction: column; border-right: 3px solid var(--gold); position: fixed; height: 100vh; z-index: 100;}
        .sidebar-brand { padding: 25px; font-family: 'Playfair Display', serif; font-size: 24px; font-weight: 700; color: var(--gold); display: flex; align-items: center; gap: 10px; border-bottom: 1px solid rgba(255,255,255,0.05);}
        .sidebar-brand svg { width: 28px; height: 28px; fill: currentColor; }
        .sidebar-menu { padding: 20px 15px; flex: 1; }
        .menu-label { font-size: 11px; text-transform: uppercase; color: #886; margin-bottom: 10px; padding-left: 15px; letter-spacing: 1px;}
        .menu-item { display: flex; align-items: center; gap: 15px; padding: 12px 15px; color: #C9B9B9; text-decoration: none; border-radius: 8px; margin-bottom: 5px; font-size: 14px; font-weight: 500; transition: 0.3s;}
        .menu-item svg { width: 20px; height: 20px; }
        .menu-item:hover { background: rgba(197, 160, 89, 0.1); color: var(--gold); }
        .menu-item.active { background: var(--maroon); color: var(--gold); border-left: 4px solid var(--gold); }
        .menu-item.logout:hover { background: #ff4757; color: #fff; }

        /* Main Content */
        .main-content { margin-left: 260px; padding: 30px 40px; width: calc(100% - 260px);}
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .topbar h1 { font-family: 'Playfair Display', serif; font-size: 28px; color: var(--maroon); }
        
        /* Card & Filter */
        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 8px 25px rgba(107, 29, 47, 0.05); border: 1px solid #EBE2D3;}
        
        .filter-group { display: flex; gap: 10px; margin-bottom: 20px; }
        .btn-filter { 
            padding: 10px 20px; border-radius: 8px; border: 1px solid var(--maroon); 
            background: #fff; color: var(--maroon); font-weight: 600; font-size: 13px; 
            text-decoration: none; transition: 0.3s;
        }
        .btn-filter:hover, .btn-filter.active { background: var(--maroon); color: #fff; }

        /* Tabel */
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { padding: 15px 10px; text-align: left; border-bottom: 1px solid var(--cream-card); font-size: 14px;}
        th { color: var(--maroon); font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; background: var(--cream);}
        tr:hover { background: #fafafa; }
        
        .td-harga { font-weight: 700; color: var(--maroon); }
        
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-lunas { background: #F0FDF4; color: var(--green); border: 1px solid #BBF7D0;}
        .badge-utang { background: #FDF2F2; color: var(--red); border: 1px solid #F8B4B4;}
        
        .btn-lihat { 
            background: var(--cream-card); color: var(--maroon); border: 1px solid var(--gold); 
            padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; 
            font-weight: 600; transition: 0.2s; text-decoration: none; display: inline-block;
        }
        .btn-lihat:hover { background: var(--gold); color: #fff; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <h1>Riwayat Transaksi</h1>
        </div>

        <div class="card">
            <!-- Tombol Filter -->
            <div class="filter-group">
                <a href="riwayat.php?filter=hari_ini" class="btn-filter <?= ($filter == 'hari_ini') ? 'active' : '' ?>">Hari Ini</a>
                <a href="riwayat.php?filter=bulan_ini" class="btn-filter <?= ($filter == 'bulan_ini') ? 'active' : '' ?>">Bulan Ini</a>
                <a href="riwayat.php?filter=semua" class="btn-filter <?= ($filter == 'semua') ? 'active' : '' ?>">Semua Transaksi</a>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID Trx</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Metode</th>
                        <th>Total Belanja</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($riwayat) > 0): ?>
                        <?php while($r = mysqli_fetch_assoc($riwayat)): 
                            $is_utang = ($r['metode_pembayaran'] == 'Utang');
                        ?>
                        <tr>
                            <td><strong>#<?= $r['id_transaksi'] ?></strong></td>
                            <td><?= date('d/m/Y H:i', strtotime($r['tanggal'])) ?></td>
                            <td><?= $r['nama_pelanggan'] ?></td>
                            <td><?= strtoupper($r['metode_pembayaran'] ?? 'Tunai') ?></td>
                            <td class="td-harga">Rp <?= number_format($r['total_belanja'], 0, ',', '.') ?></td>
                            <td>
                                <?php if($is_utang): ?>
                                    <span class="badge badge-utang">BELUM LUNAS</span>
                                <?php else: ?>
                                    <span class="badge badge-lunas">LUNAS</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="struk.php?id=<?= $r['id_transaksi'] ?>" class="btn-lihat" target="_blank">Lihat Struk</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #888; padding: 30px;">Belum ada riwayat transaksi untuk periode ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>