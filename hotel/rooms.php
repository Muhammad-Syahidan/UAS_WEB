<?php
session_start();
// Matikan notifikasi error PHP
error_reporting(0); 
include "../assets/koneksi.php";

// ==========================================
// 1. VALIDASI ID HOTEL (Support POST & GET)
// ==========================================
$id_hotel = null;

// Cek apakah ada data dari POST (Metode Tersembunyi)
if (isset($_POST['id_hotel']) && !empty($_POST['id_hotel'])) {
    $id_hotel = mysqli_real_escape_string($conn, $_POST['id_hotel']);
} 
// Cek apakah ada data dari GET (Metode Link Biasa - Fallback)
elseif (isset($_GET['id_hotel']) && !empty($_GET['id_hotel'])) {
    $id_hotel = mysqli_real_escape_string($conn, $_GET['id_hotel']);
} 

// Jika ID tidak ditemukan sama sekali, kembalikan ke main
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
$res_hotel = $q_hotel->get_result();
$hotel = $res_hotel->fetch_assoc();

if (!$hotel) {
    echo "<script>alert('Data hotel tidak ditemukan!'); window.location='../main.php';</script>";
    exit();
}

$nama_hotel      = $hotel['nama_hotel'] ?? 'Nama Hotel';
$kota_hotel      = $hotel['kota'] ?? 'Kota';
$provinsi_hotel  = $hotel['provinsi'] ?? 'Indonesia';
$deskripsi_hotel = $hotel['deskripsi'] ?? 'Deskripsi hotel belum tersedia.';
$foto_hotel_db   = $hotel['foto_utama'] ?? '';

// Logika Path Gambar
if (empty($foto_hotel_db)) {
    $bg_hotel = "https://via.placeholder.com/1200x600?text=Hotel+Image";
} elseif (strpos($foto_hotel_db, 'http') === 0) {
    $bg_hotel = $foto_hotel_db;
} else {
    $bg_hotel = "../img/" . $foto_hotel_db;
}

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
    <title><?= htmlspecialchars($nama_hotel) ?> - Pilihan Kamar</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
        <span class="badge-location">
            <i class="bi bi-geo-alt-fill me-2"></i> <?= htmlspecialchars($kota_hotel) ?>, <?= htmlspecialchars($provinsi_hotel) ?>
        </span>
        <h1 class="display-4 fw-bold mb-3"><?= htmlspecialchars($nama_hotel) ?></h1>
        <p class="fs-5 opacity-90" style="max-width: 700px; line-height: 1.6;">
            <?= htmlspecialchars($deskripsi_hotel) ?>
        </p>
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
                    $tipe_kamar = $kamar['tipe_kamar']; 
                    $harga      = number_format($kamar['harga'], 0, ',', '.');
                    $stok       = $kamar['stok'];
                    $deskripsi  = $kamar['deskripsi'] ?? 'Fasilitas lengkap tersedia.';

                    $foto_kamar_db = $kamar['foto'];
                    if (empty($foto_kamar_db)) {
                        $src_kamar = "https://via.placeholder.com/500x300?text=No+Image";
                    } elseif (strpos($foto_kamar_db, 'http') === 0) {
                        $src_kamar = $foto_kamar_db;
                    } else {
                        $src_kamar = "../img/" . $foto_kamar_db;
                    }
                ?>

                <div class="room-card-item">
                    <div class="room-card">
                        <div class="room-img-container">
                            <?php if($stok > 0): ?>
                                <div class="badge-stok bg-success-custom">
                                    <i class="bi bi-check-circle-fill me-1"></i> Sisa <?= $stok ?>
                                </div>
                            <?php else: ?>
                                <div class="badge-stok bg-danger-custom">
                                    <i class="bi bi-x-circle-fill me-1"></i> Habis
                                </div>
                            <?php endif; ?>
                            <img src="<?= $src_kamar ?>" class="room-img" alt="<?= htmlspecialchars($tipe_kamar) ?>">
                        </div>

                        <div class="card-body">
                            <span class="room-type-label">HOTEL ROOM</span>
                            <h4 class="room-title"><?= htmlspecialchars($tipe_kamar) ?></h4>
                            <p class="room-desc"><?= htmlspecialchars($deskripsi) ?></p>
                            <div class="divider"></div>
                            <div class="price-wrapper">
                                <span class="price-label">Harga per malam</span>
                                <span class="price-tag">Rp <?= $harga ?></span>
                            </div>

                            <?php if($stok <= 0): ?>
                                <button class="btn-book btn-disabled" disabled>Kamar Penuh</button>
                            <?php elseif($auth == 'Guest'): ?>
                                <button onclick="if(confirm('Anda harus login untuk memesan. Lanjut login?')){ window.location.href='../index.php'; }" class="btn-book btn-outline-custom">
                                    Pesan Sekarang
                                </button>
                            <?php elseif($auth == 'Pengguna'): ?>
                                <a href="../user/booking.php?id=<?= $kamar['id_room_type'] ?>" class="btn-book btn-primary-custom">
                                    Pilih Kamar Ini
                                </a>
                            <?php else: ?>
                                <button class="btn-book btn-disabled" disabled>Mode Admin</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="alert alert-light border shadow-sm d-inline-block px-5 py-4 rounded-4">
                    <i class="bi bi-search display-4 text-muted mb-3 d-block"></i>
                    <h5 class="text-dark">Belum ada kamar tersedia</h5>
                    <p class="text-muted mb-3">Maaf, saat ini belum ada data kamar untuk hotel ini.</p>
                    <a href="../main.php" class="btn btn-primary rounded-pill px-4">Cari Hotel Lain</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<footer class="text-center py-4 bg-white border-top mt-5">
    <div class="container">
        <small class="text-muted">&copy; 2025 HOTELID Corp. Premium Hotel.</small>
        <div class="mt-3">
            <a href="#" class="text-muted mx-2 fs-5"><i class="bi bi-instagram"></i></a>
            <a href="#" class="text-muted mx-2 fs-5"><i class="bi bi-twitter-x"></i></a>
            <a href="#" class="text-muted mx-2 fs-5"><i class="bi bi-linkedin"></i></a>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>