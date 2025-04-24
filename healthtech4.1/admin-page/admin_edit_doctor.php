<?php
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// Database connection
include 'connect.php';

// Check if doctor ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = 'ID dokter tidak valid';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}

$doctor_id = $_GET['id'];

// Initialize variables
$name = $specialization = $hospital = $bio = $email = '';
$consultation_fee = $available_days = $education = $experience = $awards = '';
$current_photo = '';
$errors = [];

// Get existing doctor data
$query = "SELECT * FROM doctors WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param('i', $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = 'Dokter tidak ditemukan';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}

$doctor = $result->fetch_assoc();

// Set initial values
$name = $doctor['name'];
$specialization = $doctor['specialization'];
$hospital = $doctor['hospital'];
$bio = $doctor['bio'];
$email = $doctor['email'];
$consultation_fee = $doctor['consultation_fee'];
$available_days = $doctor['available_days'];
$education = $doctor['education'];
$experience = $doctor['experience'];
$awards = $doctor['awards'];
$current_photo = $doctor['photo'];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $hospital = trim($_POST['hospital'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $consultation_fee = trim($_POST['consultation_fee'] ?? '');
    $available_days = trim($_POST['available_days'] ?? '');
    $education = trim($_POST['education'] ?? '');
    $experience = trim($_POST['experience'] ?? '');
    $awards = trim($_POST['awards'] ?? '');
    
    // Validate inputs
    if (empty($name)) {
        $errors[] = 'Nama dokter wajib diisi';
    }
    
    if (empty($specialization)) {
        $errors[] = 'Spesialisasi wajib diisi';
    }
    
    if (empty($hospital)) {
        $errors[] = 'Rumah sakit wajib diisi';
    }
    
    if (empty($bio)) {
        $errors[] = 'Deskripsi tentang dokter wajib diisi';
    }
    
    if (empty($email)) {
        $errors[] = 'Email wajib diisi';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid';
    }
    
    // Validate consultation fee (numeric)
    if (!empty($consultation_fee) && !is_numeric($consultation_fee)) {
        $errors[] = 'Biaya konsultasi harus berupa angka';
    }
    
    // Handle photo upload
    $photo_path = $current_photo; // Keep current photo if no new upload
    
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        $file = $_FILES['photo'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Terjadi kesalahan dalam upload foto';
        } elseif (!in_array($file['type'], $allowed_types)) {
            $errors[] = 'Format foto harus berupa JPG, JPEG, atau PNG';
        } elseif ($file['size'] > $max_size) {
            $errors[] = 'Ukuran foto tidak boleh lebih dari 2MB';
        } else {
            // Create upload directory if it doesn't exist
            $upload_dir = 'uploads/doctors/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('doctor_') . '.' . $file_extension;
            $target_path = $upload_dir . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                // Delete old photo if it exists and is not the default photo
                if (!empty($current_photo) && file_exists($current_photo) && strpos($current_photo, 'default-doctor.jpg') === false) {
                    unlink($current_photo);
                }
                
                $photo_path = $target_path;
            } else {
                $errors[] = 'Gagal mengupload foto';
            }
        }
    }
    
    // If no errors, update database
    if (empty($errors)) {
        $query = "UPDATE doctors SET 
                  name = ?, 
                  specialization = ?, 
                  hospital = ?, 
                  bio = ?, 
                  photo = ?, 
                  email = ?, 
                  consultation_fee = ?, 
                  available_days = ?, 
                  education = ?, 
                  experience = ?, 
                  awards = ?, 
                  updated_at = NOW() 
                  WHERE id = ?";
        
        $stmt = $db->prepare($query);
        $stmt->bind_param('sssssssssssi', 
                          $name, $specialization, $hospital, $bio, $photo_path, $email, 
                          $consultation_fee, $available_days, $education, $experience, $awards, $doctor_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Data dokter berhasil diperbarui';
            $_SESSION['message_type'] = 'success';
            header('Location: index.php');
            exit;
        } else {
            $errors[] = 'Gagal memperbarui data: ' . $db->error;
        }
    }
}

// Get existing specializations for dropdown
$specializations = $db->query("SELECT DISTINCT specialization FROM doctors ORDER BY specialization ASC");

// Get existing hospitals for dropdown
$hospitals = $db->query("SELECT DISTINCT hospital FROM doctors ORDER BY hospital ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon web" href="../assets/konsulku-nobg.png">
    <title>Edit Dokter - Admin Dashboard</title>
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color:#06b6d4;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--dark-color);
            background-color: #f5f7fa;
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 0;
        }
        
        header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        header h1 {
            font-size: 2.2rem;
            margin-bottom: 5px;
        }
        
        header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
            border: none;
        }
        
        .btn-white {
            background-color: white;
            color: var(--primary-color);
        }
        
        .btn-white:hover {
            background-color: #f1f5f9;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #1d4ed8;
        }
        
        .btn-success {
            background-color: var(--success-color);
            color: white;
        }
        
        .btn-success:hover {
            background-color: #059669;
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #dc2626;
        }
        
        .form-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .form-title {
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .form-full {
            grid-column: span 2;
        }
        
        .error-list {
            color: var(--danger-color);
            background-color: #fee2e2;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid var(--danger-color);
            list-style-position: inside;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
        }
        
        .form-note {
            font-size: 0.9rem;
            color: #64748b;
            margin-top: 5px;
        }
        
        .current-photo {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }
        
        .current-photo img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 10px;
        }
        
        .photo-preview {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
            margin-top: 10px;
            display: none;
        }
        
        @media (max-width: 768px) {
            .header-flex {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-full {
                grid-column: span 1;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-flex">
                <div>
                    <h1>Edit Data Dokter</h1>
                    <p>Perbarui informasi dokter</p>
                </div>
                <a href="index.php" class="btn btn-white">Kembali ke Dashboard</a>
            </div>
        </div>
    </header>   
    
    <div class="container">
        <div class="form-container">
            <?php if (!empty($errors)): ?>
                <ul class="error-list">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            
            <form action="admin_edit_doctor.php?id=<?php echo $doctor_id; ?>" method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name" class="form-label">Nama Dokter *</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="specialization" class="form-label">Spesialisasi *</label>
                        <input type="text" id="specialization" name="specialization" class="form-control" value="<?php echo htmlspecialchars($specialization); ?>" list="specialization-list" required>
                        <datalist id="specialization-list">
                            <?php while($spec = $specializations->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($spec['specialization']); ?>">
                            <?php endwhile; ?>
                        </datalist>
                    </div>
                    
                    <div class="form-group">
                        <label for="hospital" class="form-label">Rumah Sakit/Klinik *</label>
                        <input type="text" id="hospital" name="hospital" class="form-control" value="<?php echo htmlspecialchars($hospital); ?>" list="hospital-list" required>
                        <datalist id="hospital-list">
                            <?php while($hosp = $hospitals->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($hosp['hospital']); ?>">
                            <?php endwhile; ?>
                        </datalist>
                    </div>
                    
                    <div class="form-group">
                        <label for="consultation_fee" class="form-label">Biaya Konsultasi (Rp)</label>
                        <input type="number" id="consultation_fee" name="consultation_fee" class="form-control" value="<?php echo htmlspecialchars($consultation_fee); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="available_days" class="form-label">Hari Praktik</label>
                        <input type="text" id="available_days" name="available_days" class="form-control" placeholder="e.g., Senin-Jumat" value="<?php echo htmlspecialchars($available_days); ?>">
                    </div>
                    
                    <div class="form-group form-full">
                        <label for="bio" class="form-label">Tentang Dokter *</label>
                        <textarea id="bio" name="bio" class="form-control" required><?php echo htmlspecialchars($bio); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="education" class="form-label">Pendidikan</label>
                        <textarea id="education" name="education" class="form-control" placeholder="Satu pendidikan per baris, format: institusi) periode"><?php echo htmlspecialchars($education); ?></textarea>
                        <p class="form-note">Contoh: Fakultas Kedokteran Universitas Indonesia) 2010-2016</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="experience" class="form-label">Pengalaman</label>
                        <textarea id="experience" name="experience" class="form-control" placeholder="Satu pengalaman per baris, format: posisi) periode"><?php echo htmlspecialchars($experience); ?></textarea>
                        <p class="form-note">Contoh: Dokter Spesialis di RS Medika) 2018-Sekarang</p>
                    </div>
                    
                    <div class="form-group form-full">
                        <label for="awards" class="form-label">Penghargaan</label>
                        <textarea id="awards" name="awards" class="form-control" placeholder="Satu penghargaan per baris"><?php echo htmlspecialchars($awards); ?></textarea>
                    </div>
                    
                    <div class="form-group form-full">
                        <label for="photo" class="form-label">Foto Dokter</label>
                        <?php if (!empty($current_photo)): ?>
                            <div class="current-photo">
                                <img src="<?php echo htmlspecialchars($current_photo); ?>" alt="Current photo">
                                <p>Foto saat ini</p>
                            </div>
                        <?php endif; ?>
                        <input type="file" id="photo" name="photo" class="form-control" accept="image/jpeg, image/png, image/jpg">
                        <p class="form-note">Format: JPG, JPEG, atau PNG. Maksimal 2MB. Biarkan kosong jika tidak ingin mengubah foto.</p>
                        <img id="photoPreview" class="photo-preview" alt="Photo Preview">
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="index.php" class="btn btn-danger">Batal</a>
                    <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Photo preview functionality
            const photoInput = document.getElementById('photo');
            const photoPreview = document.getElementById('photoPreview');
            
            photoInput.addEventListener('change', function() {
                const file = this.files[0];
                
                if (file) {
                    const reader = new FileReader();
                    
                    reader.addEventListener('load', function() {
                        photoPreview.src = this.result;
                        photoPreview.style.display = 'block';
                    });
                    
                    reader.readAsDataURL(file);
                } else {
                    photoPreview.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>