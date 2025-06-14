<?php
include_once("../../config.php");
requireAdmin();

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Ambil data tim untuk mendapatkan info logo
    $tim_query = "SELECT LOGO_TIM, NAMA_TIM FROM tim WHERE ID_TIM = '$id'";
    $tim_result = mysqli_query($conn, $tim_query);
    $tim_data = mysqli_fetch_assoc($tim_result);
    
    if (!$tim_data) {
        echo "<script>
                alert('Tim tidak ditemukan!');
                window.location.href = 'daftar_tim.php';
                </script>";
        exit();
    }
    
    // Cek referential integrity
    $check_pemain = mysqli_query($conn, "SELECT COUNT(*) as total FROM pemain WHERE ID_TIM = '$id'");
    $pemain_count = mysqli_fetch_assoc($check_pemain)['total'];
    
    $check_pertandingan = mysqli_query($conn, "SELECT COUNT(*) as total FROM pertandingan WHERE ID_HOMETEAM = '$id' OR ID_AWAYTEAM = '$id'");
    $pertandingan_count = mysqli_fetch_assoc($check_pertandingan)['total'];
    
    if ($pemain_count > 0 || $pertandingan_count > 0) {
        echo "<script>
                alert('Tidak bisa menghapus tim \"" . addslashes($tim_data['NAMA_TIM']) . "\"!\\n\\nTim ini masih memiliki:\\n- $pemain_count pemain\\n- $pertandingan_count pertandingan\\n\\nHapus data pemain dan pertandingan terlebih dahulu.');
                window.location.href = 'daftar_tim.php';
                </script>";
    } else {
        // Hapus logo jika ada
        if (!empty($tim_data['LOGO_TIM'])) {
            $logo_path = "../../uploads/teams/" . $tim_data['LOGO_TIM'];
            if (file_exists($logo_path)) {
                unlink($logo_path);
            }
        }
        
        // Hapus tim dari database
        $result = mysqli_query($conn, "DELETE FROM tim WHERE ID_TIM = '$id'");
        
        if ($result) {
            echo "<script>
                    alert('Tim \"" . addslashes($tim_data['NAMA_TIM']) . "\" berhasil dihapus!');
                    window.location.href = 'daftar_tim.php';
                    </script>";
        } else {
            echo "<script>
                    alert('Gagal menghapus tim! Error: " . addslashes(mysqli_error($conn)) . "');
                    window.location.href = 'daftar_tim.php';
                    </script>";
        }
    }
} else {
    header("Location: daftar_tim.php");
}
?>