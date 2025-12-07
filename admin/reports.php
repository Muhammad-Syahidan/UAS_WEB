<?php
session_start();
include "../assets/koneksi.php";

// 1. Cek Admin
if (!isset($_SESSION["iduser"]) || ($_SESSION['auth'] != 'Administrator')) {
    header("Location: ../main.php"); exit();
}

// 2. Ambil List Provinsi untuk Filter
$provinsi_list = $conn->query("SELECT DISTINCT provinsi FROM hotel_list ORDER BY provinsi ASC");

// 3. Logika Filter Statistik
$where_hotel = "WHERE 1=1";
$where_kamar = "WHERE 1=1"; // Untuk join ke hotel
$judul_filter = "Semua Provinsi";

if (isset($_GET['provinsi']) && !empty($_GET['provinsi'])) {
    $prov = mysqli_real_escape_string($conn, $_GET['provinsi']);
    $where_hotel .= " AND provinsi = '$prov'";
    // Filter kamar berdasarkan hotel di provinsi tersebut
    $where_kamar .= " AND id_hotel IN (SELECT id_hotel FROM hotel_list WHERE provinsi = '$prov')";
    $judul_filter = "Provinsi " . htmlspecialchars($prov);
}

// 4. Hitung Statistik
// Total Hotel
$q_hotel = $conn->query("SELECT COUNT(*) as total FROM hotel_list $where_hotel");
$total_hotel = $q_hotel->fetch_assoc()['total'];

// Total Kamar & Estimasi Pendapatan (Harga * Stok)
// Kita join karena tabel kamar tidak punya kolom provinsi langsung
$q_kamar = $conn->query("
    SELECT 
        SUM(room_types.stok) as total_kamar, 
        SUM(room_types.harga * room_types.stok) as potensi_pendapatan 
    FROM room_types 
    JOIN hotel_list ON room_types.id_hotel = hotel_list.id_hotel
    $where_hotel
");
$data_kamar = $q_kamar->fetch_assoc();
$total_kamar_tersedia = $data_kamar['total_kamar'] ?? 0;
$potensi_pendapatan = $data_kamar['potensi_pendapatan'] ?? 0;

// Simulasi Data Booking (Karena belum ada tabel transaksi real)
// Anggaplah 10% dari total kamar sudah dipesan sebagai contoh data
$total_booking = ceil($total_kamar_tersedia * 0.1); 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan & Statistik - Admin</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        /* --- TEMA MODERN LIGHT --- */
        :root {
            --primary: #0ea5e9;          /* Biru Muda */
            --accent: #f97316;           /* Oranye */
            --bg-body: #f8fafc;          /* Abu Terang */
            --surface: #ffffff;          /* Putih */
            --text-main: #1e293b;        /* Hitam Abu */
            --text-muted: #64748b;       
            --border-light: #e2e8f0;
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex; flex-direction: column;
        }

        h1, h2, h3, h4 { font-family: 'Outfit', sans-serif; }

        /* HEADER GRADIENT */
        .admin-header {
            background: linear-gradient(135deg, #0f172a 0%, #334155 100%);
            padding: 60px 0 100px 0;
            color: white;
            margin-bottom: -60px; /* Overlap */
        }
        .btn-back-header {
            background: rgba(255,255,255,0.1);
            color: white; text-decoration: none; font-weight: 600;
            padding: 8px 20px; border-radius: 50px; border: 1px solid rgba(255,255,255,0.1);
            transition: 0.3s;
        }
        .btn-back-header:hover { background: white; color: var(--primary); }

        /* STAT CARD */
        .stat-card {
            background: var(--surface);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid var(--border-light);
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            transition: 0.3s;
            height: 100%;
        }
        .stat-card:hover { transform: translateY(-5px); border-color: var(--accent); }
        
        .stat-icon { 
            width: 60px; height: 60px; 
            border-radius: 15px; 
            display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem;
            margin-right: 20px;
            flex-shrink: 0;
        }
        .icon-blue { background: #e0f2fe; color: var(--primary); }
        .icon-orange { background: #ffedd5; color: var(--accent); }
        .icon-green { background: #dcfce7; color: #16a34a; }
        .icon-purple { background: #f3e8ff; color: #9333ea; }

        .stat-value { font-size: 2rem; font-weight: 800; line-height: 1; margin-bottom: 5px; color: #0f172a; }
        .stat-label { color: var(--text-muted); font-size: 0.9rem; font-weight: 500; }

        /* FILTER SECTION */
        .filter-box {
            background: var(--surface);
            border-radius: 20px;
            padding: 25px;
            border: 1px solid var(--border-light);
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            z-index: 10; position: relative;
        }

        .form-label { font-weight: 600; color: var(--text-main); }
        .form-select { border-radius: 10px; padding: 10px; border: 1px solid var(--border-light); background: #f8fafc; }
        
        .btn-filter { background: var(--primary); color: white; font-weight: 600; border-radius: 10px; padding: 10px 25px; border: none; }
        .btn-filter:hover { background: #0284c7; }

        .footer { margin-top: auto; padding: 30px 0; text-align: center; color: var(--text-muted); font-size: 0.85rem; }
    </style>
</head>
<body>

<section class="admin-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1">Laporan & Statistik</h2>
                <p class="mb-0 opacity-75">Ringkasan data hotel, kamar, dan transaksi.</p>
            </div>
            <a href="../main.php" class="btn-back-header">
                <i class="bi bi-arrow-left me-2"></i> Dashboard
            </a>
        </div>
    </div>
</section>

<div class="container pb-5">
    
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="filter-box">
                <form method="GET" action="reports.php" class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label class="form-label">Filter Berdasarkan Provinsi</label>
                        <select name="provinsi" class="form-select">
                            <option value="">-- Tampilkan Semua Data --</option>
                            <?php while($p = $provinsi_list->fetch_assoc()): ?>
                                <option value="<?= $p['provinsi'] ?>" <?= (isset($_GET['provinsi']) && $_GET['provinsi'] == $p['provinsi']) ? 'selected' : '' ?>>
                                    <?= $p['provinsi'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn-filter w-100">
                            <i class="bi bi-funnel me-2"></i> Tampilkan Laporan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row justify-content-center mb-4">
        <div class="col-lg-10">
            <h4 class="fw-bold text-dark border-start border-4 border-warning ps-3">
                Statistik: <?= $judul_filter ?>
            </h4>
        </div>
    </div>

    <div class="row justify-content-center g-4 mb-5">
        <div class="col-lg-10">
            <div class="row g-4">
                
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card">
                        <div class="stat-icon icon-blue"><i class="bi bi-buildings"></i></div>
                        <div>
                            <div class="stat-value"><?= number_format($total_hotel) ?></div>
                            <div class="stat-label">Hotel Terdaftar</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="stat-card">
                        <div class="stat-icon icon-orange"><i class="bi bi-door-open"></i></div>
                        <div>
                            <div class="stat-value"><?= number_format($total_kamar_tersedia) ?></div>
                            <div class="stat-label">Kamar Tersedia</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="stat-card">
                        <div class="stat-icon icon-purple"><i class="bi bi-bookmark-check"></i></div>
                        <div>
                            <div class="stat-value"><?= number_format($total_booking) ?></div>
                            <div class="stat-label">Total Pesanan</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="stat-card">
                        <div class="stat-icon icon-green"><i class="bi bi-cash-coin"></i></div>
                        <div>
                            <div class="stat-value" style="font-size: 1.2rem;">Rp <?= number_format($potensi_pendapatan / 1000000, 1) ?>M</div>
                            <div class="stat-label">Potensi Aset</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="stat-card flex-column align-items-center text-center py-5">
                <i class="bi bi-bar-chart-line display-1 text-muted opacity-25 mb-3"></i>
                <h5 class="text-muted">Grafik Pendapatan Bulanan</h5>
                <p class="small text-muted">Data grafik akan muncul setelah ada transaksi real-time.</p>
            </div>
        </div>
    </div>

</div>

<footer class="footer">
    <small>&copy; 2025 HOTELID Corp. Admin System.</small>
</footer>

</body>
</html>