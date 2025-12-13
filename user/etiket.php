<?php
session_start();
include "../assets/koneksi.php";

// 1. Cek Login
if (!isset($_SESSION["iduser"])) {
    echo "<script>alert('Akses Ditolak'); window.location='../index.php';</script>";
    exit();
}

// 2. Ambil ID Booking
if (!isset($_GET['id'])) {
    header("Location: my_bookings.php");
    exit();
}

$id_booking = mysqli_real_escape_string($conn, $_GET['id']);
$id_user    = $_SESSION['iduser'];

// 3. Ambil Data Lengkap (Booking + Room + Hotel)
// Kita pastikan id_user cocok agar orang lain tidak bisa intip tiket sembarangan
$query = "
    SELECT b.*, rt.tipe_kamar, hl.nama_hotel, hl.alamat, hl.kota, hl.foto_utama
    FROM bookings b
    JOIN room_types rt ON b.id_room_type = rt.id_room_type
    JOIN hotel_list hl ON rt.id_hotel = hl.id_hotel
    WHERE b.id_booking = '$id_booking' AND b.id_user = '$id_user'
";

$result = $conn->query($query);
$data   = $result->fetch_assoc();

if (!$data) {
    echo "<h3>Data tiket tidak ditemukan atau Anda tidak memiliki akses.</h3>";
    exit();
}

// Hitung Durasi
$tgl1 = new DateTime($data['tgl_checkin']);
$tgl2 = new DateTime($data['tgl_checkout']);
$durasi = $tgl1->diff($tgl2)->d;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>E-Tiket #<?= $data['id_booking'] ?> - HotelID</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Inter', sans-serif;
            display: flex; justify-content: center; align-items: center;
            min-height: 100vh; padding: 20px;
        }

        .ticket-container {
            background: white;
            width: 100%; max-width: 700px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            position: relative;
        }

        /* Bagian Atas (Header) */
        .ticket-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: white; padding: 30px;
            position: relative;
            border-bottom: 2px dashed rgba(255,255,255,0.2);
        }
        
        /* Lubang Tiket (Visual Effect) */
        .ticket-container::before, .ticket-container::after {
            content: ''; position: absolute;
            width: 30px; height: 30px; background: #f1f5f9;
            border-radius: 50%; top: 110px; z-index: 10;
        }
        .ticket-container::before { left: -15px; }
        .ticket-container::after { right: -15px; }

        .brand-logo { font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 1.5rem; letter-spacing: 1px; }
        .booking-id { font-family: 'Courier Prime', monospace; font-size: 1.2rem; letter-spacing: 2px; }

        /* Bagian Isi */
        .ticket-body { padding: 40px 30px 30px; }
        
        .label-small { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: #64748b; font-weight: 600; margin-bottom: 5px; }
        .data-large { font-size: 1.1rem; font-weight: 700; color: #1e293b; }
        .data-highlight { color: #f97316; }

        .qr-placeholder {
            width: 100px; height: 100px; background: #e2e8f0;
            display: flex; align-items: center; justify-content: center;
            font-size: 3rem; color: #94a3b8; border-radius: 10px;
        }

        /* Tombol Print (Hilang saat diprint) */
        .action-bar { text-align: center; margin-top: 20px; }
        
        @media print {
            body { background: white; padding: 0; }
            .ticket-container { box-shadow: none; border: 1px solid #ddd; }
            .action-bar, .btn-back { display: none !important; }
            .ticket-container::before, .ticket-container::after { background: white; }
        }
    </style>
</head>
<body>

    <div class="main-wrapper w-100" style="max-width: 700px;">
        <div class="d-flex justify-content-between mb-3 action-bar">
            <a href="my_bookings.php" class="btn btn-outline-secondary rounded-pill px-4 btn-back">
                <i class="bi bi-arrow-left me-2"></i> Kembali
            </a>
            <button onclick="window.print()" class="btn btn-primary rounded-pill px-4 fw-bold" style="background: #f97316; border:none;">
                <i class="bi bi-printer me-2"></i> Cetak Tiket
            </button>
        </div>

        <div class="ticket-container">
            <div class="ticket-header d-flex justify-content-between align-items-center">
                <div>
                    <div class="brand-logo">HOTEL<span style="color:#f97316">ID</span>.</div>
                    <div class="small opacity-75">Booking Confirmation</div>
                </div>
                <div class="text-end">
                    <div class="small opacity-50">KODE PESANAN</div>
                    <div class="booking-id">#<?= $data['id_booking'] ?></div>
                </div>
            </div>

            <div class="ticket-body">
                <div class="row mb-4">
                    <div class="col-8">
                        <div class="label-small">Hotel / Properti</div>
                        <h4 class="fw-bold mb-1 text-dark"><?= $data['nama_hotel'] ?></h4>
                        <div class="text-muted small"><i class="bi bi-geo-alt-fill me-1 text-danger"></i> <?= $data['alamat'] ?>, <?= $data['kota'] ?></div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="label-small">Status</div>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 rounded-pill">
                            LUNAS
                        </span>
                    </div>
                </div>

                <div class="row g-4 mb-4 border-top border-bottom py-4" style="border-color: #f1f5f9 !important;">
                    <div class="col-4">
                        <div class="label-small">Check-In</div>
                        <div class="data-large"><?= date('d M Y', strtotime($data['tgl_checkin'])) ?></div>
                        <div class="small text-muted">14:00 WIB</div>
                    </div>
                    <div class="col-4 text-center">
                        <div class="label-small">Durasi</div>
                        <div class="badge bg-light text-dark border rounded-pill px-3 mt-1">
                            <?= $durasi ?> Malam
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="label-small">Check-Out</div>
                        <div class="data-large"><?= date('d M Y', strtotime($data['tgl_checkout'])) ?></div>
                        <div class="small text-muted">12:00 WIB</div>
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-8">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="label-small">Tamu Menginap</div>
                                <div class="fw-bold text-dark"><?= $data['nama_pemesan'] ?></div>
                                <div class="small text-muted"><?= $data['no_hp'] ?></div>
                            </div>
                            <div class="col-12">
                                <div class="label-small">Tipe Kamar</div>
                                <div class="fw-bold text-dark"><?= $data['tipe_kamar'] ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <div class="d-flex justify-content-end">
                            <div class="qr-placeholder border">
                                <i class="bi bi-qr-code"></i>
                            </div>
                        </div>
                        <div class="small text-muted mt-2" style="font-size: 0.65rem;">Scan saat check-in</div>
                    </div>
                </div>
            </div>
            
            <div class="bg-light p-3 text-center border-top">
                <small class="text-muted" style="font-size: 0.7rem;">
                    Harap tunjukkan e-tiket ini kepada resepsionis saat kedatangan.<br>
                    &copy; 2025 HotelID System.
                </small>
            </div>
        </div>
    </div>

</body>
</html>