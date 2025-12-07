<?php
session_start();
include "../assets/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_user  = $_SESSION['iduser'];
    $id_kamar = $_POST['id_kamar'];
    $checkin  = $_POST['checkin'];
    $checkout = $_POST['checkout'];
    $total    = $_POST['total'];
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $hp       = mysqli_real_escape_string($conn, $_POST['no_hp']);

    // 1. Simpan ke Tabel Bookings
    $stmt = $conn->prepare("INSERT INTO bookings (id_user, id_room_type, tgl_checkin, tgl_checkout, total_harga, nama_pemesan, no_hp, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Confirmed')");
    $stmt->bind_param("iisssss", $id_user, $id_kamar, $checkin, $checkout, $total, $nama, $hp);

    if ($stmt->execute()) {
        // 2. Kurangi Stok Kamar
        $conn->query("UPDATE room_types SET stok = stok - 1 WHERE id_room_type = '$id_kamar'");

        // 3. Redirect ke Halaman Sukses / List Booking
        echo "<script>
                alert('Pemesanan Berhasil! Terima kasih.');
                window.location = 'my_bookings.php'; 
              </script>";
    } else {
        echo "<script>alert('Gagal memproses pesanan.'); window.history.back();</script>";
    }
} else {
    header("Location: ../main.php");
}
?>