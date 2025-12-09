<?php
session_start();
// Aktifkan error reporting sementara untuk debugging jika masih blank
// error_reporting(E_ALL); ini_set('display_errors', 1); 
error_reporting(0); // Matikan jika sudah fix

include "../assets/koneksi.php";

// 1. CEK LOGIN
if (!isset($_SESSION["iduser"]) || $_SESSION['auth'] != 'Pengguna') {
    header("Location: ../index.php");
    exit();
}

$iduser = $_SESSION["iduser"];

// 2. AMBIL DATA PESANAN (QUERY FIXED)
// Perbaikan: Menghapus 'rt.nama_kamar' yang menyebabkan crash
$query = "
    SELECT b.*, rt.tipe_kamar, hl.nama_hotel, hl.foto_utama, hl.kota
    FROM bookings b
    JOIN room_types rt ON b.id_room_type = rt.id_room_type
    JOIN hotel_list hl ON rt.id_hotel = hl.id_hotel
    WHERE b.id_user = ?
    ORDER BY b.id_booking DESC
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    // Tampilkan error jika query gagal
    die("Error Database: " . $conn->error);
}

$stmt->bind_param("i", $iduser);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pesanan Saya - HotelID</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="../css/main.css?v=<?= time(); ?>">

    <style>
        body { background-color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; }
        
        /* Navbar Style */
        .text-accent-orange { color: #f97316 !important; }
        .navbar { background: white; box-shadow: 0 4px 20px rgba(0,0,0,0.03); padding: 15px 0; }

        /* Card Pesanan */
        .booking-item {
            background: white; border-radius: 16px; overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #f1f5f9;
            transition: 0.3s; height: 100%; display: flex; flex-direction: column;
        }
        .booking-item:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(14, 165, 233, 0.1); }

        .item-img { height: 180px; width: 100%; object-fit: cover; }
        
        /* Badge Status */
        .badge-status {
            position: absolute; top: 15px; left: 15px;
            padding: 6px 14px; border-radius: 50px; font-size: 0.75rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .status-success { background: #dcfce7; color: #166534; } /* Hijau */
        .status-pending { background: #ffedd5; color: #9a3412; } /* Oranye */
        
        .info-box { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; }

        .date-box {
            background: #f8fafc; border-radius: 12px; padding: 12px;
            border: 1px solid #e2e8f0; margin: 15px 0;
            display: flex; justify-content: space-between; font-size: 0.85rem;
        }
        .date-label { color: #64748b; margin-bottom: 4px; display: block; font-size: 0.75rem; font-weight: 600; }
        .date-val { font-weight: 700; color: #1e293b; }

        .price-total { font-size: 1.1rem; font-weight: 800; color: #0ea5e9; }
        
        .btn-etiket {
            background-color: #0ea5e9; color: white; border: none;
            padding: 8px 20px; border-radius: 50px; font-size: 0.85rem; font-weight: 600;
            transition: 0.3s; text-decoration: none;
        }
        .btn-etiket:hover { background-color: #0284c7; color: white; }
    </style>
</head>
<body>

<nav class="navbar sticky-top mb-5">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="navbar-brand fw-bold fs-3 text-dark" href="../main.php">
            HOTEL<span class="text-accent-orange">ID</span>.
        </a>
        <a href="../main.php" class="btn btn-sm btn-outline-secondary rounded-pill px-4 fw-bold">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</nav>

<div class="container pb-5">
    <div class="d-flex align-items-end mb-4 border-bottom pb-3">
        <div>
            <h3 class="fw-bold mb-1 text-dark">Pesanan Saya</h3>
            <p class="text-muted small mb-0">Daftar riwayat pemesanan hotel Anda</p>
        </div>
    </div>

    <div class="row g-4">
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <?php 
                    // Logika Status Sederhana (Bisa disesuaikan jika ada kolom status di DB)
                    $status = 'Lunas'; 
                    $badge_class = 'status-success';

                    // Logika Gambar
                    $foto = $row['foto_utama'];
                    if (empty($foto)) $src = "https://via.placeholder.com/400x250?text=Hotel";
                    elseif (strpos($foto, 'http') === 0) $src = $foto;
                    else $src = "../img/" . $foto;
                    
                    // Format Tanggal
                    $ci = date('d M Y', strtotime($row['tgl_checkin']));
                    $co = date('d M Y', strtotime($row['tgl_checkout']));
                ?>

                <div class="col-md-6 col-lg-4">
                    <div class="booking-item position-relative">
                        <span class="badge-status <?= $badge_class ?>">
                            <i class="bi bi-check-circle-fill me-1"></i> <?= $status ?>
                        </span>
                        
                        <img src="<?= $src ?>" class="item-img" alt="Hotel">
                        
                        <div class="info-box">
                            <h5 class="fw-bold mb-1 text-truncate"><?= htmlspecialchars($row['nama_hotel']) ?></h5>
                            <p class="text-muted small mb-2"><i class="bi bi-geo-alt-fill me-1 text-danger"></i><?= htmlspecialchars($row['kota']) ?></p>
                            
                            <span class="badge bg-light text-primary border border-primary-subtle w-auto align-self-start mb-2 px-3">
                                <?= htmlspecialchars($row['tipe_kamar']) ?>
                            </span>

                            <div class="date-box">
                                <div>
                                    <span class="date-label">CHECK-IN</span>
                                    <span class="date-val"><?= $ci ?></span>
                                </div>
                                <div class="text-end">
                                    <span class="date-label">CHECK-OUT</span>
                                    <span class="date-val"><?= $co ?></span>
                                </div>
                            </div>

                            <div class="mt-auto d-flex justify-content-between align-items-center pt-3 border-top border-light">
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.7rem; font-weight:600;">TOTAL BAYAR</small>
                                    <div class="price-total">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></div>
                                </div>
                                <a href="#" class="btn-etiket">
                                    <i class="bi bi-ticket-perforated me-1"></i> E-Tiket
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="bg-white p-5 rounded-4 border shadow-sm d-inline-block" style="max-width: 400px;">
                    <i class="bi bi-journal-x display-1 text-muted opacity-25"></i>
                    <h5 class="fw-bold text-dark mt-3">Belum ada pesanan</h5>
                    <p class="text-muted small mb-4">Anda belum melakukan pemesanan hotel apapun. Mulai petualangan Anda sekarang!</p>
                    <a href="../main.php" class="btn btn-primary px-4 py-2 rounded-pill fw-bold w-100" style="background-color: #0ea5e9; border:none;">
                        Cari Hotel
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>