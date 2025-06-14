<?php
include_once("../../config.php");
requireAdmin();

// Konfigurasi pagination
$limit = 15; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filter status jika ada
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$status_query = '';
if (!empty($status_filter)) {
    $status_query = "WHERE STATUS = '$status_filter'";
}

// Hitung total data
$count_query = "SELECT COUNT(*) as total FROM feedback $status_query";
$count_result = mysqli_query($conn, $count_query);

if (!$count_result) {
    die("Query gagal: " . mysqli_error($conn));
}

$total_data = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_data / $limit);

// Query data feedback dengan urutan terbaru
$query = "SELECT * FROM feedback 
            $status_query 
            ORDER BY TANGGAL_FEEDBACK DESC 
            LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query gagal: " . mysqli_error($conn));
}

// Hitung statistik status
$stats_query = "SELECT 
    STATUS,
    COUNT(*) as jumlah
    FROM feedback 
    GROUP BY STATUS";
$stats_result = mysqli_query($conn, $stats_query);
$stats = [];
while($row = mysqli_fetch_assoc($stats_result)) {
    $stats[$row['STATUS']] = $row['jumlah'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Feedback - Admin KickOff</title>
    
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

        .stats-section {
            background: rgba(255,255,255,0.05);
            padding: 1.5rem 0;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: linear-gradient(135deg, #2a2a2a 0%, #3a3a3a 100%);
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }

        .stat-number {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 2rem;
            color: var(--accent-red);
        }

        .stat-label {
            color: rgba(255,255,255,0.8);
            font-weight: 500;
            margin-top: 0.5rem;
        }

        .filter-section {
            background: rgba(255,255,255,0.05);
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .filter-btn {
            background: rgba(255,255,255,0.1);
            color: white;
            border: 1px solid rgba(255,255,255,0.2);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            display: inline-block;
        }

        .filter-btn:hover {
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
        }

        .filter-btn.active {
            background: linear-gradient(135deg, var(--primary-red) 0%, var(--accent-red) 100%);
            border-color: var(--accent-red);
            color: white;
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

        .badge-pending {
            background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
            color: white;
        }

        .badge-reviewed {
            background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
            color: white;
        }

        .badge-resolved {
            background: linear-gradient(135deg, #4caf50 0%, #388e3c 100%);
            color: white;
        }

        .badge-rejected {
            background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
            color: white;
        }

        .feedback-message {
            max-height: 80px;
            overflow-y: auto;
            background: rgba(255,255,255,0.05);
            padding: 0.5rem;
            border-radius: 5px;
            font-size: 0.9rem;
            line-height: 1.4;
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

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            color: white;
        }

        .user-email {
            font-size: 0.875rem;
            color: rgba(255,255,255,0.6);
        }

        .feedback-date {
            font-size: 0.875rem;
            color: rgba(255,255,255,0.7);
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 1.5rem;
            }
            
            .table-responsive {
                border-radius: 10px;
            }
            
            .stat-card {
                margin-bottom: 1rem;
            }
            
            .feedback-message {
                max-height: 60px;
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
                        <i class="bi bi-chat-square-text me-2"></i>Kelola Feedback
                    </h1>
                    <p class="page-subtitle">Manajemen feedback dan saran dari pengguna KickOff</p>
                    <nav class="breadcrumb-nav">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="../../adminpage.php">
                                    <i class="bi bi-speedometer2 me-1"></i>Dashboard
                                </a>
                            </li>
                            <li class="breadcrumb-item active">Kelola Feedback</li>
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
    
    <!-- Content -->
    <main class="pb-4">
        <div class="container">
            <!-- Stats Info -->
            <div class="stats-info d-flex justify-content-between align-items-center">
                <div>
                    <strong>
                        <?php if (!empty($status_filter)): ?>
                            Feedback dengan status "<?php echo ucfirst($status_filter); ?>": <?php echo number_format(mysqli_num_rows($result)); ?>
                        <?php else: ?>
                            Total Feedback: <?php echo number_format($total_data); ?>
                        <?php endif; ?>
                    </strong>
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
                                    <th width="8%">ID</th>
                                    <th width="18%">Pengguna</th>
                                    <th width="35%">Pesan Feedback</th>
                                    <th width="10%">Status</th>
                                    <th width="14%">Tanggal</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = $offset + 1; 
                                while($row = mysqli_fetch_assoc($result)): 
                                    $id_feedback = $row['ID_FEEDBACK'];
                                    $nama_user = $row['NAMA_USER'];
                                    $email = $row['EMAIL'];
                                    $status = $row['STATUS'];
                                    $pesan = $row['PESAN'];
                                    $tanggal = $row['TANGGAL_FEEDBACK'];
                                    
                                    // Format tanggal
                                    $tanggal_formatted = date('d/m/Y H:i', strtotime($tanggal));
                                    
                                    // Status badge class
                                    $badge_class = 'badge-pending';
                                    switch($status) {
                                        case 'reviewed':
                                            $badge_class = 'badge-reviewed';
                                            break;
                                        case 'resolved':
                                            $badge_class = 'badge-resolved';
                                            break;
                                        case 'rejected':
                                            $badge_class = 'badge-rejected';
                                            break;
                                    }
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            #<?php echo $id_feedback; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="user-info">
                                            <span class="user-name"><?php echo htmlspecialchars($nama_user); ?></span>
                                            <span class="user-email"><?php echo htmlspecialchars($email); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="feedback-message">
                                            <?php echo nl2br(htmlspecialchars($pesan)); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <?php echo ucfirst($status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="feedback-date">
                                            <?php echo $tanggal_formatted; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="hapus_feedback.php?id=<?php echo $id_feedback; ?>" 
                                            class="btn btn-sm btn-danger" 
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus feedback dari <?php echo htmlspecialchars($nama_user); ?>?\n\nTindakan ini tidak dapat dibatalkan!')" 
                                            title="Hapus Feedback dari <?php echo htmlspecialchars($nama_user); ?>">
                                            <i class="bi bi-trash"></i>
                                        </a>
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
                            <a href="?page=<?php echo ($page-1); ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?>">
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
                                <a href="?page=<?php echo $i; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo ($page+1); ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?>">
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
                                Menampilkan <?php echo mysqli_num_rows($result); ?> dari <?php echo number_format($total_data); ?> feedback 
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
                        <i class="bi bi-chat-square-text"></i>
                        <h4>
                            <?php if (!empty($status_filter)): ?>
                                Tidak ada feedback dengan status "<?php echo ucfirst($status_filter); ?>"
                            <?php else: ?>
                                Belum Ada Feedback
                            <?php endif; ?>
                        </h4>
                        <p>
                            <?php if (!empty($status_filter)): ?>
                                Tidak ditemukan feedback dengan status "<?php echo ucfirst($status_filter); ?>".
                                <br><a href="daftar_feedback.php" class="btn-secondary-custom mt-3">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Tampilkan semua feedback
                                </a>
                            <?php else: ?>
                                Belum ada feedback yang masuk dari pengguna KickOff
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>