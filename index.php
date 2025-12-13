<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HotelID - Modern Experience</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@400;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="css/index.css">
</head>
<body>

    <div id="app-container">
        
        <div id="landing-view">
            <div class="landing-overlay">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="brand-logo">HOTEL<span style="color:var(--primary)">ID</span>.</div>
                    <div class="d-none d-md-block text-white-50 small">Premium hotel System</div>
                </div>

                <div style="max-width: 600px;">
                    <h1 class="display-text">Beyond<br>Luxury.</h1>
                    <p class="sub-text">
                        Rasakan pengalaman bermalam dengan fasilitas maksimal. Desain modern, teknologi pintar, dan kenyamanan tanpa batas.
                    </p>
                    
                    <button class="btn-modern border-0" onclick="toggleLogin()">
                        Lanjutkan <i class="fas fa-arrow-right"></i>
                    </button>
                </div>

                <div class="bottom-bar">
                    <div>&copy; 2025 HotelID Corp.</div>
                    <div class="d-flex gap-3">
                        <a href="#"><i class="fab fa-instagram text-light"></i></a>
                        <a href="#"><i class="fab fa-twitter text-light"></i></a>
                        <a href="#"><i class="fab fa-linkedin text-light"></i></a>
                    </div>
                </div>
            </div>
        </div>


        <div class="login-wrapper">
            <div class="login-card">
                <button class="btn-close" onclick="toggleLogin()">
                    <i class="fas fa-times"></i>
                </button>

                <div class="text-center mb-4">
                    <h2 class="fw-bold mb-1" style="font-family: var(--font-heading);">Welcome Back</h2>
                    <p class="text-white-50 small">Please enter your details</p>
                </div>

                <form action="assets/ceklogin.php" method="post">
                    
                    <div class="mb-3 text-start">
                        <label class="small text-white-50 mb-1">LOGIN AS</label>
                        <select name="auth" class="select-modern">
                            <option value="Pengguna">User</option>
                            <option value="Administrator">Administrator</option>
                        </select>
                    </div>

                    <div class="mb-3 text-start">
                        <label class="small text-white-50 mb-1">USERNAME</label>
                        <input type="text" name="user" class="input-modern" placeholder="Enter username" required>
                    </div>

                    <div class="mb-4 text-start">
                        <label class="small text-white-50 mb-1">PASSWORD</label>
                        <input type="password" name="pass" class="input-modern" placeholder="••••••••" required>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4 small">
                        <div class="form-check">
                            <input class="form-check-input bg-dark border-secondary" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label text-white-50" for="remember">Remember me</label>
                        </div>
                        <a href="#" class="text-primary text-decoration-none">Forgot Password?</a>
                    </div>

                    <button type="submit" class="btn-submit">Sign In Access</button>
                </form>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="js/index.js"></script>

</body>
</html>