<?php
include_once("../../config.php");
requireAdmin();

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Ambil data liga untuk mendapatkan info liga
    $liga_query = "SELECT * FROM liga WHERE ID_LIGA = '$id'";
    $liga_result = mysqli_query($conn, $liga_query);
    $liga_data = mysqli_fetch_assoc($liga_result);
    
    if (!$liga_data) {
        echo "<script>
                alert('Liga tidak ditemukan!');
                window.location.href = 'daftar_liga.php';
                </script>";
        exit();
    }
    
    // Cek referential integrity - apakah ada tim yang menggunakan liga ini
    $check_tim = mysqli_query($conn, "SELECT COUNT(*) as total FROM tim WHERE ID_LIGA = '$id'");
    $tim_count = mysqli_fetch_assoc($check_tim)['total'];
    
    if ($tim_count > 0) {
        echo "<script>
                alert('Tidak bisa menghapus liga \"" . addslashes($liga_data['NAMA_LIGA']) . "\"!\\n\\nLiga ini masih digunakan oleh $tim_count tim.\\n\\nHapus atau pindahkan tim terlebih dahulu.');
                window.location.href = 'daftar_liga.php';
                </script>";
    } else {
        // Hapus logo jika ada
        if (!empty($liga_data['LOGO'])) {
            $logo_path = "../../uploads/leagues/" . $liga_data['LOGO'];
            if (file_exists($logo_path)) {
                unlink($logo_path);
            }
        }
        
        // Hapus liga dari database
        $result = mysqli_query($conn, "DELETE FROM liga WHERE ID_LIGA = '$id'");
        
        if ($result) {
            echo "<script>
                    alert('Liga \"" . addslashes($liga_data['NAMA_LIGA']) . "\" berhasil dihapus!');
                    window.location.href = 'daftar_liga.php';
                    </script>";
        } else {
            echo "<script>
                    alert('Gagal menghapus liga! Error: " . addslashes(mysqli_error($conn)) . "');
                    window.location.href = 'daftar_liga.php';
                    </script>";
        }
    }
} else {
    header("Location: daftar_liga.php");
}
?>