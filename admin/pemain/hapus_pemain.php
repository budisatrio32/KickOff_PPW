<?php
include_once("../../config.php");
requireAdmin();

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Ambil data pemain untuk mendapatkan info pemain
    $pemain_query = "SELECT p.*, t.NAMA_TIM FROM pemain p LEFT JOIN tim t ON p.ID_TIM = t.ID_TIM WHERE p.ID_PEMAIN = '$id'";
    $pemain_result = mysqli_query($conn, $pemain_query);
    $pemain_data = mysqli_fetch_assoc($pemain_result);
    
    if (!$pemain_data) {
        echo "<script>
                alert('Pemain tidak ditemukan!');
                window.location.href = 'daftar_pemain.php';
              </script>";
        exit();
    }
    
    // Hapus pemain dari database (tidak ada dependency check karena pemain adalah leaf node)
    $result = mysqli_query($conn, "DELETE FROM pemain WHERE ID_PEMAIN = '$id'");
    
    if ($result) {
        echo "<script>
                alert('Pemain \"" . addslashes($pemain_data['NAMA_PEMAIN']) . "\" berhasil dihapus!');
                window.location.href = 'daftar_pemain.php';
              </script>";
    } else {
        echo "<script>
                alert('Gagal menghapus pemain! Error: " . addslashes(mysqli_error($conn)) . "');
                window.location.href = 'daftar_pemain.php';
              </script>";
    }
} else {
    header("Location: daftar_pemain.php");
}
?>