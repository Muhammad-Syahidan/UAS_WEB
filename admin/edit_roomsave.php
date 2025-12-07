<?php
session_start();
include "../assets/koneksi.php";

// Cek Admin
if (!isset($_SESSION["iduser"]) || ($_SESSION['auth'] != 'Administrator')) {
    header("Location: ../main.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_room  = $_POST['id_room'];
    $id_hotel = $_POST['id_hotel'];
    $tipe     = mysqli_real_escape_string($conn, $_POST['tipe_kamar']);
    $harga    = $_POST['harga'];
    $stok     = $_POST['stok'];
    $desc     = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    
    $foto = $_FILES['foto']['name'];
    $tmp  = $_FILES['foto']['tmp_name'];
    
    if (!empty($foto)) {
        // --- GANTI FOTO ---
        $fotobaru = "room_" . date('dmYHis') . $foto;
        $path = "../img/" . $fotobaru;
        
        if (move_uploaded_file($tmp, $path)) {
            $stmt = $conn->prepare("UPDATE room_types SET id_hotel=?, tipe_kamar=?, harga=?, stok=?, deskripsi=?, foto=? WHERE id_room_type=?");
            $stmt->bind_param("isdissi", $id_hotel, $tipe, $harga, $stok, $desc, $fotobaru, $id_room);
            
            if ($stmt->execute()) {
                header("Location: edit_roominput.php?id=$id_room&pesan=sukses");
            } else {
                header("Location: edit_roominput.php?id=$id_room&pesan=gagaldb");
            }
        } else {
            header("Location: edit_roominput.php?id=$id_room&pesan=gagalupload");
        }

    } else {
        // --- TIDAK GANTI FOTO ---
        $stmt = $conn->prepare("UPDATE room_types SET id_hotel=?, tipe_kamar=?, harga=?, stok=?, deskripsi=? WHERE id_room_type=?");
        $stmt->bind_param("isdisi", $id_hotel, $tipe, $harga, $stok, $desc, $id_room);
        
        if ($stmt->execute()) {
            header("Location: edit_roominput.php?id=$id_room&pesan=sukses");
        } else {
            header("Location: edit_roominput.php?id=$id_room&pesan=gagaldb");
        }
    }

} else {
    header("Location: list_rooms.php");
}
?>