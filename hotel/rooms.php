<?php
session_start();
// Matikan error notice agar tampilan bersih
error_reporting(0); 
include "../assets/koneksi.php";

// ==========================================
// 1. CEK ID HOTEL
// ==========================================
if (!isset($_GET['id_hotel']) || empty($_GET['id_hotel'])) {
    header("Location: ../main.php");
    exit();
}

$id_hotel = mysqli_real_escape_string($conn, $_GET['id_hotel']);
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
    echo "<script>alert('Hotel tidak ditemukan!'); window.location='../main.php';</script>";
    exit();
}

// Data Variabel Aman (Mencegah Error jika kosong)
$nama_hotel     = $hotel['nama_hotel'] ?? 'Nama Hotel';
$kota_hotel     = $hotel['kota'] ?? 'Lokasi';
$provinsi_hotel = $hotel['provinsi'] ?? 'Indonesia';
$foto_hotel_db  = $hotel['foto_utama'] ?? '';

// Logika URL Gambar
if (empty($foto_hotel_db)) {
    $bg_hotel = "https://via.placeholder.com/1200x600?text=Hotel+Image";
} elseif (strpos($foto_hotel_db, 'http') === 0) {
    $bg_hotel = $foto_hotel_db;
} else {
    $bg_hotel = "../img/" . $foto_hotel_db;
}

// ==========================================
// 3. AMBIL DAFTAR KAMAR
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
    <title><?= htmlspecialchars($nama_hotel) ?> - Detail</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        /* =========================================
           KONFIGURASI WARNA (Putih 60%, Biru 30%, Oranye 10%)
           ========================================= */
        :root {
            --c-primary: #0ea5e9;       
            --c-primary-dark: #0284c7;
            --c-accent: #f97316;        
            --c-bg: #f8fafc;            
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--c-bg);
            color: #333;
            overflow-x: hidden;
        }

        /* --- 1. HERO HEADER (Gambar Pudar ke Bawah) --- */
        .hero-header {
            position: relative;
            height: 65vh;           
            min-height: 450px;
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: flex-end;  
        }

        /* OVERLAY GRADIENT (Efek Pudar) */
        .hero-overlay {
            position: absolute;
            inset: 0;
            /* Gradient dari transparan (atas) ke warna background body (bawah) */
            background: linear-gradient(to bottom, 
                rgba(0,0,0,0.3) 0%, 
                rgba(0,0,0,0.5) 60%, 
                var(--c-bg) 100%
            );
        }

        .hero-content {
            position: relative;
            z-index: 2;
            width: 100%;
            padding-bottom: 60px; /* Jarak dari bawah agar tidak tertutup gradient total */
            color: white;         
            text-align: left;     /* RATA KIRI */
        }

        .badge-location {
            background-color: var(--c-accent); 
            color: white;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            margin-bottom: 15px;
            box-shadow: 0 4px 15px rgba(249, 115, 22, 0.4);
        }

        .btn-back {
            position: absolute; top: 30px; left: 30px; z-index: 10;
            background: rgba(255,255,255,0.2); backdrop-filter: blur(5px);
            color: white; padding: 10px 25px; border-radius: 50px; text-decoration: none;
            border: 1px solid rgba(255,255,255,0.4); transition: 0.3s; font-weight: 500;
        }
        .btn-back:hover { background: white; color: var(--c-primary); }


        /* --- 2. ROOM CARDS --- */
        .rooms-container {
            position: relative;
            z-index: 5;
            padding-bottom: 60px;
            margin-top: -20px; /* Sedikit naik ke area gradient */
        }

        .cards-wrapper {
            display: flex;
            justify-content: center; /* KARTU DI TENGAH */
            flex-wrap: wrap;
            gap: 30px;
        }

        .room-card-item {
            flex: 0 0 auto;
            width: 380px;  
            max-width: 100%;
        }

        .room-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
            height: 100%;
            display: flex; flex-direction: column;
            border: 1px solid #edf2f7;
        }

        .room-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(14, 165, 233, 0.15);
        }

        .room-img {
            width: 100%;
            height: 240px; 
            object-fit: cover;
        }

        .card-body {
            padding: 25px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .room-title {
            font-weight: 700;
            font-size: 1.35rem;
            margin-bottom: 10px;
            color: #1e293b;
        }

        .room-desc {
            color: #64748b;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .price-text {
            color: var(--c-accent); 
            font-size: 1.5rem;
            font-weight: 800;
        }

        .btn-action {
            background-color: var(--c-primary); 
            color: white;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            width: 100%;
            margin-top: auto;
            text-decoration: none;
            text-align: center;
            transition: 0.3s;
            display: block;
        }
        .btn-action:hover { background-color: var(--c-primary-dark); color: white; }
        
        .badge-stok {
            position: absolute; top: 15px; right: 15px;
            background: rgba(255,255,255,0.95); color: #333;
            padding: 6px 14px; border-radius: 8px;
            font-weight: 700; font-size: 0.8rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        /* Judul Section Kamar */
        .section-title {
            text-align: center;
            margin-bottom: 40px;
        }
        .section-title h3 { font-weight: 800; color: #1e293b; }
        
    </style>
</head>
<body>

<header class="hero-header" style="background-image: url('<?= $bg_hotel ?>');">
    <div class="hero-overlay"></div>
    
    <a href="../main.php" class="btn-back">
        <i class="bi bi-arrow-left me-2"></i> Kembali
    </a>

    <div class="container hero-content">
        <span class="badge-location">
            <i class="bi bi-geo-alt-fill me-2"></i> <?= htmlspecialchars($kota_hotel) ?>, <?= htmlspecialchars($provinsi_hotel) ?>
        </span>
        
        <h1 class="display-3 fw-bold mb-3"><?= htmlspecialchars($nama_hotel) ?></h1>
        <p class="fs-5 opacity-90" style="max-width: 700px; line-height: 1.6;">
            <?= htmlspecialchars($hotel['deskripsi_hotel'] ?? 'Nikmati kenyamanan dan kemewahan terbaik bersama kami dengan pelayanan bintang lima.') ?>
        </p>
    </div>
</header>

<div class="container rooms-container">
    
    <div class="section-title">
        <h3>Pilihan Tipe Kamar</h3>
        <p class="text-muted">Temukan kamar yang sesuai dengan kebutuhan Anda</p>
    </div>

    <div class="cards-wrapper">
        
        <?php if ($result_rooms->num_rows > 0): ?>
            <?php while($kamar = $result_rooms->fetch_assoc()): ?>
                <?php
                    // Gambar Kamar
                    $foto_kamar_db = $kamar['foto'] ?? '';
                    if (empty($foto_kamar_db)) {
                        $src_kamar = "https://via.placeholder.com/600x400?text=No+Image";
                    } elseif (strpos($foto_kamar_db, 'http') === 0) {
                        $src_kamar = $foto_kamar_db;
                    } else {
                        $src_kamar = "../img/" . $foto_kamar_db;
                    }

                    $harga = number_format($kamar['harga'], 0, ',', '.');
                    $stok = $kamar['stok'];
                ?>

                <div class="room-card-item">
                    <div class="room-card position-relative">
                        
                        <?php if($stok > 0): ?>
                            <div class="badge-stok text-success"><i class="bi bi-check-circle-fill"></i> Sisa <?= $stok ?></div>
                        <?php else: ?>
                            <div class="badge-stok text-danger"><i class="bi bi-x-circle-fill"></i> Habis</div>
                        <?php endif; ?>

                        <img src="<?= $src_kamar ?>" class="room-img" alt="Room Image">
                        
                        <div class="card-body">
                            <h5 class="room-title"><?= htmlspecialchars($kamar['nama_kamar'] ?? 'Tipe Kamar') ?></h5>
                            
                            <p class="room-desc">
                                <?= htmlspecialchars($kamar['deskripsi'] ?? 'Deskripsi tidak tersedia.') ?>
                            </p>

                            <div class="border-top pt-3 mt-3">
                                <div class="d-flex justify-content-between align-items-end mb-3">
                                    <span class="text-muted small">Harga per malam</span>
                                    <span class="price-text">Rp <?= $harga ?></span>
                                </div>

                                <?php if($stok <= 0): ?>
                                    <button class="btn btn-secondary w-100 rounded-3 py-3" disabled>Tidak Tersedia</button>
                                <?php elseif($auth == 'Guest'): ?>
                                    <button onclick="if(confirm('Anda harus login untuk memesan. Lanjut login?')){ window.location.href='../index.php'; }" class="btn btn-outline-primary w-100 py-3 fw-bold rounded-3">
                                        Pesan Sekarang
                                    </button>
                                <?php elseif($auth == 'Pengguna'): ?>
                                    <a href="../user/booking.php?id=<?= $kamar['id_room_type'] ?>" class="btn btn-action rounded-3">
                                        Pilih Kamar Ini
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-secondary w-100 py-3 rounded-3" disabled>Mode Admin</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="bg-white p-5 rounded-4 shadow-sm mx-auto" style="max-width: 500px;">
                    <i class="bi bi-search display-3 text-muted mb-3 d-block"></i>
                    <h4>Belum ada kamar</h4>
                    <p class="text-muted">Maaf, belum ada data kamar yang tersedia untuk hotel ini.</p>
                    <a href="../main.php" class="btn btn-primary mt-3">Cari Hotel Lain</a>
                </div>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<footer class="text-center py-4 bg-white border-top">
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