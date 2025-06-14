<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - KickOff</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@300;400;500;600;700;800;900&family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style\style_register.css">
</head>
<body>
    <a href="login.php" class="back-home">
        <i class="bi bi-arrow-left me-2"></i>
        Return Back
    </a>

    <div class="register-container">
        <div class="register-header">
            <h1>Register</h1>
            <p>Buat Akun Baru KickOff</p>
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
        
        $errors = array();
        $success = '';
        
        if (isset($_POST['register'])) {
            $username = sanitizeInput($_POST['username']);
            $email = sanitizeInput($_POST['email']);
            $full_name = sanitizeInput($_POST['full_name']);
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            
            // Validasi Username
            if (empty($username)) {
                $errors[] = "Username tidak boleh kosong";
            } elseif (strlen($username) < 3) {
                $errors[] = "Username minimal 3 karakter";
            } elseif (strlen($username) > 50) {
                $errors[] = "Username maksimal 50 karakter";
            } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
                $errors[] = "Username hanya boleh mengandung huruf, angka, dan underscore";
            }
            
            // Validasi Email
            if (empty($email)) {
                $errors[] = "Email tidak boleh kosong";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Format email tidak valid";
            } elseif (strlen($email) > 100) {
                $errors[] = "Email terlalu panjang (maksimal 100 karakter)";
            }
            
            // Validasi Nama Lengkap
            if (empty($full_name)) {
                $errors[] = "Nama lengkap tidak boleh kosong";
            } elseif (strlen($full_name) < 2) {
                $errors[] = "Nama lengkap minimal 2 karakter";
            } elseif (strlen($full_name) > 100) {
                $errors[] = "Nama lengkap maksimal 100 karakter";
            }
            
            // Validasi Password
            if (empty($password)) {
                $errors[] = "Password tidak boleh kosong";
            } elseif (strlen($password) < 6) {
                $errors[] = "Password minimal 6 karakter";
            } elseif (strlen($password) > 255) {
                $errors[] = "Password terlalu panjang";
            }
            
            // Validasi Konfirmasi Password
            if ($password !== $confirm_password) {
                $errors[] = "Konfirmasi password tidak cocok";
            }
            
            // Cek apakah username dan email sudah ada
            if (empty($errors)) {
                $check_query = "SELECT username, email FROM users WHERE username = ? OR email = ?";
                $check_stmt = mysqli_prepare($conn, $check_query);
                mysqli_stmt_bind_param($check_stmt, "ss", $username, $email);
                mysqli_stmt_execute($check_stmt);
                $check_result = mysqli_stmt_get_result($check_stmt);
                
                if (mysqli_num_rows($check_result) > 0) {
                    while ($existing_user = mysqli_fetch_assoc($check_result)) {
                        if ($existing_user['username'] == $username) {
                            $errors[] = "Username '$username' sudah terdaftar";
                        }
                        if ($existing_user['email'] == $email) {
                            $errors[] = "Email '$email' sudah terdaftar";
                        }
                    }
                }
                mysqli_stmt_close($check_stmt);
            }
            
            // Jika tidak ada error, lakukan registrasi
            if (empty($errors)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insert_query = "INSERT INTO users (username, email, full_name, password, role, status) VALUES (?, ?, ?, ?, 'user', 'active')";
                $insert_stmt = mysqli_prepare($conn, $insert_query);
                mysqli_stmt_bind_param($insert_stmt, "ssss", $username, $email, $full_name, $hashed_password);
                
                if (mysqli_stmt_execute($insert_stmt)) {
                    mysqli_stmt_close($insert_stmt);
                    // Redirect ke login dengan success message
                    header("Location: login.php?registered=success");
                    exit();
                } else {
                    $errors[] = "Terjadi kesalahan saat mendaftar. Silakan coba lagi.";
                }
            }
        }
        ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Terjadi kesalahan:</strong>
                <ul style="margin: 0.5rem 0 0 0; padding-left: 20px;">
                    <?php foreach($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">
                    <i class="bi bi-person me-2"></i>Username
                </label>
                <input type="text" name="username" id="username" 
                        placeholder="Masukkan username unik"
                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                        maxlength="50" required>
                <small class="form-text text-muted">3-50 karakter, hanya huruf, angka, dan underscore</small>
            </div>
            
            <div class="form-group">
                <label for="email">
                    <i class="bi bi-envelope me-2"></i>Email
                </label>
                <input type="email" name="email" id="email" 
                        placeholder="Masukkan alamat email"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                        maxlength="100" required>
            </div>
            
            <div class="form-group">
                <label for="full_name">
                    <i class="bi bi-person-badge me-2"></i>Nama Lengkap
                </label>
                <input type="text" name="full_name" id="full_name" 
                        placeholder="Masukkan nama lengkap"
                        value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" 
                        maxlength="100" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password">
                        <i class="bi bi-lock me-2"></i>Password
                    </label>
                    <input type="password" name="password" id="password" 
                            placeholder="Minimal 6 karakter" required>
                    <div id="password-strength" class="password-strength mt-2"></div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">
                        <i class="bi bi-lock-fill me-2"></i>Konfirmasi Password
                    </label>
                    <input type="password" name="confirm_password" id="confirm_password" 
                            placeholder="Ulangi password" required>
                    <div id="password-match" class="password-match mt-2"></div>
                </div>
            </div>
            
            <button type="submit" name="register" class="btn">
                <i class="bi bi-person-plus me-2"></i>
                Buat Akun
            </button>
        </form>
        
        <div class="login-link">
            <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('password-strength');
            
            if (password.length === 0) {
                strengthDiv.innerHTML = '';
                return;
            }
            
            let strength = 0;
            let feedback = [];
            
            if (password.length >= 6) strength++;
            else feedback.push('Minimal 6 karakter');
            
            if (/[a-z]/.test(password)) strength++;
            else feedback.push('Perlu huruf kecil');
            
            if (/[A-Z]/.test(password)) strength++;
            else feedback.push('Perlu huruf besar');
            
            if (/[0-9]/.test(password)) strength++;
            else feedback.push('Perlu angka');
            
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            else feedback.push('Perlu karakter khusus');
            
            let level = strength < 3 ? 'weak' : (strength < 4 ? 'medium' : 'strong');
            let color = level === 'weak' ? 'danger' : (level === 'medium' ? 'warning' : 'success');
            let text = level === 'weak' ? 'Lemah' : (level === 'medium' ? 'Sedang' : 'Kuat');
            
            strengthDiv.innerHTML = `<small class="text-${color}">Password: ${text}</small>`;
        });
        
        // Password match checker
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const matchDiv = document.getElementById('password-match');
            
            if (confirmPassword.length === 0) {
                matchDiv.innerHTML = '';
                return;
            }
            
            if (password === confirmPassword) {
                matchDiv.innerHTML = '<small class="text-success">Password cocok ✓</small>';
            } else {
                matchDiv.innerHTML = '<small class="text-danger">Password tidak cocok ✗</small>';
            }
        });
    </script>
</body>
</html>