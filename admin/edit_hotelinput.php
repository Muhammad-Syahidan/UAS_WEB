<?php
session_start();
include "../assets/koneksi.php";

// 1. Cek Admin
if (!isset($_SESSION["iduser"]) || ($_SESSION['auth'] != 'Administrator')) {
    header("Location: ../main.php");
    exit();
}

// 2. Cek ID di URL
if (!isset($_GET['id'])) {
    header("Location: list_hotel.php"); 
    exit();
}
$id_hotel = $_GET['id'];

// 3. Ambil Data Hotel Lama
$stmt = $conn->prepare("SELECT * FROM hotel_list WHERE id_hotel = ?");
$stmt->bind_param("i", $id_hotel);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    echo "Data hotel tidak ditemukan.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Hotel - Admin Panel</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        /* --- TEMA HYBRID KONSISTEN --- */
        :root {
            --primary: #0f172a;          /* Dark Navy (Header) */
            --accent: #f97316;           /* Oranye (Tombol/Aksen) */
            --accent-hover: #ea580c;
            --bg-body: #f8fafc;          /* Background Halaman (Abu Terang) */
            --surface: #ffffff;          /* Background Kartu (Putih) */
            --text-main: #1e293b;        /* Teks Utama (Gelap) */
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

        /* --- ADMIN HEADER BLOCK --- */
        .admin-header {
            background-color: var(--primary);
            padding: 60px 0 100px 0; /* Padding bawah besar untuk efek overlap */
            color: white;
            position: relative;
        }
        .page-title { font-weight: 800; font-size: 2.2rem; }
        .page-subtitle { opacity: 0.8; font-size: 1rem; }

        /* Tombol Kembali */
        .btn-back-header {
            background: rgba(255,255,255,0.1);
            color: white; text-decoration: none; font-weight: 600;
            display: inline-flex; align-items: center;
            padding: 8px 20px; border-radius: 50px;
            border: 1px solid rgba(255,255,255,0.1);
            transition: 0.3s;
        }
        .btn-back-header:hover { background: white; color: var(--primary); }

        /* --- FORM CARD (Putih Overlap) --- */
        .form-card {
            background: var(--surface);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08);
            margin-top: -80px; /* OVERLAP KE ATAS */
            position: relative;
            border: 1px solid var(--border-light);
            z-index: 10;
        }

        /* Styles Form Input */
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
        .form-text { color: var(--text-muted); }
        
        /* Preview Image */
        .img-preview {
            width: 100%; height: 250px; object-fit: cover;
            border-radius: 15px; border: 2px dashed var(--border-light);
            margin-top: 15px; background: #f1f5f9;
        }

        /* Tombol Simpan (Oranye) */
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
                <h1 class="page-title mb-1">Edit Data Hotel</h1>
                <p class="page-subtitle mb-0">Perbarui informasi properti hotel yang ada.</p>
            </div>
            <a href="list_hotel.php" class="btn-back-header">
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
                        echo "<div class='alert alert-success rounded-4 border-0 shadow-sm d-flex align-items-center'><i class='bi bi-check-circle-fill fs-4 me-2'></i> Data hotel berhasil diperbarui!</div>";
                    } elseif ($_GET['pesan'] == "gagaldb") {
                        echo "<div class='alert alert-danger rounded-4 border-0 shadow-sm'>Gagal menyimpan ke database.</div>";
                    } elseif ($_GET['pesan'] == "gagalupload") {
                        echo "<div class='alert alert-warning rounded-4 border-0 shadow-sm'>Gagal mengupload gambar.</div>";
                    }
                }
                ?>

                <form method="POST" action="edit_hotelsave.php" enctype="multipart/form-data">
                    
                    <input type="hidden" name="id_hotel" value="<?= $data['id_hotel'] ?>">
                    <input type="hidden" name="foto_lama" value="<?= $data['foto_utama'] ?>">

                    <div class="mb-4">
                        <label class="form-label">Nama Hotel</label>
                        <input type="text" name="nama_hotel" class="form-control fw-bold" value="<?= htmlspecialchars($data['nama_hotel']) ?>" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label">Provinsi</label>
                            <input type="text" name="provinsi" class="form-control" value="<?= htmlspecialchars($data['provinsi']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label">Kota</label>
                            <input type="text" name="kota" class="form-control" value="<?= htmlspecialchars($data['kota']) ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Alamat Lengkap</label>
                        <textarea name="alamat" class="form-control" rows="2" required><?= htmlspecialchars($data['alamat']) ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="4"><?= htmlspecialchars($data['deskripsi']) ?></textarea>
                    </div>

                    <div class="mb-5">
                        <label class="form-label">Ganti Foto Utama (Opsional)</label>
                        <input type="file" name="foto" class="form-control">
                        
                        <div class="mt-3">
                            <label class="form-label text-muted small mb-2">Foto Saat Ini:</label>
                            <?php 
                                $foto_db = $data['foto_utama'];
                                if (strpos($foto_db, 'http') === 0) {
                                    $src = $foto_db;
                                } else {
                                    $src = "../img/" . $foto_db;
                                }
                            ?>
                            <img src="<?= $src ?>" class="img-preview" alt="Preview">
                        </div>
                    </div>

                    <button type="submit" class="btn-primary-modern">
                        <i class="bi bi-save me-2"></i> Simpan Perubahan
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