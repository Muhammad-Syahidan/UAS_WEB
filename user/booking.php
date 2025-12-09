<?php
session_start();
include "../assets/koneksi.php";

// 1. CEK LOGIN
if (!isset($_SESSION["iduser"]) || $_SESSION['auth'] != 'Pengguna') {
    echo "<script>alert('Silakan login sebagai pengguna!'); window.location='../index.php';</script>";
    exit();
}

// 2. TANGKAP DATA (WAJIB POST)
// Jika user refresh halaman, data POST hilang. Kita perlu redirect atau handling.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<script>alert('Akses tidak valid. Silakan ulangi pemilihan kamar.'); window.location='../main.php';</script>";
    exit();
}

$id_room  = $_POST['id_room_type'] ?? null;
$checkin  = $_POST['ci'] ?? date('Y-m-d');
$checkout = $_POST['co'] ?? date('Y-m-d', strtotime('+1 day'));

if (!$id_room) {
    header("Location: ../main.php");
    exit();
}

// 3. AMBIL DATA DATABASE
$stmt = $conn->prepare("
    SELECT rt.*, hl.id_hotel, hl.nama_hotel, hl.alamat, hl.kota, hl.foto_utama 
    FROM room_types rt 
    JOIN hotel_list hl ON rt.id_hotel = hl.id_hotel 
    WHERE rt.id_room_type = ?
");
$stmt->bind_param("i", $id_room);
$stmt->execute();
$kamar = $stmt->get_result()->fetch_assoc();

if (!$kamar) { die("Data kamar tidak valid."); }

// 4. HITUNG HARGA
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
    <title>Konfirmasi Pesanan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { background-color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; }
        
        .navbar { background: #0ea5e9; padding: 15px 0; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        
        .booking-card { 
            background: white; border-radius: 20px; padding: 30px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; 
        }
        
        .room-img { 
            width: 100%; height: 180px; object-fit: cover; 
            border-radius: 15px; margin-bottom: 20px; 
        }
        
        /* Tombol Batal agar terlihat seperti tombol biasa tapi mengirim POST */
        .btn-cancel-form { display: inline-block; margin: 0; }
        .btn-cancel {
            background: rgba(255,255,255,0.2); color: white; 
            border: 1px solid rgba(255,255,255,0.5);
            padding: 8px 20px; border-radius: 50px; text-decoration: none;
            font-weight: 600; font-size: 0.9rem; transition: 0.3s;
        }
        .btn-cancel:hover { background: white; color: #0ea5e9; }

        .summary-row { display: flex; justify-content: space-between; margin-bottom: 12px; color: #64748b; }
        .total-pay { font-size: 1.3rem; font-weight: 800; color: #f97316; }
        
        .btn-confirm {
            background-color: #f97316; color: white; width: 100%; padding: 15px;
            border-radius: 12px; font-weight: 700; border: none; transition: 0.3s;
        }
        .btn-confirm:hover { background-color: #ea580c; transform: translateY(-2px); }
    </style>
</head>
<body>

<nav class="navbar mb-5">
    <div class="container d-flex justify-content-between align-items-center">
        <span class="navbar-brand text-white fw-bold fs-4">HOTEL<span style="color:#fff;">ID</span>.</span>
        
        <form action="../hotel/rooms.php" method="POST" class="btn-cancel-form">
            <input type="hidden" name="id_hotel" value="<?= $kamar['id_hotel'] ?>">
            <button type="submit" class="btn-cancel">
                <i class="bi bi-x-lg me-1"></i> Batal
            </button>
        </form>
    </div>
</nav>

<div class="container pb-5">
    <div class="row justify-content-center g-4">
        
        <div class="col-lg-7">
            <div class="booking-card h-100">
                <h4 class="fw-bold mb-4 text-dark">Lengkapi Data Pemesan</h4>
                
                <form action="process_booking.php" method="POST">
                    <input type="hidden" name="id_kamar" value="<?= $id_room ?>">
                    <input type="hidden" name="checkin" value="<?= $checkin ?>">
                    <input type="hidden" name="checkout" value="<?= $checkout ?>">
                    <input type="hidden" name="total" value="<?= $total_bayar ?>">

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control py-2 bg-light" value="<?= htmlspecialchars($_SESSION['user']) ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Nomor WhatsApp (Aktif)</label>
                        <input type="number" name="no_hp" class="form-control py-2" placeholder="Contoh: 08123456789" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-secondary">Metode Pembayaran</label>
                        <select name="metode" class="form-select py-2">
                            <option value="Transfer Bank">Transfer Bank (BCA/Mandiri/BRI)</option>
                            <option value="E-Wallet">E-Wallet (GoPay/OVO/Dana)</option>
                            <option value="Bayar di Hotel">Bayar di Hotel (Cash)</option>
                        </select>
                    </div>

                    <div class="alert alert-primary d-flex gap-3 align-items-center mb-4">
                        <i class="bi bi-shield-check fs-1"></i>
                        <div class="small">Data Anda dilindungi. E-Tiket akan dikirimkan ke WhatsApp setelah pembayaran terkonfirmasi.</div>
                    </div>

                    <button type="submit" class="btn-confirm">
                        Konfirmasi & Bayar <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="booking-card bg-white">
                <h5 class="fw-bold mb-3">Rincian Pesanan</h5>
                
                <?php 
                    $foto = $kamar['foto'];
                    if (empty($foto)) $src = "https://via.placeholder.com/400x200";
                    elseif (strpos($foto, 'http') === 0) $src = $foto;
                    else $src = "../img/" . $foto;
                ?>
                <img src="<?= $src ?>" class="room-img shadow-sm" alt="Kamar">
                
                <h5 class="fw-bold mb-1"><?= $kamar['nama_hotel'] ?></h5>
                <p class="text-muted small mb-3"><i class="bi bi-geo-alt-fill text-danger"></i> <?= $kamar['kota'] ?></p>
                
                <div class="badge bg-primary-subtle text-primary px-3 py-2 mb-4 rounded-pill">
                    <?= $kamar['tipe_kamar'] ?>
                </div>

                <div class="summary-row">
                    <span>Tanggal Check-in</span>
                    <span class="fw-bold text-dark"><?= date('d M Y', strtotime($checkin)) ?></span>
                </div>
                <div class="summary-row">
                    <span>Tanggal Check-out</span>
                    <span class="fw-bold text-dark"><?= date('d M Y', strtotime($checkout)) ?></span>
                </div>
                <div class="summary-row border-bottom pb-3 mb-3">
                    <span>Durasi Menginap</span>
                    <span class="fw-bold text-dark"><?= $durasi ?> Malam</span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center pt-2">
                    <span class="text-muted fw-bold">Total Pembayaran</span>
                    <span class="total-pay">Rp <?= number_format($total_bayar, 0, ',', '.') ?></span>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>