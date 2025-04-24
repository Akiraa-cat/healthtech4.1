<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database connection
include '../doctor_page/connect.php';

// Get specialization options from database
$specQuery = "SELECT DISTINCT specialization FROM doctors ORDER BY specialization ASC";
$specializations = $db->query($specQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Dokter - Konsultasi Dokter Online</title>
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
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        header h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
        }
        
        header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .form-container {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .form-section-title {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .required-label::after {
            content: " *";
            color: var(--danger-color);
        }
        
        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="password"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border 0.3s;
        }
        
        input:focus,
        textarea:focus,
        select:focus {
            border-color: var(--primary-color);
            outline: none;
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
            border: none;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #1d4ed8;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        .form-footer {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }
        
        .alert-success {
            color: #0f766e;
            background-color: #d1fae5;
            border-color: var(--success-color);
        }
        
        .alert-error {
            color: #b91c1c;
            background-color: #fee2e2;
            border-color: var(--danger-color);
        }
        
        .alert-info {
            color: #1e40af;
            background-color: #dbeafe;
            border-color: var(--primary-color);
        }
        
        .file-input-container {
            position: relative;
        }
        
        .custom-file-input {
            margin-top: 10px;
        }
        
        .help-text {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .form-footer {
                flex-direction: column;
                gap: 15px;
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
            <h1>Pendaftaran Dokter</h1>
            <p>Bergabunglah dengan Konsultasi Dokter Online untuk membantu pasien</p>
        </div>
    </header>
    
    <div class="container">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                <?php 
                    echo $_SESSION['message']; 
                    // Clear the message after displaying
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                ?>
            </div>
        <?php endif; ?>
        
        <div class="form-container">
            <form action="process_doctor_registration.php" method="post" enctype="multipart/form-data">
                <!-- Personal Information Section -->
                <div class="form-section">
                    <h2 class="form-section-title">Informasi Pribadi</h2>
                    
                    <div class="form-group">
                        <label for="name" class="required-label">Nama Lengkap (dengan gelar)</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="required-label">Email</label>
                        <input type="email" id="email" name="email" required>
                        <p class="help-text">Email ini akan digunakan untuk login jika pendaftaran disetujui</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone" class="required-label">Nomor Telepon</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="photo" class="required-label">Foto Profil</label>
                        <div class="file-input-container">
                            <input type="file" id="photo" name="photo" class="custom-file-input" accept="image/*" required>
                        </div>
                        <p class="help-text">Upload foto profesional dengan latar belakang polos. Format: JPG, PNG. Maks: 2MB</p>
                    </div>
                </div>
                
                <!-- Professional Information Section -->
                <div class="form-section">
                    <h2 class="form-section-title">Informasi Profesional</h2>
                    
                    <div class="form-group">
                        <label for="specialization" class="required-label">Spesialisasi</label>
                        <select id="specialization" name="specialization" required>
                            <option value="">Pilih Spesialisasi</option>
                            <?php 
                            // Add common specializations if the database is empty
                            $common_specializations = [
                                'Dokter Umum',
                                'Spesialis Anak',
                                'Spesialis Penyakit Dalam',
                                'Spesialis Bedah',
                                'Spesialis Jantung',
                                'Spesialis Kandungan',
                                'Spesialis Saraf',
                                'Spesialis Mata',
                                'Spesialis Kulit dan Kelamin',
                                'Dokter Gigi',
                                'Psikiater'
                            ];
                            
                            $spec_options = [];
                            
                            // Get options from database
                            if ($specializations->num_rows > 0) {
                                while($spec = $specializations->fetch_assoc()) {
                                    $spec_options[] = $spec['specialization'];
                                }
                            }
                            
                            // Merge with common specializations
                            $spec_options = array_unique(array_merge($spec_options, $common_specializations));
                            sort($spec_options);
                            
                            foreach($spec_options as $spec) {
                                echo "<option value=\"".htmlspecialchars($spec)."\">".htmlspecialchars($spec)."</option>";
                            }
                            ?>
                            <option value="other">Lainnya (tuliskan)</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="other-specialization-group" style="display: none;">
                        <label for="other_specialization">Spesialisasi Lainnya</label>
                        <input type="text" id="other_specialization" name="other_specialization">
                    </div>
                    
                    <div class="form-group">
                        <label for="hospital" class="required-label">Rumah Sakit/Klinik</label>
                        <input type="text" id="hospital" name="hospital" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="str_number" class="required-label">Nomor STR (Surat Tanda Registrasi)</label>
                        <input type="text" id="str_number" name="str_number" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="practice_license" class="required-label">Bukti SIP (Surat Izin Praktik)</label>
                        <input type="file" id="practice_license" name="practice_license" accept=".pdf,.jpg,.jpeg,.png" required>
                        <p class="help-text">Upload scan/foto SIP yang masih berlaku. Format: PDF, JPG, PNG. Maks: 2MB</p>
                    </div>
                </div>
                
                <!-- Profile Information Section -->
                <div class="form-section">
                    <h2 class="form-section-title">Informasi Profil</h2>
                    
                    <div class="form-group">
                        <label for="bio" class="required-label">Biografi Singkat</label>
                        <textarea id="bio" name="bio" required></textarea>
                        <p class="help-text">Jelaskan secara singkat tentang diri Anda dan pendekatan Anda terhadap pasien</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="education" class="required-label">Pendidikan</label>
                        <textarea id="education" name="education" required></textarea>
                        <p class="help-text">Format: Nama Institusi (Tahun) - pisahkan dengan baris baru untuk setiap pendidikan</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="experience">Pengalaman</label>
                        <textarea id="experience" name="experience"></textarea>
                        <p class="help-text">Format: Posisi di Institusi (Tahun) - pisahkan dengan baris baru untuk setiap posisi</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="awards">Penghargaan/Sertifikasi</label>
                        <textarea id="awards" name="awards"></textarea>
                        <p class="help-text">Pisahkan dengan baris baru untuk setiap penghargaan</p>
                    </div>
                </div>
                
                <!-- Consultation Settings Section -->
                <div class="form-section">
                    <h2 class="form-section-title">Pengaturan Konsultasi</h2>
                    
                    <div class="form-group">
                        <label for="consultation_fee" class="required-label">Biaya Konsultasi (Rp)</label>
                        <input type="number" id="consultation_fee" name="consultation_fee" min="0" step="10000" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="available_days" class="required-label">Hari Praktik</label>
                        <input type="text" id="available_days" name="available_days" required>
                        <p class="help-text">Contoh: Senin-Jumat, 08:00-16:00</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="languages">Bahasa</label>
                        <input type="text" id="languages" name="languages" placeholder="Indonesia, Inggris, dst">
                    </div>
                </div>
                
                <div class="form-footer">
                    <a href="../index.php" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">Kirim Pendaftaran</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Show/hide other specialization field
            const specializationSelect = document.getElementById('specialization');
            const otherSpecializationGroup = document.getElementById('other-specialization-group');
            
            specializationSelect.addEventListener('change', function() {
                if (this.value === 'other') {
                    otherSpecializationGroup.style.display = 'block';
                } else {
                    otherSpecializationGroup.style.display = 'none';
                }
            });
            
            // Form validation
            const form = document.querySelector('form');
            form.addEventListener('submit', function(event) {
                const photoInput = document.getElementById('photo');
                const licenseInput = document.getElementById('practice_license');
                
                // Validate file size (max 2MB)
                const maxSize = 2 * 1024 * 1024; // 2MB in bytes
                
                if (photoInput.files.length > 0 && photoInput.files[0].size > maxSize) {
                    alert('Foto profil tidak boleh lebih dari 2MB');
                    event.preventDefault();
                    return;
                }
                
                if (licenseInput.files.length > 0 && licenseInput.files[0].size > maxSize) {
                    alert('File SIP tidak boleh lebih dari 2MB');
                    event.preventDefault();
                    return;
                }
                
                // Additional validation if needed
            });
        });
    </script>
</body>
</html>