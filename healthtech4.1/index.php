<?php
require_once 'db.php';

// Mengecek status login
$loggedIn = isLoggedIn();
$userData = null;

if ($loggedIn) {
    // Mengambil data user yang sedang login
    $stmt = $db->prepare("SELECT id, name, email, bio, profile_image, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    $profileImageUrl = '';
    if (!empty($user['profile_image'])) {
        $imageData = base64_encode($user['profile_image']);
        $mime = 'image/jpeg'; // Default to JPEG, ideally you would detect the actual mime type
        $profileImageUrl = "data:$mime;base64,$imageData";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konsulku - Solusi Kesehatan Modern</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon web" href="assets/konsulku-nobg.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <!-- Sidebar Navigation -->
    <nav class="sidebar" id="sidebar">
        <div class="logo-container">
            <img src="assets/konsulku-nobg.png" alt="Konsulku Logo" class="logo">
            <h2>Konsulku</h2>
        </div>
        <div class="close-btn" id="close-sidebar">
            <i class="fas fa-times"></i>
        </div>
        <ul class="menu">
            <li><a href="#hero" class="active"><i class="fas fa-home"></i> Beranda</a></li>
            <li><a href="#consultation"><i class="fas fa-user-md"></i> Konsultasi</a></li>
            <li><a href="#features"><i class="fas fa-th"></i> Fitur Utama</a></li>
            <li><a href="#reviews"><i class="fas fa-star"></i> Review</a></li>
            <li><a href="#contact"><i class="fas fa-envelope"></i> Kontak</a></li>
        </ul>
        
        <div class="sidebar-footer">
            <?php if($loggedIn): ?>
                <div class="user-info">
                <div class="profile-image-container">
                                <?php if (!empty($profileImageUrl)): ?>
                                    <img src="<?php echo $profileImageUrl; ?>" alt="Profile Image" style="max-width:40px; margin-right: 5px; border-radius: 50%;">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                    <span><?php echo htmlspecialchars($user['name']); ?></span>
                </div>
                <a href="profile.php" class="btn btn-outlined"><i class="fas fa-user"></i> Profile</a>
                <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-outlined"><i class="fas fa-sign-in-alt"></i> Login</a>
                <a href="signup.php" class="btn btn-primary"><i class="fas fa-user-plus"></i> Sign Up</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header/Navbar -->
        <header class="header">
            <div class="hamburger" id="hamburger">
                <i class="fas fa-bars"></i>
            </div>
            <div class="header-logo">Konsulku</div>
            <?php if(!$loggedIn): ?>
                <div class="header-buttons">
                    <a href="login.php" class="btn btn-outlined">Login</a>
                    <a href="signup.php" class="btn btn-primary">Sign Up</a>
                </div>
            <?php else: ?>
                <div class="header-user">
                    Halo, <?php echo htmlspecialchars($user['name']); ?>
                </div>
            <?php endif; ?>
        </header>

        <!-- Hero Section -->
        <section id="hero" class="hero-section">
            <div class="hero-content">
                <h1>Konsulku</h1>
                <p>Layanan kesehatan digital yang menghubungkan Anda dengan dokter profesional dan teknologi terkini</p>
                <a href="#consultation" class="btn btn-primary">Jelajahi</a>
            </div>
            <div class="hero-image">
                <img src="assets/konsulku-nobg.png" alt="Healthcare Illustration">
            </div>
        </section>

        <!-- Consultation Section -->
        <section id="consultation" class="consultation-section">
            <div class="section-header">
                <h2>Konsultasi Dokter</h2>
                <p>Terhubung dengan dokter spesialis kapan saja, dimana saja</p>
            </div>
            <div class="consultation-content">
                <div class="consultation-info">
                    <h3>Mengapa Konsultasi dengan Kami?</h3>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> Dokter berpengalaman dari berbagai spesialisasi</li>
                        <li><i class="fas fa-check-circle"></i> Konsultasi 24/7 melalui chat, voice, atau video call</li>
                        <li><i class="fas fa-check-circle"></i> Resep digital yang dapat ditebus di apotek partner</li>
                        <li><i class="fas fa-check-circle"></i> Record medis terintegrasi untuk kebutuhan medis di masa depan</li>
                    </ul>
                    <a href="doctor_page/main.php" class="btn btn-primary">Konsultasi Sekarang</a>
                </div>
                <div class="consultation-image">
                    <img src="assets/doctor-consul.png" alt="Doctor Consultation">
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="features-section">
            <div class="section-header">
                <h2>Fitur Utama</h2>
                <p>Beragam fitur kesehatan untuk kebutuhan Anda</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <h3>Kalkulator Kesehatan</h3>
                    <p>Hitung BMI, kebutuhan kalori, dan nutrisi harian dengan mudah dan akurat</p>
                    <a href="kalkulator-kesehatan/index.html" class="feature-link">Coba Sekarang <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <h3>Tes Kesehatan</h3>
                    <p>Lakukan tes kesehatan dasar dan cek gejala untuk pemeriksaan awal</p>
                    <a href="gaya-hidup-sehat" class="feature-link">Coba Sekarang <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-gamepad"></i>
                    </div>
                    <h3>Game Interaktif</h3>
                    <p>Belajar tentang kesehatan melalui game yang edukatif dan menyenangkan</p>
                    <a href="mini-game/index.html" class="feature-link">Coba Sekarang <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h3>Forum Diskusi</h3>
                    <p>Bergabung dengan komunitas untuk diskusi dan berbagi pengalaman kesehatan</p>
                    <a href="#" class="feature-link">Coba Sekarang <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </section>

        <!-- Reviews Section -->
        <section id="reviews" class="reviews-section">
            <div class="section-header">
                <h2>Apa Kata Mereka</h2>
                <p>Pengalaman pengguna Konsulku</p>
            </div>
            <div class="reviews-slider" id="reviewsSlider">

            </div>
            
            <?php if($loggedIn): ?>
            <div class="review-form">
                <h3>Bagikan Pengalaman Anda</h3>
                <form id="reviewForm">
                    <div class="rating-select">
                        <span>Rating: </span>
                        <div class="stars">
                            <i class="fas fa-star" data-rating="1"></i>
                            <i class="fas fa-star" data-rating="2"></i>
                            <i class="fas fa-star" data-rating="3"></i>
                            <i class="fas fa-star" data-rating="4"></i>
                            <i class="fas fa-star" data-rating="5"></i>
                        </div>
                        <input type="hidden" name="rating" id="ratingInput" value="5">
                    </div>
                    <div class="form-group">
                        <textarea name="review" id="reviewInput" rows="4" placeholder="Tulis pengalaman Anda dengan Konsulku..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Kirim Review</button>
                    <div id="reviewMessage" class="message"></div>
                </form>
            </div>
            <?php else: ?>
            <div class="review-login-message">
                <p>Silahkan <a href="login.php">login</a> untuk memberikan review Anda</p>
            </div>
            <?php endif; ?>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="contact-section">
            <div class="section-header">
                <h2>Hubungi Kami</h2>
                <p>Kami siap membantu Anda</p>
            </div>
            <div class="contact-container">
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <h3>Alamat</h3>
                            <p>Jl. Ikhlaskan No. 170, Daerah Istimewa Yogyakarta</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <div>
                            <h3>Telepon</h3>
                            <p>+62 858-6808-0278</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h3>Email</h3>
                            <p>info@konsulku.com</p>
                        </div>
                    </div>
                </div>
                <div class="contact-form">
                    <form>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Nama</label>
                                <input type="text" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="subject">Subjek</label>
                            <input type="text" id="subject" name="subject" required>
                        </div>
                        <div class="form-group">
                            <label for="message">Pesan</label>
                            <textarea id="message" name="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Kirim Pesan</button>
                    </form>
                </div>
            </div>
        </section>
    </main>
    <script src="script.js"></script>
</body>
</html>
