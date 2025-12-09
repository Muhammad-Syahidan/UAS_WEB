<?php
session_start();
include "assets/koneksi.php";

// ==========================================
// 1. CEK LOGIN
// ==========================================
if (!isset($_SESSION["iduser"])) {
    header("Location: login.php");
    exit();
}
$iduser = $_SESSION["iduser"];

$sql = "SELECT * FROM user_list WHERE id_user = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $iduser);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $avatar = htmlspecialchars($row["avatar"] ?? 'default.png'); 
    $user   = htmlspecialchars($row["username"]);
    $auth   = htmlspecialchars($row["auth"]);
} else {
    session_destroy();
    header("Location: login.php");
    exit();
}

// ==========================================
// 2. DATA PROVINSI
// ==========================================
$daftar_provinsi = [
    "Nanggroe Aceh Darussalam", "Sumatera Utara", "Sumatera Selatan", "Sumatera Barat", 
    "Bengkulu", "Riau", "Kepulauan Riau", "Jambi", "Lampung", "Bangka Belitung",
    "Kalimantan Barat", "Kalimantan Timur", "Kalimantan Selatan", "Kalimantan Tengah", "Kalimantan Utara",
    "Banten", "DKI Jakarta", "Jawa Barat", "Jawa Tengah", "DI Yogyakarta", "Jawa Timur",
    "Bali", "Nusa Tenggara Timur", "Nusa Tenggara Barat",
    "Gorontalo", "Sulawesi Barat", "Sulawesi Tengah", "Sulawesi Utara", "Sulawesi Tenggara", "Sulawesi Selatan",
    "Maluku Utara", "Maluku", "Papua Barat", "Papua", "Papua Tengah", "Papua Pegunungan", "Papua Selatan", "Papua Barat Daya"
];
sort($daftar_provinsi); 

// ==========================================
// 3. REKOMENDASI HOTEL
// ==========================================
$rec_query = "SELECT * FROM hotel_list ORDER BY id_hotel DESC LIMIT 30";
$rec_result = $conn->query($rec_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HotelID - <?= $auth ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="css/main.css?v=<?= time(); ?>">

    <style>
        /* CSS KHUSUS TOMBOL FORM */
        .btn-form-action {
            background: none;
            color: inherit;
            border: none;
            padding: 0;
            font: inherit;
            cursor: pointer;
            outline: inherit;
            width: 100%;
        }
        
        .btn-lihat-kamar {
            display: block;
            width: 100%;
            padding: 6px 12px;
            font-size: 0.875rem;
            font-weight: 600;
            text-align: center;
            color: #ffffff;
            background-color: #f97316;
            border: 1px solid white;
            border-radius: 50px;
            transition: all 0.3s;
        }
        .btn-lihat-kamar:hover {
            background-color: #ea580c;
            color: #ffffffff;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="main.php">HOTEL<span class="text-primary-accent">ID</span>.</a>
        
        <button class="navbar-toggler btn-primary-modern p-2" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
            <i class="bi bi-list text-light"></i>
        </button>

        <div class="collapse navbar-collapse" id="navContent">
            
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                <li class="nav-item">
                    <a class="nav-link active" href="main.php"><i class="bi bi-house-door"></i> Beranda</a>
                </li>

                <?php if($auth == 'Administrator'): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-warning" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-shield-lock-fill"></i> Admin Panel
                        </a>
                        <ul class="dropdown-menu">
                            <li><h6 class="dropdown-header text-muted">Manajemen Hotel</h6></li>
                            <li><a class="dropdown-item" href="admin/add_hotelinput.php"><i class="bi bi-plus-lg"></i> Tambah Hotel</a></li>
                            <li><a class="dropdown-item" href="admin/list_hotel.php"><i class="bi bi-pencil-square"></i> Edit Data Hotel</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header text-muted">Manajemen Kamar</h6></li>
                            <li><a class="dropdown-item" href="admin/add_roominput.php"><i class="bi bi-plus-lg"></i> Tambah Kamar</a></li>
                            <li><a class="dropdown-item" href="admin/list_rooms.php"><i class="bi bi-pencil-square"></i> Edit Data Kamar</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header text-muted">Lainnya</h6></li>
                            <li><a class="dropdown-item" href="admin/manage_users.php"><i class="bi bi-people"></i> Kelola User</a></li>
                            <li><a class="dropdown-item" href="admin/reports.php"><i class="bi bi-file-earmark-bar-graph"></i> Laporan</a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if($auth == 'Resepsionis'): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-info" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-pc-display"></i> Front Desk
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="reception/checkin.php"><i class="bi bi-box-arrow-in-right"></i> Check In</a></li>
                            <li><a class="dropdown-item" href="reception/checkout.php"><i class="bi bi-box-arrow-right"></i> Check Out</a></li>
                            <li><a class="dropdown-item" href="reception/guest_list.php"><i class="bi bi-list-check"></i> Daftar Tamu</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reception/chat.php"><i class="bi bi-chat-dots"></i> Chat Tamu</a>
                    </li>
                <?php endif; ?>

                <?php if($auth == 'Pengguna' || $auth == 'User'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="user/my_bookings.php"><i class="bi bi-ticket-perforated"></i> Pesanan Saya</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="user/favorites.php"><i class="bi bi-heart"></i> Favorit</a>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-lg-block">
                    <div class="fw-bold"><?= $user ?></div>
                    <div class="text-primary-accent small" style="font-size: 0.75rem; letter-spacing: 1px;"><?= strtoupper($auth) ?></div>
                </div>
                <div class="dropdown">
                    <a href="#" role="button" data-bs-toggle="dropdown">
                        <img src="img/<?= $avatar ?>" class="avatar-small">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end bg-dark border-secondary shadow mt-2">
                        <li><span class="dropdown-header">Halo, <?= $user ?></span></li>
                        <li><a class="dropdown-item text-light" href="main.php?p=profil"><i class="bi bi-person me-2"></i> Profil Saya</a></li>
                        <li><hr class="dropdown-divider bg-secondary"></li>
                        <li><a class="dropdown-item text-danger" href="assets/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</nav>

<div class="hero-section">
    <div class="container">
        <h1 class="hero-title">Beyond<br>Luxury.</h1>
        <p class="hero-subtitle">
            Cari destinasi menginap Anda. Pilih hotel di lokasi atau provinsi yang Anda inginkan.
        </p>
    </div>
</div>

<div class="container">
    <div class="search-card">
        <form action="pages/hotel_list.php" method="GET">
            <div class="row g-4 align-items-end">
                <div class="col-md-5">
                    <label class="form-label"><i class="bi bi-globe-americas me-2"></i>Cari Berdasarkan Provinsi</label>
                    <select name="provinsi" class="form-select">
                        <option value="" selected>Pilih Semua Provinsi</option>
                        <?php foreach ($daftar_provinsi as $prov): ?>
                            <option value="<?= $prov ?>"><?= $prov ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label"><i class="bi bi-building me-2"></i>Atau Cari Nama Hotel</label>
                    <input type="text" name="keyword" class="form-control" placeholder="Masukkan nama hotel...">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn-primary-modern w-100">
                        Cari <i class="bi bi-search ms-2"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="container mt-5 mb-5 position-relative">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0 text-light border-start border-4 border-primary ps-3">Rekomendasi Pilihan</h4>
        <a href="pages/hotel_list.php" class="text-decoration-none text-muted small hover-primary">Lihat Semua <i class="bi bi-arrow-right"></i></a>
    </div>

    <div class="scroll-container-wrapper">
        
        <button class="scroll-btn btn-prev" id="scrollLeftBtn"><i class="bi bi-chevron-left"></i></button>

        <div class="horizontal-scroll" id="scrollContainer">
            <?php if ($rec_result && $rec_result->num_rows > 0): ?>
                <?php while($hotel = $rec_result->fetch_assoc()): ?>
                
                <?php 
                    $db_foto = $hotel['foto_utama'];
                    if (empty($db_foto)) {
                        $foto = "https://via.placeholder.com/400x250?text=Hotel+Image";
                    } elseif (strpos($db_foto, 'http') === 0) {
                        $foto = $db_foto;
                    } else {
                        $foto = "img/" . $db_foto;
                    }
                ?>

                <div class="horizontal-item">
                    <div class="rec-card position-relative">
                        <img src="<?= $foto ?>" class="rec-img" alt="Hotel">
                        
                        <span class="badge-loc">
                            <i class="bi bi-geo-alt-fill text-warning me-1"></i> <?= $hotel['provinsi'] ?>
                        </span>

                        <div class="p-3 d-flex flex-column h-100">
                            <h6 class="fw-bold text-dark mb-1 text-truncate" title="<?= $hotel['nama_hotel'] ?>"><?= $hotel['nama_hotel'] ?></h6>
                            <p class="text-warning small mb-3 text-truncate">
                                <?= $hotel['kota'] ?>
                            </p>
                            
                            <div class="mt-auto">
                                <form action="hotel/rooms.php" method="POST">
                                    <input type="hidden" name="id_hotel" value="<?= $hotel['id_hotel'] ?>">
                                    <button type="submit" class="btn-lihat-kamar">
                                        Lihat Kamar
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-4 text-muted border border-secondary border-dashed rounded w-100">
                    <i class="bi bi-building-add display-4 opacity-50"></i>
                    <p class="mt-2">Belum ada data hotel. Admin silakan tambahkan hotel.</p>
                </div>
            <?php endif; ?>
        </div>

        <button class="scroll-btn btn-next" id="scrollRightBtn"><i class="bi bi-chevron-right"></i></button>

    </div>
</div>

<footer class="footer text-center">
    <div class="container">
        <small>&copy; 2025 HOTELID Corp. Premium Hotel.</small>
        <div class="mt-2">
            <a href="#" class="text-muted mx-2"><i class="bi bi-instagram"></i></a>
            <a href="#" class="text-muted mx-2"><i class="bi bi-twitter-x"></i></a>
            <a href="#" class="text-muted mx-2"><i class="bi bi-linkedin"></i></a>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>

</body>
</html>