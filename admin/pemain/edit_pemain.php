<?php
include_once("../../config.php");
requireAdmin();

// Get ID pemain from URL
$id_pemain = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

if (empty($id_pemain)) {
    header("Location: daftar_pemain.php");
    exit();
}

// Ambil data pemain yang akan di-edit
$pemain_query = "SELECT p.*, t.NAMA_TIM FROM pemain p LEFT JOIN tim t ON p.ID_TIM = t.ID_TIM WHERE p.ID_PEMAIN = '$id_pemain'";
$pemain_result = mysqli_query($conn, $pemain_query);
$pemain_data = mysqli_fetch_assoc($pemain_result);

if (!$pemain_data) {
    echo "<script>
            alert('Pemain tidak ditemukan!');
            window.location.href = 'daftar_pemain.php';
            </script>";
    exit();
}

// Ambil data tim untuk dropdown
$tim_query = "SELECT ID_TIM, NAMA_TIM FROM tim ORDER BY NAMA_TIM ASC";
$tim_result = mysqli_query($conn, $tim_query);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_tim = mysqli_real_escape_string($conn, $_POST['id_tim']);
    $nama_pemain = mysqli_real_escape_string($conn, trim($_POST['nama_pemain']));
    $nomor_punggung = mysqli_real_escape_string($conn, trim($_POST['nomor_punggung']));
    $posisi = mysqli_real_escape_string($conn, trim($_POST['posisi']));

    $errors = [];
    
    // Validasi input
    if (empty($id_tim)) {
        $errors[] = "Tim harus dipilih!";
    }
    
    if (empty($nama_pemain)) {
        $errors[] = "Nama pemain harus diisi!";
    }

    if (empty($nomor_punggung) || $nomor_punggung < 1 || $nomor_punggung > 99) {
        $errors[] = "Nomor punggung harus antara 1-99!";
    }

    if (empty($posisi)) {
        $errors[] = "Posisi harus dipilih!";
    }

    // Cek apakah nomor punggung sudah digunakan di tim yang sama (kecuali oleh pemain ini sendiri)
    $check_nomor = mysqli_query($conn, "SELECT ID_PEMAIN FROM pemain WHERE ID_TIM = '$id_tim' AND NOMOR_PUNGGUNG = '$nomor_punggung' AND ID_PEMAIN != '$id_pemain'");
    if (mysqli_num_rows($check_nomor) > 0) {
        $errors[] = "Nomor punggung $nomor_punggung sudah digunakan di tim ini!";
    }

    // Cek apakah tim valid
    $check_tim = mysqli_query($conn, "SELECT ID_TIM FROM tim WHERE ID_TIM = '$id_tim'");
    if (mysqli_num_rows($check_tim) == 0) {
        $errors[] = "Tim yang dipilih tidak valid!";
    }

    if (!empty($errors)) {
        $error = implode("<br>", $errors);
    } else {
        $query = "UPDATE pemain SET 
                    ID_TIM = '$id_tim', 
                    NAMA_PEMAIN = '$nama_pemain', 
                    NOMOR_PUNGGUNG = '$nomor_punggung', 
                    POSISI = '$posisi'
                    WHERE ID_PEMAIN = '$id_pemain'";
        
        if (mysqli_query($conn, $query)) {
            $success = "Data pemain \"$nama_pemain\" berhasil diperbarui!";
            // Update data untuk form
            $pemain_data['ID_TIM'] = $id_tim;
            $pemain_data['NAMA_PEMAIN'] = $nama_pemain;
            $pemain_data['NOMOR_PUNGGUNG'] = $nomor_punggung;
            $pemain_data['POSISI'] = $posisi;
        } else {
            $error = "Gagal memperbarui data pemain: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pemain - Admin KickOff</title>
    
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

        .form-select option {
            background: #2a2a2a;
            color: white;
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
    <a href="daftar_pemain.php" class="back-button">
        <i class="bi bi-arrow-left"></i>
    </a>

    <!-- Main Container -->
    <div class="main-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="form-section">
                        <h1 class="page-title">
                            <i class="bi bi-pencil-square me-2"></i>Edit Pemain
                        </h1>
                        <p class="page-subtitle">Perbarui data pemain dalam database KickOff</p>
                        
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>
                                <?php echo $success; ?>
                                <div class="mt-2">
                                    <a href="daftar_pemain.php" class="btn btn-light btn-sm">
                                        <i class="bi bi-list me-1"></i>Kembali ke Daftar
                                    </a>
                                    <a href="edit_pemain.php?id=<?php echo urlencode($id_pemain); ?>" class="btn btn-outline-light btn-sm ms-2">
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
                            <h6><i class="bi bi-person-circle me-2"></i>Data Pemain Saat Ini</h6>
                            <div class="current-data">
                                <strong>ID:</strong> <?php echo htmlspecialchars($pemain_data['ID_PEMAIN']); ?> | 
                                <strong>Nama:</strong> <?php echo htmlspecialchars($pemain_data['NAMA_PEMAIN']); ?> | 
                                <strong>No:</strong> <?php echo htmlspecialchars($pemain_data['NOMOR_PUNGGUNG']); ?> | 
                                <strong>Posisi:</strong> <?php echo htmlspecialchars($pemain_data['POSISI']); ?>
                            </div>
                            <div class="current-data">
                                <strong>Tim:</strong> <?php echo htmlspecialchars($pemain_data['ID_TIM']); ?> - <?php echo htmlspecialchars($pemain_data['NAMA_TIM'] ?? 'Tim tidak ditemukan'); ?>
                            </div>
                        </div>

                        <form method="POST">
                            <div class="mb-4">
                                <label for="id_tim" class="form-label">
                                    <i class="bi bi-shield-check me-1"></i>Tim
                                    <span class="required">*</span>
                                </label>
                                <select class="form-select" id="id_tim" name="id_tim" required>
                                    <option value="">-- Pilih Tim --</option>
                                    <?php 
                                    mysqli_data_seek($tim_result, 0); // Reset pointer
                                    while($tim = mysqli_fetch_assoc($tim_result)): 
                                        $selected = ($pemain_data['ID_TIM'] == $tim['ID_TIM']) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo htmlspecialchars($tim['ID_TIM']); ?>" <?php echo $selected; ?>>
                                            <?php echo htmlspecialchars($tim['ID_TIM'] . ' - ' . $tim['NAMA_TIM']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <div class="helper-text">Pilih tim dimana pemain akan bermain</div>
                            </div>

                            <div class="form-row mb-4">
                                <div>
                                    <label for="nama_pemain" class="form-label">
                                        <i class="bi bi-person me-1"></i>Nama Pemain
                                        <span class="required">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="nama_pemain" name="nama_pemain" required 
                                           placeholder="Masukkan nama pemain" maxlength="100"
                                           value="<?php echo htmlspecialchars($pemain_data['NAMA_PEMAIN']); ?>">
                                    <div class="helper-text">Nama lengkap pemain</div>
                                </div>

                                <div>
                                    <label for="nomor_punggung" class="form-label">
                                        <i class="bi bi-123 me-1"></i>Nomor Punggung
                                        <span class="required">*</span>
                                    </label>
                                    <input type="number" class="form-control" id="nomor_punggung" name="nomor_punggung" required 
                                           placeholder="1-99" min="1" max="99"
                                           value="<?php echo htmlspecialchars($pemain_data['NOMOR_PUNGGUNG']); ?>">
                                    <div class="helper-text">1-99, unik dalam tim</div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="posisi" class="form-label">
                                    <i class="bi bi-geo-alt me-1"></i>Posisi
                                    <span class="required">*</span>
                                </label>
                                <select class="form-select" id="posisi" name="posisi" required>
                                    <option value="">-- Pilih Posisi --</option>
                                    <option value="Goalkeeper" <?php echo ($pemain_data['POSISI'] == 'Goalkeeper') ? 'selected' : ''; ?>>Goalkeeper (GK)</option>
                                    <option value="Center Back" <?php echo ($pemain_data['POSISI'] == 'Center Back') ? 'selected' : ''; ?>>Center Back (CB)</option>
                                    <option value="Left Back" <?php echo ($pemain_data['POSISI'] == 'Left Back') ? 'selected' : ''; ?>>Left Back (LB)</option>
                                    <option value="Right Back" <?php echo ($pemain_data['POSISI'] == 'Right Back') ? 'selected' : ''; ?>>Right Back (RB)</option>
                                    <option value="Defensive Midfielder" <?php echo ($pemain_data['POSISI'] == 'Defensive Midfielder') ? 'selected' : ''; ?>>Defensive Midfielder (CDM)</option>
                                    <option value="Central Midfielder" <?php echo ($pemain_data['POSISI'] == 'Central Midfielder') ? 'selected' : ''; ?>>Central Midfielder (CM)</option>
                                    <option value="Attacking Midfielder" <?php echo ($pemain_data['POSISI'] == 'Attacking Midfielder') ? 'selected' : ''; ?>>Attacking Midfielder (CAM)</option>
                                    <option value="Left Winger" <?php echo ($pemain_data['POSISI'] == 'Left Winger') ? 'selected' : ''; ?>>Left Winger (LW)</option>
                                    <option value="Right Winger" <?php echo ($pemain_data['POSISI'] == 'Right Winger') ? 'selected' : ''; ?>>Right Winger (RW)</option>
                                    <option value="Striker" <?php echo ($pemain_data['POSISI'] == 'Striker') ? 'selected' : ''; ?>>Striker (ST)</option>
                                </select>
                                <div class="helper-text">Posisi utama pemain di lapangan</div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn-submit">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Perbarui Data Pemain
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
            const tim = document.getElementById('id_tim').value;
            const namaPemain = document.getElementById('nama_pemain').value;
            const nomorPunggung = document.getElementById('nomor_punggung').value;
            const posisi = document.getElementById('posisi').value;

            if (!tim) {
                alert('Tim harus dipilih!');
                e.preventDefault();
                return;
            }

            if (!namaPemain.trim()) {
                alert('Nama pemain harus diisi!');
                e.preventDefault();
                return;
            }

            if (!nomorPunggung || nomorPunggung < 1 || nomorPunggung > 99) {
                alert('Nomor punggung harus antara 1-99!');
                e.preventDefault();
                return;
            }

            if (!posisi) {
                alert('Posisi harus dipilih!');
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