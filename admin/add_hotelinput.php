<?php
session_start();
// Cek Admin
if (!isset($_SESSION["iduser"]) || ($_SESSION['auth'] != 'Administrator')) {
    header("Location: ../main.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Hotel - Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --primary-dark: #0f172a;      
            --primary-blue: #0ea5e9;
            --accent: #f97316;           
            --bg-body: #f8fafc;          
            --surface: #ffffff;          
        }

        body {
            background-color: var(--bg-body);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex; flex-direction: column;
        }

        h1, h3 { font-family: 'Outfit', sans-serif; }

        /* --- HEADER DENGAN PATTERN --- */
        .admin-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            padding: 80px 0 120px 0; /* Ruang ekstra untuk overlap */
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        /* Hiasan Circle di background */
        .admin-header::before {
            content: ''; position: absolute; top: -50px; right: -50px;
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%);
            border-radius: 50%;
        }

        .btn-back-header {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(5px);
            color: white; text-decoration: none; font-weight: 600;
            padding: 10px 20px; border-radius: 50px;
            border: 1px solid rgba(255,255,255,0.1);
            transition: 0.3s;
        }
        .btn-back-header:hover { background: white; color: var(--primary-dark); transform: translateX(-5px); }

        /* --- FORM CARD LEBIH MODERN --- */
        .form-card {
            background: var(--surface);
            border-radius: 30px; /* Sudut lebih bulat */
            padding: 50px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            margin-top: -100px; /* Efek Overlap */
            border: 1px solid #e2e8f0;
            position: relative;
            z-index: 10;
        }

        /* FLOATING LABELS CUSTOM */
        .form-floating > .form-control,
        .form-floating > .form-select {
            background-color: #f1f5f9;
            border: 1px solid transparent;
            border-radius: 15px;
            height: 60px;
        }
        .form-floating > .form-control:focus,
        .form-floating > .form-select:focus {
            background-color: #fff;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1);
        }
        
        /* Input Group Icons */
        .input-group-text {
            background-color: #f1f5f9;
            border: none;
            border-radius: 15px 0 0 15px;
            color: #64748b;
            padding-left: 20px;
        }
        .input-group > .form-floating > .form-control {
            border-radius: 0 15px 15px 0;
            border-left: none;
        }

        /* UPLOAD AREA */
        .upload-area {
            border: 2px dashed #cbd5e1;
            border-radius: 20px;
            background: #f8fafc;
            transition: 0.3s;
            cursor: pointer;
            position: relative;
        }
        .upload-area:hover { border-color: var(--accent); background: #fff7ed; }
        
        .preview-img {
            max-height: 300px;
            width: 100%;
            object-fit: cover;
            border-radius: 15px;
            display: none; /* Sembunyi dulu */
            margin-top: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        /* TOMBOL SIMPAN */
        .btn-save {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: white; font-weight: 700; font-size: 1.1rem;
            padding: 18px; border-radius: 50px; border: none; width: 100%;
            box-shadow: 0 10px 25px -5px rgba(249, 115, 22, 0.4);
            transition: 0.3s;
        }
        .btn-save:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px -5px rgba(249, 115, 22, 0.6);
        }

        /* Footer */
        .footer { margin-top: auto; padding: 40px 0; text-align: center; color: #94a3b8; }
    </style>
</head>
<body>

<section class="admin-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="fw-bold display-5 mb-1">Input Hotel</h1>
                <p class="opacity-75 mb-0">Tambahkan properti baru ke dalam database sistem.</p>
            </div>
            <a href="../main.php" class="btn-back-header">
                <i class="bi bi-arrow-left me-2"></i> Dashboard
            </a>
        </div>
    </div>
</section>

<section class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="form-card">
                
                <?php if (isset($_GET['pesan'])): ?>
                    <div class="mb-4">
                        <?php if ($_GET['pesan'] == "sukses"): ?>
                            <div class='alert alert-success rounded-4 border-0 shadow-sm d-flex align-items-center'><i class='bi bi-check-circle-fill fs-4 me-2'></i> Hotel berhasil disimpan!</div>
                        <?php elseif ($_GET['pesan'] == "gagaldb"): ?>
                            <div class='alert alert-danger rounded-4 border-0 shadow-sm'><i class='bi bi-exclamation-triangle-fill me-2'></i> Database Error.</div>
                        <?php elseif ($_GET['pesan'] == "gagalupload"): ?>
                            <div class='alert alert-warning rounded-4 border-0 shadow-sm'><i class='bi bi-image-fill me-2'></i> Gagal upload gambar.</div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="add_hotelsave.php" enctype="multipart/form-data">
                    
                    <div class="input-group mb-4">
                        <span class="input-group-text"><i class="bi bi-building fs-4"></i></span>
                        <div class="form-floating flex-grow-1">
                            <input type="text" class="form-control" id="namaHotel" name="nama_hotel" placeholder="Nama Hotel" required>
                            <label for="namaHotel">Nama Hotel / Properti</label>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="provinsi" name="provinsi" list="listProv" placeholder="Provinsi" required>
                                <label for="provinsi"><i class="bi bi-map me-2"></i>Provinsi</label>
                                <datalist id="listProv">
                                    <option value="DKI Jakarta">
                                    <option value="Bali">
                                    <option value="Jawa Barat">
                                    <option value="Kalimantan Timur">
                                </datalist>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="kota" name="kota" placeholder="Kota" required>
                                <label for="kota"><i class="bi bi-geo-alt me-2"></i>Kota / Kabupaten</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-floating mb-4">
                        <textarea class="form-control" placeholder="Alamat" id="alamat" name="alamat" style="height: 100px" required></textarea>
                        <label for="alamat">Alamat Lengkap</label>
                    </div>

                    <div class="form-floating mb-5">
                        <textarea class="form-control" placeholder="Deskripsi" id="deskripsi" name="deskripsi" style="height: 120px"></textarea>
                        <label for="deskripsi">Deskripsi & Fasilitas Unggulan</label>
                    </div>

                    <div class="mb-5">
                        <label class="form-label mb-2 text-muted small fw-bold text-uppercase">Foto Utama Hotel</label>
                        <div class="upload-area p-4 text-center" onclick="document.getElementById('fileInput').click()">
                            <i class="bi bi-cloud-arrow-up text-primary display-4"></i>
                            <h5 class="mt-2 fw-bold text-dark">Klik untuk Upload Foto</h5>
                            <p class="text-muted small mb-0">Format JPG/PNG, Max 2MB</p>
                            <input type="file" id="fileInput" name="foto" class="d-none" accept="image/*" required onchange="previewImage(event)">
                        </div>
                        <img id="imgPreview" class="preview-img">
                    </div>

                    <button type="submit" class="btn-save">
                        Simpan Data Hotel <i class="bi bi-arrow-right-circle ms-2"></i>
                    </button>

                </form>
            </div>
        </div>
    </div>
</section>

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