<?php
session_start();
include "../assets/koneksi.php";

// Cek Admin
if (!isset($_SESSION["iduser"]) || ($_SESSION['auth'] != 'Administrator')) {
    header("Location: ../main.php");
    exit();
}

// Ambil list hotel untuk dropdown
$hotels = $conn->query("SELECT id_hotel, nama_hotel FROM hotel_list ORDER BY nama_hotel ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Input Kamar Baru - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

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
        
        /* Image Preview */
        .img-preview {
            width: 100%; height: 250px; object-fit: cover;
            border-radius: 15px; border: 2px dashed var(--border-light);
            margin-top: 15px; background: #f1f5f9;
            display: none; /* Hidden by default */
        }

        .footer { margin-top: auto; padding: 30px 0; text-align: center; color: var(--text-muted); font-size: 0.85rem; }
    </style>
</head>
<body>

<section class="admin-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="fw-bold mb-1 display-6">Input Kamar Baru</h1>
                <p class="mb-0 opacity-75">Tambahkan tipe kamar untuk hotel yang terdaftar.</p>
            </div>
            <a href="../main.php" class="btn-back-header">
                <i class="bi bi-arrow-left me-2"></i> Dashboard
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
                        echo "<div class='alert alert-success rounded-4 border-0 shadow-sm d-flex align-items-center'><i class='bi bi-check-circle-fill fs-4 me-2'></i> Kamar berhasil ditambahkan!</div>";
                    } elseif ($_GET['pesan'] == "gagaldb") {
                        echo "<div class='alert alert-danger rounded-4 border-0 shadow-sm'><i class='bi bi-exclamation-triangle-fill me-2'></i> Gagal menyimpan ke database.</div>";
                    } elseif ($_GET['pesan'] == "gagalupload") {
                        echo "<div class='alert alert-warning rounded-4 border-0 shadow-sm'><i class='bi bi-image-fill me-2'></i> Gagal mengupload gambar.</div>";
                    }
                }
                ?>

                <form method="POST" action="add_roomsave.php" enctype="multipart/form-data">
                    
                    <div class="mb-4">
                        <label class="form-label">Pilih Hotel <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-building"></i></span>
                            <select name="id_hotel" class="form-select border-start-0 ps-0" required>
                                <option value="">-- Pilih Hotel --</option>
                                <?php while($h = $hotels->fetch_assoc()): ?>
                                    <option value="<?= $h['id_hotel'] ?>"><?= $h['nama_hotel'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label">Tipe Kamar <span class="text-danger">*</span></label>
                            <input type="text" name="tipe_kamar" class="form-control" placeholder="Misal: Deluxe Room" required>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label">Harga (Per Malam) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted">Rp</span>
                                <input type="number" name="harga" class="form-control border-start-0 ps-0" placeholder="0" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label">Stok Kamar <span class="text-danger">*</span></label>
                            <input type="number" name="stok" class="form-control" value="10" required>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label">Foto Kamar <span class="text-danger">*</span></label>
                            <input type="file" name="foto" id="fotoInput" class="form-control" accept="image/*" required onchange="previewImage(event)">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <img id="imgPreview" class="img-preview">
                    </div>

                    <div class="mb-5">
                        <label class="form-label">Fasilitas / Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="4" placeholder="Wifi kencang, TV 42 Inch, AC Dingin, Sarapan gratis..."></textarea>
                    </div>

                    <button type="submit" class="btn-primary-modern">
                        <i class="bi bi-plus-circle me-2"></i> Simpan Kamar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<footer class="footer">
    <small>&copy; 2025 HOTELID Corp. Admin System.</small>
</footer>

<script>
    function previewImage(event) {
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById('imgPreview');
            output.src = reader.result;
            output.style.display = 'block';
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>

</body>
</html>