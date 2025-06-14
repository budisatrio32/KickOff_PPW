<?php
include_once("../../config.php");
requireAdmin();

// Get ID liga from URL
$id_liga = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

if (empty($id_liga)) {
    header("Location: daftar_liga.php");
    exit();
}

// Ambil data liga yang akan di-edit
$liga_query = "SELECT * FROM liga WHERE ID_LIGA = '$id_liga'";
$liga_result = mysqli_query($conn, $liga_query);
$liga_data = mysqli_fetch_assoc($liga_result);

if (!$liga_data) {
    echo "<script>
            alert('Liga tidak ditemukan!');
            window.location.href = 'daftar_liga.php';
            </script>";
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_liga = mysqli_real_escape_string($conn, trim($_POST['nama_liga']));

    $errors = [];
    
    // Validasi input
    if (empty($nama_liga)) {
        $errors[] = "Nama liga harus diisi!";
    }

    if (!empty($errors)) {
        $error = implode("<br>", $errors);
    } else {
        // Buat direktori upload jika belum ada
        $upload_dir = "../../uploads/leagues/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $logo_name = $liga_data['LOGO']; // Keep existing logo by default
        
        // Handle file upload
        if (!empty($_FILES['logo_liga']['name'])) {
            $logo_tmp = $_FILES['logo_liga']['tmp_name'];
            $logo_size = $_FILES['logo_liga']['size'];
            $file_extension = strtolower(pathinfo($_FILES['logo_liga']['name'], PATHINFO_EXTENSION));
            
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($file_extension, $allowed_types)) {
                $errors[] = "Format file tidak diizinkan! Gunakan JPG, PNG, GIF, atau SVG.";
            } elseif ($logo_size > $max_size) {
                $errors[] = "Ukuran file terlalu besar! Maksimal 5MB.";
            } else {
                $unique_name = $id_liga . '_' . time() . '.' . $file_extension;
                
                if (move_uploaded_file($logo_tmp, $upload_dir . $unique_name)) {
                    // Hapus logo lama jika ada
                    if (!empty($liga_data['LOGO']) && file_exists($upload_dir . $liga_data['LOGO'])) {
                        unlink($upload_dir . $liga_data['LOGO']);
                    }
                    $logo_name = $unique_name;
                } else {
                    $errors[] = "Gagal upload logo!";
                }
            }
        }

        if (empty($errors)) {
            $query = "UPDATE liga SET 
                        NAMA_LIGA = '$nama_liga', 
                        LOGO = " . ($logo_name ? "'$logo_name'" : "NULL") . "
                        WHERE ID_LIGA = '$id_liga'";
            
            if (mysqli_query($conn, $query)) {
                $success = "Data liga \"$nama_liga\" berhasil diperbarui!";
                // Update data untuk form
                $liga_data['NAMA_LIGA'] = $nama_liga;
                $liga_data['LOGO'] = $logo_name;
            } else {
                $error = "Gagal memperbarui data liga: " . mysqli_error($conn);
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
    <title>Edit Liga - Admin KickOff</title>
    
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

        .info-box ul {
            margin: 0;
            padding-left: 1rem;
        }

        .info-box li {
            margin-bottom: 0.5rem;
            color: rgba(255,255,255,0.9);
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
    <a href="daftar_liga.php" class="back-button">
        <i class="bi bi-arrow-left"></i>
    </a>

    <!-- Main Container -->
    <div class="main-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="form-section">
                        <h1 class="page-title">
                            <i class="bi bi-pencil-square me-2"></i>Edit Liga
                        </h1>
                        <p class="page-subtitle">Perbarui data liga dalam database KickOff</p>
                        
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>
                                <?php echo $success; ?>
                                <div class="mt-2">
                                    <a href="daftar_liga.php" class="btn btn-light btn-sm">
                                        <i class="bi bi-list me-1"></i>Kembali ke Daftar
                                    </a>
                                    <a href="edit_liga.php?id=<?php echo urlencode($id_liga); ?>" class="btn btn-outline-light btn-sm ms-2">
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

                        <!-- Current Data Info -->
                        <div class="info-box">
                            <h6><i class="bi bi-trophy-fill me-2"></i>Data Liga Saat Ini</h6>
                            <div class="current-data">
                                <strong>ID:</strong> <?php echo htmlspecialchars($liga_data['ID_LIGA']); ?> | 
                                <strong>Nama:</strong> <?php echo htmlspecialchars($liga_data['NAMA_LIGA']); ?>
                            </div>
                            <?php if (!empty($liga_data['LOGO']) && file_exists("../../uploads/leagues/".$liga_data['LOGO'])): ?>
                            <div class="current-logo">
                                <img src="../../uploads/leagues/<?php echo htmlspecialchars($liga_data['LOGO']); ?>" 
                                    alt="Logo <?php echo htmlspecialchars($liga_data['NAMA_LIGA']); ?>">
                                <span><strong>Logo saat ini</strong></span>
                            </div>
                            <?php else: ?>
                            <div class="current-data">
                                <strong>Logo:</strong> Tidak ada logo
                            </div>
                            <?php endif; ?>
                        </div>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-4">
                                <label for="nama_liga" class="form-label">
                                    <i class="bi bi-trophy me-1"></i>Nama Liga
                                    <span class="required">*</span>
                                </label>
                                <input type="text" class="form-control" id="nama_liga" name="nama_liga" required 
                                    placeholder="Premier League" maxlength="50"
                                    value="<?php echo htmlspecialchars($liga_data['NAMA_LIGA']); ?>">
                                <div class="helper-text">Nama resmi liga sepakbola</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="bi bi-image me-1"></i>Logo Liga
                                </label>
                                <div class="file-upload-wrapper">
                                    <label for="logo_liga" class="file-upload-btn">
                                        <i class="bi bi-cloud-upload me-2"></i>
                                        Ubah Logo
                                    </label>
                                    <input type="file" id="logo_liga" name="logo_liga" class="file-upload-input" 
                                            accept="image/jpeg,image/png,image/gif,image/svg+xml"
                                            onchange="previewImage(this)">
                                    <div class="helper-text">Format: JPG, PNG, GIF, SVG | Maksimal: 5MB (Kosongkan jika tidak diubah)</div>
                                </div>
                                <div id="preview-container" class="preview-container" style="display: none;">
                                    <img id="preview-image" class="preview-image" alt="Preview Logo">
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn-submit">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Perbarui Data Liga
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
            const namaLiga = document.getElementById('nama_liga').value;

            if (!namaLiga.trim()) {
                alert('Nama liga harus diisi!');
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