<?php
include_once("config.php");
requireAdmin(); // Hanya admin yang bisa akses

// Ambil statistik data untuk dashboard overview
$stats = [];

// Hitung total data di setiap tabel
$tables = ['tim', 'liga', 'stadion', 'pemain', 'pertandingan', 'feedback'];
foreach ($tables as $table) {
    $query = "SELECT COUNT(*) as total FROM $table";
    $result = mysqli_query($conn, $query);
    $stats[$table] = $result ? mysqli_fetch_assoc($result)['total'] : 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - KickOff</title>
    
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
            --text-dark: #1D1616;
            --text-light: #EEEEEE;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Urbanist', sans-serif;
            background: linear-gradient(135deg, var(--dark-bg) 0%, #2a2a2a 100%);
            color: var(--text-light);
            min-height: 100vh;
        }

        .admin-header {
            background: linear-gradient(90deg, var(--primary-red) 0%, var(--accent-red) 100%);
            padding: 1rem 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }

        .admin-header h1 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 2rem;
            color: white;
            margin: 0;
        }

        .admin-header .user-info {
            color: rgba(255,255,255,0.9);
            font-size: 0.9rem;
        }

        .admin-header .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .admin-header .logout-btn:hover {
            background: rgba(255,255,255,0.3);
            color: white;
            transform: translateY(-2px);
        }

        .dashboard-container {
            padding: 2rem 0;
            min-height: calc(100vh - 100px);
        }

        .welcome-section {
            background: linear-gradient(135deg, var(--primary-red) 0%, var(--accent-red) 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: center;
            box-shadow: 0 8px 32px rgba(142, 22, 22, 0.3);
        }

        .welcome-section h2 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 2.5rem;
            color: white;
            margin-bottom: 0.5rem;
        }

        .welcome-section p {
            color: rgba(255,255,255,0.9);
            font-size: 1.1rem;
            margin: 0;
        }

        .stats-cards {
            margin-bottom: 3rem;
        }

        .stat-card {
            background: linear-gradient(135deg, #2a2a2a 0%, #3a3a3a 100%);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s ease;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            border-color: var(--accent-red);
        }

        .stat-card .icon {
            font-size: 2.5rem;
            color: var(--accent-red);
            margin-bottom: 1rem;
        }

        .stat-card .number {
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            font-size: 2rem;
            color: white;
            margin-bottom: 0.5rem;
        }

        .stat-card .label {
            color: rgba(255,255,255,0.7);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.9rem;
        }

        .management-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .management-card {
            background: linear-gradient(135deg, #2a2a2a 0%, #3a3a3a 100%);
            border-radius: 15px;
            padding: 2rem;
            border: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
        }

        .management-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            border-color: var(--accent-red);
            color: inherit;
            text-decoration: none;
        }

        .management-card .card-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-red) 0%, var(--accent-red) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .management-card .card-icon i {
            font-size: 1.8rem;
            color: white;
        }

        .management-card h3 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 1.3rem;
            color: white;
            margin-bottom: 0.8rem;
        }

        .management-card p {
            color: rgba(255,255,255,0.7);
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 1rem;
        }

        .management-card .card-features {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .management-card .card-features li {
            color: rgba(255,255,255,0.6);
            font-size: 0.85rem;
            margin-bottom: 0.3rem;
            padding-left: 1rem;
            position: relative;
        }

        .management-card .card-features li::before {
            content: "•";
            color: var(--accent-red);
            position: absolute;
            left: 0;
            font-weight: bold;
        }

        .section-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 1.8rem;
            color: white;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .section-title::after {
            content: "";
            display: block;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-red) 0%, var(--accent-red) 100%);
            margin: 0.5rem auto;
            border-radius: 2px;
        }

        @media (max-width: 768px) {
            .admin-header h1 {
                font-size: 1.5rem;
            }

            .welcome-section h2 {
                font-size: 2rem;
            }

            .management-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .stats-cards .row > div {
                margin-bottom: 1rem;
            }
        }

        .alert-custom {
            background: linear-gradient(135deg, var(--primary-red) 0%, var(--accent-red) 100%);
            border: none;
            color: white;
            border-radius: 10px;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1><i class="bi bi-speedometer2 me-2"></i>Admin Dashboard</h1>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="user-info d-inline-block me-3">
                        <i class="bi bi-person-circle me-1"></i>
                        Welcome, <strong><?php echo htmlspecialchars(getCurrentUserFullName()); ?></strong>
                    </div>
                    <a href="logout.php" class="logout-btn">
                        <i class="bi bi-box-arrow-right me-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Dashboard Container -->
    <div class="dashboard-container">
        <div class="container">
            <!-- Welcome Section -->
            <div class="welcome-section">
                <h2>KickOff Management System</h2>
                <p>Kelola data sepakbola dengan mudah dan efisien</p>
            </div>

            <!-- Stats Cards -->
            <div class="stats-cards">
                <div class="row">
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <div class="stat-card">
                            <div class="icon">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div class="number"><?php echo number_format($stats['tim']); ?></div>
                            <div class="label">Tim</div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <div class="stat-card">
                            <div class="icon">
                                <i class="bi bi-trophy"></i>
                            </div>
                            <div class="number"><?php echo number_format($stats['liga']); ?></div>
                            <div class="label">Liga</div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <div class="stat-card">
                            <div class="icon">
                                <i class="bi bi-geo-alt"></i>
                            </div>
                            <div class="number"><?php echo number_format($stats['stadion']); ?></div>
                            <div class="label">Stadion</div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <div class="stat-card">
                            <div class="icon">
                                <i class="bi bi-people"></i>
                            </div>
                            <div class="number"><?php echo number_format($stats['pemain']); ?></div>
                            <div class="label">Pemain</div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <div class="stat-card">
                            <div class="icon">
                                <i class="bi bi-calendar-event"></i>
                            </div>
                            <div class="number"><?php echo number_format($stats['pertandingan']); ?></div>
                            <div class="label">Pertandingan</div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                        <div class="stat-card">
                            <div class="icon">
                                <i class="bi bi-chat-dots"></i>
                            </div>
                            <div class="number"><?php echo number_format($stats['feedback']); ?></div>
                            <div class="label">Feedback</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Management Section -->
            <h2 class="section-title">Data Management</h2>
            
            <div class="management-grid">
                <!-- Tim Management -->
                <a href="admin\tim\daftar_tim.php" class="management-card">
                    <div class="card-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h3>Kelola Tim</h3>
                    <p>Manajemen data tim sepakbola, logo, dan pelatih</p>
                    <ul class="card-features">
                        <li>Tambah, edit, hapus tim</li>
                        <li>Upload logo tim</li>
                        <li>Search & pagination</li>
                        <li>Dropdown liga & stadion</li>
                    </ul>
                </a>

                <!-- Liga Management -->
                <a href="admin/liga/daftar_liga.php" class="management-card">
                    <div class="card-icon">
                        <i class="bi bi-trophy"></i>
                    </div>
                    <h3>Kelola Liga</h3>
                    <p>Manajemen data liga dan kompetisi sepakbola</p>
                    <ul class="card-features">
                        <li>Tambah, edit, hapus liga</li>
                        <li>Upload logo liga</li>
                        <li>Search & pagination</li>
                        <li>Data nama liga</li>
                    </ul>
                </a>

                <!-- Stadion Management -->
                <a href="admin/stadion/daftar_stadion.php" class="management-card">
                    <div class="card-icon">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                    <h3>Kelola Stadion</h3>
                    <p>Manajemen data stadion dan venue pertandingan</p>
                    <ul class="card-features">
                        <li>Tambah, edit, hapus stadion</li>
                        <li>Data lokasi & kapasitas</li>
                        <li>Search & pagination</li>
                        <li>Upload foto stadion</li>
                    </ul>
                </a>

                <!-- Pemain Management -->
                <a href="admin/pemain/daftar_pemain.php" class="management-card">
                    <div class="card-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <h3>Kelola Pemain</h3>
                    <p>Manajemen data pemain dan informasi tim</p>
                    <ul class="card-features">
                        <li>Tambah, edit, hapus pemain</li>
                        <li>Dropdown pilih tim</li>
                        <li>Pagination 500/page</li>
                        <li>Search 4000+ data</li>
                    </ul>
                </a>

                <!-- Pertandingan Management -->
                <a href="admin/pertandingan/daftar_pertandingan.php" class="management-card">
                    <div class="card-icon">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <h3>Kelola Pertandingan</h3>
                    <p>Manajemen jadwal dan hasil pertandingan</p>
                    <ul class="card-features">
                        <li>Tambah, edit, hapus pertandingan</li>
                        <li>Dropdown tim & stadion</li>
                        <li>Jadwal & waktu</li>
                        <li>Data home/away team</li>
                    </ul>
                </a>

                <!-- Feedback Management -->
                <a href="admin/feedback/daftar_feedback.php" class="management-card">
                    <div class="card-icon">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                    <h3>Kelola Feedback</h3>
                    <p>Manajemen feedback dan pesan pengguna</p>
                    <ul class="card-features">
                        <li>View & hapus feedback</li>
                        <li>Data pengguna & email</li>
                        <li>Search & pagination</li>
                        <li>Status management</li>
                    </ul>
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Auto refresh stats setiap 30 detik -->
    <script>
        // Optional: Auto refresh stats
        setInterval(function() {
            // Bisa ditambahkan AJAX untuk refresh stats tanpa reload page
        }, 30000);
    </script>
</body>
</html>