<?php
require 'koneksi.php';

// Ambil semua data dari tbl_pelanggan
$query_pelanggan = mysqli_query($koneksi, "SELECT * FROM tbl_pelanggan");
$berhasil = 0;

while ($p = mysqli_fetch_assoc($query_pelanggan)) {
    $id_pelanggan = $p['id_pelanggan'];
    $nama = $p['nama_pelanggan'];
    
    // Buat username otomatis (misal: nama kecil semua + id_pelanggan biar unik, contoh: andi1, budi2)
    $nama_bersih = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nama));
    $username = $nama_bersih . $id_pelanggan;
    
    // Set password default sementara (misal: 123456)
    $password_default = password_hash('123456', PASSWORD_DEFAULT);
    $role = 'pelanggan';

    // Cek apakah pelanggan ini sudah punya akun di tbl_user
    $cek = mysqli_query($koneksi, "SELECT * FROM tbl_user WHERE id_pelanggan = '$id_pelanggan'");
    
    if (mysqli_num_rows($cek) == 0) {
        $insert = "INSERT INTO tbl_user (id_pelanggan, username, password, nama_lengkap, role) 
                   VALUES ('$id_pelanggan', '$username', '$password_default', '$nama', '$role')";
        if (mysqli_query($koneksi, $insert)) {
            $berhasil++;
            echo "Akun dibuat -> Nama: <b>$nama</b> | Username: <b>$username</b> | Password: <b>123456</b><br>";
        }
    }
}

echo "<hr>Total akun pelanggan yang berhasil dibuat: <b>$berhasil</b> akun.<br>";
echo "<a href='login.php'>Ke Halaman Login</a>";
?>