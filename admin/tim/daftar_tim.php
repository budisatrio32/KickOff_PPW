<?php
include_once("../../config.php");
requireAdmin();

// Konfigurasi pagination
$limit = 20; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Konfigurasi search
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$search_query = '';
if (!empty($search)) {
    $search_query = "WHERE t.NAMA_TIM LIKE '%$search%' OR t.PELATIH LIKE '%$search%' OR t.ID_TIM LIKE '%$search%' OR l.NAMA_LIGA LIKE '%$search%' OR s.NAMA_STADION LIKE '%$search%' OR t.ID_LIGA LIKE '%$search%'";
}

// Hitung total data
$count_query = "SELECT COUNT(*) as total FROM tim t 
                LEFT JOIN liga l ON t.ID_LIGA = l.ID_LIGA 
                LEFT JOIN stadion s ON t.ID_STADION = s.ID_STADION 
                $search_query";
$count_result = mysqli_query($conn, $count_query);

if (!$count_result) {
    die("Query gagal: " . mysqli_error($conn));
}

$total_data = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_data / $limit);

// Query data dengan JOIN untuk menampilkan nama liga dan stadion
$query = "SELECT t.*, l.NAMA_LIGA, s.NAMA_STADION 
            FROM tim t 
            LEFT JOIN liga l ON t.ID_LIGA = l.ID_LIGA 
            LEFT JOIN stadion s ON t.ID_STADION = s.ID_STADION 
            $search_query 
            ORDER BY t.ID_TIM 
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
    <title>Daftar Tim - Admin KickOff</title>
    
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

        .team-logo {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid rgba(255,255,255,0.2);
        }

        .no-logo {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255,255,255,0.5);
            border: 2px solid rgba(255,255,255,0.2);
        }

        .badge {
            font-weight: 500;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
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
                        <i class="bi bi-shield-check me-2"></i>Kelola Tim
                    </h1>
                    <p class="page-subtitle">Manajemen data tim sepakbola dalam database KickOff</p>
                    <nav class="breadcrumb-nav">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="../../adminpage.php">
                                    <i class="bi bi-speedometer2 me-1"></i>Dashboard
                                </a>
                            </li>
                            <li class="breadcrumb-item active">Kelola Tim</li>
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
                    <a href="tambah_tim.php" class="btn-primary-custom">
                        <i class="bi bi-plus-circle me-2"></i>
                        Tambah Tim Baru
                    </a>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <span class="text-light">
                        <i class="bi bi-info-circle me-2"></i>
                        Total: <strong><?php echo number_format($total_data); ?> Tim</strong> terdaftar
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
                                placeholder="Cari berdasarkan nama tim, pelatih, ID tim, liga, atau stadion..." 
                                value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i> Cari
                        </button>
                    </div>
                    <?php if (!empty($search)): ?>
                    <div class="col-md-2">
                        <a href="daftar_tim.php" class="btn btn-outline-secondary w-100">
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
                                    <th width="12%">ID Tim</th>
                                    <th width="15%">Liga</th>
                                    <th width="15%">Stadion</th>
                                    <th width="8%">Logo</th>
                                    <th width="20%">Nama Tim</th>
                                    <th width="15%">Pelatih</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = $offset + 1; 
                                while($row = mysqli_fetch_assoc($result)): 
                                    $id_tim = $row['ID_TIM'];
                                    $id_liga = $row['ID_LIGA'];
                                    $nama_liga = $row['NAMA_LIGA'] ?? 'Liga tidak ditemukan';
                                    $id_stadion = $row['ID_STADION'];
                                    $nama_stadion = $row['NAMA_STADION'] ?? 'Stadion tidak ditemukan';
                                    $logo_tim = $row['LOGO_TIM'];
                                    $nama_tim = $row['NAMA_TIM'];
                                    $pelatih = $row['PELATIH'];
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo htmlspecialchars($id_tim); ?>
                                        </span>
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
                                            <span class="badge bg-info mb-1">
                                                <?php echo htmlspecialchars($id_stadion); ?>
                                            </span>
                                            <br>
                                            <small class="text-light">
                                                <?php echo htmlspecialchars($nama_stadion); ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if(!empty($logo_tim) && file_exists("../../uploads/teams/".$logo_tim)): ?>
                                            <img src="../../uploads/teams/<?php echo htmlspecialchars($logo_tim); ?>" 
                                                class="team-logo" 
                                                alt="Logo <?php echo htmlspecialchars($nama_tim); ?>"
                                                title="Logo <?php echo htmlspecialchars($nama_tim); ?>">
                                        <?php else: ?>
                                            <div class="no-logo" title="Tidak ada logo">
                                                <i class="bi bi-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($nama_tim); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($pelatih); ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="edit_tim.php?id=<?php echo urlencode($id_tim); ?>" 
                                                class="btn btn-sm btn-warning" 
                                                title="Edit Tim <?php echo htmlspecialchars($nama_tim); ?>">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="hapus_tim.php?id=<?php echo urlencode($id_tim); ?>" 
                                                class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus tim <?php echo htmlspecialchars($nama_tim); ?>?\n\nTindakan ini tidak dapat dibatalkan!')" 
                                                title="Hapus Tim <?php echo htmlspecialchars($nama_tim); ?>">
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
                                Menampilkan <?php echo mysqli_num_rows($result); ?> dari <?php echo number_format($total_data); ?> tim 
                                <?php if ($total_pages > 1): ?>
                                    | Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?>
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Empty State -->
                <div class="table-container">
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <h4>
                            <?php if (!empty($search)): ?>
                                Tidak ada data yang ditemukan
                            <?php else: ?>
                                Belum Ada Data Tim
                            <?php endif; ?>
                        </h4>
                        <p>
                            <?php if (!empty($search)): ?>
                                Tidak ditemukan data tim yang sesuai dengan pencarian "<strong><?php echo htmlspecialchars($search); ?></strong>".
                                <br><a href="daftar_tim.php" class="btn-secondary-custom mt-3">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Tampilkan semua data
                                </a>
                            <?php else: ?>
                                Mulai dengan menambahkan tim pertama Anda ke dalam database KickOff
                            <?php endif; ?>
                        </p>
                        <?php if (empty($search)): ?>
                        <a href="tambah_tim.php" class="btn-primary-custom mt-3">
                            <i class="bi bi-plus-circle me-2"></i>
                            Tambah Tim Pertama
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