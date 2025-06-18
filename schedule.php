<?php
include_once("config.php");
requireLogin();
$selected_league = $_GET['league'] ?? null;

if (!$selected_league) {
    header("Location: chooseliga.php");
    exit();
}

// Ambil data liga yang dipilih
$liga_query = "SELECT * FROM liga WHERE ID_LIGA = ?";
$liga_stmt = mysqli_prepare($conn, $liga_query);
mysqli_stmt_bind_param($liga_stmt, "s", $selected_league);
mysqli_stmt_execute($liga_stmt);
$liga_result = mysqli_stmt_get_result($liga_stmt);
$liga_data = mysqli_fetch_assoc($liga_result);

if (!$liga_data) {
    header("Location: chooseliga.php");
    exit();
}

// Mapping logo fallback untuk liga
$logo_mapping = [
    'LG001' => 'uploads/leagues/premier_league.png',
    'LG002' => 'uploads/leagues/la_liga.png',
    'LG003' => 'uploads/leagues/serie_a.png',
    'LG004' => 'uploads/leagues/bundesliga.png',
    'LG005' => 'uploads/leagues/ligue_1.png',
    'LG006' => 'uploads/leagues/eredivisie.png',
    'LG007' => 'uploads/leagues/liga_1_indonesia.png',
];

// Function untuk mendapatkan logo
function getLeagueLogo($id_liga, $logo_from_db, $logo_mapping) {
    if (!empty($logo_from_db) && file_exists($logo_from_db)) {
        return $logo_from_db;
    }
    
    if (isset($logo_mapping[$id_liga]) && file_exists($logo_mapping[$id_liga])) {
        return $logo_mapping[$id_liga];
    }
    
    return 'https://via.placeholder.com/70x70/FF6B6B/FFFFFF?text=' . substr($id_liga, -2);
}

// Function untuk mendapatkan logo tim dari database dengan fallback
function getTeamLogo($logo_filename, $team_name, $team_id) {
    // Path folder logo tim
    $team_logo_path = 'uploads/teams/';
    if (!empty($logo_filename)) {
        $possible_extensions = ['', '.png', '.jpg', '.jpeg', '.gif', '.webp'];
        
        foreach ($possible_extensions as $ext) {
            $full_path = $team_logo_path . $logo_filename . $ext;
            if (file_exists($full_path)) {
                return $full_path;
            }
        }
        $direct_path = $team_logo_path . $logo_filename;
        if (file_exists($direct_path)) {
            return $direct_path;
        }
    }
    
    if (!empty($team_name)) {
        $normalized_name = strtolower(str_replace(' ', '_', $team_name));
        $possible_files = [
            $normalized_name . '.png',
            $normalized_name . '.jpg',
            $normalized_name . '.jpeg',
            str_replace('_', '', $normalized_name) . '.png',
            str_replace('_', '', $normalized_name) . '.jpg'
        ];
        
        foreach ($possible_files as $filename) {
            $full_path = $team_logo_path . $filename;
            if (file_exists($full_path)) {
                return $full_path;
            }
        }
    }
    
    $team_short = '';
    if (!empty($team_name)) {
        $words = array_filter(explode(' ', $team_name));
        if (count($words) >= 2) {
            $team_short = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        } else {
            $team_short = strtoupper(substr($team_name, 0, 3));
        }
    } else {
        $team_short = strtoupper(substr($team_id, -3));
    }
    
    $colors = ['FF6B6B', '4ECDC4', '45B7D1', 'FFA07A', '98D8C8', 'F7DC6F', 'BB8FCE', '85C1E9', 'F8C471', 'D7BDE2'];
    $color_index = abs(crc32($team_name ?: $team_id)) % count($colors);
    $bg_color = $colors[$color_index];
    
    return 'https://via.placeholder.com/45x45/' . $bg_color . '/FFFFFF?text=' . urlencode($team_short);
}

$matches_query = "
    SELECT 
        p.*,
        t1.NAMA_TIM as away_team_name,
        t1.LOGO_TIM as away_team_logo,
        t2.NAMA_TIM as home_team_name,
        t2.LOGO_TIM as home_team_logo,
        s.NAMA_STADION as stadium_name
    FROM pertandingan p
    LEFT JOIN tim t1 ON p.ID_AWAYTEAM = t1.ID_TIM
    LEFT JOIN tim t2 ON p.ID_HOMETEAM = t2.ID_TIM  
    LEFT JOIN stadion s ON p.ID_STADION = s.ID_STADION
    WHERE p.ID_LIGA = ?
    ORDER BY p.TANGGAL ASC, p.WAKTU ASC
";

$matches_stmt = mysqli_prepare($conn, $matches_query);
mysqli_stmt_bind_param($matches_stmt, "s", $selected_league);
mysqli_stmt_execute($matches_stmt);
$matches_result = mysqli_stmt_get_result($matches_stmt);

$matches_by_date = [];
$today = date('Y-m-d');

while ($match = mysqli_fetch_assoc($matches_result)) {
    $match_date = $match['TANGGAL'];
    
    $match_datetime = $match_date . ' ' . $match['WAKTU'];
    $now = date('Y-m-d H:i:s');
    
    if ($match_datetime < $now) {
        $match['status'] = 'finished';
    } elseif ($match_date == $today) {
        $match['status'] = 'today';
    } else {
        $match['status'] = 'upcoming';
    }
    
    // Format tanggal untuk display
    $formatted_date = date('l, d M Y', strtotime($match_date));
    if ($match_date == $today) {
        $formatted_date = 'Today';
    } elseif ($match_date == date('Y-m-d', strtotime('+1 day'))) {
        $formatted_date = 'Tomorrow';
    }
    
    $matches_by_date[$formatted_date][] = $match;
}

$league_logo = getLeagueLogo($liga_data['ID_LIGA'], $liga_data['LOGO'] ?? '', $logo_mapping);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($liga_data['NAMA_LIGA']); ?> Schedule - KickOff</title>
    <meta name="description" content="View <?php echo htmlspecialchars($liga_data['NAMA_LIGA']); ?> match schedules and upcoming fixtures.">
    <meta name="keywords" content="<?php echo htmlspecialchars($liga_data['NAMA_LIGA']); ?>, football, match schedule">
    <meta name="author" content="KickOff">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@300;400;500;600;700;800;900&family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/style_schedule.css">
</head>

<body>
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
                        <a class="nav-link" href="index.php#about">About Us</a>
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

    <!-- League Header -->
    <header class="league-header" role="banner">
        <div class="container">
            <div class="league-header-content">
                <figure class="league-logo-header" aria-label="<?php echo htmlspecialchars($liga_data['NAMA_LIGA']); ?> Logo">
                    <img src="<?php echo htmlspecialchars($league_logo); ?>" 
                        alt="<?php echo htmlspecialchars($liga_data['NAMA_LIGA']); ?> Official Logo" 
                        onerror="this.src='https://via.placeholder.com/70x70/FF6B6B/FFFFFF?text=<?php echo substr($liga_data['ID_LIGA'], -2); ?>'">
                </figure>
                <h1 class="league-title"><?php echo strtoupper(htmlspecialchars($liga_data['NAMA_LIGA'])); ?></h1>
                <p class="league-subtitle">Professional Football League</p>
                <nav class="breadcrumb-custom" role="navigation" aria-label="Breadcrumb">
                    <a href="index.php" aria-label="Home"><i class="fas fa-home" aria-hidden="true"></i></a>
                    <i class="fas fa-chevron-right" style="font-size: 12px; opacity: 0.6;" aria-hidden="true"></i>
                    <a href="chooseliga.php">Choose League</a>
                    <i class="fas fa-chevron-right" style="font-size: 12px; opacity: 0.6;" aria-hidden="true"></i>
                    <span aria-current="page"><?php echo htmlspecialchars($liga_data['NAMA_LIGA']); ?> Schedule</span>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content" role="main">
        <div class="container">
            <!-- Filter Section -->
            <section class="filter-section" aria-label="Match Filters">
                <h2 class="visually-hidden">Filter Matches</h2>
                <div class="filter-tabs" role="tablist" aria-label="Match filter tabs">
                    <button class="filter-tab active" role="tab" aria-selected="true" aria-controls="all-matches" onclick="filterMatches('all')">All Matches</button>
                    <button class="filter-tab" role="tab" aria-selected="false" aria-controls="today-matches" onclick="filterMatches('today')">Today</button>
                    <button class="filter-tab" role="tab" aria-selected="false" aria-controls="upcoming-matches" onclick="filterMatches('upcoming')">Upcoming</button>
                    <button class="filter-tab" role="tab" aria-selected="false" aria-controls="finished-matches" onclick="filterMatches('finished')">Finished</button>
                </div>
            </section>

            <!-- Matches by Date -->
            <?php if (empty($matches_by_date)): ?>
                <!-- No Matches State -->
                <section class="matches-section" aria-labelledby="no-matches-heading">
                    <div class="text-center py-5">
                        <div class="empty-icon mb-3">
                            <i class="bi bi-calendar-x" style="font-size: 4rem; color: var(--accent-red); opacity: 0.6;"></i>
                        </div>
                        <h3 id="no-matches-heading">No Matches Scheduled</h3>
                        <p class="text-muted">There are currently no matches scheduled for <?php echo htmlspecialchars($liga_data['NAMA_LIGA']); ?>.</p>
                        <a href="chooseliga.php" class="btn btn-primary mt-3">
                            <i class="bi bi-arrow-left me-2"></i>Choose Another League
                        </a>
                    </div>
                </section>
            <?php else: ?>
                <?php foreach ($matches_by_date as $date => $matches): ?>
                <section class="matches-section" aria-labelledby="<?php echo md5($date); ?>-heading">
                    <h2 class="section-date" id="<?php echo md5($date); ?>-heading">
                        <?php if ($date === 'Today'): ?>
                            <?php echo $date; ?>
                        <?php else: ?>
                            <time datetime="<?php echo date('Y-m-d', strtotime($date)); ?>"><?php echo $date; ?></time>
                        <?php endif; ?>
                    </h2>
                    <div class="matches-grid" role="list" aria-label="<?php echo $date; ?>'s matches">
                        <?php foreach ($matches as $index => $match): ?>
                            <?php
                            // Generate unique ID
                            $match_id = 'match' . $match['ID_PERTANDINGAN'];
                            $teams_id = $match_id . '-teams';
                            $info_id = $match_id . '-info';
                            
                            // Format waktu untuk display
                            $display_time = date('H:i', strtotime($match['WAKTU']));
                            $display_date = date('D, M d', strtotime($match['TANGGAL']));
                            
                            // Status untuk display
                            $status_display = ucfirst($match['status']);
                            if ($match['status'] === 'today') {
                                $status_display = 'Today';
                            }
                            
                            $away_logo = getTeamLogo($match['away_team_logo'], $match['away_team_name'], $match['ID_AWAYTEAM']);
                            $home_logo = getTeamLogo($match['home_team_logo'], $match['home_team_name'], $match['ID_HOMETEAM']);
                            ?>
                            <article class="match-card" 
                                    data-status="<?php echo $match['status']; ?>" 
                                    role="listitem" 
                                    tabindex="0" 
                                    aria-labelledby="<?php echo $teams_id; ?>" 
                                    aria-describedby="<?php echo $info_id; ?>"
                                    onclick="window.location.href='detailschedule.php?match=<?php echo $match['ID_PERTANDINGAN']; ?>'">
                                <div class="match-teams" id="<?php echo $teams_id; ?>">
                                    <!-- Away Team -->
                                    <div class="team">
                                        <figure class="team-logo" aria-label="<?php echo htmlspecialchars($match['away_team_name'] ?? 'Away Team'); ?> logo">
                                            <img src="<?php echo htmlspecialchars($away_logo); ?>" 
                                                alt="<?php echo htmlspecialchars($match['away_team_name'] ?? 'Away Team'); ?> team logo" 
                                                onerror="this.src='https://via.placeholder.com/45x45/FF6B6B/FFFFFF?text=<?php echo substr($match['ID_AWAYTEAM'], -2); ?>'">
                                        </figure>
                                        <h3 class="team-name"><?php echo htmlspecialchars($match['away_team_name'] ?? 'Away Team'); ?></h3>
                                    </div>
                                    
                                    <!-- VS Section -->
                                    <div class="vs-section" aria-label="Versus">
                                        <div class="vs-text">VS</div>
                                    </div>
                                    
                                    <!-- Home Team -->
                                    <div class="team">
                                        <figure class="team-logo" aria-label="<?php echo htmlspecialchars($match['home_team_name'] ?? 'Home Team'); ?> logo">
                                            <img src="<?php echo htmlspecialchars($home_logo); ?>" 
                                                alt="<?php echo htmlspecialchars($match['home_team_name'] ?? 'Home Team'); ?> team logo" 
                                                onerror="this.src='https://via.placeholder.com/45x45/4ECDC4/FFFFFF?text=<?php echo substr($match['ID_HOMETEAM'], -2); ?>'">
                                        </figure>
                                        <h3 class="team-name"><?php echo htmlspecialchars($match['home_team_name'] ?? 'Home Team'); ?></h3>
                                    </div>
                                </div>
                                
                                <div class="match-info" id="<?php echo $info_id; ?>">
                                    <time class="match-time" datetime="<?php echo $match['WAKTU']; ?>"><?php echo $display_time; ?></time>
                                    <time class="match-date" datetime="<?php echo $match['TANGGAL']; ?>"><?php echo $display_date; ?></time>
                                    <span class="match-status" aria-label="Match status"><?php echo $status_display; ?></span>
                                    <?php if (!empty($match['stadium_name'])): ?>
                                        <span class="match-stadium" aria-label="Stadium">
                                            <i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($match['stadium_name']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

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
        function filterMatches(type) {
            document.querySelectorAll('.filter-tab').forEach(tab => {
                tab.classList.remove('active');
                tab.setAttribute('aria-selected', 'false');
            });
            
            event.target.classList.add('active');
            event.target.setAttribute('aria-selected', 'true');
            
            const matchCards = document.querySelectorAll('.match-card');
            const sections = document.querySelectorAll('.matches-section');
            
            if (type === 'all') {
                sections.forEach(section => {
                    section.style.display = 'block';
                });
                matchCards.forEach(card => {
                    card.style.display = 'block';
                    card.setAttribute('aria-hidden', 'false');
                });
            } else {
                sections.forEach(section => {
                    section.style.display = 'none';
                });
                
                matchCards.forEach(card => {
                    const status = card.getAttribute('data-status');
                    const section = card.closest('.matches-section');
                    
                    if ((type === 'today' && (status === 'today' || status === 'upcoming')) ||
                        (type === status)) {
                        card.style.display = 'block';
                        card.setAttribute('aria-hidden', 'false');
                        section.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                        card.setAttribute('aria-hidden', 'true');
                    }
                });
                
                if (type === 'today') {
                    const todaySection = document.querySelector('.matches-section h2[id*="heading"]');
                    if (todaySection && todaySection.textContent.includes('Today')) {
                        todaySection.closest('.matches-section').style.display = 'block';
                    }
                }
            }
            
            matchCards.forEach(card => {
                card.style.transition = 'all 0.3s ease';
            });

            const visibleCards = document.querySelectorAll('.match-card[aria-hidden="false"]').length;
            const announcement = `Showing ${visibleCards} ${type === 'all' ? '' : type} matches`;
            announceToScreenReader(announcement);
        }

        function announceToScreenReader(message) {
            const announcement = document.createElement('div');
            announcement.setAttribute('aria-live', 'polite');
            announcement.setAttribute('aria-atomic', 'true');
            announcement.className = 'visually-hidden';
            announcement.textContent = message;
            document.body.appendChild(announcement);
            
            setTimeout(() => {
                document.body.removeChild(announcement);
            }, 1000);
        }

        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar-custom');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(0, 0, 0, 0.95)';
            } else {
                navbar.style.background = 'rgba(0, 0, 0, 0.9)';
            }
        });

        document.querySelectorAll('.match-card').forEach(card => {
            card.addEventListener('click', function() {
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });

            card.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.click();
                }
            });
        });

        document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.click();
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const matchCards = document.querySelectorAll('.match-card');
            matchCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });

            document.querySelectorAll('.match-card').forEach(card => {
                card.setAttribute('aria-hidden', 'false');
            });
        });
    </script>

    <style>
    .match-stadium {
        display: block;
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.7);
        margin-top: 0.25rem;
    }

    .empty-icon {
        opacity: 0.6;
    }

    .match-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    .match-card[data-status="finished"] {
        opacity: 0.8;
    }

    .match-card[data-status="today"] {
        border-left: 3px solid var(--accent-red);
    }

    .filter-section {
        margin-bottom: 2rem;
        top: 80px;
        backdrop-filter: blur(10px);
        padding: 1rem 0;
        z-index: 100;
    }
    </style>
</body>
</html>