<?php
session_start();
include 'koneksi.php';

// Ambil data dari form login
// Pastikan di HTML index.php name inputnya adalah: name="user", name="pass", name="auth"
$username = $_POST['user']; 
$password = $_POST['pass']; 
$auth     = $_POST['auth'];

// Query SQL (Sesuaikan dengan Gambar Database Anda)
// Kolom: username, password, auth
$sql = "SELECT * FROM user_list WHERE username='$username' AND password='$password' AND auth='$auth'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);

    // =========================================================
    // PENTING: SET SESSION AGAR COCOK DENGAN MAIN.PHP
    // =========================================================
    // Kita simpan 'id_user' dari DB ke session bernama 'iduser'
    $_SESSION['iduser'] = $row['id_user']; 
    $_SESSION['user']   = $row['username'];
    $_SESSION['auth']   = $row['auth'];
    
    // Karena di gambar DB tidak ada kolom avatar, kita buat default
    $_SESSION['avatar'] = 'default.png'; 

    // Redirect ke Main
    header("Location: ../main.php?p=home");
} else {
    // Jika gagal
    echo "<script>
            alert('Login Gagal! Username, Password, atau Role salah.'); 
            window.location='../index.php';
          </script>";
}

$conn->close();
?>