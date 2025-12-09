<?php
session_start();
include "../assets/koneksi.php";

// 1. CEK LOGIN
if (!isset($_SESSION["iduser"]) || $_SESSION['auth'] != 'Pengguna') {
    echo "<script>alert('Silakan login sebagai pengguna!'); window.location='../index.php';</script>";
    exit();
}

// 2. TANGKAP DATA (WAJIB POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../main.php");
    exit();
}

$id_room  = $_POST['id_room_type'] ?? null;
// Validasi Tanggal
$today    = date('Y-m-d');
$checkin  = $_POST['ci'] ?? $today;
$checkout = $_POST['co'] ?? date('Y-m-d', strtotime('+1 day'));

// Cegah tanggal mundur
if ($checkin < $today) $checkin = $today;
if ($checkout <= $checkin) $checkout = date('Y-m-d', strtotime($checkin . ' +1 day'));

if (!$id_room) {
    header("Location: ../main.php");
    exit();
}

// 3. AMBIL DATA KAMAR
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

// 4. HITUNG DURASI
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
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        /* TEMA KONSISTEN MAIN.PHP */
        :root {
            --primary: #0ea5e9; 
            --primary-dark: #0284c7;
            --accent: #f97316;
            --bg-body: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        body {
            background-color: var(--bg-body);
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-main);
        }

        /* Navbar Putih Bersih */
        .navbar { background: white; box-shadow: 0 4px 20px rgba(0,0,0,0.03); padding: 15px 0; }
        .text-accent-orange { color: var(--accent) !important; }

        /* Card Modern */
        .booking-card {
            background: white; border-radius: 20px; padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04); border: 1px solid #f1f5f9;
            height: 100%;
        }

        .form-label { font-weight: 600; font-size: 0.9rem; color: var(--text-muted); margin-bottom: 8px; }
        .form-control, .form-select {
            padding: 12px 15px; border-radius: 12px; border: 1px solid #e2e8f0;
            background-color: #f8fafc; font-weight: 500;
        }
        .form-control:focus, .form-select:focus {
            background-color: white; border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1);
        }

        .room-img {
            width: 100%; height: 200px; object-fit: cover;
            border-radius: 16px; margin-bottom: 20px;
        }

        .summary-row { display: flex; justify-content: space-between; margin-bottom: 12px; color: var(--text-muted); font-size: 0.95rem; }
        .total-row {
            border-top: 2px dashed #e2e8f0; margin-top: 20px; padding-top: 20px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .total-label { font-weight: 700; color: var(--text-main); }
        .total-amount { font-size: 1.5rem; font-weight: 800; color: var(--accent); }

        /* Tombol */
        .btn-confirm {
            background-color: var(--primary); color: white; width: 100%;
            padding: 14px; border-radius: 12px; font-weight: 700; border: none;
            transition: 0.3s; margin-top: 20px;
        }
        .btn-confirm:hover { background-color: var(--primary-dark); transform: translateY(-2px); }

        .btn-cancel {
            background: #f1f5f9; color: var(--text-muted); padding: 8px 20px;
            border-radius: 50px; font-weight: 600; border: none; transition: 0.3s;
        }
        .btn-cancel:hover { background: #e2e8f0; color: var(--text-main); }
    </style>
</head>
<body>

<nav class="navbar sticky-top mb-5">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="navbar-brand fw-bold fs-3 text-dark" href="#">
            HOTEL<span class="text-accent-orange">ID</span>.
        </a>
        
        <form action="../hotel/rooms.php" method="POST" class="m-0">
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
            <div class="booking-card">
                <div class="d-flex align-items-center mb-4">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary me-3">
                        <i class="bi bi-person-lines-fill fs-4"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0">Detail Pemesan</h4>
                        <p class="text-muted small mb-0">Isi data diri Anda dengan benar</p>
                    </div>
                </div>
                
                <form action="process_booking.php" method="POST" id="bookingForm" onsubmit="return validateForm()">
                    <input type="hidden" name="id_kamar" value="<?= $id_room ?>">
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Check-in</label>
                            <input type="date" name="checkin" id="checkin" class="form-control" 
                                   value="<?= $checkin ?>" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Check-out</label>
                            <input type="date" name="checkout" id="checkout" class="form-control" 
                                   value="<?= $checkout ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($_SESSION['user']) ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nomor WhatsApp</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0">+62</span>
                            <input type="number" name="no_hp" class="form-control border-start-0 ps-0" placeholder="8123456789" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Metode Pembayaran</label>
                        <select name="metode" class="form-select">
                            <option value="Transfer Bank">Transfer Bank (BCA/Mandiri/BRI)</option>
                            <option value="E-Wallet">E-Wallet (GoPay/OVO/Dana)</option>
                            <option value="Bayar di Hotel">Bayar di Hotel (Cash)</option>
                        </select>
                    </div>

                    <input type="hidden" name="total" id="inputTotal" value="<?= $total_bayar ?>">

                    <div class="alert alert-light border d-flex gap-3 align-items-center mb-0 rounded-3">
                        <i class="bi bi-shield-check fs-2 text-success"></i>
                        <div class="small text-muted" style="line-height: 1.4;">
                            Keamanan data Anda terjamin. E-Tiket akan dikirimkan otomatis setelah pembayaran berhasil dikonfirmasi.
                        </div>
                    </div>

                    <button type="submit" class="btn-confirm">
                        Konfirmasi Pembayaran <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="booking-card bg-white position-sticky" style="top: 100px;">
                <h5 class="fw-bold mb-4">Ringkasan Pesanan</h5>
                
                <?php 
                    $foto = $kamar['foto'];
                    if (empty($foto)) $src = "https://via.placeholder.com/400x250";
                    elseif (strpos($foto, 'http') === 0) $src = $foto;
                    else $src = "../img/" . $foto;
                ?>
                <div class="position-relative">
                    <img src="<?= $src ?>" class="room-img" alt="Kamar">
                    <div class="position-absolute top-0 start-0 m-3 badge bg-white text-dark shadow-sm">
                        <?= $kamar['tipe_kamar'] ?>
                    </div>
                </div>
                
                <h5 class="fw-bold mb-1"><?= $kamar['nama_hotel'] ?></h5>
                <p class="text-muted small mb-4"><i class="bi bi-geo-alt-fill text-danger me-1"></i> <?= $kamar['kota'] ?></p>
                
                <div class="summary-row">
                    <span>Harga per Malam</span>
                    <span class="fw-bold text-dark">Rp <?= number_format($kamar['harga'], 0, ',', '.') ?></span>
                </div>
                
                <div class="summary-row">
                    <span>Durasi Menginap</span>
                    <span class="fw-bold text-dark"><span id="textDurasi"><?= $durasi ?></span> Malam</span>
                </div>
                
                <div class="total-row">
                    <span class="total-label">Total Bayar</span>
                    <span class="total-amount" id="textTotal">Rp <?= number_format($total_bayar, 0, ',', '.') ?></span>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    const hargaPerMalam = <?= $kamar['harga'] ?>;
    const inputCheckin  = document.getElementById('checkin');
    const inputCheckout = document.getElementById('checkout');
    const textDurasi    = document.getElementById('textDurasi');
    const textTotal     = document.getElementById('textTotal');
    const inputTotal    = document.getElementById('inputTotal');

    function setMinCheckout() {
        const checkinDate = new Date(inputCheckin.value);
        checkinDate.setDate(checkinDate.getDate() + 1);
        const minDate = checkinDate.toISOString().split('T')[0];
        inputCheckout.min = minDate;
        if (inputCheckout.value < minDate) inputCheckout.value = minDate;
        updateHarga();
    }

    function updateHarga() {
        const d1 = new Date(inputCheckin.value);
        const d2 = new Date(inputCheckout.value);
        if (d2 <= d1) { setMinCheckout(); return; }
        const diffTime = Math.abs(d2 - d1);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
        const total = diffDays * hargaPerMalam;
        
        textDurasi.innerText = diffDays;
        textTotal.innerText  = "Rp " + new Intl.NumberFormat('id-ID').format(total);
        inputTotal.value     = total;
    }

    inputCheckin.addEventListener('change', setMinCheckout);
    inputCheckout.addEventListener('change', updateHarga);
    function validateForm() {
        if (inputCheckout.value <= inputCheckin.value) {
            alert("Tanggal Checkout harus setelah Check-in!"); return false;
        }
        return true;
    }
    setMinCheckout();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>