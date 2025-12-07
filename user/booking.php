<?php
session_start();
include "../assets/koneksi.php";

// 1. CEK LOGIN USER
if (!isset($_SESSION["iduser"]) || $_SESSION['auth'] != 'Pengguna') {
    echo "<script>alert('Silakan login sebagai pengguna!'); window.location='../index.php';</script>";
    exit();
}

// 2. AMBIL DATA DARI URL
$id_room = $_GET['id'];
$checkin = $_GET['ci'] ?? date('Y-m-d');
$checkout = $_GET['co'] ?? date('Y-m-d', strtotime('+1 day'));

// 3. AMBIL DATA KAMAR & HOTEL
$stmt = $conn->prepare("
    SELECT rt.*, hl.nama_hotel, hl.alamat, hl.kota 
    FROM room_types rt 
    JOIN hotel_list hl ON rt.id_hotel = hl.id_hotel 
    WHERE rt.id_room_type = ?
");
$stmt->bind_param("i", $id_room);
$stmt->execute();
$kamar = $stmt->get_result()->fetch_assoc();

if (!$kamar) { die("Kamar tidak valid."); }

// 4. HITUNG DURASI & TOTAL HARGA
$tgl1 = new DateTime($checkin);
$tgl2 = new DateTime($checkout);
$jarak = $tgl1->diff($tgl2);
$durasi = $jarak->d; // Jumlah malam
if ($durasi < 1) $durasi = 1; // Minimal 1 malam

$total_bayar = $kamar['harga'] * $durasi;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Pesanan - HotelID</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        /* --- TEMA MODERN LIGHT --- */
        :root {
            --primary: #0ea5e9; --accent: #f97316; --bg-body: #f8fafc; 
            --surface: #ffffff; --text-main: #1e293b; --border-light: #e2e8f0;
        }
        body { background-color: var(--bg-body); font-family: 'Inter', sans-serif; color: var(--text-main); }
        h2, h4, h5 { font-family: 'Outfit', sans-serif; }

        /* Navbar */
        .navbar { background: var(--primary); padding: 15px 0; box-shadow: 0 4px 15px rgba(14, 165, 233, 0.2); }
        .navbar-brand { font-weight: 800; color: #fff !important; }
        .text-accent { color: #fff; }

        /* Card Style */
        .booking-card {
            background: var(--surface); border-radius: 20px; padding: 30px;
            border: 1px solid var(--border-light); box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        .form-label { font-weight: 600; font-size: 0.9rem; color: var(--primary); }
        .form-control { background: #f8fafc; border: 1px solid var(--border-light); padding: 12px; border-radius: 10px; }
        .form-control:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15); background: white; }

        .summary-item { display: flex; justify-content: space-between; margin-bottom: 10px; color: #64748b; }
        .summary-total { display: flex; justify-content: space-between; margin-top: 20px; padding-top: 15px; border-top: 2px dashed var(--border-light); font-weight: 800; font-size: 1.2rem; color: var(--primary); }

        .btn-confirm {
            background: var(--accent); color: white; font-weight: 700; width: 100%;
            padding: 15px; border-radius: 50px; border: none; transition: 0.3s;
        }
        .btn-confirm:hover { background: #ea580c; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(249, 115, 22, 0.3); }

        .room-preview { width: 100%; height: 150px; object-fit: cover; border-radius: 15px; margin-bottom: 15px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="../main.php">HOTEL<span class="text-accent">ID</span>.</a>
        <a href="../hotel/rooms.php?id_hotel=<?= $kamar['id_hotel'] ?>" class="btn btn-sm btn-outline-light rounded-pill px-3">Batal</a>
    </div>
</nav>

<div class="container py-5">
    <div class="row justify-content-center">
        
        <div class="col-lg-7 mb-4">
            <h2 class="fw-bold mb-4">Konfirmasi Pesanan</h2>
            <div class="booking-card">
                <h5 class="mb-4 border-bottom pb-3"><i class="bi bi-person-lines-fill me-2"></i>Data Pemesan</h5>
                
                <form action="process_booking.php" method="POST">
                    <input type="hidden" name="id_kamar" value="<?= $id_room ?>">
                    <input type="hidden" name="checkin" value="<?= $checkin ?>">
                    <input type="hidden" name="checkout" value="<?= $checkout ?>">
                    <input type="hidden" name="total" value="<?= $total_bayar ?>">

                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" value="<?= $_SESSION['user'] ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nomor Handphone / WhatsApp</label>
                        <input type="number" name="no_hp" class="form-control" placeholder="0812xxxx" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Metode Pembayaran</label>
                        <select name="metode" class="form-control">
                            <option>Transfer Bank (BCA/Mandiri)</option>
                            <option>E-Wallet (Dana/OVO/Gopay)</option>
                            <option>Bayar di Hotel (Cash)</option>
                        </select>
                    </div>
                    
                    <div class="alert alert-info d-flex align-items-center small mt-4">
                        <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                        <div>Pastikan data diri sudah benar. Tiket akan dikirimkan setelah konfirmasi.</div>
                    </div>

                    <button type="submit" class="btn-confirm mt-2">
                        Bayar Sekarang <i class="bi bi-arrow-right-circle ms-2"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-5">
            <h4 class="fw-bold mb-4">Rincian</h4>
            <div class="booking-card">
                <?php 
                    $foto = (!empty($kamar['foto']) && file_exists("../img/".$kamar['foto'])) ? "../img/".$kamar['foto'] : "https://via.placeholder.com/400x200"; 
                    if (strpos($kamar['foto'], 'http') === 0) $foto = $kamar['foto'];
                ?>
                <img src="<?= $foto ?>" class="room-preview">
                
                <h5 class="fw-bold"><?= $kamar['nama_hotel'] ?></h5>
                <p class="text-muted small"><i class="bi bi-geo-alt"></i> <?= $kamar['alamat'] ?>, <?= $kamar['kota'] ?></p>
                
                <div class="badge bg-primary-subtle text-primary px-3 py-2 mb-3 rounded-pill"><?= $kamar['tipe_kamar'] ?></div>

                <div class="summary-item">
                    <span>Check-in</span>
                    <span class="fw-bold text-dark"><?= date('d M Y', strtotime($checkin)) ?></span>
                </div>
                <div class="summary-item">
                    <span>Check-out</span>
                    <span class="fw-bold text-dark"><?= date('d M Y', strtotime($checkout)) ?></span>
                </div>
                <div class="summary-item">
                    <span>Durasi</span>
                    <span class="fw-bold text-dark"><?= $durasi ?> Malam</span>
                </div>
                <div class="summary-item">
                    <span>Harga per malam</span>
                    <span>Rp <?= number_format($kamar['harga'],0,',','.') ?></span>
                </div>

                <div class="summary-total">
                    <span>Total Bayar</span>
                    <span>Rp <?= number_format($total_bayar,0,',','.') ?></span>
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>