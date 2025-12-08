<?php
session_start();
// Matikan error reporting
error_reporting(0); 
include "../assets/koneksi.php";

// ==========================================
// 1. CEK USER LOGIN
// ==========================================
$iduser = $_SESSION["iduser"] ?? null;
$user   = "Tamu";
$auth   = "Guest";
$avatar = "default.png";

if ($iduser) {
    $u_sql = "SELECT * FROM user_list WHERE id_user = ?";
    $stmt = $conn->prepare($u_sql);
    $stmt->bind_param("i", $iduser);
    $stmt->execute();
    $u_res = $stmt->get_result();
    
    if ($u_res->num_rows > 0) {
        $row = $u_res->fetch_assoc();
        $user = htmlspecialchars($row["username"]);
        $auth = htmlspecialchars($row["auth"]);
        $avatar = htmlspecialchars($row["avatar"] ?? 'default.png');
    }
}

// ==========================================
// 2. QUERY PENCARIAN
// ==========================================
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

$sql = "SELECT * FROM hotel_list";
if (count($where_clauses) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$stmt = $conn->prepare($sql);
if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pencarian - HotelID</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="../css/main.css?v=<?= time(); ?>">

    <style>
        body { background-color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; }
        
        .text-accent-orange { color: #f97316 !important; }

        /* HOTEL CARD */
        .hotel-card {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            background: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .hotel-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(14, 165, 233, 0.1);
        }
        
        .card-img-top { height: 200px; object-fit: cover; }

        .badge-prov {
            background-color: rgba(255, 255, 255, 0.95);
            color: #1e293b;
            font-size: 0.75rem; font-weight: 700;
            padding: 5px 12px; border-radius: 50px;
            position: absolute; top: 15px; left: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .btn-detail {
            background-color: #0ea5e9; color: white;
            border-radius: 10px; font-weight: 600;
            padding: 10px; width: 100%; border: none; transition: 0.3s;
        }
        .btn-detail:hover { background-color: #0284c7; color: white; }

        /* Text Style Sederhana (Tanpa Line Clamp Error) */
        .desc-text {
            font-size: 0.9rem;
            color: #64748b; /* Secondary color */
            min-height: 3em; /* Menjaga tinggi kartu tetap rapi */
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold fs-3" href="../main.php">
            HOTEL<span class="text-accent-orange">ID</span>.
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                <li class="nav-item">
                    <a class="nav-link" href="../main.php"><i class="bi bi-arrow-left"></i> Kembali ke Beranda</a>
                </li>
            </ul>

            <?php if($iduser): ?>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-lg-block">
                    <div class="fw-bold text-dark"><?= $user ?></div>
                    <div class="small text-muted" style="font-size: 0.75rem;"><?= strtoupper($auth) ?></div>
                </div>
                <img src="../img/<?= $avatar ?>" class="avatar-small rounded-circle border" style="width:40px; height:40px; object-fit:cover;">
            </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="bg-primary py-5 mb-5" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white;">
    <div class="container text-center">
        <h2 class="fw-bold mb-2">Hasil Pencarian Hotel</h2>
        <p class="opacity-75">
            <?php 
                if(!empty($_GET['keyword'])) echo "Kata kunci: \"" . htmlspecialchars($_GET['keyword']) . "\" ";
                if(!empty($_GET['provinsi'])) echo "• Provinsi: " . htmlspecialchars($_GET['provinsi']);
                if(empty($_GET['keyword']) && empty($_GET['provinsi'])) echo "Menampilkan semua hotel";
            ?>
        </p>
    </div>
</div>

<div class="container pb-5">
    <div class="row g-4">
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <?php 
                    // 1. Logika Gambar
                    $foto = $row['foto_utama'];
                    if (empty($foto)) {
                        $src = "https://via.placeholder.com/400x250?text=Hotel";
                    } elseif (strpos($foto, 'http') === 0) {
                        $src = $foto;
                    } else {
                        $src = "../img/" . $foto;
                    }

                    // 2. LOGIKA POTONG TEKS (Solusi Anti Error CSS)
                    $deskripsi_asli = $row['deskripsi'] ?? 'Fasilitas lengkap tersedia.';
                    // Potong jika lebih dari 100 karakter
                    if (strlen($deskripsi_asli) > 100) {
                        $deskripsi_tampil = substr($deskripsi_asli, 0, 100) . "...";
                    } else {
                        $deskripsi_tampil = $deskripsi_asli;
                    }
                ?>
                <div class="col-md-6 col-lg-3">
                    <div class="hotel-card position-relative">
                        <span class="badge-prov"><i class="bi bi-geo-alt-fill text-warning"></i> <?= $row['provinsi'] ?></span>
                        <img src="<?= $src ?>" class="card-img-top" alt="Foto Hotel">
                        
                        <div class="p-3 d-flex flex-column flex-grow-1">
                            <h5 class="fw-bold text-dark mb-1 text-truncate" title="<?= htmlspecialchars($row['nama_hotel']) ?>">
                                <?= htmlspecialchars($row['nama_hotel']) ?>
                            </h5>
                            <p class="text-muted small mb-3"><i class="bi bi-building me-1"></i> <?= htmlspecialchars($row['kota']) ?></p>
                            
                            <div class="desc-text mb-4">
                                <?= htmlspecialchars($deskripsi_tampil) ?>
                            </div>

                            <div class="mt-auto">
                                <a href="../hotel/rooms.php?id_hotel=<?= $row['id_hotel'] ?>" class="btn btn-detail">
                                    Lihat Kamar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-search display-1 text-muted opacity-25"></i>
                <h4 class="text-muted mt-3">Tidak ada hotel ditemukan</h4>
                <a href="../main.php" class="btn btn-outline-primary rounded-pill px-4 mt-2">Cari Lagi</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>