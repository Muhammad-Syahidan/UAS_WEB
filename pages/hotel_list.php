<?php
session_start();
include "../assets/koneksi.php";

// 1. CEK LOGIN
if (!isset($_SESSION["iduser"])) {
    header("Location: ../login.php");
    exit();
}
$user_name = $_SESSION['user'] ?? 'Tamu';
$user_auth = $_SESSION['auth'] ?? 'Pengguna';
$user_avatar = $_SESSION['avatar'] ?? 'default.png';

// 2. AMBIL DATA PROVINSI (Untuk Dropdown Filter)
$list_provinsi = [];
$q_prov = $conn->query("SELECT DISTINCT provinsi FROM hotel_list ORDER BY provinsi ASC");
while ($r = $q_prov->fetch_assoc()) {
    $list_provinsi[] = $r['provinsi'];
}

// 3. LOGIKA PENCARIAN
$where_clauses = [];
$params = [];
$types = "";
$judul_pencarian = "Semua Hotel";

// Filter Provinsi
if (isset($_GET['provinsi']) && !empty($_GET['provinsi']) && $_GET['provinsi'] != 'Semua Provinsi') {
    $where_clauses[] = "provinsi = ?";
    $params[] = $_GET['provinsi'];
    $types .= "s";
    $judul_pencarian = "Hotel di " . htmlspecialchars($_GET['provinsi']);
}

// Filter Keyword Nama Hotel
if (isset($_GET['keyword']) && !empty($_GET['keyword'])) {
    $where_clauses[] = "nama_hotel LIKE ?";
    $params[] = "%" . $_GET['keyword'] . "%";
    $types .= "s";
    // Update judul jika ada keyword
    if (strpos($judul_pencarian, 'Hotel di') === false) {
        $judul_pencarian = "Pencarian: \"" . htmlspecialchars($_GET['keyword']) . "\"";
    }
}

// Gabungkan Query
$where = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";
$hotel_query = "SELECT * FROM hotel_list $where ORDER BY nama_hotel ASC";

$stmt = $conn->prepare($hotel_query);
if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$hotel_result = $stmt->get_result();

// Simpan nilai inputan untuk ditampilkan kembali di form (Sticky Value)
$selected_prov = $_GET['provinsi'] ?? '';
$input_keyword = $_GET['keyword'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cari Hotel - HotelID</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        /* --- COPY STYLE DARI MAIN.CSS (Hybrid Theme) --- */
        :root {
            --primary: #0f172a;          /* Dark Navy */
            --accent: #f97316;           /* Orange */
            --accent-hover: #ea580c;
            --bg-body: #f8fafc;          /* White Grey */
            --surface: #ffffff;          /* White */
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-light: #e2e8f0;
        }

        /* SCROLLBAR */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: var(--bg-body); }
        ::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background-color: var(--accent); }
        @supports (scrollbar-width: thin) { html { scrollbar-width: thin; scrollbar-color: #cbd5e1 var(--bg-body); } }

        body {
            background-color: var(--bg-body);
            /* Gambar Header Pendek */
            background-image: linear-gradient(to bottom, rgba(15, 23, 42, 0.9), var(--bg-body)), url('https://images.unsplash.com/photo-1618773928121-c32242e63f39?q=80&w=2070&auto=format&fit=crop');
            background-repeat: no-repeat;
            background-size: 100% 350px; 
            background-position: top center;
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex; flex-direction: column;
            margin: 0;
        }

        h1, h2, h3, h4, .navbar-brand { font-family: 'Outfit', sans-serif; }

        /* Navbar (Dark) */
        .navbar { background: var(--primary) !important; padding: 15px 0; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .navbar-brand { font-weight: 800; color: #ffffff !important; font-size: 1.5rem; letter-spacing: -1px; }
        .text-primary-accent { color: #38bdf8 !important; }
        .avatar-small { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid var(--accent); }

        /* Page Header */
        .page-header { padding: 40px 0; color: white; }
        .header-title { font-size: 2rem; font-weight: 800; margin-bottom: 5px; text-shadow: 0 2px 10px rgba(0,0,0,0.3); }
        .header-subtitle { opacity: 0.8; font-size: 0.95rem; }

        /* --- FILTER CARD (SIDEBAR) --- */
        .filter-card {
            background: var(--surface);
            border: 1px solid var(--border-light);
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            position: sticky;
            top: 100px; /* Sticky saat scroll */
        }
        .filter-title { font-weight: 700; color: var(--primary); margin-bottom: 20px; border-bottom: 2px solid var(--accent); padding-bottom: 10px; display: inline-block; }

        /* Form Elements */
        .form-label { font-weight: 600; font-size: 0.85rem; color: var(--text-muted); }
        .form-control, .form-select { 
            background: #f8fafc; border: 1px solid var(--border-light); color: var(--text-main); 
            padding: 10px; border-radius: 8px; font-size: 0.9rem;
        }
        .form-control:focus, .form-select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1); }
        
        /* --- HOTEL LIST CARD --- */
        .hotel-list-card {
            background: var(--surface);
            border: 1px solid var(--border-light);
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
            margin-bottom: 20px;
        }
        .hotel-list-card:hover {
            transform: translateY(-3px);
            border-color: var(--accent);
            box-shadow: 0 15px 30px rgba(0,0,0,0.08);
        }
        .hotel-list-img { width: 240px; height: 170px; object-fit: cover; flex-shrink: 0; }
        .hotel-list-body { padding: 20px; flex-grow: 1; }

        /* Teks dalam Card */
        .card-title-hotel { color: var(--primary); font-weight: 700; font-size: 1.2rem; margin-bottom: 5px; }
        .card-text-city { color: var(--text-muted); font-size: 0.9rem; display: flex; align-items: center; }
        
        .badge-prov { 
            background: #e0f2fe; color: #0284c7; 
            font-size: 0.7rem; font-weight: 700; padding: 4px 10px; border-radius: 20px; 
            display: inline-block; margin-bottom: 8px;
        }

        /* Buttons */
        .btn-primary-modern { background: var(--accent); color: white; font-weight: 600; border: none; padding: 10px 20px; border-radius: 50px; width: 100%; transition: 0.3s; }
        .btn-primary-modern:hover { background: var(--accent-hover); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(249, 115, 22, 0.3); }

        .btn-lihat-kamar {
            border: 1px solid var(--accent); color: var(--accent); background: transparent;
            border-radius: 50px; padding: 8px 20px; font-weight: 600; text-decoration: none; transition: 0.3s; display: inline-block;
        }
        .btn-lihat-kamar:hover { background: var(--accent); color: white; }

        /* Reset Button */
        .btn-reset { color: var(--text-muted); text-decoration: none; font-size: 0.85rem; display: block; text-align: center; margin-top: 10px; }
        .btn-reset:hover { color: var(--accent); }

        .empty-state { background: white; border: 2px dashed var(--border-light); border-radius: 16px; padding: 40px; text-align: center; }
        .footer { background-color: var(--primary); margin-top: auto; padding: 30px 0; color: rgba(255,255,255,0.7); }

        /* Responsif */
        @media (max-width: 768px) {
            .hotel-list-card { flex-direction: column; align-items: flex-start; }
            .hotel-list-img { width: 100%; height: 180px; }
            .filter-card { position: relative; top: 0; margin-bottom: 20px; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="../main.php">HOTEL<span class="text-primary-accent">ID</span>.</a>
        
        <div class="ms-auto d-flex align-items-center gap-3">
            <a href="../main.php" class="btn btn-outline-light btn-sm rounded-pill px-3 d-none d-md-inline-block">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
            <div class="d-flex align-items-center gap-2 border-start border-secondary ps-3 ms-3">
                 <div class="text-end d-none d-lg-block lh-1">
                    <div class="fw-bold fs-6 text-white"><?= $user_name ?></div>
                    <small class="text-white opacity-50" style="font-size: 0.7rem;"><?= strtoupper($user_auth) ?></small>
                </div>
                <img src="../img/<?= $user_avatar ?>" class="avatar-small">
            </div>
        </div>
    </div>
</nav>

<div class="page-header">
    <div class="container">
        <h2 class="header-title"><?= $judul_pencarian ?></h2>
        <p class="header-subtitle">Ditemukan <?= $hotel_result->num_rows ?> properti yang sesuai.</p>
    </div>
</div>

<div class="container pb-5">
    <div class="row">
        
        <div class="col-lg-3 mb-4">
            <div class="filter-card">
                <h5 class="filter-title"><i class="bi bi-funnel"></i> Filter Pencarian</h5>
                
                <form action="hotel_list.php" method="GET">
                    <div class="mb-3">
                        <label class="form-label">Nama Hotel</label>
                        <input type="text" name="keyword" class="form-control" placeholder="Ketik nama..." value="<?= htmlspecialchars($input_keyword) ?>">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Provinsi</label>
                        <select name="provinsi" class="form-select">
                            <option value="Semua Provinsi">Semua Provinsi</option>
                            <?php foreach ($list_provinsi as $prov): ?>
                                <option value="<?= $prov ?>" <?= ($selected_prov == $prov) ? 'selected' : '' ?>>
                                    <?= $prov ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn-primary-modern">
                        Terapkan Filter <i class="bi bi-check-lg"></i>
                    </button>

                    <?php if(!empty($input_keyword) || !empty($selected_prov)): ?>
                        <a href="hotel_list.php" class="btn-reset"><i class="bi bi-x-circle"></i> Reset Filter</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="col-lg-9">
            <?php if ($hotel_result->num_rows > 0): ?>
                <?php while($hotel = $hotel_result->fetch_assoc()): ?>
                    
                    <?php 
                    $db_foto = $hotel['foto_utama'];
                    if (empty($db_foto)) {
                        $foto = "https://via.placeholder.com/300x200?text=No+Image";
                    } elseif (strpos($db_foto, 'http') === 0) {
                        $foto = $db_foto; 
                    } else {
                        $foto = "../img/" . $db_foto; 
                    }
                    ?>

                    <div class="hotel-list-card">
                        <img src="<?= $foto ?>" class="hotel-list-img" alt="<?= $hotel['nama_hotel'] ?>">
                        
                        <div class="hotel-list-body">
                            <div class="d-md-flex justify-content-between align-items-start">
                                <div>
                                    <span class="badge-prov">
                                        <i class="bi bi-geo-alt-fill me-1"></i> <?= $hotel['provinsi'] ?>
                                    </span>
                                    
                                    <h4 class="card-title-hotel mt-1"><?= $hotel['nama_hotel'] ?></h4>
                                    
                                    <p class="card-text-city mb-2">
                                        <i class="bi bi-pin-map me-2 text-danger"></i> <?= $hotel['kota'] ?>
                                    </p>
                                    
                                    <small class="text-muted d-block mb-3">
                                        <i class="bi bi-info-circle me-1"></i> <?= substr($hotel['alamat'], 0, 80) ?>...
                                    </small>
                                </div>
                                
                                <div class="text-md-end mt-3 mt-md-0 align-self-center">
                                    <a href="../hotel/rooms.php?id_hotel=<?= $hotel['id_hotel'] ?>" class="btn-lihat-kamar text-nowrap">
                                        Lihat Kamar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-search display-3 text-muted opacity-50"></i>
                    <h3 class="fw-bold mt-3" style="color: var(--primary);">Hotel Tidak Ditemukan</h3>
                    <p class="text-muted">Coba ubah kata kunci atau pilih provinsi lain.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<footer class="footer text-center">
    <div class="container">
        <small>&copy; 2025 HOTELID Corp. Premium Hotel.</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>