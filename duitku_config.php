<?php
define('DUITKU_MERCHANT_CODE', 'DS38185');
define('DUITKU_API_KEY', '19d538c21da14d59e2eaf6279d828494');
define('DUITKU_IS_SANDBOX', true); // true = sandbox, false = production

define('BASE_URL', 'http://localhost/kasir_toko');

// Callback URL sesuai setting proyek di dashboard Duitku
define('DUITKU_CALLBACK_URL', 'https://strerida.my.id/lita/callback.php');
define('DUITKU_RETURN_URL', BASE_URL . '/return_duitku.php');

$duitku_urls = [
    'payment_method' => DUITKU_IS_SANDBOX
        ? 'https://sandbox.duitku.com/webapi/api/merchant/paymentmethod/getpaymentmethod'
        : 'https://passport.duitku.com/webapi/api/merchant/paymentmethod/getpaymentmethod',
    'inquiry' => DUITKU_IS_SANDBOX
        ? 'https://sandbox.duitku.com/webapi/api/merchant/v2/inquiry'
        : 'https://passport.duitku.com/webapi/api/merchant/v2/inquiry',
    'status' => DUITKU_IS_SANDBOX
        ? 'https://sandbox.duitku.com/webapi/api/merchant/transactionStatus'
        : 'https://passport.duitku.com/webapi/api/merchant/transactionStatus',
];
?>