<?php
session_start();
error_reporting(0); 
include "../assets/koneksi.php";

// 1. VALIDASI ID HOTEL (Menerima POST)
$id_hotel = null;

if (isset($_POST['id_hotel']) && !empty($_POST['id_hotel'])) {
    $id_hotel = mysqli_real_escape_string($conn, $_POST['id_hotel']);
} elseif (isset($_GET['id_hotel']) && !empty($_GET['id_hotel'])) {
    // Fallback jika masih ada link manual
    $id_hotel = mysqli_real_escape_string($conn, $_GET['id_hotel']);
} 

if (!$id_hotel) {
    header("Location: ../main.php");
    exit();
}

$auth = $_SESSION['auth'] ?? 'Guest';

// 2. DATA HOTEL
$q_hotel = $conn->prepare("SELECT * FROM hotel_list WHERE id_hotel = ?");
$q_hotel->bind_param("i", $id_hotel);
$q_hotel->execute();
$hotel = $q_hotel->get_result()->fetch_assoc();

if (!$hotel) {
    echo "<script>alert('Data hotel tidak ditemukan!'); window.location='../main.php';</script>";
    exit();
}

$nama_hotel = $hotel['nama_hotel'];
$bg_hotel   = (!empty($hotel['foto_utama'])) ? "../img/" . $hotel['foto_utama'] : "https://via.placeholder.com/1200x600";

// 3. DATA KAMAR
$q_rooms = $conn->prepare("SELECT * FROM room_types WHERE id_hotel = ? ORDER BY harga ASC");
$q_rooms->bind_param("i", $id_hotel);
$q_rooms->execute();
$result_rooms = $q_rooms->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($nama_hotel) ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/main.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="../css/rooms.css?v=<?= time(); ?>">
</head>
<body>

<header class="hero-header" style="background-image: url('<?= $bg_hotel ?>');">
    <div class="hero-overlay"></div>
    <a href="../main.php" class="btn-back"><i class="bi bi-arrow-left me-2"></i> Kembali</a>
    <div class="container hero-content">
        <h1 class="display-4 fw-bold text-white"><?= htmlspecialchars($nama_hotel) ?></h1>
        <p class="text-white-50 lead"><?= htmlspecialchars($hotel['alamat'] ?? $hotel['kota']) ?></p>
    </div>
</header>

<div class="container rooms-section">
    <div class="text-center mb-5">
        <h3 class="fw-bold">Pilihan Tipe Kamar</h3>
    </div>

    <div class="rooms-wrapper">
        <?php if ($result_rooms->num_rows > 0): ?>
            <?php while($kamar = $result_rooms->fetch_assoc()): ?>
                <?php
                    $tipe = $kamar['tipe_kamar']; 
                    $harga = number_format($kamar['harga'], 0, ',', '.');
                    $stok = $kamar['stok'];
                    $foto = (!empty($kamar['foto'])) ? "../img/" . $kamar['foto'] : "https://via.placeholder.com/500x300";
                ?>
                <div class="room-card-item">
                    <div class="room-card">
                        <div class="room-img-container">
                            <span class="badge-stok <?= ($stok > 0) ? 'bg-success-custom' : 'bg-danger-custom' ?>">
                                <?= ($stok > 0) ? "Sisa $stok" : "Habis" ?>
                            </span>
                            <img src="<?= $foto ?>" class="room-img" alt="Kamar">
                        </div>
                        <div class="card-body">
                            <span class="room-type-label">HOTEL ROOM</span>
                            <h4 class="room-title"><?= htmlspecialchars($tipe) ?></h4>
                            <div class="price-wrapper mt-3">
                                <span class="price-tag">Rp <?= $harga ?></span>
                                <small class="text-muted">/ malam</small>
                            </div>
                            
                            <div class="mt-4">
                                <?php if($stok <= 0): ?>
                                    <button class="btn-book btn-disabled" disabled>Habis</button>
                                <?php elseif($auth == 'Guest'): ?>
                                    <button onclick="alert('Login dulu!'); window.location='../index.php'" class="btn-book btn-outline-custom">Pesan</button>
                                <?php elseif($auth == 'Pengguna'): ?>
                                    <form action="../user/booking.php" method="POST">
                                        <input type="hidden" name="id_room_type" value="<?= $kamar['id_room_type'] ?>">
                                        <button type="submit" class="btn-book btn-primary-custom">Pilih Kamar</button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn-book btn-disabled" disabled>Admin</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center text-muted">Belum ada kamar tersedia.</div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>