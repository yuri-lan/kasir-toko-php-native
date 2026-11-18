<?php
// Dipanggil di halaman yang khusus admin
function wajib_admin() {
    if (($_SESSION['role'] ?? '') !== 'admin') {
        http_response_code(403);
        echo "<script>alert('Akses ditolak! Halaman ini khusus admin.'); window.location='dashboard.php';</script>";
        exit;
    }
}
?>