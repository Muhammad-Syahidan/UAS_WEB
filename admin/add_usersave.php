<?php
session_start();
include "../assets/koneksi.php";

// 1. Cek Admin
if (!isset($_SESSION["iduser"]) || ($_SESSION['auth'] != 'Administrator')) {
    header("Location: ../main.php");
    exit();
}

// 2. Proses Simpan
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = mysqli_real_escape_string($conn, $_POST['password']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    
    // Cek Username Kembar
    $cek = $conn->query("SELECT * FROM user_list WHERE username='$user'");
    
    if ($cek->num_rows > 0) {
        // Gagal: Username ada
        header("Location: add_userinput.php?pesan=gagal");
    } else {
        // Simpan Data
        $stmt = $conn->prepare("INSERT INTO user_list (username, password, nama_lengkap, auth, avatar) VALUES (?, ?, ?, ?, 'default.png')");
        $stmt->bind_param("ssss", $user, $pass, $nama, $role);
        
        if ($stmt->execute()) {
            header("Location: manage_users.php?pesan=sukses");
        } else {
            echo "Error: " . $conn->error;
        }
    }
} else {
    header("Location: add_userinput.php");
}
?>