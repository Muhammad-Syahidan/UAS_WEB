<?php
session_start();
include "../assets/koneksi.php";

// 1. Cek Admin
if (!isset($_SESSION["iduser"]) || ($_SESSION['auth'] != 'Administrator')) {
    header("Location: ../main.php"); exit();
}

// 2. Hapus User
if(isset($_GET['hapus'])){
    $id = $_GET['hapus'];
    $conn->query("DELETE FROM user_list WHERE id_user='$id'");
    header("Location: manage_users.php");
}

// 3. Logika Filter
$where = "WHERE 1=1";

// Filter Keyword (Nama/Username)
if (isset($_GET['keyword']) && !empty($_GET['keyword'])) {
    $key = mysqli_real_escape_string($conn, $_GET['keyword']);
    $where .= " AND (username LIKE '%$key%' OR nama_lengkap LIKE '%$key%')";
}

// Filter Role
if (isset($_GET['role']) && !empty($_GET['role'])) {
    $role = mysqli_real_escape_string($conn, $_GET['role']);
    $where .= " AND auth = '$role'";
}

$query = "SELECT * FROM user_list $where ORDER BY id_user DESC";
$users = $conn->query($query);

// Simpan inputan filter
$filter_keyword = $_GET['keyword'] ?? '';
$filter_role = $_GET['role'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola User - Admin</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        /* --- TEMA MODERN LIGHT --- */
        :root {
            --primary: #0ea5e9;          /* Biru Muda */
            --accent: #f97316;           /* Oranye */
            --accent-hover: #ea580c;
            --bg-body: #f8fafc;          /* Abu Terang */
            --surface: #ffffff;          /* Putih */
            --text-main: #1e293b;        /* Hitam Abu */
            --text-muted: #64748b;       
            --border-light: #e2e8f0;
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex; flex-direction: column;
        }

        h1, h2, h3, h4 { font-family: 'Outfit', sans-serif; }

        /* HEADER GRADIENT */
        .admin-header {
            background: linear-gradient(135deg, #0f172a 0%, #334155 100%);
            padding: 60px 0 100px 0;
            color: white;
            margin-bottom: -60px;
        }
        .btn-back-header {
            background: rgba(255,255,255,0.1);
            color: white; text-decoration: none; font-weight: 600;
            padding: 8px 20px; border-radius: 50px; border: 1px solid rgba(255,255,255,0.1);
            transition: 0.3s;
        }
        .btn-back-header:hover { background: white; color: var(--primary); }

        /* FILTER SIDEBAR (KIRI) */
        .filter-sidebar {
            background: var(--surface);
            border-radius: 20px;
            padding: 25px;
            border: 1px solid var(--border-light);
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            position: sticky;
            top: 20px;
        }
        .filter-title { 
            font-weight: 700; color: #0f172a; margin-bottom: 20px; font-size: 1.1rem; 
            border-bottom: 2px solid var(--accent); padding-bottom: 10px; display: inline-block; 
        }

        /* TABLE CARD (KANAN) */
        .table-card {
            background: var(--surface);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid var(--border-light);
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            min-height: 500px;
        }

        /* Form Elements */
        .form-label { font-size: 0.85rem; font-weight: 600; color: var(--text-muted); }
        .form-control, .form-select { border-radius: 10px; padding: 10px; font-size: 0.9rem; border: 1px solid var(--border-light); background: #f8fafc; }
        .form-control:focus, .form-select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1); background: white; }

        /* Table Styles (Clean No Image) */
        .table-modern { --bs-table-bg: transparent; color: var(--text-main); }
        .table-modern thead th { 
            background-color: #f1f5f9; color: #0f172a; 
            font-weight: 700; border-bottom: 2px solid var(--border-light);
            text-transform: uppercase; font-size: 0.75rem; padding: 15px;
        }
        .table-modern tbody td { 
            vertical-align: middle; padding: 15px; border-bottom: 1px solid var(--border-light);
            background-color: white; font-size: 0.95rem;
        }
        .table-modern tbody tr:hover td { background-color: #fff7ed; }

        /* Buttons */
        .btn-add {
            background: var(--accent); color: white; font-weight: 600; 
            border: none; padding: 10px 25px; border-radius: 50px; text-decoration: none; transition: 0.3s;
        }
        .btn-add:hover { background: var(--accent-hover); color: white; transform: translateY(-2px); }

        .btn-filter { background: var(--primary); color: white; width: 100%; border-radius: 50px; padding: 10px; font-weight: 600; border: none; }
        .btn-filter:hover { background: #0284c7; }
        
        .btn-reset { text-decoration: none; color: var(--text-muted); font-size: 0.85rem; display: block; text-align: center; margin-top: 15px; }
        .btn-reset:hover { color: var(--accent); }

        .footer { margin-top: auto; padding: 30px 0; text-align: center; color: var(--text-muted); font-size: 0.85rem; }
    </style>
</head>
<body>

<section class="admin-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1">Kelola Pengguna</h2>
                <p class="mb-0 opacity-75">Manajemen akun Admin, Resepsionis, dan User.</p>
            </div>
            <a href="../main.php" class="btn-back-header">
                <i class="bi bi-arrow-left me-2"></i> Dashboard
            </a>
        </div>
    </div>
</section>

<div class="container pb-5">
    <div class="row">
        
        <div class="col-lg-3 mb-4">
            <div class="filter-sidebar">
                <h5 class="filter-title"><i class="bi bi-funnel"></i> Filter User</h5>
                
                <form method="GET" action="manage_users.php">
                    <div class="mb-3">
                        <label class="form-label">Cari Username / Nama</label>
                        <input type="text" name="keyword" class="form-control" placeholder="Ketik nama..." value="<?= htmlspecialchars($filter_keyword) ?>">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Role / Hak Akses</label>
                        <select name="role" class="form-select">
                            <option value="">-- Semua Role --</option>
                            <option value="Administrator" <?= ($filter_role == 'Administrator') ? 'selected' : '' ?>>Administrator</option>
                            <option value="Resepsionis" <?= ($filter_role == 'Resepsionis') ? 'selected' : '' ?>>Resepsionis</option>
                            <option value="Pengguna" <?= ($filter_role == 'Pengguna') ? 'selected' : '' ?>>Pengguna</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-filter">
                        Terapkan Filter
                    </button>

                    <?php if(!empty($filter_keyword) || !empty($filter_role)): ?>
                        <a href="manage_users.php" class="btn-reset"><i class="bi bi-arrow-counterclockwise"></i> Reset Pencarian</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="table-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold m-0 text-dark">Daftar Pengguna (<?= $users->num_rows ?>)</h4>
                    <button class="btn-add" onclick="alert('Fitur tambah user belum aktif')">
                        <i class="bi bi-plus-lg me-1"></i> User Baru
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-modern table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Nama Lengkap</th>
                                <th>Role</th>
                                <th width="100" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($users->num_rows > 0): ?>
                                <?php while($u = $users->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold text-primary">
                                        <?= $u['username'] ?>
                                    </td>
                                    <td class="text-muted">
                                        <?= $u['nama_lengkap'] ?? '-' ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $badge = 'bg-secondary-subtle text-secondary';
                                            if($u['auth']=='Administrator') $badge='bg-warning-subtle text-warning-emphasis border border-warning-subtle';
                                            if($u['auth']=='Resepsionis') $badge='bg-info-subtle text-info-emphasis border border-info-subtle';
                                            if($u['auth']=='Pengguna') $badge='bg-success-subtle text-success-emphasis border border-success-subtle';
                                        ?>
                                        <span class="badge <?= $badge ?> rounded-pill px-3 py-2">
                                            <?= $u['auth'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="manage_users.php?hapus=<?= $u['id_user'] ?>" class="btn btn-outline-danger btn-sm rounded-circle p-2" onclick="return confirm('Yakin hapus user ini?')" title="Hapus User">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <i class="bi bi-person-x text-muted display-4 opacity-50"></i>
                                        <p class="mt-3 text-muted fw-bold">User tidak ditemukan.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<footer class="footer">
    <small>&copy; 2025 HOTELID Corp. Admin System.</small>
</footer>

</body>
</html>