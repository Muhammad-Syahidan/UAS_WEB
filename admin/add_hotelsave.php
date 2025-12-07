<?php
session_start();
include "../assets/koneksi.php";

// Cek keamanan: Hanya Admin yang boleh akses
if (!isset($_SESSION["iduser"]) || ($_SESSION['auth'] != 'Administrator')) {
    header("Location: ../main.php");
    exit();
}

// Cek apakah tombol submit ditekan
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_hotel']);
    $prov = $_POST['provinsi'];
    $kota = $_POST['kota'];
    $almt = $_POST['alamat'];
    $desc = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    
    // Upload Foto
    $foto = $_FILES['foto']['name'];
    $tmp  = $_FILES['foto']['tmp_name'];
    
    // Cek apakah ada file yang diupload
    if (!empty($foto)) {
        // Rename agar unik
        $fotobaru = "hotel_" . date('dmYHis') . $foto;
        $path = "../img/" . $fotobaru;

        if (move_uploaded_file($tmp, $path)) {
            // Query Insert
            $query = "INSERT INTO hotel_list (nama_hotel, provinsi, kota, alamat, deskripsi, foto_utama) VALUES ('$nama', '$prov', '$kota', '$almt', '$desc', '$fotobaru')";
            
            if ($conn->query($query)) {
                // Berhasil: Redirect balik ke INPUT
                header("Location: add_hotelinput.php?pesan=sukses");
            } else {
                // Gagal Database
                header("Location: add_hotelinput.php?pesan=gagaldb");
            }
        } else {
            // Gagal Upload
            header("Location: add_hotelinput.php?pesan=gagalupload");
        }
    } else {
        // Jika foto kosong
        header("Location: add_hotelinput.php?pesan=gagalupload");
    }
} else {
    // Jika akses langsung
    header("Location: add_hotelinput.php");
}
?>