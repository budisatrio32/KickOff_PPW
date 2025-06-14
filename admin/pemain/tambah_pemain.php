<?php
include_once("../../config.php");
requireAdmin();

// Ambil data tim untuk dropdown
$tim_query = "SELECT ID_TIM, NAMA_TIM FROM tim ORDER BY NAMA_TIM ASC";
$tim_result = mysqli_query($conn, $tim_query);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pemain = mysqli_real_escape_string($conn, strtoupper(trim($_POST['id_pemain'])));
    $id_tim = mysqli_real_escape_string($conn, $_POST['id_tim']);
    $nama_pemain = mysqli_real_escape_string($conn, trim($_POST['nama_pemain']));
    $nomor_punggung = mysqli_real_escape_string($conn, trim($_POST['nomor_punggung']));
    $posisi = mysqli_real_escape_string($conn, trim($_POST['posisi']));


    $errors = [];
    
    // Validasi input
    if (strlen($id_pemain) !== 5) {
        $errors[] = "ID Pemain harus 5 karakter!";
    }
    
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

    // Cek apakah ID Pemain sudah digunakan
    $check_pemain = mysqli_query($conn, "SELECT ID_PEMAIN FROM pemain WHERE ID_PEMAIN = '$id_pemain'");
    if (mysqli_num_rows($check_pemain) > 0) {
        $errors[] = "ID Pemain '$id_pemain' sudah digunakan!";
    }

    // Cek apakah nomor punggung sudah digunakan di tim yang sama
    $check_nomor = mysqli_query($conn, "SELECT ID_PEMAIN FROM pemain WHERE ID_TIM = '$id_tim' AND NOMOR_PUNGGUNG = '$nomor_punggung'");
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
        $query = "INSERT INTO pemain (ID_PEMAIN, ID_TIM, NAMA_PEMAIN, NOMOR_PUNGGUNG, POSISI)
                VALUES ('$id_pemain', '$id_tim', '$nama_pemain', '$nomor_punggung', '$posisi')";
        
        if (mysqli_query($conn, $query)) {
            $success = "Pemain \"$nama_pemain\" berhasil ditambahkan!";
            // Reset form
            $_POST = [];
        } else {
            $error = "Gagal menambah pemain: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pemain - Admin KickOff</title>
    
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
                            <i class="bi bi-plus-circle me-2"></i>Tambah Pemain
                        </h1>
                        <p class="page-subtitle">Tambahkan pemain baru ke dalam database KickOff</p>
                        
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>
                                <?php echo $success; ?>
                                <div class="mt-2">
                                    <a href="daftar_pemain.php" class="btn btn-light btn-sm">
                                        <i class="bi bi-list me-1"></i>Lihat Daftar Pemain
                                    </a>
                                    <button type="button" class="btn btn-outline-light btn-sm ms-2" onclick="location.reload()">
                                        <i class="bi bi-plus me-1"></i>Tambah Pemain Lagi
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
                                <li><strong>ID Pemain:</strong> Harus 5 karakter unik (contoh: PLY01)</li>
                                <li><strong>Nomor Punggung:</strong> 1-99, unik dalam satu tim</li>
                                <li><strong>Tim:</strong> Pilih dari dropdown yang tersedia</li>
                                <li><strong>Wajib diisi:</strong> Semua field</li>
                            </ul>
                        </div>

                        <form method="POST">
                            <div class="mb-4">
                                <label for="id_pemain" class="form-label">
                                    <i class="bi bi-hash me-1"></i>ID Pemain
                                    <span class="required">*</span>
                                </label>
                                <input type="text" class="form-control" id="id_pemain" name="id_pemain" required 
                                       placeholder="PLY01" maxlength="5" pattern="[A-Z0-9]{5}" 
                                       title="5 karakter huruf besar dan angka"
                                       value="<?php echo isset($_POST['id_pemain']) ? htmlspecialchars($_POST['id_pemain']) : ''; ?>">
                                <div class="helper-text">5 karakter unik untuk mengidentifikasi pemain</div>
                            </div>

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
                                        $selected = (isset($_POST['id_tim']) && $_POST['id_tim'] == $tim['ID_TIM']) ? 'selected' : '';
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
                                           value="<?php echo isset($_POST['nama_pemain']) ? htmlspecialchars($_POST['nama_pemain']) : ''; ?>">
                                    <div class="helper-text">Nama lengkap pemain</div>
                                </div>

                                <div>
                                    <label for="nomor_punggung" class="form-label">
                                        <i class="bi bi-123 me-1"></i>Nomor Punggung
                                        <span class="required">*</span>
                                    </label>
                                    <input type="number" class="form-control" id="nomor_punggung" name="nomor_punggung" required 
                                           placeholder="1-99" min="1" max="99"
                                           value="<?php echo isset($_POST['nomor_punggung']) ? htmlspecialchars($_POST['nomor_punggung']) : ''; ?>">
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
                                    <option value="Goalkeeper" <?php echo (isset($_POST['posisi']) && $_POST['posisi'] == 'Goalkeeper') ? 'selected' : ''; ?>>Goalkeeper (GK)</option>
                                    <option value="Center Back" <?php echo (isset($_POST['posisi']) && $_POST['posisi'] == 'Center Back') ? 'selected' : ''; ?>>Center Back (CB)</option>
                                    <option value="Left Back" <?php echo (isset($_POST['posisi']) && $_POST['posisi'] == 'Left Back') ? 'selected' : ''; ?>>Left Back (LB)</option>
                                    <option value="Right Back" <?php echo (isset($_POST['posisi']) && $_POST['posisi'] == 'Right Back') ? 'selected' : ''; ?>>Right Back (RB)</option>
                                    <option value="Defensive Midfielder" <?php echo (isset($_POST['posisi']) && $_POST['posisi'] == 'Defensive Midfielder') ? 'selected' : ''; ?>>Defensive Midfielder (CDM)</option>
                                    <option value="Central Midfielder" <?php echo (isset($_POST['posisi']) && $_POST['posisi'] == 'Central Midfielder') ? 'selected' : ''; ?>>Central Midfielder (CM)</option>
                                    <option value="Attacking Midfielder" <?php echo (isset($_POST['posisi']) && $_POST['posisi'] == 'Attacking Midfielder') ? 'selected' : ''; ?>>Attacking Midfielder (CAM)</option>
                                    <option value="Left Winger" <?php echo (isset($_POST['posisi']) && $_POST['posisi'] == 'Left Winger') ? 'selected' : ''; ?>>Left Winger (LW)</option>
                                    <option value="Right Winger" <?php echo (isset($_POST['posisi']) && $_POST['posisi'] == 'Right Winger') ? 'selected' : ''; ?>>Right Winger (RW)</option>
                                    <option value="Striker" <?php echo (isset($_POST['posisi']) && $_POST['posisi'] == 'Striker') ? 'selected' : ''; ?>>Striker (ST)</option>
                                </select>
                                <div class="helper-text">Posisi utama pemain di lapangan</div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn-submit">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Tambah Pemain
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
        // Auto uppercase for ID Pemain
        document.getElementById('id_pemain').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const idPemain = document.getElementById('id_pemain').value;
            const tim = document.getElementById('id_tim').value;
            const namaPemain = document.getElementById('nama_pemain').value;
            const nomorPunggung = document.getElementById('nomor_punggung').value;
            const posisi = document.getElementById('posisi').value;

            if (idPemain.length !== 5) {
                alert('ID Pemain harus 5 karakter!');
                e.preventDefault();
                return;
            }

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