<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KickOff - All Matches, All Times, Always Accurate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Urbanist:wght@300;400;500;600;700;800;900&family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style\style_index.css">
</head>
<body>
    <?php include_once("config.php"); 

    ?>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#home">
                <img src="asset/Logo.png" alt="Kickoff Logo" style="width: 80px; height: 65px;" class="me-2">
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#testimonials">Testimonials</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="chooseliga.php">Match Schedule</a>
                    </li>
                    
                    <?php if (isLoggedIn()): ?>
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
                    <?php else: ?>
                        <!-- Guest user -->
                        <li class="nav-item">
                            <a class="nav-link btn btn-outline-light btn-sm ms-2 px-3" href="login.php">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">All Matches, All Times, Always Accurate.</h1>
                <p class="hero-subtitle">
                    Your trusted source for precise football match schedules.
                    No distractions — just the when and where.
                </p>
                
                <?php if (isLoggedIn()): ?>
                    <a href="chooseliga.php" class="btn btn-primary-custom">
                        View Match Schedule
                    </a>
                <?php else: ?>
                    <div class="hero-buttons">
                        <a href="chooseliga.php" class="btn btn-primary-custom me-3">
                            Match Schedule
                        </a>
                        <a href="register.php" class="btn btn-outline-light">
                            Join Now
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

<section class="leagues-section">
    <div class="container">
        <div class="marquee-container">
            <div class="marquee-track">
                <div class="league-item">
                    <div class="league-logo">
                        <img src="uploads/leagues/bundesliga.png" alt="Bundesliga" style="height:40px; width:auto;">
                    </div>
                </div>
                
                <div class="league-item">
                    <div class="league-logo">
                        <img src="uploads/leagues/ucl.png" alt="Champions League" style="height:40px; width:auto;">
                    </div>
                </div>
                
                <div class="league-item">
                    <div class="league-logo">
                        <img src="uploads/leagues/la_liga.png" alt="La Liga" style="height:40px; width:auto;">
                    </div>
                </div>
                
                <div class="league-item">
                    <div class="league-logo">
                        <img src="uploads/leagues/serie_a.png" alt="Serie A" style="height:40px; width:auto;">
                    </div>
                </div>
                
                <div class="league-item">
                    <div class="league-logo">
                        <img src="uploads/leagues/eredivisie.png" alt="Eredivisie" style="height:40px; width:auto;">
                    </div>
                </div>
                
                <div class="league-item">
                    <div class="league-logo">
                        <img src="uploads/leagues/liga_1_indonesia.png" alt="Liga 1 Indonesia" style="height:40px; width:auto;">
                    </div>
                </div>
                
                <div class="league-item">
                    <div class="league-logo">
                        <img src="uploads/leagues/ligue_1.png" alt="Ligue 1" style="height:40px; width:auto;">
                    </div>
                </div>
                
                <div class="league-item">
                    <div class="league-logo">
                        <img src="uploads/leagues/bundesliga.png" alt="Bundesliga" style="height:40px; width:auto;">
                    </div>
                </div>
                
                <div class="league-item">
                    <div class="league-logo">
                        <img src="uploads/leagues/ucl.png" alt="Champions League" style="height:40px; width:auto;">
                    </div>
                </div>
                
                <div class="league-item">
                    <div class="league-logo">
                        <img src="uploads/leagues/la_liga.png" alt="La Liga" style="height:40px; width:auto;">
                    </div>
                </div>
                
                <div class="league-item">
                    <div class="league-logo">
                        <img src="uploads/leagues/serie_a.png" alt="Serie A" style="height:40px; width:auto;">
                    </div>
                </div>
                
                <div class="league-item">
                    <div class="league-logo">
                        <img src="uploads/leagues/eredivisie.png" alt="Eredivisie" style="height:40px; width:auto;">
                    </div>
                </div>
                
                <div class="league-item">
                    <div class="league-logo">
                        <img src="uploads/leagues/liga_1_indonesia.png" alt="Liga 1 Indonesia" style="height:40px; width:auto;">
                    </div>
                </div>
                
                <div class="league-item">
                    <div class="league-logo">
                        <img src="uploads/leagues/ligue_1.png" alt="Ligue 1" style="height:40px; width:auto;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

    <!-- About Section -->
    <section id="about" class="about-section">
        <div class="container">
            <div class="row align-items-center pt-5">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <h2 class="section-title">
                        Ready to Accompany<br>
                        Every Match!
                    </h2>
                    <p class="about-text">
                        We're here to make sure you never miss a moment on the field. With real-time match schedules and complete information from various leagues, KickOff is ready to bring you every goal, every attack, and every victory.
                    </p>
                </div>
                <div class="col-lg-6">
                    <div class="video-container">
                        <div class="ratio ratio-16x9">
                            <video class="rounded-3 shadow-lg" controls autoplay muted>
                                <source src="asset/videoprofile.mp4" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                        <div class="video-overlay">
                            <div class="play-button">
                                <i class="bi bi-play-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="testimonials-section">
        <div class="container">
            <h2 class="section-title text-center">What Our Fans Say?</h2>
            
            <div class="row mt-5">
                <?php
                // Query untuk ambil 4 feedback terbaru
                $testimonial_query = "SELECT NAMA_USER, STATUS, PESAN, TANGGAL_FEEDBACK 
                                    FROM feedback 
                                    WHERE LENGTH(PESAN) >= 1 
                                    ORDER BY TANGGAL_FEEDBACK DESC 
                                    LIMIT 4";
                
                $testimonial_result = mysqli_query($conn, $testimonial_query);
                
                if ($testimonial_result && mysqli_num_rows($testimonial_result) > 0):
                    while ($testimonial = mysqli_fetch_assoc($testimonial_result)):
                        // Ambil inisial dari nama untuk avatar
                        $initial = strtoupper(substr($testimonial['NAMA_USER'], 0, 1));
                        
                        // Truncate pesan jika terlalu panjang
                        $pesan = strlen($testimonial['PESAN']) > 120 ? 
                                substr($testimonial['PESAN'], 0, 120) . '...' : 
                                $testimonial['PESAN'];
                        
                        // Format tanggal untuk menampilkan "recent"
                        $tanggal = new DateTime($testimonial['TANGGAL_FEEDBACK']);
                        $now = new DateTime();
                        $diff = $now->diff($tanggal);
                        
                        if ($diff->days == 0) {
                            $time_label = "Today";
                        } elseif ($diff->days == 1) {
                            $time_label = "Yesterday";
                        } elseif ($diff->days <= 7) {
                            $time_label = $diff->days . " days ago";
                        } else {
                            $time_label = "Recent";
                        }
                ?>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="testimonial-card">
                            <p class="testimonial-text">
                                "<?php echo htmlspecialchars($pesan); ?>"
                            </p>
                            <div class="testimonial-author">
                                <div class="author-avatar"><?php echo $initial; ?></div>
                                <div class="author-info">
                                    <h6><?php echo htmlspecialchars($testimonial['NAMA_USER']); ?></h6>
                                    <small><?php echo htmlspecialchars($testimonial['STATUS']); ?></small>
                                    <div class="testimonial-time">
                                        <small class="text-muted"><?php echo $time_label; ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php 
                    endwhile;
                ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact-section">
        <div class="container">
            <h2 class="section-title text-center">Get in Touch with Us Anytime!</h2>
            <p class="text-center mb-5" style="max-width: 600px; margin: 0 auto 50px;">
                Have questions or feedback? We'd love to hear from you! 
                You can reach us through social media below or send a message 
                directly by filling out the form on the right.
            </p>
            
            <!-- Show feedback messages -->
            <?php if (isset($_GET['feedback'])): ?>
                <?php if ($_GET['feedback'] == 'success'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Terima kasih!</strong> Feedback Anda telah berhasil dikirim. Tim kami akan merespons segera.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php elseif ($_GET['feedback'] == 'error'): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Error!</strong> <?php echo htmlspecialchars($_GET['msg'] ?? 'Terjadi kesalahan. Silakan coba lagi.'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <div class="contact-form">
                        <h4 class="mb-4">Send us a message</h4>
                        <form id="feedbackForm" action="process_feedback.php" method="POST">
                            <div class="mb-3">
                                <input type="text" class="form-control" name="nama_user" placeholder="Your Name :" 
                                        required maxlength="50">
                            </div>
                            <div class="mb-3">
                                <input type="text" class="form-control" name="status" placeholder="Status/Profesi :" 
                                        required maxlength="50">
                            </div>
                            <div class="mb-3">
                                <input type="email" class="form-control" name="email" placeholder="E-mail :" 
                                        required maxlength="70">
                            </div>
                            <div class="mb-4">
                                <textarea class="form-control" rows="6" name="pesan" placeholder="Message :" 
                                        required maxlength="1000"></textarea>
                                <small class="text-muted">Maksimal 1000 karakter</small>
                            </div>
                            <button type="submit" class="btn btn-primary-custom w-100" id="submitBtn">
                                <i class="fas fa-paper-plane me-2"></i>
                                <span id="btnText">Send Message</span>
                                <span id="btnSpinner" class="spinner-border spinner-border-sm ms-2" style="display: none;"></span>
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <div class="contact-info">
                        <h4>Contact Information</h4>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <strong>KickOff HQ</strong><br>
                                <span>Jl. Sudirman No. 45, Senayan, Yogyakarta, Indonesia</span>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>
                                <strong>+62 812-3456-7890</strong>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <strong>info@KickOff.com</strong>
                            </div>
                        </div>
                        
                        <div class="social-links">
                            <a href="#" class="social-link">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="social-link">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="social-link">
                                <i class="fab fa-linkedin"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row ps-4">
                <div class="col-lg-4">
                    <div class="footer-brand">
                        <div class="footer-logo d-flex align-items-center">
                            <img src="asset/Logo.png" alt="Kickoff Logo" style="width: 80px; height: 65px;" class="me-2">
                        </div>
                    </div>
                    <p>
                        At KickOff, we are committed to connecting you with the world of football through precise and reliable match schedules.
                        From local leagues to international tournaments, we offer a comprehensive view of upcoming fixtures.
                        No scores, no spoilers — just accurate timing for every game that matters.
                    </p>
                </div>
                
                <div class="col-lg-4">
                    <h5>Contact Information</h5>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <span>KickOff HQ<br>Jl. Sudirman No. 45, Senayan, Yogyakarta, Indonesia</span>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <span>+62 812-3456-7890</span>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <span>info@KickOff.com</span>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <h5>Quick Links</h5>
                    <div class="d-flex flex-column">
                        <a href="#" class="footer-link">
                            <i class="fas fa-file-alt me-2"></i>
                            Privacy Policy
                        </a>
                        <a href="#" class="footer-link">
                            <i class="fas fa-shield-alt me-2"></i>
                            Terms & Conditions
                        </a>
                        <a href="#" class="footer-link">
                            <i class="fas fa-calendar-alt me-2"></i>
                            Match Schedule Guide
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; 2025 KickOff. All Rights Reserved.<br>
                "Bringing every match closer to you, one goal at a time."</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar-custom');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(30, 22, 22, 0.95)';
            } else {
                navbar.style.background = 'rgba(30, 22, 22, 0.3)';
            }
        });
        document.getElementById('feedbackForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');
            
            submitBtn.disabled = true;
            btnText.textContent = 'Sending...';
            btnSpinner.style.display = 'inline-block';
        });
        document.querySelector('textarea[name="pesan"]').addEventListener('input', function() {
            const remaining = 1000 - this.value.length;
            const counter = this.parentNode.querySelector('small');
            counter.textContent = `${remaining} karakter tersisa`;
            
            if (remaining < 100) {
                counter.style.color = '#dc3545';
            } else {
                counter.style.color = '#6c757d';
            }
        });
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                if (alert.classList.contains('show')) {
                    alert.classList.remove('show');
                }
            });
        }, 5000);
    </script>
</body>
</html>