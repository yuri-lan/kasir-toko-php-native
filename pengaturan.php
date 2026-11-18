<?php
session_start();
require 'koneksi.php';
require 'auth.php';
wajib_admin();

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$file_json = 'pengaturan.json';
$pengaturan = file_exists($file_json) ? json_decode(file_get_contents($file_json), true) : [];
$nama_toko = $pengaturan['nama_toko'] ?? 'TOKO KITA';
$alamat = $pengaturan['alamat'] ?? 'Jl. Contoh No. 123, Ponorogo';
$telepon = $pengaturan['telepon'] ?? '0812-3456-7890';
$footer = $pengaturan['footer'] ?? 'Terima Kasih!';
$nama_kasir = $pengaturan['nama_kasir'] ?? 'Kasir 1';
$logo = $pengaturan['logo'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data_baru = [
        'nama_toko' => $_POST['nama_toko'],
        'alamat' => $_POST['alamat'],
        'telepon' => $_POST['telepon'],
        'footer' => $_POST['footer'],
        'nama_kasir' => $_POST['nama_kasir'],
        'logo' => $logo
    ];
    
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png'];
        
        if (in_array(strtolower($ext), $allowed)) {
            $nama_file_baru = 'logo_' . time() . '.' . $ext;
            $tujuan = 'uploads/' . $nama_file_baru;
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $tujuan)) {
                if (!empty($logo) && file_exists($logo)) {
                    unlink($logo);
                }
                $data_baru['logo'] = $tujuan;
            }
        }
    }
    
    file_put_contents($file_json, json_encode($data_baru, JSON_PRETTY_PRINT));
    header("Location: pengaturan.php?status=sukses");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengaturan Struk - Toko Kita</title>
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
        
        /* Layout Grid: Kiri Form, Kanan Preview */
        .pengaturan-container { display: grid; grid-template-columns: 1fr 380px; gap: 30px; align-items: start;}
        
        /* Card Form Kiri */
        .card-form { background: #fff; padding: 30px 40px; border-radius: 12px; box-shadow: 0 8px 25px rgba(107, 29, 47, 0.05); border: 1px solid #EBE2D3;}
        .card-form h2 { font-family: 'Playfair Display', serif; font-size: 22px; margin-bottom: 25px; color: var(--maroon); border-bottom: 2px solid var(--cream-card); padding-bottom: 15px; }
        
        .form-group { margin-bottom: 20px; display: flex; flex-direction: column;}
        .form-group label { display: block; margin-bottom: 8px; font-size: 13px; color: var(--maroon); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;}
        .form-group input[type="text"], .form-group textarea, .form-group input[type="file"] {
            width: 100%; padding: 14px 15px; border: 1px solid #DCD3C6; border-radius: 8px; outline: none; transition: 0.3s; font-size: 14px; background: var(--cream); color: var(--text-dark); font-weight: 500;
        }
        .form-group input[type="text"]:focus, .form-group textarea:focus { border-color: var(--maroon); box-shadow: 0 0 0 3px rgba(107, 29, 47, 0.1); background: #fff;}
        .form-group input[type="file"] { padding: 10px; background: #fdfdfd; border: 1px dashed #ccc; cursor: pointer;}
        
        .preview-logo { margin-top: 10px; max-width: 100px; border-radius: 8px; border: 1px solid #eee; padding: 5px; background: #fff;}

        .btn-simpan { background: var(--maroon); color: var(--cream); border: none; padding: 16px; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; width: 100%; transition: 0.3s; box-shadow: 0 4px 15px rgba(107, 29, 47, 0.2); margin-top: 10px; letter-spacing: 1px;}
        .btn-simpan:hover { background: var(--maroon-dark); transform: translateY(-2px);}
        .alert { background: #F0FDF4; border: 1px solid #BBF7D0; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 25px; font-weight: 500; font-size: 14px;}

        /* --- BAGIAN LIVE PREVIEW STRUK --- */
        .preview-struk-wrapper { position: sticky; top: 30px; display: flex; flex-direction: column; align-items: center;}
        .preview-title { font-size: 13px; color: var(--maroon); margin-bottom: 15px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; background: var(--cream-card); padding: 8px 15px; border-radius: 20px;}
        
        .struk-live { 
            background: #fff; width: 80mm; padding: 15px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 1px dashed #aaa; 
            font-family: 'Courier New', Courier, monospace; color: #000;
            font-size: 11px;
        }
        
        .struk-header { text-align: center; margin-bottom: 10px; }
        .struk-header img { max-width: 40mm; max-height: 15mm; object-fit: contain; margin-bottom: 5px; display: block; margin-left: auto; margin-right: auto;}
        .struk-header h2 { font-size: 14px; font-weight: bold; text-transform: uppercase; margin-bottom: 3px; line-height: 1.2;}
        .struk-header p { font-size: 10px; line-height: 1.3; }

        .struk-info { font-size: 10px; margin-bottom: 8px; line-height: 1.4;}
        .struk-info .row { display: flex; justify-content: space-between; }

        .struk-garis { border-top: 1px dashed #000; margin: 8px 0; }

        /* Gaya item sejajar di preview */
        .struk-item-row { display: flex; justify-content: space-between; margin-bottom: 4px; }
        .struk-item-name { font-weight: bold; }
        .struk-item-detail { display: flex; justify-content: space-between; font-size: 10px; color: #444; margin-bottom: 8px; }
        
        .struk-total-row { display: flex; justify-content: space-between; margin-bottom: 4px;}
        .struk-total-bold { font-size: 12px; font-weight: bold; margin-top: 5px; margin-bottom: 8px;}

        .struk-footer { text-align: center; font-size: 10px; margin-top: 15px; white-space: pre-wrap; font-style: italic;}

        /* Responsive */
        @media (max-width: 1024px) {
            .pengaturan-container { grid-template-columns: 1fr; }
            .preview-struk-wrapper { position: relative; top: 0; margin-top: 30px; }
        }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <h1>Pengaturan Struk</h1>
        </div>

        <div class="pengaturan-container">
            
            <!-- KIRI: FORM INPUT -->
            <div class="card-form">
                <h2>Edit Informasi Toko</h2>
                
                <?php if(isset($_GET['status']) && $_GET['status'] == 'sukses'): ?>
                    <div class="alert">Pengaturan berhasil disimpan!</div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Logo Toko (Format JPG/PNG)</label>
                        <input type="file" name="logo" accept="image/jpeg, image/png">
                        <?php if($logo && file_exists($logo)): ?>
                            <img src="<?= $logo ?>" class="preview-logo" alt="Logo Toko">
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Nama Toko</label>
                        <input type="text" name="nama_toko" id="inputNamaToko" value="<?= htmlspecialchars($nama_toko) ?>" required oninput="updatePreview()">
                    </div>
                    
                    <div class="form-group">
                        <label>No. Telepon</label>
                        <input type="text" name="telepon" id="inputTelepon" value="<?= htmlspecialchars($telepon) ?>" required oninput="updatePreview()">
                    </div>

                    <div class="form-group">
                        <label>Alamat Lengkap</label>
                        <input type="text" name="alamat" id="inputAlamat" value="<?= htmlspecialchars($alamat) ?>" required oninput="updatePreview()">
                    </div>
                    
                    <div class="form-group">
                        <label>Nama Kasir (Yang tercetak di struk)</label>
                        <input type="text" name="nama_kasir" id="inputKasir" value="<?= htmlspecialchars($nama_kasir) ?>" required oninput="updatePreview()">
                    </div>
                    
                    <div class="form-group">
                        <label>Ucapan / Footer Struk</label>
                        <textarea name="footer" id="inputFooter" rows="3" required oninput="updatePreview()"><?= htmlspecialchars($footer) ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn-simpan">Simpan Pengaturan</button>
                </form>
            </div>

            <!-- KANAN: LIVE PREVIEW STRUK -->
            <div class="preview-struk-wrapper">
                <div class="preview-title">Live Preview Struk</div>
                
                <div class="struk-live">
                    <div class="struk-header">
                        <?php if($logo && file_exists($logo)): ?>
                            <img src="<?= $logo ?>" alt="Logo">
                        <?php endif; ?>
                        
                        <h2 id="prevNamaToko"><?= htmlspecialchars($nama_toko) ?></h2>
                        <p id="prevAlamat"><?= htmlspecialchars($alamat) ?><br>Telp: <span id="prevTelepon"><?= htmlspecialchars($telepon) ?></span></p>
                    </div>

                    <div class="struk-garis"></div>

                    <div class="struk-info">
                        <div class="row"><span>No. #001</span><span><?= date('d/m/y H:i') ?></span></div>
                        <div class="row"><span>Pelanggan</span><span>Budi</span></div>
                        <div class="row"><span>Kasir</span><span id="prevKasir"><?= htmlspecialchars($nama_kasir) ?></span></div>
                    </div>

                    <div class="struk-garis"></div>

                    <!-- Item Dummy Sejajar -->
                    <div class="struk-item-row">
                        <span class="struk-item-name">Nasi Goreng</span>
                    </div>
                    <div class="struk-item-detail">
                        <span>1 x 25.000</span>
                        <span>Rp 25.000</span>
                    </div>
                    
                    <div class="struk-item-row">
                        <span class="struk-item-name">Es Teh Manis</span>
                    </div>
                    <div class="struk-item-detail">
                        <span>1 x 5.000</span>
                        <span>Rp 5.000</span>
                    </div>

                    <div class="struk-garis"></div>

                    <div class="struk-total-row">
                        <span>Total Item</span>
                        <span>2 Pcs</span>
                    </div>
                    <div class="struk-total-bold struk-total-row">
                        <span>TOTAL</span>
                        <span>Rp 30.000</span>
                    </div>
                    <div class="struk-garis" style="margin: 5px 0;"></div>

                    <div class="struk-total-row">
                        <span>Metode</span>
                        <span style="font-weight:bold;">TUNAI</span>
                    </div>
                    <div class="struk-total-row">
                        <span>Bayar</span>
                        <span>Rp 50.000</span>
                    </div>
                    <div class="struk-total-row">
                        <span>Kembali</span>
                        <span>Rp 20.000</span>
                    </div>

                    <div class="struk-garis"></div>

                    <div class="struk-footer">
                        <p id="prevFooter"><?= htmlspecialchars($footer) ?></p>
                        <br>
                        <p style="font-size: 9px; color: #666;">Struk ini adalah bukti pembayaran yang sah.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function updatePreview() {
            document.getElementById('prevNamaToko').innerText = document.getElementById('inputNamaToko').value;
            document.getElementById('prevAlamat').innerHTML = document.getElementById('inputAlamat').value + '<br>Telp: <span id="prevTelepon">' + document.getElementById('inputTelepon').value + '</span>';
            document.getElementById('prevKasir').innerText = document.getElementById('inputKasir').value;
            document.getElementById('prevFooter').innerText = document.getElementById('inputFooter').value;
        }
    </script>
</body>
</html>