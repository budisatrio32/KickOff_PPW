<?php
include_once("../../config.php");
requireAdmin();

// Get ID stadion from URL
$id_stadion = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

if (empty($id_stadion)) {
    header("Location: daftar_stadion.php");
    exit();
}

// Ambil data stadion yang akan di-edit
$stadion_query = "SELECT * FROM stadion WHERE ID_STADION = '$id_stadion'";
$stadion_result = mysqli_query($conn, $stadion_query);
$stadion_data = mysqli_fetch_assoc($stadion_result);

if (!$stadion_data) {
    echo "<script>
            alert('Stadion tidak ditemukan!');
            window.location.href = 'daftar_stadion.php';
          </script>";
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_stadion = mysqli_real_escape_string($conn, trim($_POST['nama_stadion']));
    $lokasi = mysqli_real_escape_string($conn, trim($_POST['lokasi']));
    $kapasitas = mysqli_real_escape_string($conn, trim($_POST['kapasitas']));

    $errors = [];
    
    // Validasi input
    if (empty($nama_stadion)) {
        $errors[] = "Nama stadion harus diisi!";
    }
    
    if (empty($lokasi)) {
        $errors[] = "Lokasi harus diisi!";
    }

    if (empty($kapasitas) || $kapasitas < 1 || $kapasitas > 200000) {
        $errors[] = "Kapasitas harus antara 1 - 200,000!";
    }

    if (!empty($errors)) {
        $error = implode("<br>", $errors);
    } else {
        // Update data stadion (menghapus koma dan WHERE yang salah)
        $query = "UPDATE stadion SET 
                    NAMA_STADION = '$nama_stadion', 
                    LOKASI = '$lokasi',
                    KAPASITAS = '$kapasitas'
                  WHERE ID_STADION = '$id_stadion'";
        
        if (mysqli_query($conn, $query)) {
            $success = "Data stadion \"$nama_stadion\" berhasil diperbarui!";
            // Update data untuk form
            $stadion_data['NAMA_STADION'] = $nama_stadion;
            $stadion_data['LOKASI'] = $lokasi;
            $stadion_data['KAPASITAS'] = $kapasitas;
        } else {
            $error = "Gagal memperbarui data stadion: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Stadion - Admin KickOff</title>
    
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
            max-width: 700px;
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
            background: linear-gradient(135deg, #2a2a2a 0%, #3a3a3a 100%);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            color: white;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .info-box h6 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            color: var(--accent-red);
        }

        .current-data {
            background: rgba(255,255,255,0.05);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.5rem;
        }

        .current-data strong {
            color: var(--accent-red);
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
                            <i class="bi bi-pencil-square me-2"></i>Edit Stadion
                        </h1>
                        <p class="page-subtitle">Perbarui data stadion dalam database KickOff</p>
                        
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>
                                <?php echo $success; ?>
                                <div class="mt-2">
                                    <a href="daftar_stadion.php" class="btn btn-light btn-sm">
                                        <i class="bi bi-list me-1"></i>Kembali ke Daftar
                                    </a>
                                    <a href="edit_stadion.php?id=<?php echo urlencode($id_stadion); ?>" class="btn btn-outline-light btn-sm ms-2">
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
                            <h6><i class="bi bi-buildings-fill me-2"></i>Data Stadion Saat Ini</h6>
                            <div class="current-data">
                                <strong>ID:</strong> <?php echo htmlspecialchars($stadion_data['ID_STADION']); ?> | 
                                <strong>Nama:</strong> <?php echo htmlspecialchars($stadion_data['NAMA_STADION']); ?> | 
                                <strong>Lokasi:</strong> <?php echo htmlspecialchars($stadion_data['LOKASI']); ?> | 
                                <strong>Kapasitas:</strong> <?php echo number_format($stadion_data['KAPASITAS']); ?> kursi
                            </div>
                        </div>

                        <form method="POST">
                            <div class="mb-4">
                                <label for="nama_stadion" class="form-label">
                                    <i class="bi bi-buildings me-1"></i>Nama Stadion
                                    <span class="required">*</span>
                                </label>
                                <input type="text" class="form-control" id="nama_stadion" name="nama_stadion" required 
                                        placeholder="Stadion Gelora Bung Karno" maxlength="70"
                                        value="<?php echo htmlspecialchars($stadion_data['NAMA_STADION']); ?>">
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
                                            value="<?php echo htmlspecialchars($stadion_data['LOKASI']); ?>">
                                    <div class="helper-text">Kota/wilayah stadion</div>
                                </div>

                                <div>
                                    <label for="kapasitas" class="form-label">
                                        <i class="bi bi-people me-1"></i>Kapasitas
                                        <span class="required">*</span>
                                    </label>
                                    <input type="number" class="form-control" id="kapasitas" name="kapasitas" required 
                                            placeholder="50000" min="1" max="200000"
                                            value="<?php echo htmlspecialchars($stadion_data['KAPASITAS']); ?>">
                                    <div class="helper-text">Jumlah penonton</div>
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn-submit">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Perbarui Data Stadion
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
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const namaStadion = document.getElementById('nama_stadion').value;
            const lokasi = document.getElementById('lokasi').value;
            const kapasitas = document.getElementById('kapasitas').value;

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