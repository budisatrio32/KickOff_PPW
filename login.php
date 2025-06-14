<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KickOff</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@300;400;500;600;700;800;900&family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style\style_login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Login</h1>
            <p>Masuk ke Sistem KickOff</p>
        </div>
        
        <?php
        include_once("config.php");
        
        // Redirect jika sudah login
        if (isLoggedIn()) {
            if (isAdmin()) {
                header("Location: adminpage.php");
            } else {
                header("Location: index.php");
            }
            exit();
        }
        
        $error = '';
        $success = '';
        
        if (isset($_POST['login'])) {
            $username = sanitizeInput($_POST['username']);
            $password = $_POST['password'];
            
            // Validasi input
            if (empty($username) || empty($password)) {
                $error = "Username dan password harus diisi!";
            } else {
                // Query untuk mencari user berdasarkan username atau email
                $query = "SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'active'";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "ss", $username, $username);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                if (mysqli_num_rows($result) == 1) {
                    $user = mysqli_fetch_assoc($result);
                    
                    // Verifikasi password
                    if (password_verify($password, $user['password'])) {
                        // Set session
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['full_name'] = $user['full_name'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['last_login'] = date('Y-m-d H:i:s');
                        
                        // Update last login di database
                        $update_query = "UPDATE users SET updated_at = CURRENT_TIMESTAMP WHERE id = ?";
                        $update_stmt = mysqli_prepare($conn, $update_query);
                        mysqli_stmt_bind_param($update_stmt, "i", $user['id']);
                        mysqli_stmt_execute($update_stmt);
                        
                        // Redirect berdasarkan role
                        if ($user['role'] === 'admin') {
                            header("Location: adminpage.php");
                        } else {
                            header("Location: index.php");
                        }
                        exit();
                    } else {
                        $error = "Username atau password salah!";
                    }
                } else {
                    $error = "Username atau password salah!";
                }
                
                mysqli_stmt_close($stmt);
            }
        }
        
        if (isset($_GET['registered']) && $_GET['registered'] == 'success') {
            $success = "Registrasi berhasil! Silakan login dengan akun baru Anda.";
        }
        ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">
                    <i class="bi bi-person me-2"></i>Username atau Email
                </label>
                <input type="text" name="username" id="username" 
                        placeholder="Masukkan username atau email"
                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                        required>
            </div>
            
            <div class="form-group">
                <label for="password">
                    <i class="bi bi-lock me-2"></i>Password
                </label>
                <input type="password" name="password" id="password" 
                        placeholder="Masukkan password" required>
            </div>
            
            <button type="submit" name="login" class="btn">
                <i class="bi bi-box-arrow-in-right me-2"></i>
                Login
            </button>
        </form>
        
        <div class="register-link">
            <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
        </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>