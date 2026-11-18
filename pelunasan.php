<?php
session_start();
require 'koneksi.php';
mysqli_report(MYSQLI_REPORT_OFF);

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$pesan = '';
$tipe_pesan = 'sukses';

// ---------- PROSES BAYAR / CICIL ----------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bayar'])) {
    $id_transaksi = (int)$_POST['id_transaksi'];
    $nominal      = round((float)$_POST['nominal'], 2);

    mysqli_begin_transaction($koneksi);

    $stmt = mysqli_prepare($koneksi, "
        SELECT t.total_belanja, t.uang_bayar,
               COALESCE((SELECT SUM(c.nominal) FROM tbl_pelunasan c WHERE c.id_transaksi = t.id_transaksi), 0) AS cicilan
        FROM tbl_transaksi t
        WHERE t.id_transaksi = ? AND t.metode_pembayaran = 'Utang'
        FOR UPDATE
    ");
    mysqli_stmt_bind_param($stmt, "i", $id_transaksi);
    mysqli_stmt_execute($stmt);
    $t = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$t) {
        mysqli_rollback($koneksi);
        $pesan = "Bon nggak ditemukan atau sudah lunas.";
        $tipe_pesan = 'gagal';
    } else {
        $sisa = round($t['total_belanja'] - $t['uang_bayar'] - $t['cicilan'], 2);

        if ($nominal <= 0) {
            mysqli_rollback($koneksi);
            $pesan = "Nominal harus lebih dari 0.";
            $tipe_pesan = 'gagal';
        } elseif ($nominal > $sisa) {
            mysqli_rollback($koneksi);
            $pesan = "Nominal melebihi sisa utang (Rp " . number_format($sisa, 0, ',', '.') . ").";
            $tipe_pesan = 'gagal';
        } else {
            // 1. catat cicilan
            $stmt_in = mysqli_prepare($koneksi, "INSERT INTO tbl_pelunasan (id_transaksi, nominal) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt_in, "id", $id_transaksi, $nominal);
            $ok = mysqli_stmt_execute($stmt_in);

            // 2. kalau sudah menutup sisa -> tandai lunas
            $lunas = ($nominal >= $sisa - 0.001);
            if ($ok && $lunas) {
                $stmt_up = mysqli_prepare($koneksi, "UPDATE tbl_transaksi SET metode_pembayaran = 'Utang (Lunas)' WHERE id_transaksi = ?");
                mysqli_stmt_bind_param($stmt_up, "i", $id_transaksi);
                $ok = mysqli_stmt_execute($stmt_up);
            }

            if ($ok) {
                mysqli_commit($koneksi);
                header("Location: pelunasan.php?status=" . ($lunas ? 'lunas' : 'cicil') . "&id=$id_transaksi");
                exit;
            }
            mysqli_rollback($koneksi);
            $pesan = "Gagal menyimpan pembayaran.";
            $tipe_pesan = 'gagal';
        }
    }
}

// ---------- DATA ----------
$bon = mysqli_query($koneksi, "
    SELECT t.*, p.nama_pelanggan,
           (t.uang_bayar + COALESCE(c.total_cicil, 0)) AS sudah_bayar,
           (t.total_belanja - t.uang_bayar - COALESCE(c.total_cicil, 0)) AS sisa
    FROM tbl_transaksi t
    JOIN tbl_pelanggan p ON t.id_pelanggan = p.id_pelanggan
    LEFT JOIN (SELECT id_transaksi, SUM(nominal) AS total_cicil FROM tbl_pelunasan GROUP BY id_transaksi) c
           ON c.id_transaksi = t.id_transaksi
    WHERE t.metode_pembayaran = 'Utang'
    ORDER BY t.id_transaksi DESC
");

$ringkas = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT COUNT(*) AS jumlah,
           COALESCE(SUM(t.total_belanja - t.uang_bayar - COALESCE(c.total_cicil, 0)), 0) AS total_sisa
    FROM tbl_transaksi t
    LEFT JOIN (SELECT id_transaksi, SUM(nominal) AS total_cicil FROM tbl_pelunasan GROUP BY id_transaksi) c
           ON c.id_transaksi = t.id_transaksi
    WHERE t.metode_pembayaran = 'Utang'
"));

$status = $_GET['status'] ?? '';
$id_status = (int)($_GET['id'] ?? 0);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pelunasan Utang - Toko Kita</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --maroon: #6B1D2F; --maroon-dark: #4A1320; --cream: #FDFBF7;
            --cream-card: #F4EFE6; --gold: #C5A059; --gold-hover: #B08D48; --text-dark: #2C2C2C;
            --sidebar-bg: #2A0A10; --green: #27ae60; --red: #e74c3c;
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
        .topbar { margin-bottom: 25px; }
        .topbar h1 { font-family: 'Playfair Display', serif; font-size: 28px; color: var(--maroon); }

        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 8px 25px rgba(107, 29, 47, 0.05); border: 1px solid #EBE2D3; margin-bottom: 25px;}
        .card h2 { font-family: 'Playfair Display', serif; font-size: 20px; margin-bottom: 20px; color: var(--maroon); border-bottom: 2px solid var(--cream-card); padding-bottom: 12px; }

        .ringkasan { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        .ringkasan .box { background: #fff; border: 1px solid #EBE2D3; border-left: 5px solid var(--red); border-radius: 12px; padding: 20px; box-shadow: 0 8px 25px rgba(107, 29, 47, 0.05);}
        .ringkasan .box small { color: #888; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
        .ringkasan .box h3 { font-size: 24px; color: var(--maroon); margin-top: 5px; }

        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; font-size: 14px; background: #F0FDF4; border: 1px solid #BBF7D0; color: #166534;}
        .alert.gagal { background: #FDF2F2; border-color: #F8B4B4; color: #9B1C1C; }
        .alert a { color: inherit; font-weight: 700; }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 14px 10px; text-align: left; border-bottom: 1px solid var(--cream-card); font-size: 14px; vertical-align: middle;}
        th { color: var(--maroon); font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; background: var(--cream);}
        tr:hover { background: #fafafa; }
        .td-sisa { font-weight: 700; color: var(--red); }

        .form-bayar { display: flex; gap: 6px; align-items: center; }
        .form-bayar input { width: 120px; padding: 8px 10px; border: 1px solid #DCD3C6; border-radius: 6px; font-size: 13px; background: var(--cream); outline: none; }
        .form-bayar input:focus { border-color: var(--maroon); }
        .btn { border: none; padding: 8px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; transition: 0.2s; text-decoration: none; display: inline-block; }
        .btn-bayar { background: var(--maroon); color: #fff; }
        .btn-bayar:hover { background: var(--maroon-dark); }
        .btn-penuh { background: #F6F0E4; color: var(--maroon); border: 1px solid #DCD3C6; }
        .btn-penuh:hover { background: var(--gold); color: #fff; }
        .btn-struk { background: #F6F0E4; color: var(--maroon); border: 1px solid #DCD3C6; }
        .empty { text-align: center; color: #888; padding: 30px; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <h1>Pelunasan Utang (Bon)</h1>
        </div>

        <div class="ringkasan">
            <div class="box">
                <small>Bon Belum Lunas</small>
                <h3><?= $ringkas['jumlah'] ?> transaksi</h3>
            </div>
            <div class="box">
                <small>Total Sisa Utang</small>
                <h3>Rp <?= number_format($ringkas['total_sisa'], 0, ',', '.') ?></h3>
            </div>
        </div>

        <?php if($pesan): ?>
            <div class="alert <?= $tipe_pesan == 'gagal' ? 'gagal' : '' ?>"><?= htmlspecialchars($pesan) ?></div>
        <?php endif; ?>
        <?php if($status == 'lunas'): ?>
            <div class="alert">Bon #<?= $id_status ?> sudah LUNAS! <a href="struk.php?id=<?= $id_status ?>" target="_blank">Cetak struk pelunasan</a></div>
        <?php elseif($status == 'cicil'): ?>
            <div class="alert">Pembayaran untuk bon #<?= $id_status ?> berhasil dicatat, masih ada sisa utang. <a href="struk.php?id=<?= $id_status ?>" target="_blank">Lihat struk</a></div>
        <?php endif; ?>

        <div class="card">
            <h2>Daftar Bon</h2>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Sudah Bayar</th>
                        <th>Sisa</th>
                        <th>Bayar</th>
                        <th>Struk</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($bon) > 0): ?>
                        <?php while($b = mysqli_fetch_assoc($bon)): ?>
                        <tr>
                            <td><strong>#<?= $b['id_transaksi'] ?></strong></td>
                            <td><?= date('d/m/Y', strtotime($b['tanggal'])) ?></td>
                            <td><?= htmlspecialchars($b['nama_pelanggan']) ?></td>
                            <td>Rp <?= number_format($b['total_belanja'], 0, ',', '.') ?></td>
                            <td>Rp <?= number_format($b['sudah_bayar'], 0, ',', '.') ?></td>
                            <td class="td-sisa">Rp <?= number_format($b['sisa'], 0, ',', '.') ?></td>
                            <td>
                                <form method="POST" action="pelunasan.php" class="form-bayar"
                                      onsubmit="return confirm('Catat pembayaran untuk bon #<?= $b['id_transaksi'] ?>?')">
                                    <input type="hidden" name="id_transaksi" value="<?= $b['id_transaksi'] ?>">
                                    <input type="number" name="nominal" min="1" max="<?= round($b['sisa']) ?>" step="any" placeholder="Nominal" required>
                                    <button type="button" class="btn btn-penuh" onclick="this.form.nominal.value='<?= round($b['sisa']) ?>'">Lunasi</button>
                                    <button type="submit" name="bayar" class="btn btn-bayar">Bayar</button>
                                </form>
                            </td>
                            <td><a href="struk.php?id=<?= $b['id_transaksi'] ?>" class="btn btn-struk" target="_blank">Lihat</a></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="empty">Mantap, nggak ada bon yang belum lunas 🎉</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>