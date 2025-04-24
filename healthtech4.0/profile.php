<?php
require_once 'db.php';

// Jika user belum login, redirect ke login page
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

// Mengambil data user
$stmt = $db->prepare("SELECT id, name, email, bio, profile_image, created_at FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Mengambil review yang ditulis oleh user
$stmt = $db->prepare("SELECT * FROM reviews WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$reviews = $stmt->fetchAll();

// Update profil jika ada form submission
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if it's a delete review request
    if (isset($_POST['delete_review'])) {
        $review_id = $_POST['review_id'];
        $stmt = $db->prepare("DELETE FROM reviews WHERE id = ? AND user_id = ?");
        $stmt->execute([$review_id, $_SESSION['user_id']]);
        header("Location: profile.php");
        exit;
    }

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $bio = isset($_POST['bio']) ? trim($_POST['bio']) : '';
    $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    // Validasi data
    if (empty($name) || empty($email)) {
        $error = "Nama dan email harus diisi.";
    } else {
        // Check if email is already used by another user
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        
        if ($stmt->rowCount() > 0) {
            $error = "Email sudah digunakan oleh pengguna lain.";
        } else {
            try {
                // Handle profile image upload
                $profile_image_data = null;
                $has_new_image = false;
                
                if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
                    // Validate file
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                    $max_size = 2 * 1024 * 1024; // 2MB
                    
                    if (!in_array($_FILES['profile_image']['type'], $allowed_types)) {
                        $error = "Hanya file gambar (JPG, PNG, GIF) yang diperbolehkan.";
                    } elseif ($_FILES['profile_image']['size'] > $max_size) {
                        $error = "Ukuran file tidak boleh lebih dari 2MB.";
                    } else {
                        // Read the file content
                        $profile_image_data = file_get_contents($_FILES['profile_image']['tmp_name']);
                        $has_new_image = true;
                    }
                }
                
                if (empty($error)) {
                    // Update profile with or without image
                    if ($has_new_image) {
                        $stmt = $db->prepare("UPDATE users SET name = ?, email = ?, bio = ?, profile_image = ? WHERE id = ?");
                        $stmt->execute([$name, $email, $bio, $profile_image_data, $_SESSION['user_id']]);
                    } else {
                        $stmt = $db->prepare("UPDATE users SET name = ?, email = ?, bio = ? WHERE id = ?");
                        $stmt->execute([$name, $email, $bio, $_SESSION['user_id']]);
                    }
                    
                    // Update password if provided
                    if (!empty($current_password) && !empty($new_password)) {
                        // Verify current password
                        $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        $current_hash = $stmt->fetchColumn();
                        
                        if (password_verify($current_password, $current_hash)) {
                            // Check if new password and confirm password match
                            if ($new_password === $confirm_password) {
                                // Update password
                                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                                $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                                $stmt->execute([$hashed_password, $_SESSION['user_id']]);
                                
                                $success = "Profil dan password berhasil diperbarui.";
                            } else {
                                $error = "Password baru dan konfirmasi password tidak cocok.";
                            }
                        } else {
                            $error = "Password saat ini tidak valid.";
                        }
                    } else {
                        $success = "Profil berhasil diperbarui.";
                    }
                    
                    // Refresh user data
                    $stmt = $db->prepare("SELECT id, name, email, bio, profile_image, created_at FROM users WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $user = $stmt->fetch();
                    
                    // Update session
                    $_SESSION['user_name'] = $user['name'];
                }
            } catch (PDOException $e) {
                $error = "Terjadi kesalahan. Silakan coba lagi. " . $e->getMessage();
            }
        }
    }
}

// Generate profile image data URI if exists
$profileImageUrl = '';
if (!empty($user['profile_image'])) {
    $imageData = base64_encode($user['profile_image']);
    $mime = 'image/jpeg'; // Default to JPEG, ideally you would detect the actual mime type
    $profileImageUrl = "data:$mime;base64,$imageData";
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Konsulku</title>
    <link rel="icon web" href="assets/konsulku-nobg.png">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .profile-image-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 20px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid #f0f0f0;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .profile-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .profile-image-container .no-image {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            background-color: #e9e9e9;
            color: #777;
            font-size: 3rem;
        }
        
        .image-upload-label {
            display: inline-block;
            padding: 8px 16px;
            background-color:rgb(184, 184, 184);
            color: white;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
            font-size: 0.9rem;
            transition: background-color 0.3s;
        }
        
        .image-upload-label:hover {
            background-color: #06b6d4;
        }
        
        #file-name {
            margin-top: 5px;
            font-size: 0.85rem;
            color: #666;
        }
        
        .bio-textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-height: 120px;
            resize: vertical;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
        }
    </style>
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
            <li><a href="index.php"><i class="fas fa-home"></i> Beranda</a></li>
            <li><a href="index.php#consultation"><i class="fas fa-user-md"></i> Konsultasi</a></li>
            <li><a href="index.php#features"><i class="fas fa-th"></i> Fitur Utama</a></li>
            <li><a href="index.php#reviews"><i class="fas fa-star"></i> Review</a></li>
            <li><a href="index.php#contact"><i class="fas fa-envelope"></i> Kontak</a></li>
        </ul>
        
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="avatar">
                    <?php if (!empty($profileImageUrl)): ?>
                        <img src="<?php echo $profileImageUrl; ?>" alt="Profile Image" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                    <?php else: ?>
                        <i class="fas fa-user-circle"></i>
                    <?php endif; ?>
                </div>
                <span><?php echo htmlspecialchars($user['name']); ?></span>
            </div>
            <a href="profile.php" class="btn btn-outlined active"><i class="fas fa-user"></i> Profile</a>
            <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
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
            <div class="header-user">
                Halo, <?php echo htmlspecialchars($user['name']); ?>
            </div>
        </header>

        <!-- Profile Section -->
        <section class="profile-section">
            <div class="container">
                <div class="section-header">
                    <h2>Profil Saya</h2>
                    <p>Kelola informasi profil Anda</p>
                </div>
                
                <?php if($error): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if($success): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <div class="profile-container">
                    <div class="profile-sidebar">
                        <div class="profile-image">
                            <div class="profile-image-container">
                                <?php if (!empty($profileImageUrl)): ?>
                                    <img src="<?php echo $profileImageUrl; ?>" alt="Profile Image">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                            <?php if (!empty($user['bio'])): ?>
                                <p class="user-bio"><?php echo nl2br(htmlspecialchars($user['bio'])); ?></p>
                            <?php endif; ?>
                            <p>Member sejak <?php echo date('d M Y', strtotime($user['created_at'])); ?></p>
                        </div>
                        <div class="profile-menu">
                            <a href="#" class="active" data-tab="info">Informasi Profil</a>
                            <a href="#" data-tab="password">Ubah Password</a>
                            <a href="#" data-tab="reviews">Review Saya</a>
                        </div>
                    </div>
                    <div class="profile-content">
                        <div id="info" class="profile-tab active">
                            <h3>Informasi Profil</h3>
                            <form method="POST" action="profile.php" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="name">Nama Lengkap</label>
                                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="bio">Bio</label>
                                    <textarea id="bio" name="bio" class="bio-textarea" placeholder="Ceritakan sedikit tentang diri Anda..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="profile_image">Foto Profil</label>
                                    <div>
                                        <input type="file" id="profile_image" name="profile_image" accept="image/*" style="display:none">
                                        <label for="profile_image" class="image-upload-label">
                                            <i class="fas fa-upload"></i> Pilih Foto
                                        </label>
                                        <div id="file-name">Tidak ada file yang dipilih</div>
                                    </div>
                                    <small class="form-text text-muted">Format: JPG, PNG, GIF. Max: 2MB</small>
                                </div>
                                <div class="form-group">
                                    <label for="member_since">Member Sejak</label>
                                    <input type="text" id="member_since" value="<?php echo date('d M Y', strtotime($user['created_at'])); ?>" disabled>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                        <div id="password" class="profile-tab">
                            <h3>Ubah Password</h3>
                            <form method="POST" action="profile.php">
                                <input type="hidden" name="name" value="<?php echo htmlspecialchars($user['name']); ?>">
                                <input type="hidden" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                                <input type="hidden" name="bio" value="<?php echo htmlspecialchars($user['bio'] ?? ''); ?>">
                                <div class="form-group">
                                    <label for="current_password">Password Saat Ini</label>
                                    <input type="password" id="current_password" name="current_password" required>
                                </div>
                                <div class="form-group">
                                    <label for="new_password">Password Baru</label>
                                    <input type="password" id="new_password" name="new_password" required>
                                </div>
                                <div class="form-group">
                                    <label for="confirm_password">Konfirmasi Password Baru</label>
                                    <input type="password" id="confirm_password" name="confirm_password" required>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">Ubah Password</button>
                                </div>
                            </form>
                        </div>
                        <div id="reviews" class="profile-tab">
                            <h3>Review Saya</h3>
                            <?php if(count($reviews) > 0): ?>
                                <div class="my-reviews">
                                    <?php foreach($reviews as $review): ?>
                                        <div class="review-item">
                                            <div class="review-rating">
                                                <?php for($i = 1; $i <= 5; $i++): ?>
                                                    <?php if($i <= $review['rating']): ?>
                                                        <i class="fas fa-star"></i>
                                                    <?php else: ?>
                                                        <i class="far fa-star"></i>
                                                    <?php endif; ?>
                                                <?php endfor; ?>
                                            </div>
                                            <div class="review-text">
                                                <?php echo htmlspecialchars($review['review_text']); ?>
                                            </div>
                                            <div class="review-date">
                                                <?php echo date('d M Y', strtotime($review['created_at'])); ?>
                                            </div>
                                            <form method="POST" class="review-actions" onsubmit="return confirm('Apakah Anda yakin ingin menghapus review ini?');">
                                                <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                                <button type="submit" name="delete_review" class="btn-danger">
                                                    <i class="fas fa-trash-alt"></i> Hapus Review
                                                </button>
                                            </form>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="no-reviews">
                                    <p>Anda belum memberikan review.</p>
                                    <a href="index.php#reviews" class="btn btn-primary">Tulis Review</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>


    <script src="script.js"></script>
    <script>
        // Profile tab switching
        document.addEventListener('DOMContentLoaded', function() {
            const tabLinks = document.querySelectorAll('.profile-menu a');
            const tabContents = document.querySelectorAll('.profile-tab');
            
            tabLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Remove active class from all tabs
                    tabLinks.forEach(item => item.classList.remove('active'));
                    tabContents.forEach(item => item.classList.remove('active'));
                    
                    // Add active class to current tab
                    this.classList.add('active');
                    document.getElementById(this.getAttribute('data-tab')).classList.add('active');
                });
            });
            
            // File input display
            const fileInput = document.getElementById('profile_image');
            const fileNameDisplay = document.getElementById('file-name');
            
            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    fileNameDisplay.textContent = this.files[0].name;
                } else {
                    fileNameDisplay.textContent = 'Tidak ada file yang dipilih';
                }
            });
        });
    </script>
</body>
</html>