<?php
session_start();
include "../assets/koneksi.php";

// 1. Tentukan Mode Akses
// Jika belum ada sesi, dianggap 'Guest' (Tamu)
$auth = $_SESSION['auth'] ?? 'Guest';

// 2. Cek ID Kamar di URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ../main.php"); // Redirect jika ID kamar tidak ada
    exit();
}
$id_room_type = mysqli_real_escape_string($conn, $_GET['id']);

// 3. Ambil Data Kamar
$q_kamar = $conn->prepare("
    SELECT 
        rt.*, 
        hl.nama_hotel, 
        hl.kota, 
        hl.provinsi 
    FROM room_types rt
    JOIN hotel_list hl ON rt.id_hotel = hl.id_hotel
    WHERE rt.id_room_type = ?
");
$q_kamar->bind_param("i", $id_room_type);
$q_kamar->execute();
$result_kamar = $q_kamar->get_result();
$kamar = $result_kamar->fetch_assoc();

if (!$kamar) {
    echo "Data kamar tidak ditemukan.";
    exit();
}

// Format Harga
$harga_formatted = number_format($kamar['harga'], 0, ',', '.');

// Tentukan sumber foto
$foto_db = $kamar['foto'];
if (strpos($foto_db, 'http') === 0) {
    $src = $foto_db;
} else {
    $src = "../img/" . $foto_db;
}

// List fasilitas (asumsi disimpan dengan koma)
$fasilitas = explode(',', $kamar['fasilitas']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($kamar['nama_kamar']) ?> - Hotel <?= htmlspecialchars($kamar['nama_hotel']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        /* --- TEMA LIGHT MODERN --- */
        :root {
            --primary: #0ea5e9;          /* Biru Muda */
            --accent: #f97316;           /* Oranye */
            --bg-body: #f8fafc;          /* Abu Terang */
            --surface: #ffffff;          /* Putih */
            --text-main: #1e293b;        /* Hitam Abu */
            --text-muted: #64748b;       
            --border-light: #e2e8f0;
        }

        body { background-color: var(--bg-body); color: var(--text-main); font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4 { font-family: 'Outfit', sans-serif; }
        
        .container-fluid-custom { padding: 0; }
        
        .room-image { 
            height: 50vh; 
            background-size: cover; 
            background-position: center; 
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: relative;
        }

        .header-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 30px;
            background: linear-gradient(to top, rgba(0,0,0,0.7), rgba(0,0,0,0));
            color: white;
        }
        
        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(0,0,0,0.4);
            color: white;
            border-radius: 50px;
            padding: 8px 15px;
            text-decoration: none;
            transition: 0.3s;
            z-index: 10;
        }
        .back-button:hover { background: rgba(0,0,0,0.7); color: white; }

        .detail-card {
            background: var(--surface);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
            margin-top: -60px; /* Overlap ke area gambar */
            position: relative;
            z-index: 5;
        }
        
        .feature-icon { color: var(--primary); font-size: 1.5rem; }
        .price-tag { font-size: 2.5rem; font-weight: 800; color: var(--accent); line-height: 1; }
        .btn-primary-modern {
            background: var(--accent); color: white; font-weight: 700; font-size: 1.1rem;
            padding: 15px 30px; border-radius: 15px; border: none; transition: 0.3s;
        }
        .btn-primary-modern:hover { background: #ea580c; transform: translateY(-2px); }
        .text-available { color: #10b981; font-weight: 600; }
        .text-unavailable { color: #ef4444; font-weight: 600; }

        .divider { height: 2px; background-color: var(--border-light); margin: 30px 0; }
    </style>
</head>
<body>

<div class="container-fluid-custom">
    
    <div class="room-image" style="background-image: url('<?= $src ?>');">
        <a href="../main.php" class="back-button">
            <i class="bi bi-arrow-left me-2"></i> Kembali
        </a>
        <div class="header-content">
            <h1 class="fw-bold mb-1 text-shadow-sm"><?= htmlspecialchars($kamar['nama_kamar']) ?></h1>
            <p class="mb-0 fs-5"><i class="bi bi-building me-2"></i><?= htmlspecialchars($kamar['nama_hotel']) ?></p>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="detail-card">
                    
                    <div class="row">
                        <div class="col-lg-8">
                            <h3 class="fw-bold text-main mb-3">Deskripsi Kamar</h3>
                            <p class="text-muted"><?= nl2br(htmlspecialchars($kamar['deskripsi'])) ?></p>
                            
                            <div class="divider"></div>

                            <h4 class="fw-bold mb-3">Fasilitas Utama</h4>
                            <div class="row g-3">
                                <?php 
                                // Mapping ikon ke teks (Anda bisa menyesuaikannya)
                                $icon_map = [
                                    'wifi' => 'bi-wifi',
                                    'ac' => 'bi-wind',
                                    'breakfast' => 'bi-cup-hot',
                                    'tv' => 'bi-tv',
                                    'pool' => 'bi-water',
                                    'king bed' => 'bi-person-badge',
                                    'ocean view' => 'bi-sea',
                                    'balcony' => 'bi-tree',
                                ];
                                ?>
                                <?php foreach($fasilitas as $fas): 
                                    $fas_trimmed = trim(strtolower($fas));
                                    $icon_class = $icon_map[$fas_trimmed] ?? 'bi-check-circle'; // Default icon
                                ?>
                                    <div class="col-md-4 col-6 d-flex align-items-center">
                                        <i class="bi <?= $icon_class ?> feature-icon me-3"></i>
                                        <span class="text-main fw-medium"><?= htmlspecialchars(ucwords($fas)) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="col-lg-4 mt-4 mt-lg-0">
                            <div class="p-4 border rounded-3 bg-light h-100 d-flex flex-column">
                                <p class="mb-1 text-muted small">Harga Mulai Dari:</p>
                                <div class="price-tag mb-3">Rp <?= $harga_formatted ?></div>
                                <p class="mb-3 text-muted small">per malam</p>

                                <p class="fw-bold mb-2">
                                    Ketersediaan: 
                                    <?php if($kamar['stok'] > 0): ?>
                                        <span class="text-available"><?= $kamar['stok'] ?> Kamar Tersedia</span>
                                    <?php else: ?>
                                        <span class="text-unavailable">Habis</span>
                                    <?php endif; ?>
                                </p>

                                <div class="mt-auto pt-3">
                                    <?php if($kamar['stok'] <= 0): ?>
                                        <button class="btn btn-secondary w-100 rounded-pill" disabled>Kamar Habis</button>
                                        
                                    <?php elseif($auth == 'Guest'): ?>
                                        <button class="btn btn-warning w-100 fw-bold rounded-pill" onclick="alert('Anda harus Login atau Daftar akun sebagai Pengguna untuk memesan kamar!'); window.location.href='../index.php'">
                                            <i class="bi bi-lock-fill me-2"></i> Login untuk Memesan
                                        </button>
                                        
                                    <?php elseif($auth == 'Pengguna'): ?>
                                        <a href="../user/booking.php?id=<?= $kamar['id_room_type'] ?>" class="btn btn-primary-modern w-100 text-decoration-none d-block">
                                            <i class="bi bi-bag-fill me-2"></i> Pesan Sekarang
                                        </a>
                                        
                                    <?php else: ?>
                                        <button class="btn btn-outline-secondary w-100" disabled>Akses Terbatas (Admin/Resepsionis)</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>