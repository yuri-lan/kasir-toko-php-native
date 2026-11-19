<?php
require 'koneksi.php';
require 'duitku_config.php';

$merchantCode    = $_POST['merchantCode'] ?? '';
$amount          = $_POST['amount'] ?? '';
$merchantOrderId = $_POST['merchantOrderId'] ?? '';
$resultCode      = $_POST['resultCode'] ?? '';
$signature       = $_POST['signature'] ?? '';

if ($merchantCode && $amount && $merchantOrderId && $signature) {
    $validSignature = md5($merchantCode . $amount . $merchantOrderId . DUITKU_API_KEY);

    if (hash_equals($validSignature, $signature)) {
        // Format merchantOrderId: ORD-{id_transaksi}-{timestamp}
        $pecah = explode('-', $merchantOrderId);
        $id_trx = isset($pecah[1]) ? (int)$pecah[1] : 0;

        if ($id_trx > 0 && $resultCode == '00') {
            $q = mysqli_query($koneksi, "SELECT total_belanja FROM tbl_transaksi WHERE id_transaksi = '$id_trx'");
            $trx = mysqli_fetch_assoc($q);
            $total = $trx['total_belanja'] ?? $amount;

            mysqli_query($koneksi, "UPDATE tbl_transaksi 
                SET uang_bayar = '$total', 
                    kembalian = 0, 
                    status_bayar = 'Lunas', 
                    status_pengiriman = 'Proses Packing' 
                WHERE id_transaksi = '$id_trx'");
        }
        echo "SUCCESS";
        exit;
    }
}

http_response_code(400);
echo "INVALID";
?>