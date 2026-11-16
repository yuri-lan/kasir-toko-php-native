<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$pelanggan = mysqli_query($koneksi, "SELECT * FROM tbl_pelanggan ORDER BY nama_pelanggan ASC");
$produk = mysqli_query($koneksi, "SELECT p.*, k.nama_kategori FROM tbl_produk p JOIN tbl_kategori k ON p.id_kategori = k.id_kategori ORDER BY p.nama_produk ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kasir - Toko Kita</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --maroon: #6B1D2F; --maroon-dark: #4A1320; --cream: #FDFBF7;
            --cream-card: #F4EFE6; --gold: #C5A059; --gold-hover: #B08D48; --text-dark: #2C2C2C;
            --sidebar-bg: #2A0A10;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: var(--cream); color: var(--text-dark); display: flex; min-height: 100vh; }

        /* --- STYLE SIDEBAR --- */
        .sidebar {
            width: 260px; background: var(--sidebar-bg); color: #fff;
            display: flex; flex-direction: column; border-right: 3px solid var(--gold);
            position: fixed; height: 100vh; z-index: 100;
        }
        .sidebar-brand {
            padding: 25px; font-family: 'Playfair Display', serif; font-size: 24px; font-weight: 700;
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
        .main-content { margin-left: 260px; padding: 30px; width: calc(100% - 260px); }
        
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .topbar h1 { font-family: 'Playfair Display', serif; font-size: 28px; color: var(--maroon); }
        .user-profile { display: flex; align-items: center; gap: 15px; font-size: 14px; font-weight: 500; color: var(--maroon); }
        .user-profile .avatar {
            width: 40px; height: 40px; background: var(--gold); color: var(--maroon);
            border-radius: 50%; display: flex; justify-content: center; align-items: center;
            font-weight: 700; font-size: 18px;
        }

        .container { display: grid; grid-template-columns: 2fr 1fr; gap: 25px; align-items: start;}
        
        /* Card Style */
        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(107, 29, 47, 0.03); border: 1px solid #EBE2D3;}
        .card h2 { font-family: 'Playfair Display', serif; font-size: 20px; margin-bottom: 20px; color: var(--maroon); border-bottom: 2px solid var(--cream-card); padding-bottom: 12px; display: flex; align-items: center; gap: 10px;}
        .card h2 svg { width: 22px; height: 22px; fill: var(--gold); }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 14px; color: var(--maroon); font-weight: 600;}
        .form-group input[type="text"] {
            width: 100%; padding: 12px; border: 1px solid #DCD3C6; border-radius: 8px; outline: none; transition: 0.3s; font-size: 15px; background: var(--cream); color: var(--text-dark); font-weight: 500;
        }
        .form-group input[type="text"]:focus { border-color: var(--maroon); box-shadow: 0 0 0 3px rgba(107, 29, 47, 0.1); background: #fff;}

        /* Search Produk Layout */
        .search-produk-wrapper { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid var(--cream-card); padding-bottom: 12px;}
        .search-produk-wrapper h2 { border: none; margin: 0; padding: 0;}
        .search-produk-wrapper input {
            width: 200px; padding: 8px 12px; border: 1px solid #DCD3C6; border-radius: 20px; 
            outline: none; font-size: 13px; background: var(--cream); transition: 0.3s;
        }
        .search-produk-wrapper input:focus { border-color: var(--maroon); background: #fff; box-shadow: 0 0 0 3px rgba(107, 29, 47, 0.1);}

        /* Grid Produk */
        .produk-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; }
        .produk-item {
            background: var(--cream-card); border: 1px solid #E8DFD1; border-radius: 10px; padding: 15px;
            text-align: center; cursor: pointer; transition: 0.2s; user-select: none;
        }
        .produk-item:hover {
            border-color: var(--maroon); transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(107, 29, 47, 0.1); background: #fff;
        }
        .produk-item.habis { opacity: 0.5; cursor: not-allowed; }
        .produk-item.habis:hover { transform: none; border-color: #E8DFD1; box-shadow: none; background: var(--cream-card);}
        
        .produk-item h3 { font-size: 14px; margin-bottom: 4px; color: var(--text-dark); font-weight: 600;}
        .produk-item .kategori { font-size: 11px; color: var(--text-light); margin-bottom: 8px; }
        .produk-item .harga { font-size: 16px; font-weight: 700; color: var(--maroon); }
        .produk-item .stok { font-size: 11px; color: var(--gold-hover); margin-top: 5px; background: #F6F0E4; padding: 3px 10px; border-radius: 12px; display: inline-block; font-weight: 600;}

        /* Tabel Keranjang */
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { padding: 12px 5px; text-align: left; border-bottom: 1px solid var(--cream-card); font-size: 13px;}
        th { background: var(--cream); color: var(--maroon); font-weight: 600; }
        
        /* Kontrol Qty */
        .qty-control { display: flex; align-items: center; gap: 8px; }
        .btn-qty { 
            background: #fff; border: 1px solid #DCD3C6; width: 26px; height: 26px; border-radius: 6px; 
            cursor: pointer; font-weight: 600; color: var(--maroon); display: flex; justify-content: center; align-items: center; transition: 0.2s;
        }
        .btn-qty:hover { background: var(--maroon); color: #fff; border-color: var(--maroon); }
        .btn-qty:disabled { background: #eee; color: #ccc; cursor: not-allowed; border-color: #eee; }
        .qty-text { font-weight: 600; width: 20px; text-align: center; font-size: 14px; color: var(--text-dark);}
        
        .btn-hapus { 
            background: transparent; color: #d9534f; border: none; padding: 6px; border-radius: 6px; 
            cursor: pointer; transition: 0.2s; display: flex; justify-content: center; align-items: center;
        }
        .btn-hapus:hover { background: #ffebeb; }
        .btn-hapus svg { width: 16px; height: 16px; fill: currentColor; }
        
        /* Total & Proses */
        .total-box { margin-top: 20px; text-align: right; border-top: 2px dashed #E8DFD1; padding-top: 20px;}
        .total-box h3 { font-size: 24px; color: var(--maroon); margin-bottom: 20px; font-weight: 700;}
        .total-box h3 span { color: var(--gold-hover); }
        .btn-proses { 
            background: var(--maroon); color: var(--cream); border: none; padding: 16px; border-radius: 8px; 
            font-size: 16px; font-weight: 600; cursor: pointer; width: 100%; transition: 0.3s;
            display: flex; justify-content: center; align-items: center; gap: 8px;
            box-shadow: 0 4px 15px rgba(107, 29, 47, 0.2); letter-spacing: 0.5px;
        }
        .btn-proses:hover { background: var(--maroon-dark); box-shadow: 0 6px 20px rgba(107, 29, 47, 0.3); transform: translateY(-2px);}
        .btn-proses svg { width: 20px; height: 20px; fill: currentColor; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <h1>Kasir</h1>
            <div class="user-profile">
                <span>Halo, <strong><?= $_SESSION['nama_lengkap'] ?></strong></span>
                <div class="avatar"><?= strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)) ?></div>
            </div>
        </div>

        <form action="pembayaran.php" method="POST" id="formKasir">
            <div class="container">
                
                <!-- KIRI: Katalog Produk -->
                <div class="card">
                    <div class="form-group">
                        <label>Cari Pelanggan (Ketik Nama)</label>
                        <input type="text" id="inputCariPelanggan" list="listPelanggan" placeholder="Ketik nama pelanggan..." required autocomplete="off">
                        <datalist id="listPelanggan">
                            <?php 
                            $pelangganArray = [];
                            while($p = mysqli_fetch_assoc($pelanggan)): 
                                $pelangganArray[] = $p;
                            ?>
                                <option data-id="<?= $p['id_pelanggan'] ?>" value="<?= $p['nama_pelanggan'] ?> - <?= $p['kota'] ?>"></option>
                            <?php endwhile; ?>
                        </datalist>
                        <input type="hidden" name="id_pelanggan" id="idPelangganAsli" required>
                    </div>

                    <!-- HEADER PRODUK + SEARCH BOX -->
                    <div class="search-produk-wrapper">
                        <h2 style="margin-top: 30px;">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H8V4h12v12zM10 9h8v2h-8zm0 3h4v2h-4zm0-6h8v2h-8z"/></svg>
                            Katalog Produk
                        </h2>
                        <input type="text" id="cariProduk" placeholder="Cari produk...">
                    </div>

                    <div class="produk-grid" id="produkGrid">
                        <?php while($pr = mysqli_fetch_assoc($produk)): ?>
                            <?php $habis = ($pr['stok'] <= 0) ? 'habis' : ''; ?>
                            <div class="produk-item <?= $habis ?>" 
                                 data-nama="<?= strtolower($pr['nama_produk']) ?>" 
                                 onclick="<?= $habis ? '' : "tambahKeKeranjang({$pr['id_produk']}, '" . addslashes($pr['nama_produk']) . "', {$pr['harga']}, {$pr['stok']})" ?>">
                                <h3><?= $pr['nama_produk'] ?></h3>
                                <div class="kategori"><?= $pr['nama_kategori'] ?></div>
                                <div class="harga">Rp <?= number_format($pr['harga'],0,',','.') ?></div>
                                <div class="stok">Sisa: <?= $pr['stok'] ?></div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- KANAN: Keranjang & Total -->
                <div class="card" style="border-top: 4px solid var(--gold);">
                    <h2>
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg>
                        Keranjang
                    </h2>
                    <table id="tabelKeranjang">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Qty</th>
                                <th style="text-align:right;">Subtotal</th>
                                <th style="text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Baris keranjang masuk sini via JS -->
                        </tbody>
                    </table>
                    
                    <div class="total-box">
                        <h3>Total: <span>Rp <span id="totalHarga">0</span></span></h3>
                        <input type="hidden" name="keranjang" id="inputKeranjang">
                        <button type="submit" class="btn-proses">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>
                            Proses Transaksi
                        </button>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <script>
        // --- LOGIKA SEARCH PELANGGAN ---
        const dataPelanggan = <?= json_encode($pelangganArray) ?>;
        const inputCari = document.getElementById('inputCariPelanggan');
        const inputHiddenId = document.getElementById('idPelangganAsli');

        inputCari.addEventListener('input', function() {
            const val = this.value;
            const ketemu = dataPelanggan.find(p => `${p.nama_pelanggan} - ${p.kota}` === val);
            inputHiddenId.value = ketemu ? ketemu.id_pelanggan : '';
        });

        // --- LOGIKA SEARCH PRODUK ---
        const inputCariProduk = document.getElementById('cariProduk');
        const produkGrid = document.getElementById('produkGrid');

        inputCariProduk.addEventListener('input', function() {
            const keyword = this.value.toLowerCase();
            const items = produkGrid.querySelectorAll('.produk-item');

            items.forEach(item => {
                const namaProduk = item.getAttribute('data-nama');
                if (namaProduk.includes(keyword)) {
                    item.style.display = 'block'; // Tampilkan
                } else {
                    item.style.display = 'none';  // Sembunyikan
                }
            });
        });

        // --- LOGIKA KERANJANG ---
        let keranjang = [];

        function tambahKeKeranjang(id_produk, nama, harga, stok) {
            const existing = keranjang.find(item => item.id_produk === id_produk);
            
            if (existing) {
                if (existing.jumlah >= stok) {
                    alert('Stok tidak mencukupi! Maksimal: ' + stok);
                    return;
                }
                existing.jumlah += 1;
            } else {
                if (stok < 1) { alert('Stok habis!'); return; }
                keranjang.push({ id_produk, nama, harga, jumlah: 1, maxStok: stok });
            }

            renderKeranjang();
        }

        function ubahQty(index, perubahan) {
            const item = keranjang[index];
            const newQty = item.jumlah + perubahan;

            if (perubahan > 0 && newQty > item.maxStok) {
                alert('Stok tidak mencukupi! Maksimal: ' + item.maxStok);
                return;
            }

            item.jumlah = newQty;
            
            if (item.jumlah <= 0) {
                keranjang.splice(index, 1);
            }
            renderKeranjang();
        }

        function renderKeranjang() {
            const tbody = document.querySelector('#tabelKeranjang tbody');
            tbody.innerHTML = '';
            let total = 0;

            keranjang.forEach((item, index) => {
                const subtotal = item.harga * item.jumlah;
                total += subtotal;
                
                const iconTrash = `<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>`;
                const isPlusDisabled = (item.jumlah >= item.maxStok) ? 'disabled' : '';

                tbody.innerHTML += `
                    <tr>
                        <td style="font-weight: 500;">${item.nama}</td>
                        <td>
                            <div class="qty-control">
                                <button type="button" class="btn-qty" onclick="ubahQty(${index}, -1)">-</button>
                                <span class="qty-text">${item.jumlah}</span>
                                <button type="button" class="btn-qty" onclick="ubahQty(${index}, 1)" ${isPlusDisabled}>+</button>
                            </div>
                        </td>
                        <td style="text-align:right; font-weight:600; color: var(--maroon);">Rp ${subtotal.toLocaleString('id-ID')}</td>
                        <td style="text-align:center;">
                            <button type="button" class="btn-hapus" onclick="ubahQty(${index}, -999)">
                                ${iconTrash}
                            </button>
                        </td>
                    </tr>
                `;
            });

            document.getElementById('totalHarga').innerText = total.toLocaleString('id-ID');
            document.getElementById('inputKeranjang').value = JSON.stringify(keranjang);
        }
    </script>
</body>
</html>