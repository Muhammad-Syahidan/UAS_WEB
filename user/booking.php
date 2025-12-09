<?php
session_start();
include "../assets/koneksi.php";

// 1. CEK LOGIN USER
if (!isset($_SESSION["iduser"]) || $_SESSION['auth'] != 'Pengguna') {
    echo "<script>alert('Silakan login sebagai pengguna!'); window.location='../index.php';</script>";
    exit();
}

// 2. TANGKAP DATA DARI POST (Bukan GET Lagi)
// Default tanggal jika tidak ada input
$checkin  = $_POST['ci'] ?? date('Y-m-d');
$checkout = $_POST['co'] ?? date('Y-m-d', strtotime('+1 day'));

// Cek apakah ID Room dikirim via POST
if (isset($_POST['id_room_type'])) {
    $id_room = $_POST['id_room_type'];
} else {
    // Jika user akses langsung tanpa lewat tombol pilih kamar
    echo "<script>alert('Akses tidak valid! Silakan pilih kamar terlebih dahulu.'); window.location='../main.php';</script>";
    exit();
}

// 3. AMBIL DATA KAMAR & HOTEL
$stmt = $conn->prepare("
    SELECT rt.*, hl.id_hotel, hl.nama_hotel, hl.alamat, hl.kota, hl.foto_utama 
    FROM room_types rt 
    JOIN hotel_list hl ON rt.id_hotel = hl.id_hotel 
    WHERE rt.id_room_type = ?
");
$stmt->bind_param("i", $id_room);
$stmt->execute();
$kamar = $stmt->get_result()->fetch_assoc();

if (!$kamar) { die("Data kamar tidak ditemukan."); }

// 4. HITUNG DURASI & TOTAL
$tgl1 = new DateTime($checkin);
$tgl2 = new DateTime($checkout);
$durasi = $tgl1->diff($tgl2)->d;
if ($durasi < 1) $durasi = 1;

$total_bayar = $kamar['harga'] * $durasi;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Konfirmasi Pesanan - HotelID</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        body { background-color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; }
        .navbar { background: #0ea5e9; padding: 15px 0; }
        .booking-card { background: white; border-radius: 16px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        .room-preview { width: 100%; height: 180px; object-fit: cover; border-radius: 12px; margin-bottom: 20px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 10px; color: #64748b; font-size: 0.95rem; }
        .total-row { border-top: 2px dashed #e2e8f0; margin-top: 20px; padding-top: 15px; font-weight: 800; font-size: 1.2rem; color: #0ea5e9; display: flex; justify-content: space-between; }
        .btn-pay { background: #f97316; color: white; border: none; padding: 15px; width: 100%; border-radius: 12px; font-weight: 700; transition: 0.3s; }
        .btn-pay:hover { background: #ea580c; color: white; transform: translateY(-2px); }
    </style>
</head>
<body>

<nav class="navbar mb-5">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="navbar-brand text-white fw-bold" href="../main.php">HOTEL<span style="color:#fff">ID</span>.</a>
        
        <form action="../hotel/rooms.php" method="POST">
            <input type="hidden" name="id_hotel" value="<?= $kamar['id_hotel'] ?>">
            <button type="submit" class="btn btn-sm btn-outline-light rounded-pill px-4">Batal</button>
        </form>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-center g-4">
        
        <div class="col-lg-7">
            <div class="booking-card h-100">
                <h4 class="fw-bold mb-4">Data Pemesan</h4>
                
                <form action="process_booking.php" method="POST">
                    <input type="hidden" name="id_kamar" value="<?= $id_room ?>">
                    <input type="hidden" name="checkin" value="<?= $checkin ?>">
                    <input type="hidden" name="checkout" value="<?= $checkout ?>">
                    <input type="hidden" name="total" value="<?= $total_bayar ?>">

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control py-2" value="<?= htmlspecialchars($_SESSION['user']) ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Nomor WhatsApp</label>
                        <input type="number" name="no_hp" class="form-control py-2" placeholder="08xxxxxxxx" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted">Metode Pembayaran</label>
                        <select name="metode" class="form-select py-2">
                            <option value="Transfer">Transfer Bank (BCA/Mandiri)</option>
                            <option value="E-Wallet">E-Wallet (GoPay/OVO)</option>
                            <option value="Cash">Bayar di Hotel (Cash)</option>
                        </select>
                    </div>

                    <div class="alert alert-primary d-flex align-items-center small gap-2">
                        <i class="bi bi-shield-check fs-4"></i>
                        <div>Data Anda aman. Tiket akan diterbitkan setelah pembayaran.</div>
                    </div>

                    <button type="submit" class="btn-pay mt-2">
                        Konfirmasi Pembayaran <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="booking-card bg-light h-100">
                <h5 class="fw-bold mb-3">Rincian Hotel</h5>
                
                <?php 
                    $foto = (!empty($kamar['foto']) && file_exists("../img/".$kamar['foto'])) ? "../img/".$kamar['foto'] : "https://via.placeholder.com/400x250"; 
                    if (strpos($kamar['foto'], 'http') === 0) $foto = $kamar['foto'];
                ?>
                <img src="<?= $foto ?>" class="room-preview shadow-sm">
                
                <h5 class="fw-bold mb-1"><?= $kamar['nama_hotel'] ?></h5>
                <p class="text-muted small mb-3"><?= $kamar['alamat'] ?>, <?= $kamar['kota'] ?></p>
                
                <div class="badge bg-white text-primary border px-3 py-2 mb-4 rounded-pill">
                    <?= $kamar['tipe_kamar'] ?>
                </div>

                <div class="summary-row">
                    <span>Check-in</span>
                    <span class="fw-bold text-dark"><?= date('d M Y', strtotime($checkin)) ?></span>
                </div>
                <div class="summary-row">
                    <span>Check-out</span>
                    <span class="fw-bold text-dark"><?= date('d M Y', strtotime($checkout)) ?></span>
                </div>
                <div class="summary-row">
                    <span>Durasi</span>
                    <span class="fw-bold text-dark"><?= $durasi ?> Malam</span>
                </div>
                
                <div class="total-row">
                    <span>Total Bayar</span>
                    <span>Rp <?= number_format($total_bayar, 0, ',', '.') ?></span>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>