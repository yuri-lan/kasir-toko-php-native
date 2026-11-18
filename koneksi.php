<?php
date_default_timezone_set('Asia/Jakarta'); // jam PHP = WIB

$host = "localhost";
$user = "root"; // default XAMPP
$pass = "";     // default XAMPP kosong
$db   = "db_toko";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

mysqli_query($koneksi, "SET time_zone = '+07:00'"); // jam MySQL = WIB
?>