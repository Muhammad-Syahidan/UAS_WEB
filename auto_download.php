<?php
// Set waktu eksekusi tidak terbatas (karena download butuh waktu)
set_time_limit(0); 
ini_set('memory_limit', '256M');

include "assets/koneksi.php";

echo "<h1>⏳ Memulai Proses Download Gambar...</h1>";
echo "<p>Mohon jangan tutup halaman ini sampai selesai.</p><hr>";

// 1. Ambil semua hotel
$sql = "SELECT id_hotel, nama_hotel FROM hotel_list";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    
    $counter = 0;
    
    // URL Sumber Gambar (LoremFlickr - Menyediakan gambar hotel random gratis)
    // Kita tambahkan parameter random lock agar setiap request dapat gambar beda
    $base_url = "https://loremflickr.com/800/600/hotel,luxury,bedroom/all?lock=";

    while ($row = $result->fetch_assoc()) {
        $id = $row['id_hotel'];
        $nama_file = "hotel_" . $id . ".jpg";
        $target_file = "img/" . $nama_file;

        // Cek apakah gambar sudah ada? Jika sudah, skip (biar hemat kuota/waktu)
        if (file_exists($target_file)) {
            echo "<span style='color:gray'>[SKIP] Hotel ID $id sudah ada gambar.</span><br>";
            continue;
        }

        // 2. Download Gambar
        $url_download = $base_url . $id; // ID unik agar gambar beda-beda
        
        // Mengambil konten gambar
        $image_content = @file_get_contents($url_download);

        if ($image_content) {
            // 3. Simpan ke folder img/
            file_put_contents($target_file, $image_content);

            // 4. Update Database
            $conn->query("UPDATE hotel_list SET foto_utama = '$nama_file' WHERE id_hotel = '$id'");

            echo "<span style='color:green'>[BERHASIL] Download gambar untuk: <b>" . $row['nama_hotel'] . "</b></span><br>";
            $counter++;
        } else {
            echo "<span style='color:red'>[GAGAL] Tidak bisa download untuk ID: $id</span><br>";
        }

        // Beri jeda sedikit agar server penyedia gambar tidak memblokir kita
        usleep(500000); // 0.5 detik
        
        // Flush output buffer agar teks muncul satu per satu saat loading
        flush();
        ob_flush();
    }

    echo "<hr><h3>✅ Selesai! Berhasil mendownload $counter gambar baru.</h3>";
    echo "<a href='main.php'>Kembali ke Dashboard</a>";

} else {
    echo "Tidak ada data hotel.";
}
?>