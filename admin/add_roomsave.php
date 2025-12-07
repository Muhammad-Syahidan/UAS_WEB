<?php
session_start();
include "../assets/koneksi.php";

// Cek Keamanan
if (!isset($_SESSION["iduser"]) || ($_SESSION['auth'] != 'Administrator')) {
    header("Location: ../main.php");
    exit();
}

// Cek POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_hotel = $_POST['id_hotel'];
    $tipe     = mysqli_real_escape_string($conn, $_POST['tipe_kamar']);
    $harga    = $_POST['harga'];
    $stok     = $_POST['stok'];
    $desc     = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    
    // Proses Upload Foto
    $foto = $_FILES['foto']['name'];
    $tmp  = $_FILES['foto']['tmp_name'];
    
    if (!empty($foto)) {
        $fotobaru = "room_" . date('dmYHis') . $foto;
        $path = "../img/" . $fotobaru;
        
        if (move_uploaded_file($tmp, $path)) {
            // Query Insert
            $stmt = $conn->prepare("INSERT INTO room_types (id_hotel, tipe_kamar, harga, stok, deskripsi, foto) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isdiss", $id_hotel, $tipe, $harga, $stok, $desc, $fotobaru);
            
            if ($stmt->execute()) {
                // Redirect Balik ke INPUT dengan pesan SUKSES
                header("Location: add_roominput.php?pesan=sukses");
            } else {
                // Gagal Database
                header("Location: add_roominput.php?pesan=gagaldb");
            }
        } else {
            // Gagal Upload
            header("Location: add_roominput.php?pesan=gagalupload");
        }
    } else {
        // Foto Kosong
        header("Location: add_roominput.php?pesan=gagalupload");
    }
} else {
    header("Location: add_roominput.php");
}
?>