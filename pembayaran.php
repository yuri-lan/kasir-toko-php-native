<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$id_pelanggan = $_POST['id_pelanggan'] ?? '';
$keranjang_json = $_POST['keranjang'] ?? '';

if (empty($id_pelanggan) || empty($keranjang_json)) {
    echo "<script>alert('Keranjang kosong atau pelanggan belum dipilih!'); window.location='index.php';</script>";
    exit;
}

$keranjang = json_decode($keranjang_json, true);
$total_belanja = 0;
foreach ($keranjang as $item) {
    $total_belanja += ($item['harga'] * $item['jumlah']);
}

$q_pel = mysqli_query($koneksi, "SELECT nama_pelanggan FROM tbl_pelanggan WHERE id_pelanggan = '$id_pelanggan'");
$nama_pelanggan = mysqli_fetch_assoc($q_pel)['nama_pelanggan'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pembayaran - Toko Kita</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --maroon: #6B1D2F; --maroon-dark: #4A1320; --cream: #FDFBF7;
            --cream-card: #F4EFE6; --gold: #C5A059; --gold-hover: #B08D48; --text-dark: #2C2C2C;
            --sidebar-bg: #2A0A10;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: var(--cream); color: var(--text-dark); display: flex; min-height: 100vh; }

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

        .main-content { margin-left: 260px; padding: 30px; width: calc(100% - 260px); display: flex; justify-content: center; align-items: center;}
        .card { background: #fff; padding: 40px; border-radius: 15px; border: 1px solid #EBE2D3; box-shadow: 0 10px 30px rgba(107, 29, 47, 0.08); width: 100%; max-width: 500px; text-align: center;}
        
        .icon-header { width: 60px; height: 60px; background: var(--gold); color: #fff; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 20px auto; }
        .icon-header svg { width: 30px; height: 30px; fill: currentColor; }

        .card h1 { font-family: 'Playfair Display', serif; color: var(--maroon); font-size: 24px; margin-bottom: 5px;}
        .card p.pelanggan { font-size: 14px; color: #888; margin-bottom: 30px;}
        
        .total-box { background: var(--cream); padding: 20px; border-radius: 10px; border: 2px dashed var(--gold); margin-bottom: 30px;}
        .total-box p { font-size: 14px; color: #666; margin-bottom: 5px;}
        .total-box h2 { font-size: 36px; color: var(--maroon); font-weight: 700; }

        .form-group { margin-bottom: 20px; text-align: left;}
        .form-group label { display: block; margin-bottom: 8px; font-size: 14px; color: var(--maroon); font-weight: 600;}
        .form-group input, .form-group select {
            width: 100%; padding: 15px; border: 1px solid #DCD3C6; border-radius: 8px; outline: none; transition: 0.3s; font-size: 18px; font-weight: 600; background: var(--cream); color: var(--maroon);
        }
        .form-group input:focus, .form-group select:focus { border-color: var(--maroon); box-shadow: 0 0 0 3px rgba(107, 29, 47, 0.1); background: #fff;}
        .form-group input:disabled { background: #eee; color: #888; cursor: not-allowed; }

        .kembalian-box { background: #F0FDF4; border: 1px solid #BBF7D0; padding: 15px; border-radius: 8px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;}
        .kembalian-box span { font-size: 14px; color: #166534; font-weight: 500;}
        .kembalian-box h3 { font-size: 20px; color: #166534; font-weight: 700;}
        .kembalian-box.minus { background: #FDF2F2; border-color: #F8B4B4; }
        .kembalian-box.minus span, .kembalian-box.minus h3 { color: #9B1C1C; }

        .btn-group { display: flex; gap: 10px;}
        .btn-batal { background: #fff; color: var(--maroon); border: 1px solid var(--maroon); padding: 15px; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; text-decoration: none; width: 30%; transition: 0.3s; display: flex; justify-content: center; align-items: center;}
        .btn-batal:hover { background: var(--maroon); color: #fff; }
        
        .btn-proses { background: var(--maroon); color: var(--cream); border: none; padding: 15px; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; width: 70%; transition: 0.3s; display: flex; justify-content: center; align-items: center; gap: 10px; box-shadow: 0 4px 15px rgba(107, 29, 47, 0.2);}
        .btn-proses:hover { background: var(--maroon-dark); transform: translateY(-2px);}
        .btn-proses:disabled { background: #ccc; cursor: not-allowed; transform: none; box-shadow: none;}
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="card">
            <div class="icon-header">
                <svg viewBox="0 0 24 24"><path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>
            </div>
            <h1>Pembayaran</h1>
            <p class="pelanggan">Pelanggan: <strong><?= $nama_pelanggan ?></strong></p>

            <div class="total-box">
                <p>Total yang harus dibayar</p>
                <h2>Rp <?= number_format($total_belanja, 0, ',', '.') ?></h2>
            </div>

            <form action="proses.php" method="POST" id="formBayar">
                <input type="hidden" name="id_pelanggan" value="<?= $id_pelanggan ?>">
                <input type="hidden" name="keranjang" value="<?= htmlspecialchars($keranjang_json) ?>">
                <input type="hidden" name="total_belanja" value="<?= $total_belanja ?>">
                <input type="hidden" name="uang_bayar" id="inputUangBayar" value="0">
                <input type="hidden" name="kembalian" id="inputKembalian" value="0">

                <div class="form-group">
                    <label>Metode Pembayaran</label>
                    <select name="metode_pembayaran" id="metodeBayar" required>
                        <option value="Tunai">Tunai (Cash)</option>
                        <option value="QRIS">QRIS / E-Wallet</option>
                        <option value="Utang">Utang (Bon)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Uang Diterima (Rp)</label>
                    <input type="number" name="uang_bayar_input" id="uangBayar" placeholder="0" autocomplete="off">
                </div>

                <div class="kembalian-box" id="boxKembalian">
                    <span>Kembalian</span>
                    <h3 id="textKembalian">Rp 0</h3>
                </div>

                <div class="btn-group">
                    <a href="index.php" class="btn-batal">Batal</a>
                    <button type="submit" class="btn-proses" id="btnSubmit">
                        Proses & Cetak Struk
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const totalBelanja = <?= $total_belanja ?>;
        const inputUang = document.getElementById('uangBayar');
        const selectMetode = document.getElementById('metodeBayar');
        const textKembalian = document.getElementById('textKembalian');
        const boxKembalian = document.getElementById('boxKembalian');
        const btnSubmit = document.getElementById('btnSubmit');
        const hiddenUangBayar = document.getElementById('inputUangBayar');
        const hiddenKembalian = document.getElementById('inputKembalian');

        selectMetode.addEventListener('change', function() {
            if (this.value === 'QRIS' || this.value === 'Utang') {
                // Kalau QRIS, uang pas. Kalau Utang, uang bisa dikosongin atau diisi DP
                if (this.value === 'QRIS') inputUang.value = totalBelanja;
                if (this.value === 'Utang') inputUang.value = 0;
                hitungKembalian();
            } else {
                inputUang.value = '';
                hitungKembalian();
            }
        });

        inputUang.addEventListener('input', hitungKembalian);

        function hitungKembalian() {
            const uang = parseFloat(inputUang.value) || 0;
            const kembalian = uang - totalBelanja;
            const metode = selectMetode.value;

            // Jika metode Utang
            if (metode === 'Utang') {
                if (uang > 0) {
                    textKembalian.innerText = 'Sisa Utang: Rp ' + Math.abs(kembalian).toLocaleString('id-ID');
                    boxKembalian.classList.add('minus');
                } else {
                    textKembalian.innerText = 'Utang Penuh: Rp ' + totalBelanja.toLocaleString('id-ID');
                    boxKembalian.classList.add('minus');
                }
                btnSubmit.disabled = false;
                hiddenUangBayar.value = uang;
                hiddenKembalian.value = totalBelanja - uang; // Kembalian jadi sisa utang
                return;
            }

            // Logika Non-Utang
            if (uang === 0) {
                textKembalian.innerText = 'Rp 0';
                boxKembalian.classList.remove('minus');
                btnSubmit.disabled = true;
                hiddenUangBayar.value = 0;
                hiddenKembalian.value = 0;
            } else if (kembalian < 0) {
                textKembalian.innerText = 'Kurang Rp ' + Math.abs(kembalian).toLocaleString('id-ID');
                boxKembalian.classList.add('minus');
                btnSubmit.disabled = true;
                hiddenUangBayar.value = uang;
                hiddenKembalian.value = 0;
            } else {
                textKembalian.innerText = 'Rp ' + kembalian.toLocaleString('id-ID');
                boxKembalian.classList.remove('minus');
                btnSubmit.disabled = false;
                hiddenUangBayar.value = uang;
                hiddenKembalian.value = kembalian;
            }
        }
    </script>
</body>
</html>