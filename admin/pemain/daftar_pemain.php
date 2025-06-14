<?php
include_once("../../config.php");
requireAdmin();

// Konfigurasi pagination khusus untuk pemain
$limit = 500; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Konfigurasi search
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$search_query = '';
if (!empty($search)) {
    $search_query = "WHERE p.NAMA_PEMAIN LIKE '%$search%' OR p.ID_PEMAIN LIKE '%$search%' OR p.POSISI LIKE '%$search%' OR p.NOMOR_PUNGGUNG LIKE '%$search%' OR t.NAMA_TIM LIKE '%$search%' OR t.ID_TIM LIKE '%$search%'";
}

// Hitung total data
$count_query = "SELECT COUNT(*) as total FROM pemain p 
                LEFT JOIN tim t ON p.ID_TIM = t.ID_TIM 
                $search_query";
$count_result = mysqli_query($conn, $count_query);

if (!$count_result) {
    die("Query gagal: " . mysqli_error($conn));
}

$total_data = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_data / $limit);

// Query data dengan JOIN untuk menampilkan nama tim
$query = "SELECT p.*, t.NAMA_TIM 
            FROM pemain p 
            LEFT JOIN tim t ON p.ID_TIM = t.ID_TIM 
            $search_query 
            ORDER BY p.ID_PEMAIN 
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
    <title>Daftar Pemain - Admin KickOff</title>
    
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

        .nomor-punggung {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-red) 0%, var(--accent-red) 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
            font-family: 'Montserrat', sans-serif;
        }

        .posisi-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .posisi-gk { background: #28a745; }
        .posisi-def { background: #007bff; }
        .posisi-mid { background: #ffc107; color: #000; }
        .posisi-att { background: #dc3545; }

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
                        <i class="bi bi-people me-2"></i>Kelola Pemain
                    </h1>
                    <p class="page-subtitle">Manajemen data pemain sepakbola dalam database KickOff</p>
                    <nav class="breadcrumb-nav">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="../../adminpage.php">
                                    <i class="bi bi-speedometer2 me-1"></i>Dashboard
                                </a>
                            </li>
                            <li class="breadcrumb-item active">Kelola Pemain</li>
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
                    <a href="tambah_pemain.php" class="btn-primary-custom">
                        <i class="bi bi-plus-circle me-2"></i>
                        Tambah Pemain Baru
                    </a>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <span class="text-light">
                        <i class="bi bi-info-circle me-2"></i>
                        Total: <strong><?php echo number_format($total_data); ?> Pemain</strong> terdaftar
                        <small class="d-block mt-1">Pagination: <?php echo $limit; ?> per halaman</small>
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
                                placeholder="Cari berdasarkan nama pemain, ID, posisi, nomor punggung, atau tim..." 
                                value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i> Cari
                        </button>
                    </div>
                    <?php if (!empty($search)): ?>
                    <div class="col-md-2">
                        <a href="daftar_pemain.php" class="btn btn-outline-secondary w-100">
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
                                    <th style="width:5%;">No</th>
                                    <th style="width:12%;">ID Pemain</th>
                                    <th style="width:10%;">No. Punggung</th>
                                    <th style="width:22%;">Nama Pemain</th>
                                    <th style="width:20%;">Posisi</th>
                                    <th style="width:21%;">Tim</th>
                                    <th style="width:10%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = $offset + 1; 
                                while($row = mysqli_fetch_assoc($result)): 
                                    $id_pemain = $row['ID_PEMAIN'];
                                    $id_tim = $row['ID_TIM'];
                                    $nama_tim = $row['NAMA_TIM'] ?? 'Tim tidak ditemukan';
                                    $nama_pemain = $row['NAMA_PEMAIN'];
                                    $nomor_punggung = $row['NOMOR_PUNGGUNG'];
                                    $posisi = $row['POSISI'];
                                    $tinggi_badan = $row['TINGGI_BADAN'] ?? 0;
                                    $berat_badan = $row['BERAT_BADAN'] ?? 0;
                                    
                                    // Tentukan class posisi
                                    $posisi_class = 'posisi-mid';
                                    if (strpos(strtolower($posisi), 'keeper') !== false || strpos(strtolower($posisi), 'gk') !== false) {
                                        $posisi_class = 'posisi-gk';
                                    } elseif (strpos(strtolower($posisi), 'def') !== false || strpos(strtolower($posisi), 'back') !== false) {
                                        $posisi_class = 'posisi-def';
                                    } elseif (strpos(strtolower($posisi), 'forward') !== false || strpos(strtolower($posisi), 'striker') !== false || strpos(strtolower($posisi), 'wing') !== false) {
                                        $posisi_class = 'posisi-att';
                                    }
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo htmlspecialchars($id_pemain); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="nomor-punggung">
                                            <?php echo htmlspecialchars($nomor_punggung); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($nama_pemain); ?></strong>
                                    </td>
                                    <td>
                                        <span class="posisi-badge <?php echo $posisi_class; ?>">
                                            <?php echo htmlspecialchars($posisi); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="badge bg-primary mb-1">
                                                <?php echo htmlspecialchars($id_tim); ?>
                                            </span>
                                            <br>
                                            <small class="text-light">
                                                <?php echo htmlspecialchars($nama_tim); ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="edit_pemain.php?id=<?php echo urlencode($id_pemain); ?>" 
                                                class="btn btn-sm btn-warning" 
                                                title="Edit Pemain <?php echo htmlspecialchars($nama_pemain); ?>">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="hapus_pemain.php?id=<?php echo urlencode($id_pemain); ?>" 
                                                class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus pemain <?php echo htmlspecialchars($nama_pemain); ?>?\n\nTindakan ini tidak dapat dibatalkan!')" 
                                                title="Hapus Pemain <?php echo htmlspecialchars($nama_pemain); ?>">
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
                                Menampilkan <?php echo mysqli_num_rows($result); ?> dari <?php echo number_format($total_data); ?> pemain 
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
                        <i class="bi bi-people"></i>
                        <h4>
                            <?php if (!empty($search)): ?>
                                Tidak ada data yang ditemukan
                            <?php else: ?>
                                Belum Ada Data Pemain
                            <?php endif; ?>
                        </h4>
                        <p>
                            <?php if (!empty($search)): ?>
                                Tidak ditemukan data pemain yang sesuai dengan pencarian "<strong><?php echo htmlspecialchars($search); ?></strong>".
                                <br><a href="daftar_pemain.php" class="btn-secondary-custom mt-3">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Tampilkan semua data
                                </a>
                            <?php else: ?>
                                Mulai dengan menambahkan pemain pertama Anda ke dalam database KickOff
                            <?php endif; ?>
                        </p>
                        <?php if (empty($search)): ?>
                        <a href="tambah_pemain.php" class="btn-primary-custom mt-3">
                            <i class="bi bi-plus-circle me-2"></i>
                            Tambah Pemain Pertama
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