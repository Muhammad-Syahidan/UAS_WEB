<?php
include "koneksi.php";

$nama = $_POST['nama'];
$user = $_POST['user'];
$pass = $_POST['pass']; // Catatan: Di produksi, gunakan password_hash()

// Cek Username Kembar
$cek = $conn->query("SELECT * FROM user_list WHERE username='$user'");
if($cek->num_rows > 0){
    echo "<script>alert('Username sudah terpakai!'); window.location='../register.php';</script>";
} else {
    // Simpan (Role default Pengguna)
    $sql = "INSERT INTO user_list (username, password, nama_lengkap, auth, avatar) VALUES ('$user', '$pass', '$nama', 'Pengguna', 'default.png')";
    
    if($conn->query($sql)){
        echo "<script>alert('Pendaftaran Berhasil! Silakan Login.'); window.location='../index.php';</script>";
    } else {
        echo "<script>alert('Gagal Daftar. Error: " . $conn->error . "'); window.location='../register.php';</script>";
    }
}
?>