<?php
include_once("../../config.php");
requireAdmin();

// Ambil ID tim dari parameter URL
$id_tim = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

if (empty($id_tim)) {
    header("Location: daftar_tim.php?error=ID tim tidak valid");
    exit();
}

// Ambil data tim yang akan diedit
$tim_query = "SELECT t.*, l.NAMA_LIGA, s.NAMA_STADION 
              FROM tim t 
              LEFT JOIN liga l ON t.ID_LIGA = l.ID_LIGA 
              LEFT JOIN stadion s ON t.ID_STADION = s.ID_STADION 
              WHERE t.ID_TIM = '$id_tim'";
$tim_result = mysqli_query($conn, $tim_query);

if (!$tim_result || mysqli_num_rows($tim_result) == 0) {
    header("Location: daftar_tim.php?error=Tim tidak ditemukan");
    exit();
}

$tim_data = mysqli_fetch_assoc($tim_result);

// Ambil data liga untuk dropdown
$liga_query = "SELECT ID_LIGA, NAMA_LIGA FROM liga ORDER BY NAMA_LIGA ASC";
$liga_result = mysqli_query($conn, $liga_query);

// Ambil data stadion untuk dropdown
$stadion_query = "SELECT ID_STADION, NAMA_STADION, LOKASI FROM stadion ORDER BY NAMA_STADION ASC";
$stadion_result = mysqli_query($conn, $stadion_query);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_liga = mysqli_real_escape_string($conn, $_POST['id_liga']);
    $id_stadion = mysqli_real_escape_string($conn, $_POST['id_stadion']);
    $nama_tim = mysqli_real_escape_string($conn, trim($_POST['nama_tim']));
    $pelatih = mysqli_real_escape_string($conn, trim($_POST['pelatih']));

    $errors = [];
    
    // Validasi input
    if (empty($id_liga)) {
        $errors[] = "Liga harus dipilih!";
    }
    
    if (empty($id_stadion)) {
        $errors[] = "Stadion harus dipilih!";
    }

    if (empty($nama_tim)) {
        $errors[] = "Nama tim harus diisi!";
    }

    if (strlen($nama_tim) > 70) {
        $errors[] = "Nama tim maksimal 70 karakter!";
    }

    if (empty($pelatih)) {
        $errors[] = "Nama pelatih harus diisi!";
    }

    if (strlen($pelatih) > 70) {
        $errors[] = "Nama pelatih maksimal 70 karakter!";
    }

    // Cek apakah liga dan stadion valid
    if (!empty($id_liga)) {
        $check_liga = mysqli_query($conn, "SELECT ID_LIGA FROM liga WHERE ID_LIGA = '$id_liga'");
        if (mysqli_num_rows($check_liga) == 0) {
            $errors[] = "Liga yang dipilih tidak valid!";
        }
    }

    if (!empty($id_stadion)) {
        $check_stadion = mysqli_query($conn, "SELECT ID_STADION FROM stadion WHERE ID_STADION = '$id_stadion'");
        if (mysqli_num_rows($check_stadion) == 0) {
            $errors[] = "Stadion yang dipilih tidak valid!";
        }
    }

    if (!empty($errors)) {
        $error = implode("<br>", $errors);
    } else {
        // Buat direktori upload jika belum ada
        $upload_dir = "../../uploads/teams/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $logo_name = $tim_data['LOGO_TIM']; // Gunakan logo lama sebagai default
        
        // Handle file upload jika ada file baru
        if (!empty($_FILES['logo_tim']['name'])) {
            $logo_tmp = $_FILES['logo_tim']['tmp_name'];
            $logo_size = $_FILES['logo_tim']['size'];
            $file_extension = strtolower(pathinfo($_FILES['logo_tim']['name'], PATHINFO_EXTENSION));
            
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($file_extension, $allowed_types)) {
                $errors[] = "Format file tidak diizinkan! Gunakan JPG, PNG, GIF, atau SVG.";
            } elseif ($logo_size > $max_size) {
                $errors[] = "Ukuran file terlalu besar! Maksimal 5MB.";
            } else {
                // Generate nama file unik berdasarkan ID tim
                $unique_name = strtolower($id_tim) . '_' . time() . '.' . $file_extension;
                
                if (move_uploaded_file($logo_tmp, $upload_dir . $unique_name)) {
                    // Hapus logo lama jika ada dan berbeda dengan yang baru
                    if (!empty($tim_data['LOGO_TIM']) && $tim_data['LOGO_TIM'] !== $unique_name && file_exists($upload_dir . $tim_data['LOGO_TIM'])) {
                        unlink($upload_dir . $tim_data['LOGO_TIM']);
                    }
                    $logo_name = $unique_name;
                } else {
                    $errors[] = "Gagal upload logo!";
                }
            }
        }

        if (empty($errors)) {
            // Update data tim
            $update_query = "UPDATE tim SET 
                            ID_LIGA = '$id_liga', 
                            ID_STADION = '$id_stadion', 
                            LOGO_TIM = " . ($logo_name ? "'$logo_name'" : "NULL") . ", 
                            NAMA_TIM = '$nama_tim', 
                            PELATIH = '$pelatih'
                            WHERE ID_TIM = '$id_tim'";
            
            if (mysqli_query($conn, $update_query)) {
                $success = "Tim \"$nama_tim\" berhasil diperbarui!";
                
                // Refresh data tim setelah update
                $tim_result = mysqli_query($conn, $tim_query);
                $tim_data = mysqli_fetch_assoc($tim_result);
            } else {
                $error = "Gagal memperbarui tim: " . mysqli_error($conn);
            }
        } else {
            $error = implode("<br>", $errors);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tim - Admin KickOff</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@300;400;500;600;700;800;900&family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-red: #8E1616;
            --dark-bg: #1D1616;
            --light-gray: #EEEEEE;
            --accent-red: #D84040;
        }

        body {
            font-family: 'Urbanist', sans-serif;
            background: linear-gradient(135deg, var(--dark-bg) 0%, #2a2a2a 100%);
            color: var(--light-gray);
            min-height: 100vh;
        }

        .back-button {
            position: fixed;
            top: 2rem;
            left: 2rem;
            z-index: 1000;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-red) 0%, var(--accent-red) 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(142, 22, 22, 0.4);
            transition: all 0.3s ease;
        }

        .back-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(142, 22, 22, 0.6);
            color: white;
            text-decoration: none;
        }

        .main-container {
            padding: 2rem 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-section {
            background: rgba(255,255,255,0.05);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 15px 45px rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
            width: 100%;
            max-width: 600px;
        }

        .page-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 2.5rem;
            color: white;
            text-align: center;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: rgba(255,255,255,0.7);
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        .info-box {
            background: linear-gradient(135deg, var(--primary-red) 0%, var(--accent-red) 100%);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            color: white;
        }

        .info-box h6 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }

        .current-data {
            margin-bottom: 1rem;
        }

        .current-logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 1rem;
        }

        .current-logo img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid rgba(255,255,255,0.2);
        }

        .tim-info-text h5 {
            margin: 0;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
        }

        .tim-info-text small {
            opacity: 0.9;
        }

        .form-label {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            color: white;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }

        .form-label .required {
            color: var(--accent-red);
            margin-left: 0.25rem;
        }

        .form-control, .form-select {
            background: rgba(255,255,255,0.08);
            border: 2px solid rgba(255,255,255,0.2);
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(255,255,255,0.12);
            border-color: var(--accent-red);
            color: white;
            box-shadow: 0 0 0 0.2rem rgba(216, 64, 64, 0.25);
        }

        .form-control::placeholder {
            color: rgba(255,255,255,0.5);
        }

        .form-select option {
            background: #2a2a2a;
            color: white;
        }

        .readonly-field {
            background: rgba(255,255,255,0.03) !important;
            color: rgba(255,255,255,0.7) !important;
            cursor: not-allowed;
        }

        .helper-text {
            font-size: 0.875rem;
            color: rgba(255,255,255,0.6);
            margin-top: 0.25rem;
        }

        .file-upload-wrapper {
            position: relative;
            margin-top: 0.5rem;
        }

        .file-upload-btn {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, var(--primary-red) 0%, var(--accent-red) 100%);
            color: white;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            border: none;
        }

        .file-upload-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(142, 22, 22, 0.4);
        }

        .file-upload-input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary-red) 0%, var(--accent-red) 100%);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1rem;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(142, 22, 22, 0.5);
        }

        .alert {
            border-radius: 10px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            border: none;
        }

        .alert-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }

        .alert-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }

        .preview-container {
            margin-top: 1rem;
            text-align: center;
        }

        .preview-image {
            max-width: 100px;
            max-height: 100px;
            border-radius: 10px;
            border: 2px solid rgba(255,255,255,0.2);
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }
            
            .form-section {
                padding: 2rem 1.5rem;
            }
            
            .page-title {
                font-size: 2rem;
            }
            
            .back-button {
                top: 1rem;
                left: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Back Button -->
    <a href="daftar_tim.php" class="back-button">
        <i class="bi bi-arrow-left"></i>
    </a>

    <!-- Main Container -->
    <div class="main-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="form-section">
                        <h1 class="page-title">
                            <i class="bi bi-pencil-square me-2"></i>Edit Tim
                        </h1>
                        <p class="page-subtitle">Perbarui informasi tim dalam database KickOff</p>
                        
                        <!-- Current Data Info -->
                        <div class="info-box">
                            <h6><i class="bi bi-shield-check me-2"></i>Data Tim Saat Ini</h6>
                            <div class="current-data">
                                <strong>ID:</strong> <?php echo htmlspecialchars($tim_data['ID_TIM']); ?> | 
                                <strong>Nama:</strong> <?php echo htmlspecialchars($tim_data['NAMA_TIM']); ?>
                            </div>
                            <div class="current-data">
                                <strong>Liga:</strong> <?php echo htmlspecialchars($tim_data['NAMA_LIGA'] ?? 'Tidak ada'); ?> | 
                                <strong>Pelatih:</strong> <?php echo htmlspecialchars($tim_data['PELATIH']); ?>
                            </div>
                            <?php if (!empty($tim_data['LOGO_TIM']) && file_exists("../../uploads/teams/".$tim_data['LOGO_TIM'])): ?>
                            <div class="current-logo">
                                <img src="../../uploads/teams/<?php echo htmlspecialchars($tim_data['LOGO_TIM']); ?>" 
                                    alt="Logo <?php echo htmlspecialchars($tim_data['NAMA_TIM']); ?>">
                                <span><strong>Logo saat ini</strong></span>
                            </div>
                            <?php else: ?>
                            <div class="current-data">
                                <strong>Logo:</strong> Tidak ada logo
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>
                                <?php echo $success; ?>
                                <div class="mt-2">
                                    <a href="daftar_tim.php" class="btn btn-light btn-sm">
                                        <i class="bi bi-list me-1"></i>Kembali ke Daftar
                                    </a>
                                    <a href="edit_tim.php?id=<?php echo urlencode($id_tim); ?>" class="btn btn-outline-light btn-sm ms-2">
                                        <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-4">
                                <label for="id_tim" class="form-label">
                                    <i class="bi bi-hash me-1"></i>ID Tim
                                </label>
                                <input type="text" class="form-control readonly-field" id="id_tim" 
                                        value="<?php echo htmlspecialchars($tim_data['ID_TIM']); ?>" readonly>
                                <div class="helper-text">ID Tim tidak dapat diubah</div>
                            </div>

                            <div class="mb-4">
                                <label for="id_liga" class="form-label">
                                    <i class="bi bi-trophy me-1"></i>Liga
                                    <span class="required">*</span>
                                </label>
                                <select class="form-select" id="id_liga" name="id_liga" required>
                                    <option value="">-- Pilih Liga --</option>
                                    <?php 
                                    mysqli_data_seek($liga_result, 0); // Reset pointer
                                    while($liga = mysqli_fetch_assoc($liga_result)): 
                                        $selected = ($tim_data['ID_LIGA'] == $liga['ID_LIGA']) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo htmlspecialchars($liga['ID_LIGA']); ?>" <?php echo $selected; ?>>
                                            <?php echo htmlspecialchars($liga['ID_LIGA'] . ' - ' . $liga['NAMA_LIGA']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <div class="helper-text">Pilih liga tempat tim akan bermain</div>
                            </div>

                            <div class="mb-4">
                                <label for="id_stadion" class="form-label">
                                    <i class="bi bi-geo-alt me-1"></i>Stadion Kandang
                                    <span class="required">*</span>
                                </label>
                                <select class="form-select" id="id_stadion" name="id_stadion" required>
                                    <option value="">-- Pilih Stadion --</option>
                                    <?php 
                                    mysqli_data_seek($stadion_result, 0); // Reset pointer
                                    while($stadion = mysqli_fetch_assoc($stadion_result)): 
                                        $selected = ($tim_data['ID_STADION'] == $stadion['ID_STADION']) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo htmlspecialchars($stadion['ID_STADION']); ?>" <?php echo $selected; ?>>
                                            <?php echo htmlspecialchars($stadion['ID_STADION'] . ' - ' . $stadion['NAMA_STADION'] . ' (' . $stadion['LOKASI'] . ')'); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <div class="helper-text">Pilih stadion kandang tim</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="bi bi-image me-1"></i>Logo Tim
                                </label>
                                <div class="file-upload-wrapper">
                                    <label for="logo_tim" class="file-upload-btn">
                                        <i class="bi bi-cloud-upload me-2"></i>
                                        Ubah Logo
                                    </label>
                                    <input type="file" id="logo_tim" name="logo_tim" class="file-upload-input" 
                                            accept="image/jpeg,image/png,image/gif,image/svg+xml"
                                            onchange="previewImage(this)">
                                    <div class="helper-text">Format: JPG, PNG, GIF, SVG | Maksimal: 5MB (Kosongkan jika tidak diubah)</div>
                                </div>
                                <div id="preview-container" class="preview-container" style="display: none;">
                                    <img id="preview-image" class="preview-image" alt="Preview Logo">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="nama_tim" class="form-label">
                                    <i class="bi bi-shield-check me-1"></i>Nama Tim
                                    <span class="required">*</span>
                                </label>
                                <input type="text" class="form-control" id="nama_tim" name="nama_tim" required 
                                        placeholder="Masukkan nama tim" maxlength="70"
                                        value="<?php echo htmlspecialchars($tim_data['NAMA_TIM']); ?>">
                                <div class="helper-text">Nama lengkap tim sepakbola (maksimal 70 karakter)</div>
                            </div>

                            <div class="mb-4">
                                <label for="pelatih" class="form-label">
                                    <i class="bi bi-person-badge me-1"></i>Nama Pelatih
                                    <span class="required">*</span>
                                </label>
                                <input type="text" class="form-control" id="pelatih" name="pelatih" required 
                                        placeholder="Masukkan nama pelatih" maxlength="70"
                                        value="<?php echo htmlspecialchars($tim_data['PELATIH']); ?>">
                                <div class="helper-text">Nama lengkap pelatih kepala tim (maksimal 70 karakter)</div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn-submit">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Perbarui Data Tim
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Preview image function
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const preview = document.getElementById('preview-image');
                    const container = document.getElementById('preview-container');
                    
                    preview.src = e.target.result;
                    container.style.display = 'block';
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const liga = document.getElementById('id_liga').value;
            const stadion = document.getElementById('id_stadion').value;
            const namaTim = document.getElementById('nama_tim').value;
            const pelatih = document.getElementById('pelatih').value;

            if (!liga) {
                alert('Liga harus dipilih!');
                e.preventDefault();
                return;
            }

            if (!stadion) {
                alert('Stadion harus dipilih!');
                e.preventDefault();
                return;
            }

            if (!namaTim.trim()) {
                alert('Nama tim harus diisi!');
                e.preventDefault();
                return;
            }

            if (namaTim.length > 70) {
                alert('Nama tim maksimal 70 karakter!');
                e.preventDefault();
                return;
            }

            if (!pelatih.trim()) {
                alert('Nama pelatih harus diisi!');
                e.preventDefault();
                return;
            }

            if (pelatih.length > 70) {
                alert('Nama pelatih maksimal 70 karakter!');
                e.preventDefault();
                return;
            }
        });

        // Auto-hide success message after 5 seconds
        <?php if (!empty($success)): ?>
        setTimeout(function() {
            const alertElement = document.querySelector('.alert-success');
            if (alertElement) {
                alertElement.style.transition = 'opacity 0.5s ease';
                alertElement.style.opacity = '0';
                setTimeout(() => alertElement.remove(), 500);
            }
        }, 5000);
        <?php endif; ?>
    </script>
</body>
</html>