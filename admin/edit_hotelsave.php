<?php
session_start();
include "../assets/koneksi.php";

// 1. CEK ADMIN
if (!isset($_SESSION["iduser"]) || ($_SESSION['auth'] != 'Administrator')) {
    header("Location: ../main.php");
    exit();
}

// 2. PROSES UPDATE
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_hotel = $_POST['id_hotel'];
    $nama     = mysqli_real_escape_string($conn, $_POST['nama_hotel']);
    $prov     = $_POST['provinsi'];
    $kota     = $_POST['kota'];
    $almt     = $_POST['alamat'];
    $desc     = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    
    // Cek upload foto baru
    $foto = $_FILES['foto']['name'];
    $tmp  = $_FILES['foto']['tmp_name'];
    
    if (!empty($foto)) {
        // --- JIKA GANTI FOTO ---
        $fotobaru = "hotel_" . date('dmYHis') . $foto;
        $path = "../img/" . $fotobaru;
        
        if (move_uploaded_file($tmp, $path)) {
            $stmt = $conn->prepare("UPDATE hotel_list SET nama_hotel=?, provinsi=?, kota=?, alamat=?, deskripsi=?, foto_utama=? WHERE id_hotel=?");
            $stmt->bind_param("ssssssi", $nama, $prov, $kota, $almt, $desc, $fotobaru, $id_hotel);
            
            if ($stmt->execute()) {
                header("Location: edit_hotelinput.php?id=$id_hotel&pesan=sukses");
            } else {
                header("Location: edit_hotelinput.php?id=$id_hotel&pesan=gagaldb");
            }
        } else {
            header("Location: edit_hotelinput.php?id=$id_hotel&pesan=gagalupload");
        }

    } else {
        // --- JIKA TIDAK GANTI FOTO ---
        $stmt = $conn->prepare("UPDATE hotel_list SET nama_hotel=?, provinsi=?, kota=?, alamat=?, deskripsi=? WHERE id_hotel=?");
        $stmt->bind_param("sssssi", $nama, $prov, $kota, $almt, $desc, $id_hotel);
        
        if ($stmt->execute()) {
            header("Location: edit_hotelinput.php?id=$id_hotel&pesan=sukses");
        } else {
            header("Location: edit_hotelinput.php?id=$id_hotel&pesan=gagaldb");
        }
    }

} else {
    header("Location: list_hotel.php");
}
?>