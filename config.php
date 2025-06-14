<?php
session_start();

// DEBUGGING
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'error.log');

// Konfigurasi database
$host = "localhost";
$username = "root";
$password = ""; // Sesuaikan dengan password MySQL Anda, biasanya kosong
$database = "finaljadwalpertandingan"; // Nama database yang Anda buat

// Membuat koneksi
$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

// Security functions
function sanitizeInput($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

// Authentication functions
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isUser() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'user';
}

function getCurrentUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

function getCurrentUserRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

function getCurrentUserName() {
    return isset($_SESSION['username']) ? $_SESSION['username'] : null;
}

function getCurrentUserFullName() {
    return isset($_SESSION['full_name']) ? $_SESSION['full_name'] : null;
}

// Middleware functions
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header("Location: index.php");
        exit();
    }
}

function requireUser() {
    requireLogin();
    if (!isUser() && !isAdmin()) {
        header("Location: login.php");
        exit();
    }
}

// Utility functions
function redirect($url) {
    header("Location: $url");
    exit();
}

function showAlert($message, $type = 'info') {
    return "<div class='alert alert-$type'>
                <i class='bi bi-info-circle me-2'></i>
                " . htmlspecialchars($message) . "
            </div>";
}

function generateToken() {
    return bin2hex(random_bytes(32));
}

// Password strength checker
function checkPasswordStrength($password) {
    $strength = 0;
    $feedback = [];
    
    if (strlen($password) >= 8) {
        $strength += 1;
    } else {
        $feedback[] = "Minimal 8 karakter";
    }
    
    if (preg_match('/[a-z]/', $password)) {
        $strength += 1;
    } else {
        $feedback[] = "Perlu huruf kecil";
    }
    
    if (preg_match('/[A-Z]/', $password)) {
        $strength += 1;
    } else {
        $feedback[] = "Perlu huruf besar";
    }
    
    if (preg_match('/[0-9]/', $password)) {
        $strength += 1;
    } else {
        $feedback[] = "Perlu angka";
    }
    
    if (preg_match('/[^a-zA-Z0-9]/', $password)) {
        $strength += 1;
    } else {
        $feedback[] = "Perlu karakter khusus";
    }
    
    return [
        'strength' => $strength,
        'feedback' => $feedback,
        'level' => $strength < 3 ? 'weak' : ($strength < 4 ? 'medium' : 'strong')
    ];
}

// Close connection when script ends
function closeConnection() {
    global $conn;
    if ($conn) {
        mysqli_close($conn);
    }
}

register_shutdown_function('closeConnection');
?>