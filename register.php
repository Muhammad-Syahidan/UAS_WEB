<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Akun - HotelID</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/index.css"> <style>
        #app-container {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>
<body>
    <div id="app-container" class="d-flex justify-content-center align-items-center">
        <div class="login-card" style="opacity: 1; transform: none;">
            <h3 class="text-center fw-bold text-white mb-4">Buat Akun Baru</h3>
            
            <form action="assets/proses_register.php" method="POST">
                <div class="mb-3">
                    <label class="small text-white-50 mb-1">NAMA LENGKAP</label>
                    <input type="text" name="nama" class="input-modern" placeholder="Cth: Budi Santoso" required>
                </div>
                <div class="mb-3">
                    <label class="small text-white-50 mb-1">USERNAME</label>
                    <input type="text" name="user" class="input-modern" placeholder="Username unik" required>
                </div>
                <div class="mb-4">
                    <label class="small text-white-50 mb-1">PASSWORD</label>
                    <input type="password" name="pass" class="input-modern" placeholder="Buat password" required>
                </div>
                <button type="submit" class="btn-submit">Daftar Akun Pengguna</button>
                <div class="text-center mt-3">
                    <a href="index.php" class="text-white-50 small text-decoration-none">Sudah punya akun? Masuk di sini</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>