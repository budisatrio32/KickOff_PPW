<?php
include_once("config.php");
requireLogin();

// Ambil parameter match ID dari URL
$match_id = $_GET['match'] ?? null;

if (!$match_id) {
    header("Location: chooseliga.php");
    exit();
}

// Query untuk mendapatkan detail pertandingan
$match_query = "
    SELECT 
        p.*,
        t1.NAMA_TIM as away_team_name,
        t1.LOGO_TIM as away_team_logo,
        t1.PELATIH as away_team_coach,
        t2.NAMA_TIM as home_team_name,
        t2.LOGO_TIM as home_team_logo,
        t2.PELATIH as home_team_coach,
        s.NAMA_STADION as stadium_name,
        s.LOKASI as stadium_location,
        s.KAPASITAS as stadium_capacity,
        l.NAMA_LIGA as league_name,
        l.LOGO as league_logo,
        m.TAHUN_MULAI,
        m.TAHUN_SELESAI
    FROM pertandingan p
    LEFT JOIN tim t1 ON p.ID_AWAYTEAM = t1.ID_TIM
    LEFT JOIN tim t2 ON p.ID_HOMETEAM = t2.ID_TIM  
    LEFT JOIN stadion s ON p.ID_STADION = s.ID_STADION
    LEFT JOIN liga l ON p.ID_LIGA = l.ID_LIGA
    LEFT JOIN musim m ON p.ID_MUSIM = m.ID_MUSIM
    WHERE p.ID_PERTANDINGAN = ?
";

$match_stmt = mysqli_prepare($conn, $match_query);
mysqli_stmt_bind_param($match_stmt, "s", $match_id);
mysqli_stmt_execute($match_stmt);
$match_result = mysqli_stmt_get_result($match_stmt);
$match_data = mysqli_fetch_assoc($match_result);

if (!$match_data) {
    header("Location: chooseliga.php");
    exit();
}

// Function untuk mendapatkan logo tim dengan fallback
function getTeamLogo($logo_filename, $team_name, $team_id) {
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
    
    return 'https://via.placeholder.com/90x90/' . $bg_color . '/FFFFFF?text=' . urlencode($team_short);
}

// Query untuk mendapatkan pemain tim away
$away_players_query = "
    SELECT 
        pm.NOMOR_PUNGGUNG,
        pm.NAMA_PEMAIN,
        pm.POSISI
    FROM pemain pm
    WHERE pm.ID_TIM = ?
    ORDER BY pm.NOMOR_PUNGGUNG ASC
";

$away_players_stmt = mysqli_prepare($conn, $away_players_query);
mysqli_stmt_bind_param($away_players_stmt, "s", $match_data['ID_AWAYTEAM']);
mysqli_stmt_execute($away_players_stmt);
$away_players_result = mysqli_stmt_get_result($away_players_stmt);
$away_players = [];
while ($player = mysqli_fetch_assoc($away_players_result)) {
    $away_players[] = $player;
}

// Query untuk mendapatkan pemain tim home
$home_players_query = "
    SELECT 
        pm.NOMOR_PUNGGUNG,
        pm.NAMA_PEMAIN,
        pm.POSISI
    FROM pemain pm
    WHERE pm.ID_TIM = ?
    ORDER BY pm.NOMOR_PUNGGUNG ASC
";

$home_players_stmt = mysqli_prepare($conn, $home_players_query);
mysqli_stmt_bind_param($home_players_stmt, "s", $match_data['ID_HOMETEAM']);
mysqli_stmt_execute($home_players_stmt);
$home_players_result = mysqli_stmt_get_result($home_players_stmt);
$home_players = [];
while ($player = mysqli_fetch_assoc($home_players_result)) {
    $home_players[] = $player;
}

// Set data untuk tampilan
$away_logo = getTeamLogo($match_data['away_team_logo'], $match_data['away_team_name'], $match_data['ID_AWAYTEAM']);
$home_logo = getTeamLogo($match_data['home_team_logo'], $match_data['home_team_name'], $match_data['ID_HOMETEAM']);

// Format tanggal dan waktu
$match_date = date('D, M d', strtotime($match_data['TANGGAL']));
$match_time = date('H:i', strtotime($match_data['WAKTU']));
$match_datetime = $match_date . ' - ' . $match_time;

// Tentukan status pertandingan
$match_full_datetime = $match_data['TANGGAL'] . ' ' . $match_data['WAKTU'];
$now = date('Y-m-d H:i:s');
$match_status = 'upcoming';

if ($match_full_datetime < $now) {
    $match_status = 'finished';
} elseif ($match_data['TANGGAL'] == date('Y-m-d')) {
    $match_status = 'today';
}

// URL untuk kembali ke schedule
$back_url = "schedule.php?league=" . urlencode($match_data['ID_LIGA']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Match Details - <?php echo htmlspecialchars($match_data['away_team_name'] . ' vs ' . $match_data['home_team_name']); ?> - KickOff</title>
    <meta name="description" content="Complete match information for <?php echo htmlspecialchars($match_data['away_team_name'] . ' vs ' . $match_data['home_team_name']); ?> including lineups, stadium details, and match statistics">
    <meta name="keywords" content="<?php echo htmlspecialchars($match_data['away_team_name'] . ', ' . $match_data['home_team_name'] . ', ' . $match_data['league_name']); ?>, football, match details">
    <meta name="author" content="KickOff">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@300;400;500;600;700;800;900&family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/style_detailschedule.css">
</head>
<body>
    
    <div class="match-detail-container">
        <!-- Match Header -->
        <header class="match-header">
            <div class="match-header-content">
                <button class="back-button" onclick="window.location.href='<?php echo htmlspecialchars($back_url); ?>'" aria-label="Go back to schedule">
                    <i class="fas fa-arrow-left" aria-hidden="true"></i>
                </button>

                <div class="teams-showcase">
                    <!-- Away Team -->
                    <div class="team-showcase">
                        <figure class="team-logo-large" aria-label="<?php echo htmlspecialchars($match_data['away_team_name']); ?> logo">
                            <img src="<?php echo htmlspecialchars($away_logo); ?>" 
                                alt="<?php echo htmlspecialchars($match_data['away_team_name']); ?> team logo" 
                                onerror="this.src='https://via.placeholder.com/90x90/FF6B6B/FFFFFF?text=<?php echo substr($match_data['ID_AWAYTEAM'], -2); ?>'">
                        </figure>
                        <h1 class="team-name-large"><?php echo htmlspecialchars($match_data['away_team_name']); ?></h1>
                    </div>

                    <!-- VS Section with Match Info -->
                    <div class="vs-section-large">
                        <div class="vs-circle" aria-label="Versus">VS</div>
                        <div class="match-details-info">
                            <time class="match-time" datetime="<?php echo $match_data['TANGGAL'] . 'T' . $match_data['WAKTU']; ?>">
                                <?php echo $match_datetime; ?>
                            </time>
                            <div class="stadium-info">
                                <div class="stadium-name">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    <?php echo htmlspecialchars($match_data['stadium_name'] ?: 'TBA'); ?>
                                </div>
                                <?php if (!empty($match_data['stadium_capacity']) || !empty($match_data['stadium_location'])): ?>
                                <div class="stadium-details">
                                    <?php if (!empty($match_data['stadium_capacity'])): ?>
                                        <span><i class="bi bi-people me-1"></i>Capacity: <?php echo number_format($match_data['stadium_capacity']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($match_data['stadium_location'])): ?>
                                        <span><i class="bi bi-pin-map me-1"></i><?php echo htmlspecialchars($match_data['stadium_location']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                <div class="match-status-badge">
                                    <span class="badge status-<?php echo $match_status; ?>">
                                        <?php 
                                        switch ($match_status) {
                                            case 'today':
                                                echo '<i class="bi bi-calendar-day me-1"></i>Today';
                                                break;
                                            case 'finished':
                                                echo '<i class="bi bi-check-circle me-1"></i>Finished';
                                                break;
                                            default:
                                                echo '<i class="bi bi-clock me-1"></i>Upcoming';
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Home Team -->
                    <div class="team-showcase">
                        <figure class="team-logo-large" aria-label="<?php echo htmlspecialchars($match_data['home_team_name']); ?> logo">
                            <img src="<?php echo htmlspecialchars($home_logo); ?>" 
                                alt="<?php echo htmlspecialchars($match_data['home_team_name']); ?> team logo" 
                                onerror="this.src='https://via.placeholder.com/90x90/4ECDC4/FFFFFF?text=<?php echo substr($match_data['ID_HOMETEAM'], -2); ?>'">
                        </figure>
                        <h1 class="team-name-large"><?php echo htmlspecialchars($match_data['home_team_name']); ?></h1>
                    </div>
                </div>

                <!-- League Info -->
                <div class="league-info-badge">
                    <span class="league-badge">
                        <?php echo htmlspecialchars($match_data['league_name']); ?>
                        <?php if (!empty($match_data['TAHUN_MULAI']) && !empty($match_data['TAHUN_SELESAI'])): ?>
                            <small><?php echo $match_data['TAHUN_MULAI'] . '/' . $match_data['TAHUN_SELESAI']; ?></small>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Lineups Tab -->
                <div class="tab-pane active" id="lineups">
                    <div class="lineups-container">
                        <!-- Away Team Lineup -->
                        <div class="team-lineup">
                            <div class="lineup-header">
                                <figure class="lineup-logo" aria-label="<?php echo htmlspecialchars($match_data['away_team_name']); ?> logo">
                                    <img src="<?php echo htmlspecialchars($away_logo); ?>" 
                                        alt="<?php echo htmlspecialchars($match_data['away_team_name']); ?>" 
                                        onerror="this.src='https://via.placeholder.com/35x35/FF6B6B/FFFFFF?text=<?php echo substr($match_data['ID_AWAYTEAM'], -2); ?>'">
                                </figure>
                                <h2 class="lineup-team-name"><?php echo htmlspecialchars($match_data['away_team_name']); ?></h2>
                            </div>

                            <!-- Coach -->
                            <div class="coach-section">
                                <h3 class="section-title">
                                    <i class="fas fa-user-tie" aria-hidden="true"></i>
                                    Pelatih
                                </h3>
                                <div class="coach-table">
                                    <div class="table-responsive">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>Nama</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="coach-name-cell">
                                                        <?php echo htmlspecialchars($match_data['away_team_coach'] ?: 'TBA'); ?>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Players -->
                            <div class="players-section">
                                <h3 class="section-title">
                                    <i class="fas fa-users" aria-hidden="true"></i>
                                    Squad (<?php echo count($away_players); ?> players)
                                </h3>
                                <div class="lineup-table">
                                    <div class="table-responsive">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>No.</th>
                                                    <th>Nama Pemain</th>
                                                    <th>Posisi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($away_players)): ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted">
                                                            <i class="bi bi-info-circle me-1"></i>
                                                            Squad information not available
                                                        </td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($away_players as $player): ?>
                                                        <tr>
                                                            <td class="player-number"><?php echo htmlspecialchars($player['NOMOR_PUNGGUNG']); ?></td>
                                                            <td class="player-name"><?php echo htmlspecialchars($player['NAMA_PEMAIN']); ?></td>
                                                            <td class="player-position">
                                                                <span class="position-badge"><?php echo htmlspecialchars($player['POSISI']); ?></span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Home Team Lineup -->
                        <div class="team-lineup">
                            <div class="lineup-header">
                                <figure class="lineup-logo" aria-label="<?php echo htmlspecialchars($match_data['home_team_name']); ?> logo">
                                    <img src="<?php echo htmlspecialchars($home_logo); ?>" 
                                        alt="<?php echo htmlspecialchars($match_data['home_team_name']); ?>" 
                                        onerror="this.src='https://via.placeholder.com/35x35/4ECDC4/FFFFFF?text=<?php echo substr($match_data['ID_HOMETEAM'], -2); ?>'">
                                </figure>
                                <h2 class="lineup-team-name"><?php echo htmlspecialchars($match_data['home_team_name']); ?></h2>
                            </div>

                            <!-- Coach -->
                            <div class="coach-section">
                                <h3 class="section-title">
                                    <i class="fas fa-user-tie" aria-hidden="true"></i>
                                    Pelatih
                                </h3>
                                <div class="coach-table">
                                    <div class="table-responsive">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>Nama</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="coach-name-cell">
                                                        <?php echo htmlspecialchars($match_data['home_team_coach'] ?: 'TBA'); ?>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Players -->
                            <div class="players-section">
                                <h3 class="section-title">
                                    <i class="fas fa-users" aria-hidden="true"></i>
                                    Squad (<?php echo count($home_players); ?> players)
                                </h3>
                                <div class="lineup-table">
                                    <div class="table-responsive">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>No.</th>
                                                    <th>Nama Pemain</th>
                                                    <th>Posisi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($home_players)): ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted">
                                                            <i class="bi bi-info-circle me-1"></i>
                                                            Squad information not available
                                                        </td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($home_players as $player): ?>
                                                        <tr>
                                                            <td class="player-number"><?php echo htmlspecialchars($player['NOMOR_PUNGGUNG']); ?></td>
                                                            <td class="player-name"><?php echo htmlspecialchars($player['NAMA_PEMAIN']); ?></td>
                                                            <td class="player-position">
                                                                <span class="position-badge"><?php echo htmlspecialchars($player['POSISI']); ?></span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add keyboard navigation for back button
        document.querySelector('.back-button').addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Add entrance animations
            const elements = document.querySelectorAll('.team-showcase, .stadium-detail-card, .team-lineup');
            elements.forEach((element, index) => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    element.style.transition = 'all 0.6s ease';
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Add hover effects for table rows
            document.querySelectorAll('.lineup-table tbody tr, .coach-table tbody tr').forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateX(8px)';
                    this.style.boxShadow = '0 5px 15px rgba(255, 107, 107, 0.1)';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateX(0)';
                    this.style.boxShadow = 'none';
                });
            });

            // Add click effects for interactive elements
            document.querySelectorAll('.back-button').forEach(button => {
                button.addEventListener('click', function() {
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 100);
                });
            });

            // Loading state for images
            document.querySelectorAll('img').forEach(img => {
                img.addEventListener('load', function() {
                    this.style.opacity = '1';
                });
                
                img.addEventListener('error', function() {
                    this.classList.add('image-error');
                });
            });

            // Add ripple effect to buttons
            document.querySelectorAll('.back-button').forEach(button => {
                button.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.cssText = `
                        position: absolute;
                        width: ${size}px;
                        height: ${size}px;
                        left: ${x}px;
                        top: ${y}px;
                        background: rgba(255, 255, 255, 0.3);
                        border-radius: 50%;
                        transform: scale(0);
                        animation: rippleAnimation 0.6s ease-out;
                        pointer-events: none;
                        z-index: 1;
                    `;
                    
                    this.style.position = 'relative';
                    this.style.overflow = 'hidden';
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });

            // Smooth scroll to top when back button is clicked
            document.querySelector('.back-button').addEventListener('click', function(e) {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        });

        // Add CSS for ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes rippleAnimation {
                to {
                    transform: scale(2);
                    opacity: 0;
                }
            }
            
            .image-error {
                opacity: 0.5;
                filter: grayscale(100%);
            }
            
            .status-today {
                background: linear-gradient(135deg, #FF6B6B, #FF8E8E);
                animation: pulse 2s infinite;
            }
            
            .status-finished {
                background: linear-gradient(135deg, #28a745, #34ce57);
            }
            
            .status-upcoming {
                background: linear-gradient(135deg, #17a2b8, #20c997);
            }
            
            @keyframes pulse {
                0% { box-shadow: 0 0 0 0 rgba(255, 107, 107, 0.7); }
                70% { box-shadow: 0 0 0 10px rgba(255, 107, 107, 0); }
                100% { box-shadow: 0 0 0 0 rgba(255, 107, 107, 0); }
            }
            
            .league-info-badge {
                position: absolute;
                top: 1rem;
                right: 1rem;
                z-index: 10;
            }
            
            .league-badge {
                padding: 0.5rem 1rem;
                font-size: 0.85rem;
                font-weight: 600;
                backdrop-filter: blur(10px);
            }
            
            .league-badge small {
                display: block;
                font-size: 0.7rem;
                opacity: 0.8;
                margin-top: 0.125rem;
            }

            .tab-navigation {
                display: none;
            }
            
            .tab-button {
                display: none;
            }
            
            .match-status-badge {
                margin-top: 0.5rem;
            }
            
            .match-status-badge .badge {
                padding: 0.5rem 1rem;
                border-radius: 20px;
                font-weight: 500;
                font-size: 0.85rem;
            }
            
            @media (max-width: 768px) {
                .league-info-badge {
                    position: static;
                    text-align: center;
                    margin-top: 1rem;
                }
                
                .stadium-photo-container {
                    height: 200px;
                    margin: 0 0.5rem;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>