<?php
include_once("../../config.php");
requireAdmin();

// Get ID from parameter
$id_pertandingan = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

if (empty($id_pertandingan)) {
    header("Location: daftar_pertandingan.php");
    exit();
}

// Get existing data
$current_query = "SELECT * FROM pertandingan WHERE ID_PERTANDINGAN = '$id_pertandingan'";
$current_result = mysqli_query($conn, $current_query);

if (!$current_result || mysqli_num_rows($current_result) == 0) {
    echo "<script>
            alert('Pertandingan tidak ditemukan!');
            window.location.href = 'daftar_pertandingan.php';
          </script>";
    exit();
}

$current_data = mysqli_fetch_assoc($current_result);

// Ambil data tim untuk dropdown
$tim_query = "SELECT ID_TIM, NAMA_TIM FROM tim ORDER BY NAMA_TIM ASC";
$tim_result = mysqli_query($conn, $tim_query);

// Ambil data stadion untuk dropdown
$stadion_query = "SELECT ID_STADION, NAMA_STADION, LOKASI FROM stadion ORDER BY NAMA_STADION ASC";
$stadion_result = mysqli_query($conn, $stadion_query);

// Ambil data liga untuk dropdown
$liga_query = "SELECT ID_LIGA, NAMA_LIGA FROM liga ORDER BY NAMA_LIGA ASC";
$liga_result = mysqli_query($conn, $liga_query);

// Ambil data musim untuk dropdown
$musim_query = "SELECT ID_MUSIM, TAHUN_MULAI, TAHUN_SELESAI FROM musim ORDER BY TAHUN_MULAI DESC";
$musim_result = mysqli_query($conn, $musim_query);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $waktu = mysqli_real_escape_string($conn, $_POST['waktu']);
    $id_hometeam = mysqli_real_escape_string($conn, $_POST['id_hometeam']);
    $id_awayteam = mysqli_real_escape_string($conn, $_POST['id_awayteam']);
    $id_stadion = mysqli_real_escape_string($conn, $_POST['id_stadion']);
    $id_liga = mysqli_real_escape_string($conn, $_POST['id_liga']);
    $id_musim = mysqli_real_escape_string($conn, $_POST['id_musim']);

    $errors = [];
    
    // Validasi input
    if (empty($tanggal)) {
        $errors[] = "Tanggal pertandingan harus diisi!";
    }
    
    if (empty($waktu)) {
        $errors[] = "Waktu pertandingan harus diisi!";
    }
    
    if (empty($id_hometeam)) {
        $errors[] = "Tim kandang harus dipilih!";
    }
    
    if (empty($id_awayteam)) {
        $errors[] = "Tim tandang harus dipilih!";
    }
    
    if ($id_hometeam === $id_awayteam) {
        $errors[] = "Tim kandang dan tim tandang tidak boleh sama!";
    }
    
    if (empty($id_stadion)) {
        $errors[] = "Stadion harus dipilih!";
    }
    
    if (empty($id_liga)) {
        $errors[] = "Liga harus dipilih!";
    }
    
    if (empty($id_musim)) {
        $errors[] = "Musim harus dipilih!";
    }

    // Cek apakah tim, stadion, liga, dan musim valid
    $check_hometeam = mysqli_query($conn, "SELECT ID_TIM FROM tim WHERE ID_TIM = '$id_hometeam'");
    if (mysqli_num_rows($check_hometeam) == 0) {
        $errors[] = "Tim kandang yang dipilih tidak valid!";
    }

    $check_awayteam = mysqli_query($conn, "SELECT ID_TIM FROM tim WHERE ID_TIM = '$id_awayteam'");
    if (mysqli_num_rows($check_awayteam) == 0) {
        $errors[] = "Tim tandang yang dipilih tidak valid!";
    }

    $check_stadion = mysqli_query($conn, "SELECT ID_STADION FROM stadion WHERE ID_STADION = '$id_stadion'");
    if (mysqli_num_rows($check_stadion) == 0) {
        $errors[] = "Stadion yang dipilih tidak valid!";
    }

    $check_liga = mysqli_query($conn, "SELECT ID_LIGA FROM liga WHERE ID_LIGA = '$id_liga'");
    if (mysqli_num_rows($check_liga) == 0) {
        $errors[] = "Liga yang dipilih tidak valid!";
    }

    $check_musim = mysqli_query($conn, "SELECT ID_MUSIM FROM musim WHERE ID_MUSIM = '$id_musim'");
    if (mysqli_num_rows($check_musim) == 0) {
        $errors[] = "Musim yang dipilih tidak valid!";
    }

    // Cek konflik jadwal stadion (exclude current match)
    $check_conflict = mysqli_query($conn, "SELECT ID_PERTANDINGAN FROM pertandingan 
                                            WHERE ID_STADION = '$id_stadion' 
                                            AND DATE(TANGGAL) = '$tanggal' 
                                            AND ABS(TIME_TO_SEC(TIMEDIFF('$waktu', WAKTU))) < 7200
                                            AND ID_PERTANDINGAN != '$id_pertandingan'");
    if (mysqli_num_rows($check_conflict) > 0) {
        $errors[] = "Stadion sudah digunakan dalam rentang waktu 2 jam dari waktu yang dipilih!";
    }

    // Cek konflik jadwal tim (exclude current match)
    $check_team_conflict = mysqli_query($conn, "SELECT ID_PERTANDINGAN FROM pertandingan 
                                                WHERE (ID_HOMETEAM = '$id_hometeam' OR ID_AWAYTEAM = '$id_hometeam' 
                                                OR ID_HOMETEAM = '$id_awayteam' OR ID_AWAYTEAM = '$id_awayteam')
                                                AND DATE(TANGGAL) = '$tanggal'
                                                AND ID_PERTANDINGAN != '$id_pertandingan'");
    if (mysqli_num_rows($check_team_conflict) > 0) {
        $errors[] = "Salah satu tim sudah memiliki pertandingan pada tanggal yang sama!";
    }

    if (!empty($errors)) {
        $error = implode("<br>", $errors);
    } else {
        $query = "UPDATE pertandingan SET 
                    TANGGAL = '$tanggal',
                    WAKTU = '$waktu',
                    ID_HOMETEAM = '$id_hometeam',
                    ID_AWAYTEAM = '$id_awayteam',
                    ID_STADION = '$id_stadion',
                    ID_LIGA = '$id_liga',
                    ID_MUSIM = '$id_musim'
                    WHERE ID_PERTANDINGAN = '$id_pertandingan'";
        
        if (mysqli_query($conn, $query)) {
            // Ambil nama tim untuk success message
            $home_team_query = mysqli_query($conn, "SELECT NAMA_TIM FROM tim WHERE ID_TIM = '$id_hometeam'");
            $away_team_query = mysqli_query($conn, "SELECT NAMA_TIM FROM tim WHERE ID_TIM = '$id_awayteam'");
            $home_team_name = mysqli_fetch_assoc($home_team_query)['NAMA_TIM'];
            $away_team_name = mysqli_fetch_assoc($away_team_query)['NAMA_TIM'];
            
            $success = "Pertandingan \"$home_team_name vs $away_team_name\" berhasil diperbarui!";
            
            // Refresh current data
            $current_result = mysqli_query($conn, $current_query);
            $current_data = mysqli_fetch_assoc($current_result);
        } else {
            $error = "Gagal memperbarui pertandingan: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pertandingan - Admin KickOff</title>
    
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

        .current-info {
            background: linear-gradient(135deg, #2196F3 0%, #21CBF3 100%);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            color: white;
        }

        .current-info h6 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }

        .current-info .match-display {
            text-align: center;
            font-size: 1.2rem;
            font-weight: 600;
            margin: 1rem 0;
        }

        .current-info .vs-text {
            color: #FFE082;
            font-weight: 700;
            margin: 0 1rem;
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

        .readonly-field {
            background: rgba(100, 100, 100, 0.2) !important;
            border-color: rgba(255,255,255,0.1) !important;
            color: rgba(255,255,255,0.6) !important;
        }

        .datetime-row {
            background: rgba(255,255,255,0.03);
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        .teams-row {
            background: rgba(76, 175, 80, 0.05);
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            border: 1px solid rgba(76, 175, 80, 0.2);
        }

        .match-info-row {
            background: rgba(33, 150, 243, 0.05);
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            border: 1px solid rgba(33, 150, 243, 0.2);
        }

        .vs-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 1rem 0;
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--accent-red);
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
    <a href="daftar_pertandingan.php" class="back-button">
        <i class="bi bi-arrow-left"></i>
    </a>

    <!-- Main Container -->
    <div class="main-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="form-section">
                        <h1 class="page-title">
                            <i class="bi bi-pencil-square me-2"></i>Edit Pertandingan
                        </h1>
                        <p class="page-subtitle">Perbarui data pertandingan dalam database KickOff</p>
                        
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>
                                <?php echo $success; ?>
                                <div class="mt-2">
                                    <a href="daftar_pertandingan.php" class="btn btn-light btn-sm">
                                        <i class="bi bi-list me-1"></i>Lihat Daftar Pertandingan
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

                        <!-- Current Info Box -->
                        <div class="current-info">
                            <h6><i class="bi bi-info-circle me-2"></i>Data Pertandingan Saat Ini</h6>
                            <div class="match-display">
                                <strong>ID: <?php echo htmlspecialchars($current_data['ID_PERTANDINGAN']); ?></strong>
                            </div>
                            <div class="text-center">
                                <small>
                                    <?php echo date('d/m/Y H:i', strtotime($current_data['TANGGAL'] . ' ' . $current_data['WAKTU'])); ?> WIB
                                </small>
                            </div>
                        </div>

                        <form method="POST">
                            <!-- ID Field (Read Only) -->
                            <div class="mb-4">
                                <label for="id_pertandingan_display" class="form-label">
                                    <i class="bi bi-hash me-1"></i>ID Pertandingan
                                </label>
                                <input type="text" class="form-control readonly-field" id="id_pertandingan_display" 
                                        value="<?php echo htmlspecialchars($current_data['ID_PERTANDINGAN']); ?>" readonly>
                                <div class="helper-text">ID pertandingan tidak dapat diubah</div>
                            </div>

                            <!-- DateTime Section -->
                            <div class="datetime-row">
                                <h6 class="text-white mb-3">
                                    <i class="bi bi-calendar-event me-2"></i>Waktu Pertandingan
                                </h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="tanggal" class="form-label">
                                            <i class="bi bi-calendar3 me-1"></i>Tanggal Pertandingan
                                            <span class="required">*</span>
                                        </label>
                                        <input type="date" class="form-control" id="tanggal" name="tanggal" required
                                                value="<?php echo isset($_POST['tanggal']) ? htmlspecialchars($_POST['tanggal']) : htmlspecialchars($current_data['TANGGAL']); ?>">
                                        <div class="helper-text">Pilih tanggal pelaksanaan pertandingan</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="waktu" class="form-label">
                                            <i class="bi bi-clock me-1"></i>Waktu Kick Off
                                            <span class="required">*</span>
                                        </label>
                                        <input type="time" class="form-control" id="waktu" name="waktu" required
                                                value="<?php echo isset($_POST['waktu']) ? htmlspecialchars($_POST['waktu']) : htmlspecialchars($current_data['WAKTU']); ?>">
                                        <div class="helper-text">Waktu mulai pertandingan (WIB)</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Teams Section -->
                            <div class="teams-row">
                                <h6 class="text-white mb-3">
                                    <i class="bi bi-people-fill me-2"></i>Tim Bertanding
                                </h6>
                                <div class="row">
                                    <div class="col-md-5">
                                        <label for="id_hometeam" class="form-label">
                                            <i class="bi bi-house-fill me-1"></i>Tim Kandang
                                            <span class="required">*</span>
                                        </label>
                                        <select class="form-select" id="id_hometeam" name="id_hometeam" required>
                                            <option value="">-- Pilih Tim Kandang --</option>
                                            <?php 
                                            mysqli_data_seek($tim_result, 0);
                                            while($tim = mysqli_fetch_assoc($tim_result)): 
                                                $selected_home = '';
                                                if (isset($_POST['id_hometeam'])) {
                                                    $selected_home = ($_POST['id_hometeam'] == $tim['ID_TIM']) ? 'selected' : '';
                                                } else {
                                                    $selected_home = ($current_data['ID_HOMETEAM'] == $tim['ID_TIM']) ? 'selected' : '';
                                                }
                                            ?>
                                                <option value="<?php echo htmlspecialchars($tim['ID_TIM']); ?>" <?php echo $selected_home; ?>>
                                                    <?php echo htmlspecialchars($tim['ID_TIM'] . ' - ' . $tim['NAMA_TIM']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                        <div class="helper-text">Tim yang bermain di kandang</div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="vs-indicator">VS</div>
                                    </div>
                                    <div class="col-md-5">
                                        <label for="id_awayteam" class="form-label">
                                            <i class="bi bi-airplane me-1"></i>Tim Tandang
                                            <span class="required">*</span>
                                        </label>
                                        <select class="form-select" id="id_awayteam" name="id_awayteam" required>
                                            <option value="">-- Pilih Tim Tandang --</option>
                                            <?php 
                                            mysqli_data_seek($tim_result, 0);
                                            while($tim = mysqli_fetch_assoc($tim_result)): 
                                                $selected_away = '';
                                                if (isset($_POST['id_awayteam'])) {
                                                    $selected_away = ($_POST['id_awayteam'] == $tim['ID_TIM']) ? 'selected' : '';
                                                } else {
                                                    $selected_away = ($current_data['ID_AWAYTEAM'] == $tim['ID_TIM']) ? 'selected' : '';
                                                }
                                            ?>
                                                <option value="<?php echo htmlspecialchars($tim['ID_TIM']); ?>" <?php echo $selected_away; ?>>
                                                    <?php echo htmlspecialchars($tim['ID_TIM'] . ' - ' . $tim['NAMA_TIM']); ?>
                                                </option>
                                            <?php endwhile; ?>