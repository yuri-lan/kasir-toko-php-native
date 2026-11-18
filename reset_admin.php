<?php
$conn = new mysqli("localhost", "root", "", "db_toko");

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

$passwordBaru = "AdminToko2026!";
$hash = password_hash($passwordBaru, PASSWORD_BCRYPT);

$stmt = $conn->prepare("UPDATE tbl_user SET password = ? WHERE username = 'admin'");
$stmt->bind_param("s", $hash);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "Password admin berhasil diganti!";
} else {
    echo "Password tidak berubah atau username admin tidak ditemukan.";
}

$stmt->close();
$conn->close();
?>