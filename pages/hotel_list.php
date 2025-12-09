<?php
session_start();
error_reporting(0); 
include "../assets/koneksi.php";

// 1. DATA USER
$iduser = $_SESSION["iduser"] ?? null;
$user   = "Tamu";
$auth   = "Guest";
$avatar = "default.png";

if ($iduser) {
    $sql = "SELECT * FROM user_list WHERE id_user = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $iduser);
    $stmt->execute();
    $result_user = $stmt->get_result();

    if ($result_user->num_rows > 0) {
        $row_u  = $result_user->fetch_assoc();
        $avatar = htmlspecialchars($row_u["avatar"] ?? 'default.png'); 
        $user   = htmlspecialchars($row_u["username"]);
        $auth   = htmlspecialchars($row_u["auth"]);
    }
}

// 2. DATA PROVINSI
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

// 3. LOGIKA PENCARIAN
$where_clauses = [];
$params = [];
$types = "";

if (!empty($_GET['provinsi']) && $_GET['provinsi'] != "Pilih Semua Provinsi") {
    $where_clauses[] = "provinsi = ?";
    $params[] = $_GET['provinsi'];
    $types .= "s";
}

if (!empty($_GET['keyword'])) {
    $where_clauses[] = "nama_hotel LIKE ?";
    $params[] = "%" . $_GET['keyword'] . "%";
    $types .= "s";
}

$sql_hotel = "SELECT * FROM hotel_list";
if (count($where_clauses) > 0) {
    $sql_hotel .= " WHERE " . implode(" AND ", $where_clauses);
}

$stmt_hotel = $conn->prepare($sql_hotel);
if (count($params) > 0) {
    $stmt_hotel->bind_param($types, ...$params);
}
$stmt_hotel->execute();
$result_hotel = $stmt_hotel->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Hotel - HotelID</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/main.css?v=<?= time(); ?>">

    <style>
        body { background-color: #f8fafc; }
        
        .header-bg {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            padding-bottom: 80px;
            color: white;
            padding-top: 40px;
        }

        .search-container {
            margin-top: -50px;
            position: relative;
            z-index: 10;
        }

        .hotel-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid #f1f5f9;
            transition: 0.3s;
            height: 100%;
            display: flex; flex-direction: column;
        }
        .hotel-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        .card-img { height: 200px; object-fit: cover; width: 100%; }
        .text-accent-orange { color: #f97316 !important; }

        /* BUTTON STYLE (Agar Form Button terlihat seperti Link Button sebelumnya) */
        .btn-detail {
            background-color: #0ea5e9;
            color: white;
            border-radius: 10px;
            font-weight: 600;
            padding: 10px;
            width: 100%;
            border: none;
            cursor: pointer;
            transition: 0.3s;
            display: block;
            text-align: center;
        }
        .btn-detail:hover { background-color: #0284c7; color: white; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="../main.php">HOTEL<span class="text-accent-orange">ID</span>.</a>
        
        <button class="navbar-toggler btn-primary-modern p-2" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
            <i class="bi bi-list text-dark"></i>
        </button>

        <div class="collapse navbar-collapse" id="navContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                <li class="nav-item">
                    <a class="nav-link" href="../main.php"><i class="bi bi-house-door"></i> Beranda</a>
                </li>
            </ul>

            <?php if($iduser): ?>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-lg-block">
                    <div class="fw-bold text-dark"><?= $user ?></div>
                    <div class="text-primary small" style="font-size: 0.75rem; letter-spacing: 1px;"><?= strtoupper($auth) ?></div>
                </div>
                <div class="dropdown">
                    <a href="#" role="button" data-bs-toggle="dropdown">
                        <img src="../img/<?= $avatar ?>" class="avatar-small">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end bg-dark border-secondary shadow mt-2">
                        <li><span class="dropdown-header">Halo, <?= $user ?></span></li>
                        <li><a class="dropdown-item text-light" href="../main.php?p=profil"><i class="bi bi-person me-2"></i> Profil Saya</a></li>
                        <li><hr class="dropdown-divider bg-secondary"></li>
                        <li><a class="dropdown-item text-danger" href="../assets/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="header-bg">
    <div class="container text-center">
        <h2 class="fw-bold">Temukan Penginapan Impian</h2>
        <p class="opacity-75">Hasil pencarian untuk preferensi Anda</p>
    </div>
</div>

<div class="container search-container">
    <div class="card border-0 shadow-sm p-4 rounded-4">
        <form action="hotel_list.php" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-bold small text-muted">Filter Provinsi</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-geo-alt"></i></span>
                        <select name="provinsi" class="form-select border-start-0 ps-0 bg-light">
                            <option value="" selected>Semua Provinsi</option>
                            <?php foreach ($daftar_provinsi as $prov): ?>
                                <option value="<?= $prov ?>" <?= (isset($_GET['provinsi']) && $_GET['provinsi'] == $prov) ? 'selected' : '' ?>>
                                    <?= $prov ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-bold small text-muted">Cari Nama Hotel</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="keyword" class="form-control border-start-0 ps-0 bg-light" 
                               placeholder="Contoh: Aston, Mercure..." 
                               value="<?= isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '' ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2 rounded-3" style="background-color: #0ea5e9; border:none;">
                        Cari
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4">
        <?php if ($result_hotel && $result_hotel->num_rows > 0): ?>
            <?php while($row = $result_hotel->fetch_assoc()): ?>
                
                <?php 
                    $foto = $row['foto_utama'];
                    if (empty($foto)) {
                        $src = "https://via.placeholder.com/400x250?text=No+Image";
                    } elseif (strpos($foto, 'http') === 0) {
                        $src = $foto;
                    } else {
                        $src = "../img/" . $foto;
                    }
                ?>

                <div class="col-md-6 col-lg-3">
                    <div class="hotel-card">
                        <div class="position-relative">
                            <img src="<?= $src ?>" class="card-img" alt="Foto Hotel">
                            <span class="position-absolute top-0 start-0 m-3 badge bg-light text-dark shadow-sm">
                                <i class="bi bi-geo-alt-fill text-warning me-1"></i> <?= $row['provinsi'] ?>
                            </span>
                        </div>
                        
                        <div class="p-3 d-flex flex-column flex-grow-1">
                            <h6 class="fw-bold mb-1 text-truncate" title="<?= htmlspecialchars($row['nama_hotel']) ?>">
                                <?= htmlspecialchars($row['nama_hotel']) ?>
                            </h6>
                            <p class="text-muted small mb-3">
                                <i class="bi bi-building me-1"></i> <?= htmlspecialchars($row['kota']) ?>
                            </p>
                            
                            <p class="small text-secondary mb-4" style="line-height: 1.5; height: 3em; overflow: hidden;">
                                <?php
                                    $desc = $row['deskripsi'] ?? 'Fasilitas lengkap.';
                                    echo htmlspecialchars(strlen($desc) > 80 ? substr($desc, 0, 80) . "..." : $desc);
                                ?>
                            </p>

                            <div class="mt-auto">
                                <form action="../hotel/rooms.php" method="POST">
                                    <input type="hidden" name="id_hotel" value="<?= $row['id_hotel'] ?>">
                                    <button type="submit" class="btn-detail">Lihat Kamar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="py-5">
                    <i class="bi bi-emoji-frown display-1 text-muted opacity-25"></i>
                    <h4 class="mt-3 text-muted">Tidak ada hotel ditemukan</h4>
                    <p class="text-secondary">Coba ubah kata kunci atau filter provinsi Anda.</p>
                    <a href="hotel_list.php" class="btn btn-secondary rounded-pill px-4">Reset Pencarian</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>