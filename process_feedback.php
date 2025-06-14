<?php
// process_feedback.php - Backend logic untuk feedback
include_once("config.php");

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Ambil data dari form
    $nama_user = sanitizeInput($_POST['nama_user']);
    $status = sanitizeInput($_POST['status']);
    $email = sanitizeInput($_POST['email']);
    $pesan = sanitizeInput($_POST['pesan']);
    
    // Validasi input
    if (empty($nama_user) || empty($status) || empty($email) || empty($pesan)) {
        $response['message'] = "Semua field harus diisi!";
    } 
    // Validasi email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = "Format email tidak valid!";
    } 
    // Validasi panjang pesan
    elseif (strlen($pesan) > 1000) {
        $response['message'] = "Pesan terlalu panjang! Maksimal 1000 karakter.";
    } 
    else {
        // Insert ke database
        $query = "INSERT INTO feedback (NAMA_USER, STATUS, PESAN, EMAIL, TANGGAL_FEEDBACK) VALUES (?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssss", $nama_user, $status, $pesan, $email);
            
            if (mysqli_stmt_execute($stmt)) {
                $response['success'] = true;
                $response['message'] = "Terima kasih atas feedback Anda! Pesan telah berhasil dikirim dan akan tampil di testimonials.";
                
                // Log successful feedback
                error_log("New feedback from: " . $nama_user . " (" . $status . ")");
                
                // Optional: Send email notification to admin
                // mail("admin@kickoff.com", "New Feedback from $nama_user", $pesan);
                
            } else {
                $response['message'] = "Gagal menyimpan feedback. Silakan coba lagi.";
                error_log("Database error: " . mysqli_stmt_error($stmt));
            }
            
            mysqli_stmt_close($stmt);
        } else {
            $response['message'] = "Terjadi kesalahan sistem. Silakan coba lagi.";
            error_log("Prepare statement failed: " . mysqli_error($conn));
        }
    }
    
    // Redirect back dengan message
    if ($response['success']) {
        header("Location: index.php?feedback=success");
    } else {
        header("Location: index.php?feedback=error&msg=" . urlencode($response['message']));
    }
    exit();
    
} else {
    // Jika bukan POST request
    header("Location: index.php");
    exit();
}

mysqli_close($conn);
?>