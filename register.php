<?php
session_start();
require 'koneksi.php';

if (isset($_SESSION['id_user'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] == 'pelanggan') {
        header("Location: riwayat_pelanggan.php");
    } else {
        header("Location: dashboard.php");
    }
    exit;
}

$error = '';
$sukses = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = trim($_POST['nama_lengkap']);
    $kota = trim($_POST['kota']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Cek username apakah sudah terpakai
    $cek = mysqli_query($koneksi, "SELECT * FROM tbl_user WHERE username = '$username'");
    if (mysqli_num_rows($cek) > 0) {
        $error = "Username sudah dipakai!";
    } else {
        // 1. Masukkan ke tbl_pelanggan terlebih dahulu
        $q_pelanggan = "INSERT INTO tbl_pelanggan (nama_pelanggan, kota) VALUES (?, ?)";
        $stmt_p = mysqli_prepare($koneksi, $q_pelanggan);
        mysqli_stmt_bind_param($stmt_p, "ss", $nama, $kota);
        
        if (mysqli_stmt_execute($stmt_p)) {
            $id_pelanggan_baru = mysqli_insert_id($koneksi);
            
            // 2. Masukkan ke tbl_user dengan role 'pelanggan' dan hubungkan id_pelanggan
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $q_user = "INSERT INTO tbl_user (id_pelanggan, nama_lengkap, username, password, role) VALUES (?, ?, ?, ?, 'pelanggan')";
            $stmt_u = mysqli_prepare($koneksi, $q_user);
            mysqli_stmt_bind_param($stmt_u, "isss", $id_pelanggan_baru, $nama, $username, $hash);
            
            if (mysqli_stmt_execute($stmt_u)) {
                $sukses = "Registrasi berhasil! Silakan login.";
            } else {
                $error = "Gagal membuat akun user.";
            }
        } else {
            $error = "Gagal mendaftarkan data pelanggan.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register Pelanggan - Kasir Toko</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --maroon: #6B1D2F; --maroon-dark: #4A1320; --cream: #FDFBF7;
            --gold: #C5A059; --gold-hover: #B08D48; --text-dark: #2C2C2C;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body {
            display: flex; justify-content: center; align-items: center;
            min-height: 100vh; background: var(--cream);
            background-image: radial-gradient(circle at top right, #F4EFE6 0%, transparent 40%), radial-gradient(circle at bottom left, #F4EFE6 0%, transparent 40%);
            padding: 20px;
        }
        .card {
            background: #fff; padding: 40px; border-radius: 15px;
            box-shadow: 0 10px 30px rgba(107, 29, 47, 0.1);
            width: 100%; max-width: 400px;
            border-top: 5px solid var(--gold);
        }
        .card h2 { font-family: 'Playfair Display', serif; text-align: center; margin-bottom: 25px; color: var(--maroon); font-size: 26px; font-weight: 700;}
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 6px; color: var(--maroon); font-size: 13px; font-weight: 600;}
        .form-group input {
            width: 100%; padding: 10px 12px; border: 1px solid #DCD3C6; border-radius: 8px;
            outline: none; transition: 0.3s; background: var(--cream); font-size: 14px;
        }
        .form-group input:focus { border-color: var(--maroon); box-shadow: 0 0 0 3px rgba(107, 29, 47, 0.1); background: #fff;}
        .btn {
            width: 100%; padding: 12px; background: var(--maroon); color: var(--cream);
            border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer;
            transition: 0.3s; margin-top: 10px; letter-spacing: 0.5px;
        }
        .btn:hover { background: var(--maroon-dark); box-shadow: 0 4px 15px rgba(107, 29, 47, 0.3); transform: translateY(-1px);}
        .alert { padding: 10px; border-radius: 8px; margin-bottom: 15px; text-align: center; font-size: 13px; font-weight: 500;}
        .alert-error { background: #FDF2F2; color: #9B1C1C; border: 1px solid #F8B4B4; }
        .alert-sukses { background: #F0FDF4; color: #166534; border: 1px solid #BBF7D0; }
        .link { text-align: center; margin-top: 20px; font-size: 14px; color: #666;}
        .link a { color: var(--gold-hover); text-decoration: none; font-weight: 600; transition: 0.2s;}
        .link a:hover { color: var(--maroon); }
    </style>
</head>
<body>
    <div class="card">
        <h2>Daftar Pelanggan</h2>
        <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
        <?php if ($sukses): ?><div class="alert alert-sukses"><?= $sukses ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama_lengkap" required placeholder="Contoh: Rina Wijaya">
            </div>
            <div class="form-group">
                <label>Kota Asal</label>
                <input type="text" name="kota" required placeholder="Contoh: Ponorogo">
            </div>
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="Buat username">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Buat password">
            </div>
            <button type="submit" class="btn">Daftar Sebagai Pelanggan</button>
        </form>
        <div class="link">Sudah punya akun? <a href="login.php">Login di sini</a></div>
    </div>
</body>
</html>