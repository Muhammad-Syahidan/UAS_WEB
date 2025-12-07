<?php
session_start();
include "../assets/koneksi.php";

if (!isset($_SESSION["iduser"])) { header("Location: ../index.php"); exit(); }
$id_user = $_SESSION['iduser'];

// Ambil Data Pesanan User Ini
$query = "SELECT b.*, r.tipe_kamar, h.nama_hotel, h.foto_utama 
          FROM bookings b
          JOIN room_types r ON b.id_room_type = r.id_room_type
          JOIN hotel_list h ON r.id_hotel = h.id_hotel
          WHERE b.id_user = '$id_user'
          ORDER BY b.id_booking DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pesanan Saya - HotelID</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --primary: #0ea5e9; --accent: #f97316; --bg-body: #f8fafc; }
        body { background-color: var(--bg-body); font-family: 'Inter', sans-serif; }
        .navbar { background: var(--primary); padding: 15px 0; }
        .booking-item { background: white; border-radius: 15px; padding: 20px; margin-bottom: 20px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 20px; }
        .img-thumb { width: 120px; height: 90px; object-fit: cover; border-radius: 10px; }
        .badge-status { background: #dcfce7; color: #166534; padding: 5px 15px; border-radius: 50px; font-size: 0.8rem; font-weight: 600; }
    </style>
</head>
<body>

<nav class="navbar sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold text-white" href="../main.php">HOTEL<span class="text-warning">ID</span>.</a>
        <a href="../main.php" class="btn btn-outline-light btn-sm rounded-pill">Kembali</a>
    </div>
</nav>

<div class="container py-5">
    <h2 class="fw-bold mb-4" style="font-family: 'Outfit', sans-serif;">Riwayat Pesanan</h2>
    
    <?php if($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): 
             $foto = (!empty($row['foto_utama']) && file_exists("../img/".$row['foto_utama'])) ? "../img/".$row['foto_utama'] : $row['foto_utama'];
        ?>
        <div class="booking-item shadow-sm">
            <img src="<?= $foto ?>" class="img-thumb">
            <div class="flex-grow-1">
                <h5 class="fw-bold mb-1"><?= $row['nama_hotel'] ?></h5>
                <p class="text-muted mb-1 small"><?= $row['tipe_kamar'] ?></p>
                <div class="small text-muted">
                    <i class="bi bi-calendar-check"></i> <?= date('d M', strtotime($row['tgl_checkin'])) ?> - <?= date('d M Y', strtotime($row['tgl_checkout'])) ?>
                </div>
            </div>
            <div class="text-end">
                <div class="fs-5 fw-bold text-primary">Rp <?= number_format($row['total_harga']) ?></div>
                <span class="badge-status">Berhasil</span>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="text-center py-5 text-muted">Belum ada riwayat pesanan.</div>
    <?php endif; ?>
</div>

</body>
</html>