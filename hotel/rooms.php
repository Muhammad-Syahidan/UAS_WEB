<?php
session_start();
error_reporting(0); 
include "../assets/koneksi.php";

// ==========================================
// 1. VALIDASI DATA MASUK (Support POST & GET)
// ==========================================
$id_hotel = null;

if (isset($_POST['id_hotel']) && !empty($_POST['id_hotel'])) {
    $id_hotel = mysqli_real_escape_string($conn, $_POST['id_hotel']);
} elseif (isset($_GET['id_hotel']) && !empty($_GET['id_hotel'])) {
    $id_hotel = mysqli_real_escape_string($conn, $_GET['id_hotel']);
} 

if (!$id_hotel) {
    header("Location: ../main.php");
    exit();
}

$auth = $_SESSION['auth'] ?? 'Guest';

// ==========================================
// 2. AMBIL DATA HOTEL
// ==========================================
$q_hotel = $conn->prepare("SELECT * FROM hotel_list WHERE id_hotel = ?");
$q_hotel->bind_param("i", $id_hotel);
$q_hotel->execute();
$hotel = $q_hotel->get_result()->fetch_assoc();

if (!$hotel) {
    echo "<script>alert('Hotel tidak ditemukan!'); window.location='../main.php';</script>";
    exit();
}

// Variabel Data
$nama_hotel      = $hotel['nama_hotel'];
$kota_hotel      = $hotel['kota'];
$provinsi_hotel  = $hotel['provinsi'];
$deskripsi_hotel = $hotel['deskripsi'] ?? 'Fasilitas lengkap.';
$bg_hotel        = (!empty($hotel['foto_utama'])) ? "../img/" . $hotel['foto_utama'] : "https://via.placeholder.com/1200x600";

// ==========================================
// 3. AMBIL DATA KAMAR
// ==========================================
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
        <span class="badge-location"><i class="bi bi-geo-alt-fill me-1"></i> <?= htmlspecialchars($kota_hotel) ?>, <?= htmlspecialchars($provinsi_hotel) ?></span>
        <h1 class="display-4 fw-bold mb-3 text-white"><?= htmlspecialchars($nama_hotel) ?></h1>
        <p class="fs-5 opacity-90 text-white" style="max-width: 700px;"><?= htmlspecialchars($deskripsi_hotel) ?></p>
    </div>
</header>

<div class="container rooms-section">
    <div class="text-center mb-5">
        <h3 class="fw-bold text-dark">Pilihan Tipe Kamar</h3>
        <p class="text-muted">Pilih kamar yang sesuai dengan kebutuhan Anda</p>
    </div>

    <div class="rooms-wrapper">
        <?php if ($result_rooms->num_rows > 0): ?>
            <?php while($kamar = $result_rooms->fetch_assoc()): ?>
                <?php
                    $tipe  = $kamar['tipe_kamar']; 
                    $harga = number_format($kamar['harga'], 0, ',', '.');
                    $stok  = $kamar['stok'];
                    $foto  = (!empty($kamar['foto'])) ? "../img/" . $kamar['foto'] : "https://via.placeholder.com/500x300";
                    $desc  = $kamar['deskripsi'] ?? 'Fasilitas lengkap tersedia.';
                ?>

                <div class="room-card-item">
                    <div class="room-card">
                        <div class="room-img-container">
                            <?php if($stok > 0): ?>
                                <div class="badge-stok bg-success-custom">Sisa <?= $stok ?></div>
                            <?php else: ?>
                                <div class="badge-stok bg-danger-custom">Habis</div>
                            <?php endif; ?>
                            <img src="<?= $foto ?>" class="room-img" alt="Kamar">
                        </div>

                        <div class="card-body">
                            <span class="room-type-label">HOTEL ROOM</span>
                            <h4 class="room-title"><?= htmlspecialchars($tipe) ?></h4>
                            <p class="room-desc"><?= htmlspecialchars($desc) ?></p>
                            
                            <div class="divider"></div>
                            
                            <div class="price-wrapper">
                                <span class="price-label">Harga per malam</span>
                                <span class="price-tag">Rp <?= $harga ?></span>
                            </div>

                            <?php if($stok <= 0): ?>
                                <button class="btn-book btn-disabled" disabled>Kamar Penuh</button>
                            <?php elseif($auth == 'Guest'): ?>
                                <button onclick="alert('Silakan login untuk memesan!'); window.location='../index.php'" class="btn-book btn-outline-custom">
                                    Pesan Sekarang
                                </button>
                            <?php elseif($auth == 'Pengguna'): ?>
                                <form action="../user/booking.php" method="POST">
                                    <input type="hidden" name="id_room_type" value="<?= $kamar['id_room_type'] ?>">
                                    <button type="submit" class="btn-book btn-primary-custom">
                                        Pilih Kamar Ini
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="btn-book btn-disabled" disabled>Mode Admin</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="alert alert-warning d-inline-block px-5 rounded-pill">Belum ada kamar tersedia.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<footer class="text-center py-4 bg-white border-top mt-5">
    <div class="container text-muted small">&copy; 2025 HOTELID Corp.</div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>