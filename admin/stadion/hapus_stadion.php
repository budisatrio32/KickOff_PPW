<?php
include_once("../../config.php");
requireAdmin();

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Ambil data stadion untuk mendapatkan info stadion
    $stadion_query = "SELECT * FROM stadion WHERE ID_STADION = '$id'";
    $stadion_result = mysqli_query($conn, $stadion_query);
    $stadion_data = mysqli_fetch_assoc($stadion_result);
    
    if (!$stadion_data) {
        echo "<script>
                alert('Stadion tidak ditemukan!');
                window.location.href = 'daftar_stadion.php';
                </script>";
        exit();
    }
    
    // Cek referential integrity - apakah ada tim yang menggunakan stadion ini
    $check_tim = mysqli_query($conn, "SELECT COUNT(*) as total FROM tim WHERE ID_STADION = '$id'");
    $tim_count = mysqli_fetch_assoc($check_tim)['total'];
    
    // Cek apakah ada pertandingan yang menggunakan stadion ini
    $check_pertandingan = mysqli_query($conn, "SELECT COUNT(*) as total FROM pertandingan WHERE ID_STADION = '$id'");
    $pertandingan_count = mysqli_fetch_assoc($check_pertandingan)['total'];
    
    if ($tim_count > 0 || $pertandingan_count > 0) {
        echo "<script>
                alert('Tidak bisa menghapus stadion \"" . addslashes($stadion_data['NAMA_STADION']) . "\"!\\n\\nStadion ini masih digunakan oleh:\\n- $tim_count tim\\n- $pertandingan_count pertandingan\\n\\nHapus atau pindahkan data tersebut terlebih dahulu.');
                window.location.href = 'daftar_stadion.php';
                </script>";
    } else {
        // Hapus foto jika ada
        if (!empty($stadion_data['FOTO'])) {
            $foto_path = "../../uploads/stadions/" . $stadion_data['FOTO'];
            if (file_exists($foto_path)) {
                unlink($foto_path);
            }
        }
        
        // Hapus stadion dari database
        $result = mysqli_query($conn, "DELETE FROM stadion WHERE ID_STADION = '$id'");
        
        if ($result) {
            echo "<script>
                    alert('Stadion \"" . addslashes($stadion_data['NAMA_STADION']) . "\" berhasil dihapus!');
                    window.location.href = 'daftar_stadion.php';
                    </script>";
        } else {
            echo "<script>
                    alert('Gagal menghapus stadion! Error: " . addslashes(mysqli_error($conn)) . "');
                    window.location.href = 'daftar_stadion.php';
                    </script>";
        }
    }
} else {
    header("Location: daftar_stadion.php");
}
?>