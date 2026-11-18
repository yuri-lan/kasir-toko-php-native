<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

// ==========================================
// QUERY DATA UNTUK DASHBOARD
// ==========================================
$tgl_hari_ini = date('Y-m-d');
$bulan_ini = date('m');
$tahun_ini = date('Y');

// 1. Data Statistik Utama
$pendapatan_hari_ini = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(total_belanja) as total FROM tbl_transaksi WHERE DATE(tanggal) = '$tgl_hari_ini'"))['total'] ?? 0;
$trx_hari_ini = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as jml FROM tbl_transaksi WHERE DATE(tanggal) = '$tgl_hari_ini'"))['jml'];
$total_produk = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as jml FROM tbl_produk"))['jml'];
$total_pelanggan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as jml FROM tbl_pelanggan"))['jml'];

// 2. Data Transaksi Terakhir (5)
$q_terakhir = mysqli_query($koneksi, "SELECT t.*, p.nama_pelanggan FROM tbl_transaksi t JOIN tbl_pelanggan p ON t.id_pelanggan = p.id_pelanggan ORDER BY t.id_transaksi DESC LIMIT 5");

// 3. Data Grafik Batang (Pendapatan 6 Bulan Terakhir)
$data_bulanan = [];
for ($i = 5; $i >= 0; $i--) {
    $bln = date('m', strtotime("-$i months"));
    $thn = date('Y', strtotime("-$i months"));
    $nama_bln = date('M', strtotime("-$i months"));
    
    $q = mysqli_query($koneksi, "SELECT SUM(total_belanja) as total FROM tbl_transaksi WHERE MONTH(tanggal) = '$bln' AND YEAR(tanggal) = '$thn'");
    $total = mysqli_fetch_assoc($q)['total'] ?? 0;
    $data_bulanan[] = ['bulan' => $nama_bln, 'total' => $total];
}
// Cari nilai tertinggi buat skala grafik
$max_pendapatan = max(array_column($data_bulanan, 'total')) ?: 1000000;

// 4. Data Grafik Donat (Stok Produk Habis vs Tersedia)
$produk_habis = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as jml FROM tbl_produk WHERE stok = 0"))['jml'];
$produk_tersedia = $total_produk - $produk_habis;
$persen_habis = ($total_produk > 0) ? ($produk_habis / $total_produk) * 100 : 0;

// 5. Data Peringatan Stok Tipis (di bawah 10)
$q_stok_tipis = mysqli_query($koneksi, "SELECT nama_produk, stok FROM tbl_produk WHERE stok < 10 AND stok > 0 LIMIT 1");
$stok_tipis = mysqli_fetch_assoc($q_stok_tipis);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Toko Kita</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --maroon: #6B1D2F; --maroon-dark: #4A1320; --cream: #FDFBF7;
            --cream-card: #F4EFE6; --gold: #C5A059; --gold-hover: #B08D48; --text-dark: #2C2C2C;
            --sidebar-bg: #2A0A10;
            --red-light: #FDF2F2; --red-text: #9B1C1C;
            --green-light: #F0FDF4; --green-text: #166534;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: var(--cream); color: var(--text-dark); display: flex; min-height: 100vh; }

        /* --- SIDEBAR --- */
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
        .menu-item svg { width: 20px; height: 20px; }
        .menu-item:hover { background: rgba(197, 160, 89, 0.1); color: var(--gold); }
        .menu-item.active { background: var(--maroon); color: var(--gold); border-left: 4px solid var(--gold); }
        .menu-item.logout:hover { background: #ff4757; color: #fff; }

        /* --- MAIN CONTENT --- */
        .main-content { margin-left: 250px; padding: 30px; width: calc(100% - 250px); }
        
        /* Header */
        .header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; }
        .header h1 { font-family: 'Playfair Display', serif; font-size: 28px; color: var(--maroon); margin-bottom: 5px;}
        .header p { color: #888; font-size: 14px; }
        .header-right { display: flex; align-items: center; gap: 15px; }
        .date-picker {
            background: #fff; border: 1px solid #EBE2D3; padding: 10px 15px; border-radius: 8px;
            font-size: 13px; color: var(--maroon); font-weight: 600; display: flex; align-items: center; gap: 8px;
        }

        /* Alert Banner */
        .alert-banner {
            background: var(--red-light); border: 1px solid #F8B4B4; color: var(--red-text);
            padding: 15px 20px; border-radius: 10px; margin-bottom: 25px;
            display: flex; justify-content: space-between; align-items: center; font-size: 14px;
        }
        .alert-banner strong { font-weight: 600; }

        /* 4 Kotak Warna (Seperti di Gambar) */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px; }
        .stat-box {
            padding: 20px; border-radius: 12px; color: #fff; position: relative; overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1); display: flex; flex-direction: column; justify-content: space-between;
        }
        /* Gradasi Warna Tema Kita */
        .bg-1 { background: linear-gradient(135deg, #C5A059 0%, #B08D48 100%); } /* Emas */
        .bg-2 { background: linear-gradient(135deg, #4A1320 0%, #2A0A10 100%); } /* Maroon Gelap */
        .bg-3 { background: linear-gradient(135deg, #8A2B3E 0%, #6B1D2F 100%); } /* Maroon Terang */
        .bg-4 { background: linear-gradient(135deg, #D4B57E 0%, #C5A059 100%); } /* Emas Muda */
        
        .stat-box .icon-circle {
            width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 50%;
            display: flex; justify-content: center; align-items: center; margin-bottom: 15px;
        }
        .stat-box .icon-circle svg { width: 20px; height: 20px; fill: #fff; }
        .stat-box h4 { font-size: 13px; font-weight: 400; opacity: 0.9; margin-bottom: 5px; }
        .stat-box .val { font-size: 22px; font-weight: 700; display: flex; align-items: center; justify-content: space-between;}
        .stat-box .val span.badge-persen { font-size: 11px; background: rgba(255,255,255,0.2); padding: 4px 8px; border-radius: 20px; font-weight: 500;}

        /* 4 Kotak Putih Bawahnya */
        .stats-grid-sub { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px; }
        .stat-card-sub {
            background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #EBE2D3;
            box-shadow: 0 4px 15px rgba(107, 29, 47, 0.03); position: relative;
        }
        .stat-card-sub h4 { font-size: 13px; color: #888; font-weight: 500; margin-bottom: 10px;}
        .stat-card-sub .val { font-size: 20px; font-weight: 700; color: var(--maroon); margin-bottom: 10px;}
        .stat-card-sub .trend { font-size: 12px; display: flex; align-items: center; gap: 5px; }
        .trend.up { color: var(--green-text); }
        .trend.down { color: var(--red-text); }
        .stat-card-sub .icon-float {
            position: absolute; top: 20px; right: 20px; width: 35px; height: 35px; 
            background: var(--cream-card); border-radius: 8px; display: flex; justify-content: center; align-items: center;
        }
        .stat-card-sub .icon-float svg { width: 18px; height: 18px; fill: var(--gold); }

        /* Area Grafik (Grid 2 Kolom) */
        .charts-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 25px; margin-bottom: 25px; }

        /* Card Style Umum */
        .card { background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #EBE2D3; box-shadow: 0 4px 15px rgba(107, 29, 47, 0.03); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;}
        .card-header h3 { font-family: 'Playfair Display', serif; font-size: 18px; color: var(--maroon); }
        .card-header .tabs { display: flex; background: var(--cream); border-radius: 8px; padding: 4px; }
        .card-header .tabs span { padding: 4px 12px; font-size: 12px; font-weight: 600; color: #888; cursor: pointer; border-radius: 6px;}
        .card-header .tabs span.active { background: #fff; color: var(--maroon); box-shadow: 0 2px 5px rgba(0,0,0,0.05); }

        /* Grafik Batang CSS Murni */
        .bar-chart-container { display: flex; align-items: flex-end; justify-content: space-between; height: 200px; padding-top: 20px; border-bottom: 2px solid var(--cream-card); }
        .bar-group { display: flex; flex-direction: column; align-items: center; gap: 10px; width: 100%;}
        .bar-wrapper { display: flex; gap: 5px; align-items: flex-end; height: 100%; width: 100%; justify-content: center;}
        .bar {
            width: 15px; border-radius: 4px 4px 0 0; transition: height 0.5s ease;
            position: relative;
        }
        .bar.maroon { background: var(--maroon); }
        .bar.gold { background: var(--gold); }
        .bar-label { font-size: 12px; color: #888; font-weight: 500;}

        /* Grafik Donat CSS/SVG Murni */
        .donut-container { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 20px 0;}
        .donut-chart {
            width: 150px; height: 150px; border-radius: 50%;
            background: conic-gradient(var(--maroon) 0% <?= $persen_habis ?>%, var(--cream-card) <?= $persen_habis ?>% 100%);
            display: flex; justify-content: center; align-items: center; position: relative; margin-bottom: 20px;
        }
        .donut-inner { width: 110px; height: 110px; background: #fff; border-radius: 50%; display: flex; flex-direction: column; justify-content: center; align-items: center; box-shadow: inset 0 2px 10px rgba(0,0,0,0.05);}
        .donut-inner h4 { font-size: 20px; font-weight: 700; color: var(--maroon); margin-bottom: 2px;}
        .donut-inner p { font-size: 11px; color: #888; }
        .donut-legend { display: flex; gap: 20px; font-size: 12px; font-weight: 500;}
        .legend-item { display: flex; align-items: center; gap: 8px; }
        .legend-color { width: 12px; height: 12px; border-radius: 3px; }

        /* Tabel */
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px 10px; text-align: left; border-bottom: 1px solid var(--cream-card); font-size: 14px;}
        th { color: var(--maroon); font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;}
        .td-harga { font-weight: 700; color: var(--maroon); }
        .badge { background: var(--cream-card); color: var(--gold-hover); padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;}

    </style>
</head>
<body>

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Konten Utama -->
    <div class="main-content">
        
        <!-- Header -->
        <div class="header">
            <div>
                <h1>Welcome, <?= $_SESSION['nama_lengkap'] ?></h1>
                <p>You have <strong><?= $trx_hari_ini ?> Orders</strong>, Today</p>
            </div>
            <div class="header-right">
                <div class="date-picker">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg>
                    <?= date('d M Y') ?>
                </div>
                <div class="user-profile" style="display:flex; align-items:center; gap:10px;">
                    <div class="avatar" style="width:40px; height:40px; background:var(--maroon); color:var(--gold); border-radius:50%; display:flex; justify-content:center; align-items:center; font-weight:700;">
                        <?= strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Banner Alert Peringatan Stok -->
        <?php if($stok_tipis): ?>
        <div class="alert-banner">
            <div>
                <strong>Perhatian!</strong> Produk <strong><?= $stok_tipis['nama_produk'] ?></strong> stoknya tinggal <?= $stok_tipis['stok'] ?> Pcs. Segera lakukan <em>Restock</em>.
            </div>
            <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
        </div>
        <?php endif; ?>

        <!-- 4 Kotak Berwarna -->
        <div class="stats-grid">
            <div class="stat-box bg-1">
                <div class="icon-circle"><svg viewBox="0 0 24 24"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg></div>
                <h4>Total Penjualan (Hari Ini)</h4>
                <div class="val">
                    Rp <?= number_format($pendapatan_hari_ini, 0, ',', '.') ?>
                    <span class="badge-persen">+12%</span>
                </div>
            </div>

            <div class="stat-box bg-2">
                <div class="icon-circle"><svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm-4 10h8v1H8v-1z"/></svg></div>
                <h4>Transaksi (Hari Ini)</h4>
                <div class="val">
                    <?= $trx_hari_ini ?> Trx
                    <span class="badge-persen">+5%</span>
                </div>
            </div>

            <div class="stat-box bg-3">
                <div class="icon-circle"><svg viewBox="0 0 24 24"><path d="M20 6h-4V4c0-1.11-.89-2-2-2h-4c-1.11 0-2 .89-2 2v2H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-6 0h-4V4h4v2z"/></svg></div>
                <h4>Total Produk</h4>
                <div class="val">
                    <?= $total_produk ?> Item
                    <span class="badge-persen">+2%</span>
                </div>
            </div>

            <div class="stat-box bg-4">
                <div class="icon-circle"><svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg></div>
                <h4>Total Pelanggan</h4>
                <div class="val">
                    <?= $total_pelanggan ?> Orang
                    <span class="badge-persen">+8%</span>
                </div>
            </div>
        </div>

        <!-- 4 Kotak Putih Bawah -->
        <div class="stats-grid-sub">
            <div class="stat-card-sub">
                <h4>Laba Bersih</h4>
                <div class="val">Rp <?= number_format($pendapatan_hari_ini * 0.2, 0, ',', '.') ?></div>
                <div class="trend up">↑ +35% vs Bulan Lalu</div>
                <div class="icon-float"><svg viewBox="0 0 24 24"><path d="M16 6l2.29 2.29-4.88 4.88-4-4L2 16.59 3.41 18l6-6 4 4 6.3-6.29L22 12V6z"/></svg></div>
            </div>
            <div class="stat-card-sub">
                <h4>Produk Terjual</h4>
                <div class="val"><?= $trx_hari_ini * 2 ?> Pcs</div>
                <div class="trend up">↑ +12% vs Bulan Lalu</div>
                <div class="icon-float"><svg viewBox="0 0 24 24"><path d="M19 6h-2c0-2.76-2.24-5-5-5S7 3.24 7 6H5c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-7-3c1.66 0 3 1.34 3 3H9c0-1.66 1.34-3 3-3zm7 17H5V8h14v12zm-7-8c-1.66 0-3-1.34-3-3H7c0 2.76 2.24 5 5 5s5-2.24 5-5h-2c0 1.66-1.34 3-3 3z"/></svg></div>
            </div>
            <div class="stat-card-sub">
                <h4>Total Pengeluaran</h4>
                <div class="val">Rp <?= number_format($pendapatan_hari_ini * 0.8, 0, ',', '.') ?></div>
                <div class="trend down">↓ -4% vs Bulan Lalu</div>
                <div class="icon-float"><svg viewBox="0 0 24 24"><path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg></div>
            </div>
            <div class="stat-card-sub">
                <h4>Retur Penjualan</h4>
                <div class="val">Rp 0</div>
                <div class="trend down">↓ 0% vs Bulan Lalu</div>
                <div class="icon-float"><svg viewBox="0 0 24 24"><path d="M12 5V1L7 6l5 5V7c3.31 0 6 2.69 6 6s-2.69 6-6 6-6-2.69-6-6H4c0 4.42 3.58 8 8 8s8-3.58 8-8-3.58-8-8-8z"/></svg></div>
            </div>
        </div>

        <!-- Area Grafik -->
        <div class="charts-grid">
            <!-- Grafik Batang -->
            <div class="card">
                <div class="card-header">
                    <h3>Sales & Purchase</h3>
                    <div class="tabs">
                        <span>1D</span><span>1W</span><span>1M</span><span class="active">6M</span>
                    </div>
                </div>
                
                <div class="bar-chart-container">
                    <?php foreach($data_bulanan as $data): 
                        // Hitung tinggi batang (persentase dari nilai max)
                        $tinggi_maroon = ($data['total'] > 0) ? ($data['total'] / $max_pendapatan) * 100 : 0;
                        $tinggi_gold = ($data['total'] > 0) ? (($data['total'] * 0.6) / $max_pendapatan) * 100 : 0; // Simulasi data purchase
                    ?>
                    <div class="bar-group">
                        <div class="bar-wrapper">
                            <div class="bar maroon" style="height: <?= $tinggi_maroon ?>%;" title="Penjualan: Rp <?= number_format($data['total'],0,',','.') ?>"></div>
                            <div class="bar gold" style="height: <?= $tinggi_gold ?>%;" title="Pembelian"></div>
                        </div>
                        <div class="bar-label"><?= $data['bulan'] ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div style="display:flex; gap:20px; margin-top:20px; font-size:12px; font-weight:600; color:#666;">
                    <div style="display:flex; align-items:center; gap:8px;"><div style="width:12px;height:12px;background:var(--maroon);border-radius:3px;"></div> Total Sales</div>
                    <div style="display:flex; align-items:center; gap:8px;"><div style="width:12px;height:12px;background:var(--gold);border-radius:3px;"></div> Total Purchase</div>
                </div>
            </div>

            <!-- Grafik Donat & Info -->
            <div class="card">
                <div class="card-header">
                    <h3>Inventory Overview</h3>
                </div>
                <div class="donut-container">
                    <div class="donut-chart">
                        <div class="donut-inner">
                            <h4><?= $total_produk ?></h4>
                            <p>Produk</p>
                        </div>
                    </div>
                    <div class="donut-legend">
                        <div class="legend-item"><div class="legend-color" style="background:var(--maroon);"></div> Tersedia (<?= $produk_tersedia ?>)</div>
                        <div class="legend-item"><div class="legend-color" style="background:var(--cream-card); border:1px solid #ddd;"></div> Habis (<?= $produk_habis ?>)</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Transaksi Terakhir -->
        <div class="card">
            <div class="card-header">
                <h3>Recent Orders</h3>
                <a href="index.php" style="color:var(--gold-hover); text-decoration:none; font-size:14px; font-weight:600;">View All</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Pelanggan</th>
                        <th>Tanggal</th>
                        <th>Total Belanja</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($q_terakhir) > 0): ?>
                        <?php while($t = mysqli_fetch_assoc($q_terakhir)): ?>
                        <tr>
                            <td><span class="badge">#<?= $t['id_transaksi'] ?></span></td>
                            <td style="font-weight: 500;"><?= $t['nama_pelanggan'] ?></td>
                            <td><?= date('d-m-Y', strtotime($t['tanggal'])) ?></td>
                            <td class="td-harga">Rp <?= number_format($t['total_belanja'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: #888; padding: 20px;">Belum ada transaksi hari ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>