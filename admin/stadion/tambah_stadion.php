<?php
include_once("../../config.php");
requireAdmin();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_stadion = mysqli_real_escape_string($conn, strtoupper(trim($_POST['id_stadion'])));
    $nama_stadion = mysqli_real_escape_string($conn, trim($_POST['nama_stadion']));
    $lokasi = mysqli_real_escape_string($conn, trim($_POST['lokasi']));
    $kapasitas = mysqli_real_escape_string($conn, trim($_POST['kapasitas']));

    $errors = [];
    
    // Validasi input
    if (strlen($id_stadion) !== 5) {
        $errors[] = "ID Stadion harus 5 karakter!";
    }
    
    if (empty($nama_stadion)) {
        $errors[] = "Nama stadion harus diisi!";
    }
    
    if (empty($lokasi)) {
        $errors[] = "Lokasi harus diisi!";
    }

    if (empty($kapasitas) || $kapasitas < 1 || $kapasitas > 200000) {
        $errors[] = "Kapasitas harus antara 1 - 200,000!";
    }

    // Cek apakah ID Stadion sudah digunakan
    $check_stadion = mysqli_query($conn, "SELECT ID_STADION FROM stadion WHERE ID_STADION = '$id_stadion'");
    if (mysqli_num_rows($check_stadion) > 0) {
        $errors[] = "ID Stadion '$id_stadion' sudah digunakan!";
    }

    if (!empty($errors)) {
        $error = implode("<br>", $errors);
    } else {
        // Insert data stadion (menghapus koma di akhir)
        $query = "INSERT INTO stadion (ID_STADION, NAMA_STADION, LOKASI, KAPASITAS)
                VALUES ('$id_stadion', '$nama_stadion', '$lokasi', '$kapasitas')";
        
        if (mysqli_query($conn, $query)) {
            $success = "Stadion \"$nama_stadion\" berhasil ditambahkan!";
            // Reset form
            $_POST = [];
        } else {
            $error = "Gagal menambah stadion: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Stadion - Admin KickOff</title>
    
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

        .helper-text {
            font-size: 0.875rem;
            color: rgba(255,255,255,0.6);
            margin-top: 0.25rem;
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

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
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

            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Back Button -->
    <a href="daftar_stadion.php" class="back-button">
        <i class="bi bi-arrow-left"></i>
    </a>

    <!-- Main Container -->
    <div class="main-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="form-section">
                        <h1 class="page-title">
                            <i class="bi bi-plus-circle me-2"></i>Tambah Stadion
                        </h1>
                        <p class="page-subtitle">Tambahkan stadion baru ke dalam database KickOff</p>
                        
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>
                                <?php echo $success; ?>
                                <div class="mt-2">
                                    <a href="daftar_stadion.php" class="btn btn-light btn-sm">
                                        <i class="bi bi-list me-1"></i>Lihat Daftar Stadion
                                    </a>
                                    <button type="button" class="btn btn-outline-light btn-sm ms-2" onclick="location.reload()">
                                        <i class="bi bi-plus me-1"></i>Tambah Stadion Lagi
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Info Box -->
                        <div class="info-box">
                            <h6><i class="bi bi-info-circle me-2"></i>Informasi Pengisian</h6>
                            <ul>
                                <li><strong>ID Stadion:</strong> Harus 5 karakter unik (contoh: STD01)</li>
                                <li><strong>Nama Stadion:</strong> Nama resmi stadion sepakbola</li>
                                <li><strong>Lokasi:</strong> Kota/wilayah stadion berada</li>
                                <li><strong>Kapasitas:</strong> 1 - 200,000 penonton</li>
                            </ul>
                        </div>

                        <form method="POST">
                            <div class="mb-4">
                                <label for="id_stadion" class="form-label">
                                    <i class="bi bi-hash me-1"></i>ID Stadion
                                    <span class="required">*</span>
                                </label>
                                <input type="text" class="form-control" id="id_stadion" name="id_stadion" required 
                                        placeholder="STD01" maxlength="5" pattern="[A-Z0-9]{5}" 
                                        title="5 karakter huruf besar dan angka"
                                        value="<?php echo isset($_POST['id_stadion']) ? htmlspecialchars($_POST['id_stadion']) : ''; ?>">
                                <div class="helper-text">5 karakter unik untuk mengidentifikasi stadion</div>
                            </div>

                            <div class="mb-4">
                                <label for="nama_stadion" class="form-label">
                                    <i class="bi bi-buildings me-1"></i>Nama Stadion
                                    <span class="required">*</span>
                                </label>
                                <input type="text" class="form-control" id="nama_stadion" name="nama_stadion" required 
                                        placeholder="Stadion Gelora Bung Karno" maxlength="70"
                                        value="<?php echo isset($_POST['nama_stadion']) ? htmlspecialchars($_POST['nama_stadion']) : ''; ?>">
                                <div class="helper-text">Nama resmi stadion sepakbola</div>
                            </div>

                            <div class="form-row mb-4">
                                <div>
                                    <label for="lokasi" class="form-label">
                                        <i class="bi bi-geo-alt me-1"></i>Lokasi
                                        <span class="required">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="lokasi" name="lokasi" required 
                                            placeholder="Jakarta" maxlength="70"
                                            value="<?php echo isset($_POST['lokasi']) ? htmlspecialchars($_POST['lokasi']) : ''; ?>">
                                    <div class="helper-text">Kota/wilayah stadion</div>
                                </div>

                                <div>
                                    <label for="kapasitas" class="form-label">
                                        <i class="bi bi-people me-1"></i>Kapasitas
                                        <span class="required">*</span>
                                    </label>
                                    <input type="number" class="form-control" id="kapasitas" name="kapasitas" required 
                                            placeholder="50000" min="1" max="200000"
                                            value="<?php echo isset($_POST['kapasitas']) ? htmlspecialchars($_POST['kapasitas']) : ''; ?>">
                                    <div class="helper-text">Jumlah penonton</div>
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn-submit">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Tambah Stadion
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
        // Auto uppercase for ID Stadion
        document.getElementById('id_stadion').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const idStadion = document.getElementById('id_stadion').value;
            const namaStadion = document.getElementById('nama_stadion').value;
            const lokasi = document.getElementById('lokasi').value;
            const kapasitas = document.getElementById('kapasitas').value;

            if (idStadion.length !== 5) {
                alert('ID Stadion harus 5 karakter!');
                e.preventDefault();
                return;
            }

            if (!namaStadion.trim()) {
                alert('Nama stadion harus diisi!');
                e.preventDefault();
                return;
            }

            if (!lokasi.trim()) {
                alert('Lokasi harus diisi!');
                e.preventDefault();
                return;
            }

            if (!kapasitas || kapasitas < 1 || kapasitas > 200000) {
                alert('Kapasitas harus antara 1 - 200,000!');
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