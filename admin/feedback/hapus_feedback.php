<?php
include_once("../../config.php");
requireAdmin();

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Ambil data feedback untuk mendapatkan info
    $feedback_query = "SELECT * FROM feedback WHERE ID_FEEDBACK = '$id'";
    $feedback_result = mysqli_query($conn, $feedback_query);
    $feedback_data = mysqli_fetch_assoc($feedback_result);
    
    if (!$feedback_data) {
        echo "<script>
                alert('Feedback tidak ditemukan!');
                window.location.href = 'daftar_feedback.php';
                </script>";
        exit();
    }
    
    // Format data untuk display
    $nama_user = $feedback_data['NAMA_USER'];
    $email = $feedback_data['EMAIL'];
    $tanggal = date('d/m/Y H:i', strtotime($feedback_data['TANGGAL_FEEDBACK']));
    $pesan_preview = substr($feedback_data['PESAN'], 0, 100) . (strlen($feedback_data['PESAN']) > 100 ? '...' : '');
    
    // Hapus feedback dari database
    $result = mysqli_query($conn, "DELETE FROM feedback WHERE ID_FEEDBACK = '$id'");
    
    if ($result) {
        echo "<script>
                alert('Feedback dari \"" . addslashes($nama_user) . "\" (" . addslashes($email) . ") pada " . addslashes($tanggal) . " berhasil dihapus!');
                window.location.href = 'daftar_feedback.php';
                </script>";
    } else {
        echo "<script>
                alert('Gagal menghapus feedback! Error: " . addslashes(mysqli_error($conn)) . "');
                window.location.href = 'daftar_feedback.php';
                </script>";
    }
} else {
    header("Location: daftar_feedback.php");
    exit();
}
?>