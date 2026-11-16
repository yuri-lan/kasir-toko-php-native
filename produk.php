<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

// Proses Tambah Barang
$pesan = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah'])) {
    $id_kategori = $_POST['id_kategori'];
    $nama_produk = $_POST['nama_produk'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];

    $query = "INSERT INTO tbl_produk (id_kategori, nama_produk, harga, stok) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "isdi", $id_kategori, $nama_produk, $harga, $stok);
    
    if (mysqli_stmt_execute($stmt)) {
        $pesan = "Barang berhasil ditambahkan!";
    } else {
        $pesan = "Gagal menambahkan barang.";
    }
}

// Proses Hapus Barang
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    // Hapus dari tabel (karena ada foreign key, pastikan barang belum pernah ditransaksikan. Kalau pernah, ini bakal error)
    mysqli_query($koneksi, "DELETE FROM tbl_produk WHERE id_produk = '$id_hapus'");
    header("Location: produk.php?status=hapus");
    exit;
}

// Ambil data kategori buat dropdown
$kategori = mysqli_query($koneksi, "SELECT * FROM tbl_kategori ORDER BY nama_kategori ASC");

// Ambil data produk buat tabel
$produk = mysqli_query($koneksi, "SELECT p.*, k.nama_kategori FROM tbl_produk p JOIN tbl_kategori k ON p.id_kategori = k.id_kategori ORDER BY p.id_produk DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Produk - Toko Kita</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --maroon: #6B1D2F; --maroon-dark: #4A1320; --cream: #FDFBF7;
            --cream-card: #F4EFE6; --gold: #C5A059; --gold-hover: #B08D48; --text-dark: #2C2C2C;
            --sidebar-bg: #2A0A10;
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
        
        /* Grid Layout: Kiri Form (Kecil), Kanan Tabel (Besar) */
        .produk-container { display: grid; grid-template-columns: 350px 1fr; gap: 30px; align-items: start;}
        
        /* Card Style */
        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 8px 25px rgba(107, 29, 47, 0.05); border: 1px solid #EBE2D3;}
        .card h2 { font-family: 'Playfair Display', serif; font-size: 20px; margin-bottom: 20px; color: var(--maroon); border-bottom: 2px solid var(--cream-card); padding-bottom: 12px; }
        
        /* Form Style */
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 13px; color: var(--maroon); font-weight: 600;}
        .form-group input, .form-group select {
            width: 100%; padding: 12px; border: 1px solid #DCD3C6; border-radius: 8px; outline: none; transition: 0.3s; font-size: 14px; background: var(--cream); color: var(--text-dark); font-weight: 500;
        }
        .form-group input:focus, .form-group select:focus { border-color: var(--maroon); box-shadow: 0 0 0 3px rgba(107, 29, 47, 0.1); background: #fff;}

        .btn-tambah { background: var(--maroon); color: var(--cream); border: none; padding: 14px; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; width: 100%; transition: 0.3s; box-shadow: 0 4px 15px rgba(107, 29, 47, 0.2); margin-top: 10px;}
        .btn-tambah:hover { background: var(--maroon-dark); transform: translateY(-2px);}

        .alert { background: #F0FDF4; border: 1px solid #BBF7D0; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; font-size: 14px;}
        
        /* Tabel */
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { padding: 15px 10px; text-align: left; border-bottom: 1px solid var(--cream-card); font-size: 14px;}
        th { color: var(--maroon); font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; background: var(--cream);}
        tr:hover { background: #fafafa; }
        
        .td-harga { font-weight: 700; color: var(--maroon); }
        .badge-stok { background: #F6F0E4; color: var(--gold-hover); padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;}
        
        .btn-hapus { 
            background: #FDF2F2; color: #9B1C1C; border: 1px solid #F8B4B4; padding: 6px 12px; border-radius: 6px; 
            cursor: pointer; font-size: 12px; font-weight: 600; transition: 0.2s; text-decoration: none; display: inline-block;
        }
        .btn-hapus:hover { background: #9B1C1C; color: #fff; }
        
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <h1>Manajemen Produk</h1>
        </div>

        <div class="produk-container">
            
            <!-- KIRI: FORM TAMBAH BARANG -->
            <div class="card">
                <h2>Tambah Barang Baru</h2>
                
                <?php if($pesan): ?>
                    <div class="alert"><?= $pesan ?></div>
                <?php endif; ?>
                <?php if(isset($_GET['status']) && $_GET['status'] == 'hapus'): ?>
                    <div class="alert" style="background:#FDF2F2; border-color:#F8B4B4; color:#9B1C1C;">Barang berhasil dihapus!</div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label>Nama Produk</label>
                        <input type="text" name="nama_produk" required placeholder="Contoh: Kopi Susu">
                    </div>
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="id_kategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php while($k = mysqli_fetch_assoc($kategori)): ?>
                                <option value="<?= $k['id_kategori'] ?>"><?= $k['nama_kategori'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Harga (Rp)</label>
                        <input type="number" name="harga" required placeholder="Contoh: 15000" min="0">
                    </div>
                    <div class="form-group">
                        <label>Stok Awal</label>
                        <input type="number" name="stok" required placeholder="Contoh: 50" min="0">
                    </div>
                    
                    <button type="submit" name="tambah" class="btn-tambah">Simpan Barang</button>
                </form>
            </div>

            <!-- KANAN: DAFTAR BARANG -->
            <div class="card">
                <h2>Daftar Produk</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($produk) > 0): ?>
                            <?php while($p = mysqli_fetch_assoc($produk)): ?>
                            <tr>
                                <td>#<?= $p['id_produk'] ?></td>
                                <td style="font-weight: 600; color: var(--text-dark);"><?= $p['nama_produk'] ?></td>
                                <td><?= $p['nama_kategori'] ?></td>
                                <td class="td-harga">Rp <?= number_format($p['harga'], 0, ',', '.') ?></td>
                                <td><span class="badge-stok"><?= $p['stok'] ?> Pcs</span></td>
                                <td>
                                    <!-- Tombol hapus dengan konfirmasi JS -->
                                    <a href="produk.php?hapus=<?= $p['id_produk'] ?>" class="btn-hapus" onclick="return confirm('Yakin mau hapus produk ini?')">Hapus</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: #888; padding: 20px;">Belum ada data produk.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</body>
</html>