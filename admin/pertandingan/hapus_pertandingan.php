<?php
include_once("../../config.php");
requireAdmin();

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Ambil data pertandingan untuk mendapatkan info
    $pertandingan_query = "SELECT p.*, 
                        home_tim.NAMA_TIM as NAMA_HOMETEAM, 
                        away_tim.NAMA_TIM as NAMA_AWAYTEAM
                        FROM pertandingan p 
                        LEFT JOIN tim home_tim ON p.ID_HOMETEAM = home_tim.ID_TIM
                        LEFT JOIN tim away_tim ON p.ID_AWAYTEAM = away_tim.ID_TIM
                        WHERE p.ID_PERTANDINGAN = '$id'";
    $pertandingan_result = mysqli_query($conn, $pertandingan_query);
    $pertandingan_data = mysqli_fetch_assoc($pertandingan_result);
    
    if (!$pertandingan_data) {
        echo "<script>
                alert('Pertandingan tidak ditemukan!');
                window.location.href = 'daftar_pertandingan.php';
                </script>";
        exit();
    }
    
    // Format data untuk display
    $home_team = $pertandingan_data['NAMA_HOMETEAM'] ?? 'Tim tidak ditemukan';
    $away_team = $pertandingan_data['NAMA_AWAYTEAM'] ?? 'Tim tidak ditemukan';
    $match_datetime = date('d/m/Y H:i', strtotime($pertandingan_data['TANGGAL'] . ' ' . $pertandingan_data['WAKTU']));
    $match_display = "$home_team vs $away_team";
    
    // Note: Dalam aplikasi nyata, mungkin perlu cek referential integrity
    // seperti apakah ada data hasil pertandingan, statistik, dll yang terkait
    // Untuk saat ini, kita langsung hapus karena tidak ada tabel dependent lain yang disebutkan
    
    // Hapus pertandingan dari database
    $result = mysqli_query($conn, "DELETE FROM pertandingan WHERE ID_PERTANDINGAN = '$id'");
    
    if ($result) {
        echo "<script>
                alert('Pertandingan \"" . addslashes($match_display) . "\" pada " . addslashes($match_datetime) . " berhasil dihapus!');
                window.location.href = 'daftar_pertandingan.php';
                </script>";
    } else {
        echo "<script>
                alert('Gagal menghapus pertandingan! Error: " . addslashes(mysqli_error($conn)) . "');
                window.location.href = 'daftar_pertandingan.php';
                </script>";
    }
} else {
    header("Location: daftar_pertandingan.php");
    exit();
}
?>