<?php
session_start();
include "../assets/koneksi.php";

// 1. Cek Admin
if (!isset($_SESSION["iduser"]) || ($_SESSION['auth'] != 'Administrator')) {
    header("Location: ../main.php"); exit();
}

// 2. Logika Hapus
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $conn->query("DELETE FROM hotel_list WHERE id_hotel='$id'");
    $conn->query("DELETE FROM room_types WHERE id_hotel='$id'");
    header("Location: list_hotel.php");
}

// 3. Ambil Data Provinsi untuk Dropdown
$provinsi_data = $conn->query("SELECT DISTINCT provinsi FROM hotel_list ORDER BY provinsi ASC");

// 4. Logika Filter
$where = "WHERE 1=1"; 
if (isset($_GET['keyword']) && !empty($_GET['keyword'])) {
    $key = mysqli_real_escape_string($conn, $_GET['keyword']);
    $where .= " AND (nama_hotel LIKE '%$key%' OR kota LIKE '%$key%')";
}
if (isset($_GET['provinsi']) && !empty($_GET['provinsi'])) {
    $prov = mysqli_real_escape_string($conn, $_GET['provinsi']);
    $where .= " AND provinsi = '$prov'";
}

$query = "SELECT * FROM hotel_list $where ORDER BY id_hotel DESC";
$data = $conn->query($query);

// Simpan value input
$filter_keyword = $_GET['keyword'] ?? '';
$filter_provinsi = $_GET['provinsi'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Data Hotel - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        /* --- TEMA HYBRID MODERN --- */
        :root {
            --primary: #0f172a;          /* Navy Gelap */
            --accent: #f97316;           /* Oranye */
            --accent-hover: #ea580c;
            --bg-body: #f8fafc;          /* Abu Terang */
            --surface: #ffffff;          /* Putih */
            --text-main: #1e293b;        
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
            background: linear-gradient(135deg, var(--primary) 0%, #334155 100%);
            padding: 60px 0 100px 0;
            color: white;
            margin-bottom: -60px; /* Overlap effect */
        }
        .btn-back-header {
            background: rgba(255,255,255,0.1);
            color: white; text-decoration: none; font-weight: 600;
            padding: 8px 20px; border-radius: 50px; border: 1px solid rgba(255,255,255,0.1);
            transition: 0.3s;
        }
        .btn-back-header:hover { background: white; color: var(--primary); }

        /* LAYOUT */
        .main-container { position: relative; z-index: 10; }

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
        .filter-title { font-weight: 700; color: var(--primary); margin-bottom: 20px; font-size: 1.1rem; border-bottom: 2px solid var(--accent); padding-bottom: 10px; display: inline-block; }

        /* DATA TABLE CARD (KANAN) */
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

        /* Table */
        .table-modern thead th { background-color: #f1f5f9; color: var(--primary); font-weight: 700; text-transform: uppercase; font-size: 0.75rem; padding: 15px; border-bottom: 2px solid var(--border-light); }
        .table-modern tbody td { vertical-align: middle; padding: 15px; background: white; border-bottom: 1px solid var(--border-light); font-size: 0.95rem; }
        .table-modern tbody tr:hover td { background-color: #fff7ed; }

        .img-tiny { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-light); }
        
        /* Buttons */
        .btn-add { background: var(--accent); color: white; font-weight: 600; padding: 10px 25px; border-radius: 50px; text-decoration: none; transition: 0.3s; border: none; }
        .btn-add:hover { background: var(--accent-hover); color: white; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(249, 115, 22, 0.3); }

        .btn-filter { background: var(--primary); color: white; width: 100%; border-radius: 50px; padding: 10px; font-weight: 600; border: none; }
        .btn-filter:hover { background: #334155; }
        
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
                <h2 class="fw-bold mb-1">Kelola Data Hotel</h2>
                <p class="mb-0 opacity-75">Manajemen properti dan lokasi hotel.</p>
            </div>
            <a href="../main.php" class="btn-back-header">
                <i class="bi bi-arrow-left me-2"></i> Dashboard
            </a>
        </div>
    </div>
</section>

<div class="container main-container pb-5">
    <div class="row">
        
        <div class="col-lg-3 mb-4">
            <div class="filter-sidebar">
                <h5 class="filter-title"><i class="bi bi-funnel"></i> Filter Data</h5>
                
                <form method="GET" action="list_hotel.php">
                    <div class="mb-3">
                        <label class="form-label">Cari Nama / Kota</label>
                        <input type="text" name="keyword" class="form-control" placeholder="Ketikan sesuatu..." value="<?= htmlspecialchars($filter_keyword) ?>">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Provinsi</label>
                        <select name="provinsi" class="form-select">
                            <option value="">Semua Provinsi</option>
                            <?php while($p = $provinsi_data->fetch_assoc()): ?>
                                <option value="<?= $p['provinsi'] ?>" <?= ($filter_provinsi == $p['provinsi']) ? 'selected' : '' ?>>
                                    <?= $p['provinsi'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn-filter">
                        Terapkan Filter
                    </button>

                    <?php if(!empty($filter_keyword) || !empty($filter_provinsi)): ?>
                        <a href="list_hotel.php" class="btn-reset"><i class="bi bi-arrow-counterclockwise"></i> Reset Pencarian</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="table-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold m-0 text-dark">Daftar Hotel (<?= $data->num_rows ?>)</h4>
                    <a href="add_hotelinput.php" class="btn-add">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Hotel
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-modern table-hover align-middle">
                        <thead>
                            <tr>
                                <th width="80">Foto</th>
                                <th>Nama Hotel</th>
                                <th>Lokasi</th>
                                <th width="120" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($data->num_rows > 0): ?>
                                <?php while($row = $data->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-center">
                                        <?php 
                                            $foto = (!empty($row['foto_utama']) && file_exists("../img/".$row['foto_utama'])) ? "../img/".$row['foto_utama'] : "https://via.placeholder.com/100x100?text=No+Img"; 
                                            if (strpos($row['foto_utama'], 'http') === 0) $foto = $row['foto_utama'];
                                        ?>
                                        <img src="<?= $foto ?>" class="img-tiny">
                                    </td>
                                    <td>
                                        <span class="fw-bold d-block text-dark"><?= $row['nama_hotel'] ?></span>
                                        <small class="text-muted" style="font-size: 0.75rem;">ID: #<?= $row['id_hotel'] ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border border-secondary-subtle mb-1">
                                            <?= $row['provinsi'] ?>
                                        </span>
                                        <div class="small text-muted"><?= $row['kota'] ?></div>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="edit_hotelinput.php?id=<?= $row['id_hotel'] ?>" class="btn btn-outline-primary btn-sm" title="Edit">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>
                                            <a href="list_hotel.php?hapus=<?= $row['id_hotel'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('PERINGATAN: Menghapus hotel ini akan menghapus SEMUA KAMAR yang ada di dalamnya. Lanjutkan?')" title="Hapus">
                                                <i class="bi bi-trash-fill"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <i class="bi bi-search text-muted display-4 opacity-50"></i>
                                        <p class="mt-3 text-muted fw-bold">Data tidak ditemukan.</p>
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