<?php
// review_application.php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// Database connection
include 'connect.php';

// Check if application ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = "ID aplikasi tidak valid.";
    $_SESSION['message_type'] = "error";
    header("Location: admin_notification.php");
    exit();
}

$application_id = $_GET['id'];

// Fetch application details
$query = "SELECT * FROM doctor_applications WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $application_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['message'] = "Aplikasi tidak ditemukan.";
    $_SESSION['message_type'] = "error";
    header("Location: admin_notification.php");
    exit();
}

$application = $result->fetch_assoc();

// Mark related notification as read
$notification_update = "UPDATE admin_notifications SET is_read = 1 WHERE reference_id = ? AND type = 'doctor_application'";
$notification_stmt = $db->prepare($notification_update);
$notification_stmt->bind_param("i", $application_id);
$notification_stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Doctor Application</title>
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #06b6d4;
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
            width: 95%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        header h1 {
            font-size: 1.8rem;
        }
        
        .application-container {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .left-column {
            flex: 2;
        }
        
        .right-column {
            flex: 1;
        }
        
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .card-header {
            background-color: #f0f4f8;
            padding: 15px 20px;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .doctor-profile {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .doctor-image {
            width: 150px;
            height: 150px;
            border-radius: 8px;
            object-fit: cover;
        }
        
        .doctor-details h2 {
            margin-bottom: 5px;
            color: var(--primary-color);
        }
        
        .doctor-specialty {
            color: #64748b;
            margin-bottom: 10px;
        }
        
        .info-group {
            margin-bottom: 15px;
        }
        
        .info-label {
            font-weight: 600;
            margin-bottom: 5px;
            color: #334155;
        }
        
        .info-value {
            margin-bottom: 10px;
        }
        
        pre {
            white-space: pre-wrap;
            font-family: inherit;
        }
        
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .status-pending {
            background-color: #fff7ed;
            color: #c2410c;
            border: 1px solid #fdba74;
        }
        
        .status-approved {
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }
        
        .status-rejected {
            background-color: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }
        
        .document-link {
            display: block;
            padding: 10px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            margin-bottom: 10px;
            text-decoration: none;
            color: var(--primary-color);
            transition: all 0.2s;
        }
        
        .document-link:hover {
            background-color: #f1f5f9;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            min-height: 100px;
            font-family: inherit;
            font-size: 0.95rem;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
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
        
        .btn-light {
            background-color: #e2e8f0;
            color: #334155;
        }
        
        .btn-light:hover {
            background-color: #cbd5e1;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
        }
        
        .nav-links {
            margin-top: 20px;
            margin-bottom: 30px;
        }
        
        .nav-links a {
            margin-right: 15px;
            text-decoration: none;
            color: var(--primary-color);
            font-weight: 500;
        }
        
        .nav-links a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .application-container {
                flex-direction: column;
            }
            
            .doctor-profile {
                flex-direction: column;
            }
            
            .doctor-image {
                width: 100%;
                max-width: 200px;
                margin: 0 auto;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container header-content">
            <h1>Review Doctor Application</h1>
            <a href="admin_notification.php" class="btn btn-light">Back to Notifications</a>
        </div>
    </header>
    
    <div class="container">
        <div class="nav-links">
            <a href="index.php">Dashboard</a>
            <a href="admin_notification.php">Notifications</a>
            <a href="doctor_applications.php">Doctor Applications</a>
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                <?php 
                    echo $_SESSION['message']; 
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                ?>
            </div>
        <?php endif; ?>
        
        <div class="application-container">
            <div class="left-column">
                <div class="card">
                    <div class="card-header">
                        Application Information
                        <span class="status-badge status-<?php echo $application['status']; ?>">
                            <?php 
                                if ($application['status'] == 'pending') echo 'Menunggu';
                                elseif ($application['status'] == 'approved') echo 'Disetujui';
                                else echo 'Ditolak';
                            ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="doctor-profile">
                            <img src="../<?php echo htmlspecialchars($application['photo']); ?>" alt="Doctor Photo" class="doctor-image">
                            <div class="doctor-details">
                                <h2><?php echo htmlspecialchars($application['name']); ?></h2>
                                <div class="doctor-specialty"><?php echo htmlspecialchars($application['specialization']); ?></div>
                                <div class="info-group">
                                    <div class="info-label">Email</div>
                                    <div class="info-value"><?php echo htmlspecialchars($application['email']); ?></div>
                                </div>
                                <div class="info-group">
                                    <div class="info-label">Telepon</div>
                                    <div class="info-value"><?php echo htmlspecialchars($application['phone']); ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Rumah Sakit/Klinik</div>
                            <div class="info-value"><?php echo htmlspecialchars($application['hospital']); ?></div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Nomor STR</div>
                            <div class="info-value"><?php echo htmlspecialchars($application['str_number']); ?></div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Biografi</div>
                            <div class="info-value"><pre><?php echo htmlspecialchars($application['bio']); ?></pre></div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Pendidikan</div>
                            <div class="info-value"><pre><?php echo htmlspecialchars($application['education']); ?></pre></div>
                        </div>
                        
                        <?php if (!empty($application['experience'])): ?>
                        <div class="info-group">
                            <div class="info-label">Pengalaman</div>
                            <div class="info-value"><pre><?php echo htmlspecialchars($application['experience']); ?></pre></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($application['awards'])): ?>
                        <div class="info-group">
                            <div class="info-label">Penghargaan/Sertifikasi</div>
                            <div class="info-value"><pre><?php echo htmlspecialchars($application['awards']); ?></pre></div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="info-group">
                            <div class="info-label">Biaya Konsultasi</div>
                            <div class="info-value">Rp <?php echo number_format($application['consultation_fee'], 0, ',', '.'); ?></div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Hari Praktik</div>
                            <div class="info-value"><?php echo htmlspecialchars($application['available_days']); ?></div>
                        </div>
                        
                        <?php if (!empty($application['languages'])): ?>
                        <div class="info-group">
                            <div class="info-label">Bahasa</div>
                            <div class="info-value"><?php echo htmlspecialchars($application['languages']); ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="info-group">
                            <div class="info-label">Tanggal Pengajuan</div>
                            <div class="info-value"><?php echo date('d M Y, H:i', strtotime($application['created_at'])); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="right-column">
                <div class="card">
                    <div class="card-header">Documents</div>
                    <div class="card-body">
                        <!-- <a href="../doctor-form/<?php echo htmlspecialchars($application['practice_license']); ?>" target="_blank" class="document-link">
                            View SIP (Surat Izin Praktik)
                        </a> -->
                    </div>
                </div>
                
                <?php if ($application['status'] == 'pending'): ?>
                <div class="card">
                    <div class="card-header">Review Application</div>
                    <div class="card-body">
                        <form action="process_application.php" method="post">
                            <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                            
                            <div class="form-group">
                                <label for="admin_notes">Admin Notes (Optional)</label>
                                <textarea id="admin_notes" name="admin_notes"><?php echo htmlspecialchars($application['admin_notes'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="btn-group">
                                <button type="submit" name="action" value="approve" class="btn btn-success">Approve Application</button>
                                <button type="submit" name="action" value="reject" class="btn btn-danger">Reject Application</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php else: ?>
                <div class="card">
                    <div class="card-header">Application Status</div>
                    <div class="card-body">
                        <div class="info-group">
                            <div class="info-label">Status</div>
                            <div class="info-value">
                                <span class="status-badge status-<?php echo $application['status']; ?>">
                                    <?php 
                                        if ($application['status'] == 'approved') echo 'Disetujui';
                                        else echo 'Ditolak';
                                    ?>
                                </span>
                            </div>
                        </div>
                        
                        <?php if (!empty($application['admin_notes'])): ?>
                        <div class="info-group">
                            <div class="info-label">Admin Notes</div>
                            <div class="info-value"><pre><?php echo htmlspecialchars($application['admin_notes']); ?></pre></div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="info-group">
                            <div class="info-label">Last Updated</div>
                            <div class="info-value"><?php echo date('d M Y, H:i', strtotime($application['updated_at'])); ?></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>