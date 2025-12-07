<?php
session_start();
include "../assets/koneksi.php";

// 1. Cek Admin
if (!isset($_SESSION["iduser"]) || ($_SESSION['auth'] != 'Administrator')) {
    header("Location: ../main.php");
    exit();
}

// 2. Cek ID Kamar
if (!isset($_GET['id'])) {
    header("Location: list_rooms.php");
    exit();
}
$id_room = $_GET['id'];

// 3. Ambil Data Kamar
$stmt = $conn->prepare("SELECT * FROM room_types WHERE id_room_type = ?");
$stmt->bind_param("i", $id_room);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    echo "Data kamar tidak ditemukan.";
    exit();
}

// 4. Ambil list hotel untuk dropdown
$hotels = $conn->query("SELECT id_hotel, nama_hotel FROM hotel_list ORDER BY nama_hotel ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Kamar - Admin Panel</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        /* --- TEMA HYBRID MODERN --- */
        :root {
            --primary: #0f172a;          /* Dark Navy (Header) */
            --accent: #f97316;           /* Oranye (Tombol/Aksen) */
            --accent-hover: #ea580c;
            --bg-body: #f8fafc;          /* Abu Terang */
            --surface: #ffffff;          /* Putih */
            --text-main: #1e293b;        /* Teks Utama */
            --text-muted: #64748b;       /* Teks Pendukung */
            --border-light: #e2e8f0;
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex; flex-direction: column;
        }

        h1, h3, h4 { font-family: 'Outfit', sans-serif; }

        /* --- ADMIN HEADER --- */
        .admin-header {
            background-color: var(--primary);
            padding: 60px 0 100px 0; /* Padding bawah besar untuk overlap */
            color: white;
            position: relative;
        }
        .page-title { font-weight: 800; font-size: 2.2rem; }
        .page-subtitle { opacity: 0.8; font-size: 1rem; }

        .btn-back-header {
            background: rgba(255,255,255,0.1);
            color: white; text-decoration: none; font-weight: 600;
            display: inline-flex; align-items: center;
            padding: 8px 20px; border-radius: 50px;
            border: 1px solid rgba(255,255,255,0.1);
            transition: 0.3s;
        }
        .btn-back-header:hover { background: white; color: var(--primary); }

        /* --- FORM CARD (Overlap) --- */
        .form-card {
            background: var(--surface);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08);
            margin-top: -80px;
            position: relative;
            border: 1px solid var(--border-light);
            z-index: 10;
        }

        /* Input Styles */
        .form-label { font-weight: 600; color: var(--text-main); margin-bottom: 8px; font-size: 0.9rem; }
        .form-control, .form-select {
            background-color: var(--bg-body);
            border: 1px solid var(--border-light);
            color: var(--text-main);
            padding: 12px 15px;
            border-radius: 12px;
            font-size: 0.95rem;
            transition: 0.3s;
        }
        .form-control:focus, .form-select:focus {
            background-color: var(--surface);
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.1);
        }
        
        .img-preview {
            width: 100%; height: 250px; object-fit: cover;
            border-radius: 15px; border: 2px dashed var(--border-light);
            margin-top: 10px; background: #f1f5f9;
        }

        /* Buttons */
        .btn-primary-modern {
            background: var(--accent); color: white; font-weight: 700; font-size: 1rem;
            padding: 15px 30px; border-radius: 50px; border: none; width: 100%; transition: 0.3s;
            box-shadow: 0 10px 20px -10px rgba(249, 115, 22, 0.5);
        }
        .btn-primary-modern:hover { 
            background: var(--accent-hover); 
            transform: translateY(-3px); 
            box-shadow: 0 15px 30px -10px rgba(249, 115, 22, 0.7);
        }

        .footer { margin-top: auto; padding: 30px 0; text-align: center; color: var(--text-muted); font-size: 0.85rem; }
    </style>
</head>
<body>

<section class="admin-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="page-title mb-1">Edit Kamar</h1>
                <p class="page-subtitle mb-0">Perbarui informasi harga dan stok kamar.</p>
            </div>
            <a href="list_rooms.php" class="btn-back-header">
                <i class="bi bi-arrow-left me-2"></i> Kembali
            </a>
        </div>
    </div>
</section>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="form-card">
                
                <?php 
                if (isset($_GET['pesan'])) {
                    if ($_GET['pesan'] == "sukses") {
                        echo "<div class='alert alert-success rounded-4 border-0 shadow-sm d-flex align-items-center'><i class='bi bi-check-circle-fill fs-4 me-2'></i> Perubahan berhasil disimpan!</div>";
                    } elseif ($_GET['pesan'] == "gagaldb") {
                        echo "<div class='alert alert-danger rounded-4 border-0 shadow-sm'>Gagal menyimpan ke database.</div>";
                    } elseif ($_GET['pesan'] == "gagalupload") {
                        echo "<div class='alert alert-warning rounded-4 border-0 shadow-sm'>Gagal mengupload gambar.</div>";
                    }
                }
                ?>

                <form method="POST" action="edit_roomsave.php" enctype="multipart/form-data">
                    <input type="hidden" name="id_room" value="<?= $data['id_room_type'] ?>">
                    <input type="hidden" name="foto_lama" value="<?= $data['foto'] ?>">

                    <div class="mb-4">
                        <label class="form-label">Pilih Hotel <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-building"></i></span>
                            <select name="id_hotel" class="form-select border-start-0 ps-0" required>
                                <option value="">-- Pilih Hotel --</option>
                                <?php while($h = $hotels->fetch_assoc()): ?>
                                    <option value="<?= $h['id_hotel'] ?>" <?= ($h['id_hotel'] == $data['id_hotel']) ? 'selected' : '' ?>>
                                        <?= $h['nama_hotel'] ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label">Tipe Kamar <span class="text-danger">*</span></label>
                            <input type="text" name="tipe_kamar" class="form-control fw-bold" value="<?= htmlspecialchars($data['tipe_kamar']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label">Harga (Per Malam) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted">Rp</span>
                                <input type="number" name="harga" class="form-control border-start-0 ps-0" value="<?= $data['harga'] ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label">Stok Kamar</label>
                            <input type="number" name="stok" class="form-control" value="<?= $data['stok'] ?>" required>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label">Ganti Foto (Opsional)</label>
                            <input type="file" name="foto" class="form-control">
                        </div>
                    </div>

                    <div class="mb-4 text-center">
                        <label class="form-label small text-muted">Foto Saat Ini:</label>
                        <?php 
                            $src = "../img/" . $data['foto'];
                            if(!file_exists($src)) $src = "https://via.placeholder.com/400x250?text=No+Image";
                        ?>
                        <img src="<?= $src ?>" class="img-preview">
                    </div>

                    <div class="mb-5">
                        <label class="form-label">Fasilitas / Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="4" required><?= htmlspecialchars($data['deskripsi']) ?></textarea>
                    </div>

                    <button type="submit" class="btn-primary-modern">
                        <i class="bi bi-save me-2"></i> Update Data Kamar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<footer class="footer">
    <small>&copy; 2025 HOTELID Corp. Admin System.</small>
</footer>

</body>
</html>