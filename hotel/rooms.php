<?php
session_start();
include "../assets/koneksi.php";

// 1. Cek Parameter ID Hotel
// Menggunakan 'id_hotel' sesuai yang dikirim dari main.php
if (!isset($_GET['id_hotel']) || empty($_GET['id_hotel'])) {
    header("Location: ../main.php");
    exit();
}

$id_hotel = mysqli_real_escape_string($conn, $_GET['id_hotel']);
$auth = $_SESSION['auth'] ?? 'Guest';

// 2. Ambil Data Informasi Hotel
$q_hotel = $conn->prepare("SELECT * FROM hotel_list WHERE id_hotel = ?");
$q_hotel->bind_param("i", $id_hotel);
$q_hotel->execute();
$res_hotel = $q_hotel->get_result();
$hotel = $res_hotel->fetch_assoc();

if (!$hotel) {
    echo "<script>alert('Hotel tidak ditemukan!'); window.location='../main.php';</script>";
    exit();
}

// Persiapan Foto Hotel untuk Header
$foto_hotel_db = $hotel['foto_utama'];
if (empty($foto_hotel_db)) {
    $bg_hotel = "https://via.placeholder.com/1200x600?text=Hotel+Image";
} elseif (strpos($foto_hotel_db, 'http') === 0) {
    $bg_hotel = $foto_hotel_db;
} else {
    // Karena file ini ada di folder 'hotel/', kita harus mundur satu folder (../) untuk ke img/
    $bg_hotel = "../img/" . $foto_hotel_db;
}

// 3. Ambil Daftar Kamar (Room Types) berdasarkan ID Hotel
$q_rooms = $conn->prepare("SELECT * FROM room_types WHERE id_hotel = ?");
$q_rooms->bind_param("i", $id_hotel);
$q_rooms->execute();
$result_rooms = $q_rooms->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kamar di <?= htmlspecialchars($hotel['nama_hotel']) ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --primary: #0ea5e9;
            --accent: #f97316;
            --bg-body: #f8fafc;
            --text-main: #1e293b;
        }
        body { background-color: var(--bg-body); font-family: 'Inter', sans-serif; color: var(--text-main); }
        h1, h2, h3, h4, h5 { font-family: 'Outfit', sans-serif; }

        /* Header Hotel Image */
        .hotel-header {
            position: relative;
            height: 60vh;
            background-size: cover;
            background-position: center;
            border-bottom-left-radius: 30px;
            border-bottom-right-radius: 30px;
            overflow: hidden;
            margin-bottom: 40px;
        }
        .hotel-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), rgba(0,0,0,0.2));
            display: flex;
            align-items: flex-end;
            padding: 40px;
        }
        .back-btn {
            position: absolute;
            top: 20px; left: 20px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(5px);
            color: white;
            padding: 8px 20px;
            border-radius: 50px;
            text-decoration: none;
            transition: 0.3s;
            z-index: 10;
        }
        .back-btn:hover { background: rgba(255,255,255,0.4); color: white; }

        /* Room Cards */
        .room-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            background: white;
            transition: transform 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .room-card:hover { transform: translateY(-5px); }
        
        .room-img-top {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        
        .card-body { padding: 20px; display: flex; flex-direction: column; flex-grow: 1; }
        
        .price-text {
            color: var(--accent);
            font-weight: 800;
            font-size: 1.25rem;
        }
        
        .facility-item { font-size: 0.9rem; color: #64748b; margin-right: 10px; margin-bottom: 5px; display: inline-block; }
        
        .btn-book {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 10px;
            font-weight: 600;
            width: 100%;
            margin-top: auto; /* Push button to bottom */
            transition: 0.3s;
        }
        .btn-book:hover { background-color: #0284c7; }
        .btn-disabled { background-color: #cbd5e1; color: #94a3b8; cursor: not-allowed; }
    </style>
</head>
<body>

<div class="hotel-header" style="background-image: url('<?= $bg_hotel ?>');">
    <a href="../main.php" class="back-btn"><i class="bi bi-arrow-left"></i> Kembali ke Beranda</a>
    
    <div class="container">
        <div class="hotel-overlay">
            <div class="text-white">
                <span class="badge bg-warning text-dark mb-2"><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($hotel['provinsi']) ?></span>
                <h1 class="display-4 fw-bold mb-0"><?= htmlspecialchars($hotel['nama_hotel']) ?></h1>
                <p class="fs-5 opacity-75"><i class="bi bi-building me-2"></i><?= htmlspecialchars($hotel['kota']) ?></p>
                <div class="mt-3 text-white-50 small" style="max-width: 700px;">
                    <?= htmlspecialchars($hotel['deskripsi_hotel'] ?? 'Nikmati pengalaman menginap terbaik bersama kami.') ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <h3 class="fw-bold mb-4 border-start border-4 border-primary ps-3">Pilihan Kamar Tersedia</h3>
    
    <div class="row g-4">
        <?php if ($result_rooms->num_rows > 0): ?>
            <?php while($kamar = $result_rooms->fetch_assoc()): ?>
                <?php
                    // Logika Foto Kamar
                    $foto_kamar_db = $kamar['foto'];
                    if (empty($foto_kamar_db)) {
                        $src_kamar = "https://via.placeholder.com/400x250?text=Room+Image";
                    } elseif (strpos($foto_kamar_db, 'http') === 0) {
                        $src_kamar = $foto_kamar_db;
                    } else {
                        $src_kamar = "../img/" . $foto_kamar_db;
                    }

                    // Format Rupiah
                    $harga = number_format($kamar['harga'], 0, ',', '.');
                    
                    // Fasilitas Array
                    $fasilitas = explode(',', $kamar['fasilitas']);
                    $stok = $kamar['stok'];
                ?>

                <div class="col-md-6 col-lg-4">
                    <div class="room-card">
                        <img src="<?= $src_kamar ?>" class="room-img-top" alt="<?= htmlspecialchars($kamar['nama_kamar']) ?>">
                        
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title fw-bold mb-0"><?= htmlspecialchars($kamar['nama_kamar']) ?></h5>
                            </div>
                            
                            <p class="price-text mb-2">Rp <?= $harga ?> <span class="text-muted fs-6 fw-normal">/ malam</span></p>

                            <div class="mb-3">
                                <?php if($stok > 0): ?>
                                    <span class="badge bg-success-subtle text-success border border-success"><i class="bi bi-check-circle"></i> Tersedia <?= $stok ?> Unit</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger"><i class="bi bi-x-circle"></i> Habis</span>
                                <?php endif; ?>
                            </div>

                            <p class="card-text text-muted small mb-3">
                                <?= substr(htmlspecialchars($kamar['deskripsi']), 0, 100) ?>...
                            </p>

                            <div class="mb-4">
                                <?php foreach(array_slice($fasilitas, 0, 4) as $fas): ?>
                                    <span class="facility-item bg-light px-2 py-1 rounded"><i class="bi bi-star-fill text-warning me-1" style="font-size: 0.7rem;"></i><?= trim($fas) ?></span>
                                <?php endforeach; ?>
                            </div>

                            <?php if($stok <= 0): ?>
                                <button class="btn btn-disabled w-100 rounded-pill" disabled>Kamar Penuh</button>
                            <?php elseif($auth == 'Guest'): ?>
                                <button onclick="alert('Silakan Login terlebih dahulu untuk memesan kamar.'); window.location.href='../index.php'" class="btn btn-outline-primary w-100 rounded-pill">
                                    <i class="bi bi-lock"></i> Login untuk Pesan
                                </button>
                            <?php elseif($auth == 'Pengguna'): ?>
                                <a href="../user/booking.php?id=<?= $kamar['id_room_type'] ?>" class="btn btn-book rounded-pill text-center text-decoration-none">
                                    Pesan Sekarang <i class="bi bi-arrow-right"></i>
                                </a>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100 rounded-pill" disabled>Mode Admin/Staff</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center py-5">
                    <i class="bi bi-info-circle display-4 mb-3 d-block"></i>
                    <h4>Belum ada kamar tersedia</h4>
                    <p>Mohon maaf, saat ini belum ada data kamar untuk hotel ini.</p>
                    <a href="../main.php" class="btn btn-primary mt-3">Cari Hotel Lain</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>