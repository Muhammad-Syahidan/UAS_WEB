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
    <title>Tambah User - Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --primary: #0ea5e9;
            --accent: #f97316;
            --bg-body: #f8fafc;
            --surface: #ffffff;
            --text-main: #1e293b;
            --border-light: #e2e8f0;
        }

        body {
            background-color: var(--bg-body);
            font-family: 'Inter', sans-serif;
            color: var(--text-main);
            min-height: 100vh;
            display: flex; flex-direction: column;
        }

        h1, h2, h3 { font-family: 'Outfit', sans-serif; }

        /* HEADER */
        .admin-header {
            background: linear-gradient(135deg, #0f172a 0%, #334155 100%);
            padding: 60px 0 100px 0;
            color: white;
            margin-bottom: -60px;
        }
        
        .btn-back-header {
            background: rgba(255,255,255,0.1);
            color: white; text-decoration: none; font-weight: 600;
            padding: 8px 20px; border-radius: 50px; 
            border: 1px solid rgba(255,255,255,0.1);
            transition: 0.3s;
        }
        .btn-back-header:hover { background: white; color: var(--primary); }

        /* FORM CARD */
        .form-card {
            background: var(--surface);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid var(--border-light);
        }

        .form-label { font-weight: 600; font-size: 0.9rem; margin-bottom: 8px; color: #64748b; }
        .form-control, .form-select {
            padding: 12px 15px; border-radius: 10px;
            border: 1px solid var(--border-light);
            background-color: #f8fafc;
        }
        .form-control:focus, .form-select:focus {
            background-color: #fff;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
        }

        .btn-save {
            background: var(--accent); color: white; width: 100%;
            padding: 12px; border-radius: 50px; font-weight: 600; border: none;
            transition: 0.3s; margin-top: 20px;
        }
        .btn-save:hover { background: #ea580c; transform: translateY(-2px); }

        .footer { margin-top: auto; padding: 30px 0; text-align: center; color: #94a3b8; font-size: 0.85rem; }
    </style>
</head>
<body>

<section class="admin-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1">Tambah User Baru</h2>
                <p class="mb-0 opacity-75">Buat akun untuk Admin, Resepsionis, atau Pengguna.</p>
            </div>
            <a href="manage_users.php" class="btn-back-header">
                <i class="bi bi-arrow-left me-2"></i> Kembali
            </a>
        </div>
    </div>
</section>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="form-card">
                
                <?php if (isset($_GET['pesan']) && $_GET['pesan'] == "gagal"): ?>
                    <div class="alert alert-danger rounded-3 border-0 shadow-sm mb-4">
                        <i class="bi bi-exclamation-circle-fill me-2"></i> Username sudah digunakan!
                    </div>
                <?php endif; ?>

                <form action="add_usersave.php" method="POST">
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" placeholder="Contoh: alwan syaluna" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" placeholder="Username unik (tanpa spasi)" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Role / Hak Akses</label>
                        <select name="role" class="form-select" required>
                            <option value="" selected disabled>-- Pilih Role --</option>
                            <option value="Pengguna">Pengguna (User Biasa)</option>
                            <option value="Administrator">Administrator (Full Akses)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-save">
                        <i class="bi bi-person-plus-fill me-2"></i> Simpan User
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