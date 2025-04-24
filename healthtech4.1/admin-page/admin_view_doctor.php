<?php
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = 'ID dokter tidak valid';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}

$doctor_id = $_GET['id'];

// Database connection
include 'connect.php';

// Get doctor data
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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon web" href="../assets/konsulku-nobg.png">
    <title>Detail Dokter - Konsultasi Dokter Online</title>
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
        
        .header-actions {
            display: flex;
            gap: 15px;
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
        
        .btn-warning {
            background-color: var(--warning-color);
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #d97706;
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #dc2626;
        }
        
        .doctor-details {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .doctor-header {
            display: flex;
            align-items: center;
            padding: 30px;
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .doctor-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 30px;
            border: 4px solid white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .doctor-title h2 {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .doctor-title p {
            font-size: 1.2rem;
            color: #64748b;
        }
        
        .doctor-content {
            padding: 30px;
        }
        
        .doctor-info {
            margin-bottom: 30px;
        }
        
        .info-group {
            margin-bottom: 20px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 20px;
        }
        
        .info-label {
            font-weight: 600;
            color: #64748b;
            margin-bottom: 5px;
            display: block;
        }
        
        .info-value {
            font-size: 1.1rem;
        }
        
        .doctor-bio {
            background-color: #f8fafc;
            padding: 20px;
            border-radius: 5px;
            border-left: 4px solid var(--primary-color);
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        
        @media (max-width: 768px) {
            .header-flex {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .header-actions {
                flex-direction: column;
                width: 100%;
            }
            
            .doctor-header {
                flex-direction: column;
                text-align: center;
            }
            
            .doctor-photo {
                margin-right: 0;
                margin-bottom: 20px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-flex">
                <div>
                    <h1>Detail Dokter</h1>
                    <p>Informasi lengkap dokter</p>
                </div>
                <div class="header-actions">
                    <a href="index.php" class="btn btn-white">Kembali ke Dashboard</a>
                    <a href="admin_logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="doctor-details">
            <div class="doctor-header">
                <img src="<?php echo $doctor['photo'] ?: 'images/default-doctor.jpg'; ?>" alt="<?php echo htmlspecialchars($doctor['name']); ?>" class="doctor-photo">
                <div class="doctor-title">
                    <h2><?php echo htmlspecialchars($doctor['name']); ?></h2>
                    <p><?php echo htmlspecialchars($doctor['specialization']); ?></p>
                </div>
            </div>
            
            <div class="doctor-content">
                <div class="doctor-info">
                    <div class="info-group">
                        <span class="info-label">Email</span>
                        <span class="info-value"><?php echo htmlspecialchars($doctor['email']); ?></span>
                    </div>
                    
                    <div class="info-group">
                        <span class="info-label">Rumah Sakit</span>
                        <span class="info-value"><?php echo htmlspecialchars($doctor['hospital']); ?></span>
                    </div>
                    
                    <div class="info-group">
                        <span class="info-label">Pengalaman</span>
                        <span class="info-value"><?php echo htmlspecialchars($doctor['experience']); ?> Tahun</span>
                    </div>
                    
                    <div class="info-group">
                        <span class="info-label">Biaya Konsultasi</span>
                        <span class="info-value">Rp <?php echo number_format($doctor['consultation_fee'], 0, ',', '.'); ?></span>
                    </div>
                    
                    <div class="info-group">
                        <span class="info-label">Jadwal Praktek</span>
                        <span class="info-value"><?php echo htmlspecialchars($doctor['available_days']); ?></span>
                    </div>
                </div>
                
                <div class="doctor-bio">
                    <h3>Biografi</h3>
                    <p><?php echo nl2br(htmlspecialchars($doctor['bio'])); ?></p>
                </div>
                
                <div class="action-buttons">
                    <a href="admin_edit_doctor.php?id=<?php echo $doctor['id']; ?>" class="btn btn-warning">Edit Data</a>
                    <a href="index.php" class="btn btn-primary">Kembali ke Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>