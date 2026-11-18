<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'pelanggan') {
    header("Location: login.php");
    exit;
}

// Inisialisasi keranjang
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

// Proses tambah ke keranjang
if (isset($_POST['tambah_keranjang'])) {
    $id_produk = $_POST['id_produk'];
    $jumlah = intval($_POST['jumlah']);
    
    if (isset($_SESSION['keranjang'][$id_produk])) {
        $_SESSION['keranjang'][$id_produk] += $jumlah;
    } else {
        $_SESSION['keranjang'][$id_produk] = $jumlah;
    }
    header("Location: katalog_pelanggan.php");
    exit;
}

// Proses hapus item dari keranjang
if (isset($_GET['hapus_item'])) {
    $id_hapus = $_GET['hapus_item'];
    unset($_SESSION['keranjang'][$id_hapus]);
    header("Location: katalog_pelanggan.php");
    exit;
}

$produk = mysqli_query($koneksi, "SELECT p.*, k.nama_kategori FROM tbl_produk p JOIN tbl_kategori k ON p.id_kategori = k.id_kategori WHERE p.stok > 0");
$nama_lengkap = $_SESSION['nama_lengkap'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Katalog & Keranjang - Pelanggan</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --maroon: #6B1D2F; --maroon-dark: #4A1320; --cream: #FDFBF7;
            --cream-card: #F4EFE6; --gold: #C5A059; --gold-hover: #B08D48; --text-dark: #2C2C2C;
            --sidebar-bg: #2A0A10;
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

        .main-content { margin-left: 250px; padding: 30px; width: calc(100% - 250px); display: grid; grid-template-columns: 2fr 1fr; gap: 25px; }
        .header { grid-column: span 2; margin-bottom: 5px; }
        .header h1 { font-family: 'Playfair Display', serif; font-size: 28px; color: var(--maroon); }
        
        .grid-produk { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
        .card-produk {
            background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #EBE2D3;
            display: flex; flex-direction: column; justify-content: space-between;
        }
        .badge-kat { display: inline-block; background: var(--cream-card); color: var(--gold-hover); font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 20px; margin-bottom: 8px; }
        .card-produk h3 { font-family: 'Playfair Display', serif; font-size: 17px; color: var(--maroon); }
        .card-produk .stok { font-size: 12px; color: #888; margin: 5px 0 10px; }
        .card-produk .harga { font-size: 16px; font-weight: 700; color: var(--maroon); margin-bottom: 12px; }
        .form-beli { display: flex; gap: 8px; }
        .form-beli input { width: 65px; padding: 8px; border: 1px solid #DCD3C6; border-radius: 8px; text-align: center; }
        .btn-beli { flex: 1; background: var(--maroon); color: var(--cream); border: none; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; }
        .btn-beli:hover { background: var(--maroon-dark); }

        .keranjang-box {
            background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #EBE2D3;
            height: fit-content; position: sticky; top: 30px; border-top: 4px solid var(--gold);
        }
        .keranjang-box h3 { font-family: 'Playfair Display', serif; font-size: 20px; color: var(--maroon); margin-bottom: 15px; }
        .item-keranjang { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--cream-card); font-size: 13px; }
        .btn-hapus { color: #ff4757; text-decoration: none; font-weight: 700; margin-left: 8px; }
        .total-keranjang { display: flex; justify-content: space-between; font-size: 16px; font-weight: 700; color: var(--maroon); margin: 20px 0; padding-top: 10px; border-top: 2px solid var(--cream-card); }
        .btn-checkout { width: 100%; padding: 12px; background: var(--gold); color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; }
        .btn-checkout:hover { background: var(--gold-hover); }
    </style>
</head>
<body>
    <?php include 'sidebar_pelanggan.php'; ?>

    <div class="main-content">
        <div class="header">
            <h1>Katalog Produk & Keranjang</h1>
            <p style="color:#888; font-size:14px;">Pilih beberapa barang sekaligus ke keranjang sebelum melakukan pembayaran transfer.</p>
        </div>

        <div class="grid-produk">
            <?php while($p = mysqli_fetch_assoc($produk)): ?>
            <div class="card-produk">
                <div>
                    <span class="badge-kat"><?= htmlspecialchars($p['nama_kategori']) ?></span>
                    <h3><?= htmlspecialchars($p['nama_produk']) ?></h3>
                    <div class="stok">Stok: <strong><?= $p['stok'] ?> Pcs</strong></div>
                    <div class="harga">Rp <?= number_format($p['harga'], 0, ',', '.') ?></div>
                </div>
                <form method="POST" class="form-beli">
                    <input type="hidden" name="id_produk" value="<?= $p['id_produk'] ?>">
                    <input type="number" name="jumlah" value="1" min="1" max="<?= $p['stok'] ?>" required>
                    <button type="submit" name="tambah_keranjang" class="btn-beli">+ Keranjang</button>
                </form>
            </div>
            <?php endwhile; ?>
        </div>

        <div class="keranjang-box">
            <h3>Keranjang Belanja</h3>
            <?php 
            $grand_total = 0;
            if (!empty($_SESSION['keranjang'])): 
                foreach ($_SESSION['keranjang'] as $id_prod => $qty):
                    $q_p = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM tbl_produk WHERE id_produk = '$id_prod'"));
                    if ($q_p):
                        $sub = $q_p['harga'] * $qty;
                        $grand_total += $sub;
            ?>
                <div class="item-keranjang">
                    <div>
                        <strong><?= htmlspecialchars($q_p['nama_produk']) ?></strong><br>
                        <span style="color:#888;"><?= $qty ?> x Rp <?= number_format($q_p['harga'], 0, ',', '.') ?></span>
                    </div>
                    <div>
                        <strong>Rp <?= number_format($sub, 0, ',', '.') ?></strong>
                        <a href="?hapus_item=<?= $id_prod ?>" class="btn-hapus">×</a>
                    </div>
                </div>
            <?php 
                    endif;
                endforeach; 
            ?>
                <div class="total-keranjang">
                    <span>Total Bayar</span>
                    <span>Rp <?= number_format($grand_total, 0, ',', '.') ?></span>
                </div>
                <form action="proses_beli.php" method="POST">
                    <button type="submit" name="checkout_sekarang" class="btn-checkout">Checkout Sekarang (Transfer)</button>
                </form>
            <?php else: ?>
                <p style="color:#888; font-size:13px; text-align:center; padding: 20px 0;">Keranjang masih kosong.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>