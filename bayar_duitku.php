<?php
session_start();
require 'koneksi.php';
require 'duitku_config.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'pelanggan') {
    header("Location: login.php");
    exit;
}

$id_trx = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$q = mysqli_query($koneksi, "SELECT t.*, p.nama_pelanggan FROM tbl_transaksi t JOIN tbl_pelanggan p ON t.id_pelanggan = p.id_pelanggan WHERE t.id_transaksi = '$id_trx'");
$trx = mysqli_fetch_assoc($q);

if (!$trx) {
    echo "Transaksi tidak ditemukan.";
    exit;
}

$paymentAmount   = (int)$trx['total_belanja'];
$error_msg       = '';
$payment_url     = $trx['payment_url'] ?? '';
$merchantOrderId = $trx['merchant_order_id'] ?? '';
$referenceDuitku = $trx['reference_duitku'] ?? '';

// 1. Cek status pembayaran ke API Duitku (berguna saat testing di localhost tanpa callback publik)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cek_status_duitku'])) {
    $order_cek = $trx['merchant_order_id'] ?? '';
    if (!empty($order_cek)) {
        $sig_status = md5(DUITKU_MERCHANT_CODE . $order_cek . DUITKU_API_KEY);

        $ch3 = curl_init($duitku_urls['status']);
        curl_setopt_array($ch3, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'merchantCode'    => DUITKU_MERCHANT_CODE,
                'merchantOrderId' => $order_cek,
                'signature'       => $sig_status
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        $res_status = curl_exec($ch3);
        curl_close($ch3);

        $data_status = json_decode($res_status, true);
        $status_code = $data_status['statusCode'] ?? '';

        if ($status_code === '00') {
            mysqli_query($koneksi, "UPDATE tbl_transaksi 
                SET uang_bayar = '$paymentAmount', 
                    kembalian = 0, 
                    status_bayar = 'Lunas', 
                    status_pengiriman = 'Proses Packing' 
                WHERE id_transaksi = '$id_trx'");

            header("Location: struk.php?id=$id_trx");
            exit;
        } else {
            $error_msg = "Status di Duitku belum Lunas (" . ($data_status['statusMessage'] ?? ($status_code ?: 'Menunggu pembayaran')) . ").";
        }
    } else {
        $error_msg = "Merchant Order ID belum tersedia. Silakan pilih metode pembayaran terlebih dahulu.";
    }
}

// 2. Saat pelanggan memilih salah satu metode pembayaran (request inquiry ke Duitku)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method'])) {
    $selected_method = mysqli_real_escape_string($koneksi, $_POST['payment_method']);
    $merchantOrderId = 'ORD-' . $trx['id_transaksi'] . '-' . time();
    $signature = md5(DUITKU_MERCHANT_CODE . $merchantOrderId . $paymentAmount . DUITKU_API_KEY);

    $payload_inquiry = [
        'merchantCode'    => DUITKU_MERCHANT_CODE,
        'paymentAmount'   => $paymentAmount,
        'paymentMethod'   => $selected_method,
        'merchantOrderId' => $merchantOrderId,
        'productDetails'  => 'Belanja Online #' . $trx['id_transaksi'],
        'customerVaName'  => $trx['nama_pelanggan'],
        'email'           => 'pelanggan@tokokita.local',
        'callbackUrl'     => DUITKU_CALLBACK_URL,
        'returnUrl'       => DUITKU_RETURN_URL . '?id_trx=' . $trx['id_transaksi'],
        'expiryPeriod'    => 60,
        'signature'       => $signature
    ];

    $ch2 = curl_init($duitku_urls['inquiry']);
    curl_setopt_array($ch2, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload_inquiry),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $res_inq = curl_exec($ch2);
    curl_close($ch2);

    $data_inq = json_decode($res_inq, true);

    if (isset($data_inq['paymentUrl'])) {
        $payment_url     = $data_inq['paymentUrl'];
        $referenceDuitku = $data_inq['reference'] ?? '';
        $ref_safe        = mysqli_real_escape_string($koneksi, $referenceDuitku);
        $url_safe        = mysqli_real_escape_string($koneksi, $payment_url);
        $order_safe      = mysqli_real_escape_string($koneksi, $merchantOrderId);

        mysqli_query($koneksi, "UPDATE tbl_transaksi 
            SET metode_pembayaran = 'Duitku ($selected_method)', 
                merchant_order_id = '$order_safe', 
                reference_duitku = '$ref_safe', 
                payment_url = '$url_safe' 
            WHERE id_transaksi = '$id_trx'");
    } else {
        $error_msg = $data_inq['Message'] ?? 'Gagal membuat transaksi ke Duitku. Cek API Key & metode pembayaran.';
    }
}

// Refresh data transaksi terbaru jika belum lunas
$q_latest = mysqli_query($koneksi, "SELECT * FROM tbl_transaksi WHERE id_transaksi = '$id_trx'");
$trx_latest = mysqli_fetch_assoc($q_latest);
if ($trx_latest) {
    $trx = $trx_latest;
    $payment_url     = $trx['payment_url'] ?? $payment_url;
    $merchantOrderId = $trx['merchant_order_id'] ?? $merchantOrderId;
    $referenceDuitku = $trx['reference_duitku'] ?? $referenceDuitku;
}

// 3. Ambil daftar metode pembayaran dari API Duitku
$datetime   = date('Y-m-d H:i:s');
$sig_method = hash('sha256', DUITKU_MERCHANT_CODE . $paymentAmount . $datetime . DUITKU_API_KEY);

$ch = curl_init($duitku_urls['payment_method']);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        'merchantcode' => DUITKU_MERCHANT_CODE,
        'amount'       => $paymentAmount,
        'datetime'     => $datetime,
        'signature'    => $sig_method
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_SSL_VERIFYPEER => false
]);
$res_method = curl_exec($ch);
curl_close($ch);

$data_method     = json_decode($res_method, true);
$payment_methods = $data_method['paymentFee'] ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pilih Pembayaran Duitku - Order #<?= $trx['id_transaksi'] ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --maroon: #6B1D2F; --cream: #FDFBF7; --gold: #C5A059; }
        body { background: var(--cream); font-family: 'Poppins', sans-serif; padding: 30px; display: flex; justify-content: center; }
        .box { background: #fff; max-width: 720px; width: 100%; padding: 30px; border-radius: 14px; border-top: 5px solid var(--gold); box-shadow: 0 8px 25px rgba(0,0,0,0.06); }
        h2 { font-family: 'Playfair Display', serif; color: var(--maroon); margin-bottom: 6px; }
        .total { font-size: 22px; font-weight: 700; color: var(--maroon); margin: 15px 0 25px; }
        .grid-method { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; }
        .method-card { border: 1px solid #E5DEC9; border-radius: 10px; padding: 14px; background: #FDFBF7; display: flex; flex-direction: column; justify-content: space-between; gap: 10px; }
        .method-card img { max-height: 28px; object-fit: contain; align-self: flex-start; }
        .btn-pilih { background: var(--maroon); color: #fff; border: none; padding: 9px 12px; border-radius: 7px; font-weight: 600; font-size: 12px; cursor: pointer; width: 100%; }
        .btn-pilih:hover { background: #4A1320; }
        .btn-link { display: inline-block; margin-top: 10px; margin-right: 6px; padding: 11px 16px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 13px; border: none; }
        .btn-primary { background: var(--maroon); color: #fff; }
        .btn-demo { background: #F4EFE6; color: var(--maroon); border: 1px solid var(--gold); cursor: pointer; }
        .err { background: #FEE2E2; color: #991B1B; padding: 12px; border-radius: 8px; margin-bottom: 15px; font-size: 13px; }
        .ready-box { background: #F0FDF4; border: 1px solid #BBF7D0; padding: 18px; border-radius: 10px; margin-bottom: 20px; }
    </style>
</head>
<body>
<div class="box">
    <h2>Pilih Metode Pembayaran</h2>
    <p style="font-size: 13px; color: #777;">Order ID: <strong>#<?= $trx['id_transaksi'] ?></strong></p>
    <div class="total">Total: Rp <?= number_format($paymentAmount, 0, ',', '.') ?></div>

    <?php if ($error_msg): ?>
        <div class="err"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <?php if ($trx['status_bayar'] == 'Lunas'): ?>
        <div class="ready-box">
            <strong>Pembayaran sudah LUNAS!</strong>
            <div><a href="struk.php?id=<?= $trx['id_transaksi'] ?>" class="btn-link btn-primary">Buka Struk LUNAS</a></div>
        </div>
    <?php elseif ($payment_url): ?>
        <div class="ready-box">
            <p style="font-size: 13px; color: #166534; margin-bottom: 6px;">Invoice Duitku berhasil dibuat! Silakan lanjutkan pembayaran:</p>
            <?php if ($merchantOrderId): ?>
                <p style="font-size: 12px; color: #555; margin: 2px 0;">Merchant Order ID: <strong><?= htmlspecialchars($merchantOrderId) ?></strong></p>
            <?php endif; ?>
            <?php if ($referenceDuitku): ?>
                <p style="font-size: 12px; color: #555; margin: 2px 0;">Duitku Reference: <strong><?= htmlspecialchars($referenceDuitku) ?></strong></p>
            <?php endif; ?>

            <div style="margin-top: 8px;">
                <a href="<?= htmlspecialchars($payment_url) ?>" class="btn-link btn-primary">Bayar di Halaman Duitku</a>
                <a href="https://sandbox.duitku.com/payment/demo/demosuccesstransaction.aspx" target="_blank" class="btn-link btn-demo">Buka Demo Sukses Duitku</a>
                <form method="POST" style="display: inline-block;">
                    <button type="submit" name="cek_status_duitku" class="btn-link btn-demo">Sudah Bayar / Cek Status</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($trx['status_bayar'] != 'Lunas'): ?>
        <?php if (!empty($payment_methods)): ?>
            <form method="POST">
                <div class="grid-method">
                    <?php foreach ($payment_methods as $m): ?>
                        <?php
                            $kode = $m['paymentMethod'] ?? '';
                            $nama = $m['paymentName'] ?? $kode;
                            $fee  = (int)($m['totalFee'] ?? 0);
                            $img  = $m['paymentImage'] ?? '';
                        ?>
                        <div class="method-card">
                            <div>
                                <?php if ($img): ?>
                                    <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($nama) ?>">
                                <?php endif; ?>
                                <div style="font-weight: 600; font-size: 13px; margin-top: 8px;"><?= htmlspecialchars($nama) ?></div>
                                <div style="font-size: 11px; color: #777;">Biaya: Rp <?= number_format($fee, 0, ',', '.') ?></div>
                            </div>
                            <button type="submit" name="payment_method" value="<?= htmlspecialchars($kode) ?>" class="btn-pilih">Pilih <?= htmlspecialchars($kode) ?></button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </form>
        <?php else: ?>
            <p style="font-size: 13px; color: #888;">Daftar metode pembayaran belum muncul. Pastikan <code>DUITKU_API_KEY</code> di <code>duitku_config.php</code> sudah diisi dan server PHP mendukung cURL.</p>
        <?php endif; ?>
    <?php endif; ?>

    <div style="margin-top: 20px;">
        <a href="riwayat_pelanggan.php" style="font-size: 13px; color: #777; text-decoration: none;">← Kembali ke Riwayat Belanja</a>
    </div>
</div>
</body>
</html>