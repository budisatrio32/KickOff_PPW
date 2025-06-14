<?php
include_once("../../config.php");
requireAdmin();

// Konfigurasi pagination
$limit = 200; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Konfigurasi search
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$search_query = '';
if (!empty($search)) {
    $search_query = "WHERE p.ID_PERTANDINGAN LIKE '%$search%' OR 
                    home_tim.NAMA_TIM LIKE '%$search%' OR 
                    away_tim.NAMA_TIM LIKE '%$search%' OR 
                    s.NAMA_STADION LIKE '%$search%' OR 
                    l.NAMA_LIGA LIKE '%$search%' OR 
                    m.ID_MUSIM LIKE '%$search%'";
}

// Hitung total data
$count_query = "SELECT COUNT(*) as total FROM pertandingan p 
                LEFT JOIN tim home_tim ON p.ID_HOMETEAM = home_tim.ID_TIM
                LEFT JOIN tim away_tim ON p.ID_AWAYTEAM = away_tim.ID_TIM
                LEFT JOIN stadion s ON p.ID_STADION = s.ID_STADION 
                LEFT JOIN liga l ON p.ID_LIGA = l.ID_LIGA
                LEFT JOIN musim m ON p.ID_MUSIM = m.ID_MUSIM
                $search_query";
$count_result = mysqli_query($conn, $count_query);

if (!$count_result) {
    die("Query gagal: " . mysqli_error($conn));
}

$total_data = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_data / $limit);

// Query data dengan JOIN untuk menampilkan nama tim, stadion, liga, musim
$query = "SELECT p.*, 
            home_tim.NAMA_TIM as NAMA_HOMETEAM, 
            away_tim.NAMA_TIM as NAMA_AWAYTEAM,
            s.NAMA_STADION, 
            l.NAMA_LIGA,
            m.TAHUN_MULAI, m.TAHUN_SELESAI
            FROM pertandingan p 
            LEFT JOIN tim home_tim ON p.ID_HOMETEAM = home_tim.ID_TIM
            LEFT JOIN tim away_tim ON p.ID_AWAYTEAM = away_tim.ID_TIM
            LEFT JOIN stadion s ON p.ID_STADION = s.ID_STADION 
            LEFT JOIN liga l ON p.ID_LIGA = l.ID_LIGA
            LEFT JOIN musim m ON p.ID_MUSIM = m.ID_MUSIM
            $search_query 
            ORDER BY p.TANGGAL DESC, p.WAKTU DESC
            LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query gagal: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pertandingan - Admin KickOff</title>
    
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

        .admin-header {
            background: linear-gradient(90deg, var(--primary-red) 0%, var(--accent-red) 100%);
            padding: 1rem 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }

        .page-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 2rem;
            color: white;
            margin: 0;
        }

        .page-subtitle {
            color: rgba(255,255,255,0.9);
            margin: 0;
        }

        .breadcrumb-nav {
            background: none;
            padding: 0;
            margin: 0;
        }

        .breadcrumb-nav .breadcrumb {
            background: none;
            padding: 0;
            margin: 0;
        }

        .breadcrumb-nav .breadcrumb-item a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
        }

        .breadcrumb-nav .breadcrumb-item.active {
            color: white;
        }

        .action-bar {
            background: rgba(255,255,255,0.05);
            padding: 1.5rem 0;
            margin-bottom: 2rem;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-red) 0%, var(--accent-red) 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(142, 22, 22, 0.4);
            color: white;
            text-decoration: none;
        }

        .search-section {
            background: rgba(255,255,255,0.05);
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .search-box {
            background: #3a3a3a;
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 8px;
        }

        .search-box:focus {
            outline: none;
            border-color: var(--accent-red);
            background: #404040;
            color: white;
            box-shadow: 0 0 0 0.2rem rgba(216, 64, 64, 0.25);
        }

        .search-box::placeholder {
            color: rgba(255,255,255,0.6);
        }

        .stats-info {
            background: linear-gradient(135deg, #2a2a2a 0%, #3a3a3a 100%);
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            color: white;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .table-container {
            background: rgba(255,255,255,0.05);
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }

        .table-dark {
            --bs-table-bg: transparent;
            --bs-table-border-color: rgba(255,255,255,0.2);
        }

        .table-dark th {
            background: linear-gradient(135deg, var(--primary-red) 0%, var(--accent-red) 100%);
            color: white;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            border: none;
            padding: 1rem 0.75rem;
        }

        .table-dark td {
            background: rgba(255,255,255,0.03);
            border-color: rgba(255,255,255,0.1);
            color: var(--light-gray);
            padding: 1rem 0.75rem;
            vertical-align: middle;
        }

        .table-dark tbody tr:hover {
            background: rgba(255,255,255,0.08);
        }

        .badge {
            font-weight: 500;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
        }

        .match-teams {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-weight: 600;
        }

        .vs-divider {
            color: var(--accent-red);
            font-weight: 700;
            font-size: 0.9rem;
        }

        .match-datetime {
            text-align: center;
        }

        .match-date {
            font-weight: 600;
            color: var(--light-gray);
        }

        .match-time {
            font-size: 0.875rem;
            color: rgba(255,255,255,0.7);
        }

        .pagination-custom {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 2rem;
            gap: 8px;
        }

        .pagination-custom a, .pagination-custom span {
            padding: 0.75rem 1rem;
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            background: rgba(255,255,255,0.05);
            font-weight: 500;
        }

        .pagination-custom a:hover {
            background: var(--accent-red);
            border-color: var(--accent-red);
            color: white;
            transform: translateY(-2px);
        }

        .pagination-custom .current {
            background: linear-gradient(135deg, var(--primary-red) 0%, var(--accent-red) 100%);
            color: white;
            border-color: var(--accent-red);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: rgba(255,255,255,0.7);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--accent-red);
            margin-bottom: 1.5rem;
        }

        .empty-state h4 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            color: white;
            margin-bottom: 1rem;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 6px;
        }

        .btn-warning {
            background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
            border: none;
            color: #000;
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border: none;
            color: white;
        }

        .btn-secondary-custom {
            background: rgba(255,255,255,0.1);
            color: white;
            padding: 0.5rem 1rem;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
        }

        .btn-secondary-custom:hover {
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
        }

        .future-match {
            background: rgba(76, 175, 80, 0.1) !important;
            border-left: 4px solid #4caf50;
        }

        .past-match {
            background: rgba(158, 158, 158, 0.1) !important;
            border-left: 4px solid #9e9e9e;
        }

        .today-match {
            background: rgba(216, 64, 64, 0.1) !important;
            border-left: 4px solid var(--accent-red);
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 1.5rem;
            }
            
            .table-responsive {
                border-radius: 10px;
            }
            
            .action-bar .row > div {
                margin-bottom: 1rem;
            }
            
            .match-teams {
                flex-direction: column;
                gap: 0.25rem;
            }
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="page-title">
                        <i class="bi bi-calendar-event me-2"></i>Kelola Pertandingan
                    </h1>
                    <p class="page-subtitle">Manajemen jadwal pertandingan sepakbola dalam database KickOff</p>
                    <nav class="breadcrumb-nav">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="../../adminpage.php">
                                    <i class="bi bi-speedometer2 me-1"></i>Dashboard
                                </a>
                            </li>
                            <li class="breadcrumb-item active">Kelola Pertandingan</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="../../adminpage.php" class="btn-secondary-custom">
                        <i class="bi bi-arrow-left me-2"></i>
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Action Bar -->
    <section class="action-bar">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <a href="tambah_pertandingan.php" class="btn-primary-custom">
                        <i class="bi bi-plus-circle me-2"></i>
                        Tambah Pertandingan Baru
                    </a>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <span class="text-light">
                        <i class="bi bi-info-circle me-2"></i>
                        Total: <strong><?php echo number_format($total_data); ?> Pertandingan</strong> terjadwal
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- Content -->
    <main class="pb-4">
        <div class="container">
            <!-- Search Section -->
            <div class="search-section">
                <form method="GET" class="row g-3 align-items-center">
                    <div class="col-md-8">
                        <input type="text" name="search" class="form-control search-box" 
                                placeholder="Cari berdasarkan ID pertandingan, nama tim, stadion, liga, atau musim..." 
                                value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i> Cari
                        </button>
                    </div>
                    <?php if (!empty($search)): ?>
                    <div class="col-md-2">
                        <a href="daftar_pertandingan.php" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-circle me-1"></i> Reset
                        </a>
                    </div>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Stats Info -->
            <div class="stats-info d-flex justify-content-between align-items-center">
                <div>
                    <strong>Total Data: <?php echo number_format($total_data); ?></strong>
                    <?php if (!empty($search)): ?>
                        <span> | Hasil pencarian untuk: "<em><?php echo htmlspecialchars($search); ?></em>"</span>
                    <?php endif; ?>
                </div>
                <div>Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?></div>
            </div>

            <?php if(mysqli_num_rows($result) > 0): ?>
                <!-- Table with Data -->
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="10%">ID Match</th>
                                    <th width="12%">Tanggal & Waktu</th>
                                    <th width="25%">Pertandingan</th>
                                    <th width="15%">Stadion</th>
                                    <th width="12%">Liga</th>
                                    <th width="10%">Musim</th>
                                    <th width="11%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = $offset + 1; 
                                $today = date('Y-m-d');
                                while($row = mysqli_fetch_assoc($result)): 
                                    $id_pertandingan = $row['ID_PERTANDINGAN'];
                                    $tanggal = $row['TANGGAL'];
                                    $waktu = $row['WAKTU'];
                                    $id_hometeam = $row['ID_HOMETEAM'];
                                    $id_awayteam = $row['ID_AWAYTEAM'];
                                    $nama_hometeam = $row['NAMA_HOMETEAM'] ?? 'Tim tidak ditemukan';
                                    $nama_awayteam = $row['NAMA_AWAYTEAM'] ?? 'Tim tidak ditemukan';
                                    $id_stadion = $row['ID_STADION'];
                                    $nama_stadion = $row['NAMA_STADION'] ?? 'Stadion tidak ditemukan';
                                    $id_liga = $row['ID_LIGA'];
                                    $nama_liga = $row['NAMA_LIGA'] ?? 'Liga tidak ditemukan';
                                    $id_musim = $row['ID_MUSIM'];
                                    $tahun_mulai = $row['TAHUN_MULAI'];
                                    $tahun_selesai = $row['TAHUN_SELESAI'];
                                    
                                    // Tentukan status pertandingan berdasarkan tanggal
                                    $row_class = '';
                                    if ($tanggal == $today) {
                                        $row_class = 'today-match';
                                    } elseif ($tanggal > $today) {
                                        $row_class = 'future-match';
                                    } else {
                                        $row_class = 'past-match';
                                    }
                                ?>
                                <tr class="<?php echo $row_class; ?>">
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo htmlspecialchars($id_pertandingan); ?>
                                        </span>
                                    </td>
                                    <td class="match-datetime">
                                        <div class="match-date">
                                            <?php echo date('d/m/Y', strtotime($tanggal)); ?>
                                        </div>
                                        <div class="match-time">
                                            <?php echo date('H:i', strtotime($waktu)); ?> WIB
                                        </div>
                                    </td>
                                    <td>
                                        <div class="match-teams">
                                            <span class="text-info"><?php echo htmlspecialchars($nama_hometeam); ?></span>
                                            <span class="vs-divider">VS</span>
                                            <span class="text-warning"><?php echo htmlspecialchars($nama_awayteam); ?></span>
                                        </div>
                                        <div class="text-center mt-1">
                                            <small class="text-muted">
                                                <span class="badge bg-info me-1"><?php echo htmlspecialchars($id_hometeam); ?></span>
                                                <span class="badge bg-warning"><?php echo htmlspecialchars($id_awayteam); ?></span>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="badge bg-success mb-1">
                                                <?php echo htmlspecialchars($id_stadion); ?>
                                            </span>
                                            <br>
                                            <small class="text-light">
                                                <?php echo htmlspecialchars($nama_stadion); ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="badge bg-primary mb-1">
                                                <?php echo htmlspecialchars($id_liga); ?>
                                            </span>
                                            <br>
                                            <small class="text-light">
                                                <?php echo htmlspecialchars($nama_liga); ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="badge bg-dark mb-1">
                                                <?php echo htmlspecialchars($id_musim); ?>
                                            </span>
                                            <br>
                                            <small class="text-light">
                                                <?php echo $tahun_mulai . '/' . $tahun_selesai; ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="edit_pertandingan.php?id=<?php echo urlencode($id_pertandingan); ?>" 
                                                class="btn btn-sm btn-warning" 
                                                title="Edit Pertandingan <?php echo htmlspecialchars($nama_hometeam . ' vs ' . $nama_awayteam); ?>">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="hapus_pertandingan.php?id=<?php echo urlencode($id_pertandingan); ?>" 
                                                class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus pertandingan <?php echo htmlspecialchars($nama_hometeam . ' vs ' . $nama_awayteam); ?> pada <?php echo date('d/m/Y H:i', strtotime($tanggal . ' ' . $waktu)); ?>?\n\nTindakan ini tidak dapat dibatalkan!')" 
                                                title="Hapus Pertandingan <?php echo htmlspecialchars($nama_hometeam . ' vs ' . $nama_awayteam); ?>">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="pagination-custom">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo ($page-1); ?>&search=<?php echo urlencode($search); ?>">
                                <i class="bi bi-chevron-left"></i> Previous
                            </a>
                        <?php endif; ?>
                        
                        <?php
                        $start = max(1, $page - 2);
                        $end = min($total_pages, $page + 2);
                        
                        for ($i = $start; $i <= $end; $i++):
                        ?>
                            <?php if ($i == $page): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo ($page+1); ?>&search=<?php echo urlencode($search); ?>">
                                Next <i class="bi bi-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Table Footer Info -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <small class="text-light">
                                <i class="bi bi-info-circle me-1"></i>
                                Menampilkan <?php echo mysqli_num_rows($result); ?> dari <?php echo number_format($total_data); ?> pertandingan 
                                <?php if ($total_pages > 1): ?>
                                    | Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?>
                                <?php endif; ?>
                                <br>
                                <i class="bi bi-square-fill text-success me-1"></i> Mendatang
                                <i class="bi bi-square-fill text-danger me-1 ms-3"></i> Hari Ini  
                                <i class="bi bi-square-fill text-secondary me-1 ms-3"></i> Selesai
                            </small>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Empty State -->
                <div class="table-container">
                    <div class="empty-state">
                        <i class="bi bi-calendar-x"></i>
                        <h4>
                            <?php if (!empty($search)): ?>
                                Tidak ada data yang ditemukan
                            <?php else: ?>
                                Belum Ada Data Pertandingan
                            <?php endif; ?>
                        </h4>
                        <p>
                            <?php if (!empty($search)): ?>
                                Tidak ditemukan data pertandingan yang sesuai dengan pencarian "<strong><?php echo htmlspecialchars($search); ?></strong>".
                                <br><a href="daftar_pertandingan.php" class="btn-secondary-custom mt-3">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Tampilkan semua data
                                </a>
                            <?php else: ?>
                                Mulai dengan menambahkan pertandingan pertama Anda ke dalam database KickOff
                            <?php endif; ?>
                        </p>
                        <?php if (empty($search)): ?>
                        <a href="tambah_pertandingan.php" class="btn-primary-custom mt-3">
                            <i class="bi bi-plus-circle me-2"></i>
                            Tambah Pertandingan Pertama
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>