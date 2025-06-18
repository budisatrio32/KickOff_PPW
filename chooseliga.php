<?php
include_once("config.php");
requireLogin();

// Ambil data liga dari database dengan informasi tambahan
$liga_query = "SELECT 
    l.*,
    COUNT(DISTINCT p.ID_PERTANDINGAN) as total_pertandingan,
    COUNT(DISTINCT t.ID_TIM) as total_tim,
    MIN(p.TANGGAL) as pertandingan_terdekat
    FROM liga l
    LEFT JOIN pertandingan p ON l.ID_LIGA = p.ID_LIGA 
    LEFT JOIN tim t ON l.ID_LIGA = t.ID_LIGA
    GROUP BY l.ID_LIGA, l.NAMA_LIGA, l.LOGO
    ORDER BY l.NAMA_LIGA ASC";

$liga_result = mysqli_query($conn, $liga_query);

if (!$liga_result) {
    die("Error fetching leagues: " . mysqli_error($conn));
}

// ✅ FIXED: Mapping berdasarkan ID_LIGA yang sesuai dengan database kamu
$logo_mapping = [
    'LG001' => 'uploads/leagues/premier_league.png',      // Premier League
    'LG002' => 'uploads/leagues/la_liga.png',             // La Liga
    'LG003' => 'uploads/leagues/serie_a.png',             // Serie A
    'LG004' => 'uploads/leagues/bundesliga.png',          // Bundesliga
    'LG005' => 'uploads/leagues/ligue_1.png',             // Ligue 1
    'LG006' => 'uploads/leagues/eredivisie.png',          // Eredivisie
    'LG007' => 'uploads/leagues/liga_1_indonesia.png',    // Liga 1 Indonesia
];

// ✅ FIXED: Deskripsi berdasarkan ID_LIGA yang sesuai
$liga_descriptions = [
    'LG001' => "England's top-flight football league with global superstars and intense competition.",
    'LG002' => "Spain's premier football league featuring Barcelona, Real Madrid, and other top clubs.",
    'LG003' => "Italy's premier football league known for tactical excellence and historic clubs.",
    'LG004' => "Germany's top football league featuring Bayern Munich and Borussia Dortmund.",
    'LG005' => "France's top football division home to PSG, Monaco, and emerging talents.",
    'LG006' => "Netherlands' premier football league known for developing young talents.",
    'LG007' => "Indonesia's top professional football league featuring the best local clubs.",
];

// Function untuk mendapatkan logo path yang benar
function getLogoPath($id_liga, $logo_from_db, $logo_mapping) {
    // 1. Cek apakah ada logo dari database dan file exists
    if (!empty($logo_from_db)) {
        // Normalize path separators untuk Windows/Linux compatibility
        $normalized_path = str_replace('\\', '/', $logo_from_db);
        
        // Cek apakah file exists
        if (file_exists($normalized_path)) {
            return $normalized_path;
        }
        
        // Coba dengan path uploads/leagues/ jika logo dari DB hanya filename
        $uploads_path = 'uploads/leagues/' . basename($normalized_path);
        if (file_exists($uploads_path)) {
            return $uploads_path;
        }
    }
    
    // 2. Fallback ke mapping berdasarkan ID_LIGA
    if (isset($logo_mapping[$id_liga])) {
        $fallback_path = $logo_mapping[$id_liga];
        if (file_exists($fallback_path)) {
            return $fallback_path;
        }
    }
    
    // 3. Ultimate fallback - placeholder dengan nama liga
    $liga_short = substr($id_liga, -2); // Ambil 2 digit terakhir (01, 02, etc)
    return 'https://via.placeholder.com/80x80/D84040/FFFFFF?text=' . $liga_short;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Your League - KickOff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@300;400;500;600;700;800;900&family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/style_chooseliga.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo isLoggedIn() ? 'index.php' : 'index.php'; ?>">
                <img src="asset/Logo.png" alt="Kickoff Logo" style="width: 80px; height: 65px;" class="me-2">
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (!isLoggedIn()): ?>
                        <!-- Guest Navigation -->
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php#contact">Contact Us</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="chooseliga.php">Match Schedule</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-outline-light btn-sm ms-2 px-3" href="login.php">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Login
                            </a>
                        </li>
                    <?php else: ?>
                        <!-- Logged In Navigation -->
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="chooseliga.php">Leagues</a>
                        </li>
                        
                        <?php if (isAdmin()): ?>
                            <!-- Admin Only Links -->
                            <li class="nav-item">
                                <a class="nav-link" href="adminpage.php">
                                    <i class="bi bi-gear me-1"></i>Admin
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Simple User Info -->
                        <li class="nav-item">
                            <span class="nav-link text-white">
                                Selamat datang, <strong><?php echo htmlspecialchars(getCurrentUserName()); ?></strong>
                                <?php if (isAdmin()): ?>
                                    <span class="badge bg-danger ms-1">Admin</span>
                                <?php endif; ?>
                            </span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-outline-light btn-sm ms-2 px-3" href="logout.php" onclick="return confirm('Yakin ingin logout?')">
                                <i class="bi bi-box-arrow-right me-1"></i>Logout
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header <?php echo isLoggedIn() ? 'pt-5' : 'pt-5'; ?>">
                <h1 class="page-title">Choose Your League</h1>
                <p class="page-subtitle">
                    Find your favorite league and never miss a match! KickOff brings you live 
                    schedules, real-time scores, and exclusive updates all in one place. Stay connected 
                    with every kick, every goal, and every victory.
                </p>
                
                <!-- Statistics Info -->
                <div class="leagues-stats mt-4">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="stat-item">
                                <h3><?php echo mysqli_num_rows($liga_result); ?></h3>
                                <p>Available Leagues</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-item">
                                <?php 
                                $total_pertandingan_query = "SELECT COUNT(*) as total FROM pertandingan";
                                $total_pertandingan_result = mysqli_query($conn, $total_pertandingan_query);
                                $total_pertandingan = mysqli_fetch_assoc($total_pertandingan_result)['total'];
                                ?>
                                <h3><?php echo number_format($total_pertandingan); ?></h3>
                                <p>Total Matches</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-item">
                                <?php 
                                $total_tim_query = "SELECT COUNT(*) as total FROM tim";
                                $total_tim_result = mysqli_query($conn, $total_tim_query);
                                $total_tim = mysqli_fetch_assoc($total_tim_result)['total'];
                                ?>
                                <h3><?php echo number_format($total_tim); ?></h3>
                                <p>Registered Teams</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leagues Grid -->
            <div class="leagues-grid">
                <?php if (mysqli_num_rows($liga_result) > 0): ?>
                    <?php while ($liga = mysqli_fetch_assoc($liga_result)): 
                        $id_liga = $liga['ID_LIGA'];
                        $nama_liga = $liga['NAMA_LIGA'];
                        $logo_from_db = $liga['LOGO'];
                        $total_pertandingan = $liga['total_pertandingan'];
                        $total_tim = $liga['total_tim'];
                        
                        // Dapatkan logo path yang benar
                        $logo_liga = getLogoPath($id_liga, $logo_from_db, $logo_mapping);
                        
                        // Fallback description berdasarkan ID_LIGA yang sesuai
                        $description = $liga_descriptions[$id_liga] ?? "Professional football league featuring top teams and exciting matches.";
                        
                        // Generate placeholder text untuk fallback
                        $placeholder_text = substr($id_liga, -2); // LG001 -> 01
                    ?>
                        <div class="league-card" 
                            data-league-id="<?php echo htmlspecialchars($id_liga); ?>"
                            data-league-name="<?php echo htmlspecialchars($nama_liga); ?>"
                            onclick="selectLeague('<?php echo htmlspecialchars($id_liga); ?>', 'schedule.php?league=<?php echo urlencode($id_liga); ?>')">
                            <div class="league-logo">
                                <img src="<?php echo htmlspecialchars($logo_liga); ?>" 
                                    alt="<?php echo htmlspecialchars($nama_liga); ?>" 
                                    onerror="this.src='https://via.placeholder.com/80x80/D84040/FFFFFF?text=<?php echo $placeholder_text; ?>'"
                                    loading="lazy"
                                    style="width: 80px; height: 80px; object-fit: contain;">
                            </div>
                            <h3 class="league-name"><?php echo htmlspecialchars($nama_liga); ?></h3>
                            <p class="league-description"><?php echo htmlspecialchars($description); ?></p>
                            
                            <!-- League Stats -->
                            <div class="league-stats">
                                <div class="stat-row">
                                    <span class="stat-label">
                                        <i class="bi bi-calendar-event me-1"></i>Matches:
                                    </span>
                                    <span class="stat-value"><?php echo number_format($total_pertandingan); ?></span>
                                </div>
                                <div class="stat-row">
                                    <span class="stat-label">
                                        <i class="bi bi-people-fill me-1"></i>Teams:
                                    </span>
                                    <span class="stat-value"><?php echo number_format($total_tim); ?></span>
                                </div>
                            </div>
                            
                            <!-- Debug Info (hanya untuk admin) -->
                            <?php if (isAdmin()): ?>
                                <div class="admin-actions mt-2">
                                    <small class="text-danger">
                                        <i class="bi bi-gear me-1"></i>Admin: Manage matches
                                    </small>
                                    <!-- Debug info untuk troubleshooting -->
                                    <div style="font-size: 10px; color: #999; margin-top: 5px; background: rgba(0,0,0,0.3); padding: 5px; border-radius: 3px;">
                                        <strong>Debug Info:</strong><br>
                                        ID: <?php echo $id_liga; ?><br>
                                        DB Logo: <?php echo $logo_from_db ? basename($logo_from_db) : 'NULL'; ?><br>
                                        Final Path: <?php echo basename($logo_liga); ?><br>
                                        File Exists: <?php echo file_exists($logo_liga) ? 'YES' : 'NO'; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <!-- Empty State -->
                    <div class="col-12">
                        <div class="empty-state text-center py-5">
                            <div class="empty-icon mb-3">
                                <i class="bi bi-trophy" style="font-size: 4rem; color: var(--accent-red);"></i>
                            </div>
                            <h3>No Leagues Available</h3>
                            <p class="text-danger">No football leagues have been set up yet. Please check back later.</p>
                            <?php if (isAdmin()): ?>
                                <a href="admin/pages/liga/tambah_liga.php" class="btn btn-primary mt-3">
                                    <i class="bi bi-plus-circle me-2"></i>Add First League
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="footer-brand">
                        <div class="footer-logo d-flex align-items-center">
                            <img src="asset/Logo.png" alt="Kickoff Logo" style="width: 80px; height: 65px;" class="me-2">
                        </div>
                    </div>
                    <p class="footer-description">
                        At KickOff, we are committed to connecting you with the world of football through precise and reliable match schedules.
                        From local leagues to international tournaments, we offer a comprehensive view of upcoming fixtures.
                        No scores, no spoilers — just accurate timing for every game that matters.
                    </p>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Contact Information</h5>
                    <div class="footer-contact-item">
                        <div class="footer-contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="footer-contact-text">
                            <strong>KickOff HQ</strong><br>
                            <span>Jl. Sudirman No. 45, Senayan, Yogyakarta, Indonesia</span>
                        </div>
                    </div>
                    
                    <div class="footer-contact-item">
                        <div class="footer-contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="footer-contact-text">
                            <span>+62 812-3456-7890</span>
                        </div>
                    </div>
                    
                    <div class="footer-contact-item">
                        <div class="footer-contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="footer-contact-text">
                            <span>info@kickoff.com</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Quick Links</h5>
                    <div class="footer-links">
                        <a href="#" class="footer-link">
                            <i class="fas fa-file-alt"></i>
                            <span>Privacy Policy</span>
                        </a>
                        <a href="#" class="footer-link">
                            <i class="fas fa-shield-alt"></i>
                            <span>Terms & Conditions</span>
                        </a>
                        <a href="#" class="footer-link">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Match Schedule Guide</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Social Links Bar -->
            <div class="social-bar">
                <div class="social-links-footer">
                    <a href="#" class="social-link-footer">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="social-link-footer">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="social-link-footer">
                        <i class="fab fa-linkedin"></i>
                    </a>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; 2025 KickOff. All Rights Reserved.<br>
                "Bringing every match closer to you, one score at a time."</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectLeague(leagueId, url) {
            // Add selection feedback
            const card = event.currentTarget;
            const leagueName = card.getAttribute('data-league-name');
            
            // Visual feedback
            card.style.transform = 'scale(0.95)';
            card.style.background = 'rgba(216, 64, 64, 0.2)';
            card.style.borderColor = 'var(--secondary-red)';
            
            // Show loading state
            const originalContent = card.innerHTML;
            card.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-danger mb-2" role="status" style="width: 2rem; height: 2rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="text-white">Loading ${leagueName}...</div>
                </div>
            `;
            
            // Store selection in localStorage for the schedule page
            localStorage.setItem('selectedLeague', JSON.stringify({
                id: leagueId,
                name: leagueName,
                timestamp: new Date().getTime()
            }));
            
            // Reset after animation and redirect
            setTimeout(() => {
                card.innerHTML = originalContent;
                card.style.background = 'rgba(255, 255, 255, 0.1)';
                card.style.borderColor = 'rgba(255, 255, 255, 0.1)';
                card.style.transform = '';
                
                // Redirect to league schedule
                window.location.href = url;
            }, 800);
            
            console.log('Selected league:', leagueId, leagueName);
        }

        // Navbar background change on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar-custom');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(0, 0, 0, 0.95)';
            } else {
                navbar.style.background = 'rgba(0, 0, 0, 0.8)';
            }
        });

        // Add hover effects for league cards
        document.querySelectorAll('.league-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 8px 25px rgba(0,0,0,0.15)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 4px 15px rgba(0,0,0,0.1)';
            });
        });

        // Add entrance animations
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.league-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 150);
            });
        });

        // Add keyboard navigation
        document.querySelectorAll('.league-card').forEach(card => {
            card.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.click();
                }
            });
            
            // Make cards focusable
            card.setAttribute('tabindex', '0');
        });
    </script>

    <style>
    .leagues-stats {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 15px;
        padding: 2rem 1rem;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .stat-item h3 {
        font-family: 'Montserrat', sans-serif;
        font-weight: 700;
        font-size: 2.5rem;
        color: var(--accent-red);
        margin-bottom: 0.5rem;
    }

    .stat-item p {
        color: rgba(255, 255, 255, 0.8);
        font-weight: 500;
        margin: 0;
    }

    .league-stats {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .stat-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .stat-label {
        color: rgba(255, 255, 255, 0.7);
    }

    .stat-value {
        color: white;
        font-weight: 600;
    }

    .empty-state {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 15px;
        margin: 2rem 0;
    }

    .empty-icon {
        opacity: 0.6;
    }

    /* Loading animation */
    .spinner-border {
        animation: spinner-border .75s linear infinite;
    }

    @keyframes spinner-border {
        to {
            transform: rotate(360deg);
        }
    }
    </style>
</body>
</html>