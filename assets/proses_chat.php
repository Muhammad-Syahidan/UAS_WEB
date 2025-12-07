<?php
include 'koneksi.php';

// 1. KIRIM PESAN (Dari User)
if(isset($_POST['tipe']) && $_POST['tipe'] == 'kirim'){
    $pengirim = $_POST['id_pengirim'];
    $pesan = mysqli_real_escape_string($koneksi, $_POST['pesan']);
    
    // id_penerima 2 diasumsikan ID Resepsionis (sesuaikan database)
    mysqli_query($koneksi, "INSERT INTO chat (id_pengirim, id_penerima, pesan) VALUES ('$pengirim', '2', '$pesan')");
}

// 2. AMBIL PESAN (Tampilan User)
if(isset($_POST['tipe']) && $_POST['tipe'] == 'ambil'){
    $id_user = $_POST['id_user'];
    
    $query = mysqli_query($koneksi, "SELECT * FROM chat WHERE id_pengirim='$id_user' OR id_penerima='$id_user' ORDER BY waktu ASC");
    
    while($d = mysqli_fetch_assoc($query)){
        if($d['id_pengirim'] == $id_user){
            echo '<div class="bubble me">'.$d['pesan'].'</div>';
        } else {
            echo '<div class="bubble you">'.$d['pesan'].'</div>';
        }
    }
}

// 3. LOAD LIST USER (Tampilan Resepsionis)
if(isset($_GET['tipe']) && $_GET['tipe'] == 'list_user'){
    // Ambil user yang pernah mengirim pesan (Group By)
    $query = mysqli_query($koneksi, "SELECT DISTINCT users.id_user, users.nama_lengkap FROM chat JOIN users ON chat.id_pengirim = users.id_user WHERE users.role='Pengguna'");
    
    while($user = mysqli_fetch_assoc($query)){
        echo '<div class="user-item" onclick="bukaChat('.$user['id_user'].')">
                <i class="fas fa-user-circle me-2"></i> '.$user['nama_lengkap'].'
              </div>';
    }
}

// 4. AMBIL CHAT (Tampilan Resepsionis)
if(isset($_POST['tipe']) && $_POST['tipe'] == 'ambil_resep'){
    $id_lawan = $_POST['id_lawan'];
    // Asumsi resepsionis login ID = 2 (Sesuaikan session nanti jika perlu)
    
    $query = mysqli_query($koneksi, "SELECT * FROM chat WHERE (id_pengirim='$id_lawan' AND id_penerima='2') OR (id_pengirim='2' AND id_penerima='$id_lawan') ORDER BY waktu ASC");
    
    while($d = mysqli_fetch_assoc($query)){
        if($d['id_pengirim'] == '2'){ // 2 Adalah ID Resepsionis
            echo '<div class="bubble keluar">'.$d['pesan'].'</div>';
        } else {
            echo '<div class="bubble masuk">'.$d['pesan'].'</div>';
        }
    }
}

// 5. KIRIM BALASAN (Dari Resepsionis)
if(isset($_POST['tipe']) && $_POST['tipe'] == 'kirim_resep'){
    $pengirim = $_POST['id_pengirim'];
    $penerima = $_POST['id_penerima'];
    $pesan = mysqli_real_escape_string($koneksi, $_POST['pesan']);
    
    mysqli_query($koneksi, "INSERT INTO chat (id_pengirim, id_penerima, pesan) VALUES ('$pengirim', '$penerima', '$pesan')");
}
?>