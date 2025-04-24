<?php
// Mulai session
session_start();

// Koneksi ke database
require_once 'connect.php';

// Buat tabel jika belum ada
function createTables($conn) {
    // Tabel konsulku_email untuk subscriber
    $sql = "CREATE TABLE IF NOT EXISTS konsulku_email (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        name VARCHAR(255),
        subscribe_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($sql)) {
        echo "Error creating table konsulku_email: " . $conn->error;
    }
    
    // Tabel konsulku_feedback untuk masukan user
    $sql = "CREATE TABLE IF NOT EXISTS konsulku_feedback (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        read_status TINYINT(1) DEFAULT 0,
        submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($sql)) {
        echo "Error creating table konsulku_feedback: " . $conn->error;
    }
    
    // Tabel konsulku_settings untuk pengaturan
    $sql = "CREATE TABLE IF NOT EXISTS konsulku_settings (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        setting_name VARCHAR(255) NOT NULL UNIQUE,
        setting_value TEXT NOT NULL,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($sql)) {
        echo "Error creating table konsulku_settings: " . $conn->error;
    }
    
    // Tabel konsulku_email_log untuk log pengiriman email
    $sql = "CREATE TABLE IF NOT EXISTS konsulku_email_log (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        subject VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        recipients INT(11) NOT NULL,
        sent_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($sql)) {
        echo "Error creating table konsulku_email_log: " . $conn->error;
    }
    
    // Inisialisasi pengaturan default
    $settings = [
        ['maintenance_end_date', date('Y-m-d H:i:s', strtotime('+2 days'))],
        ['maintenance_message', 'Maaf atas ketidaknyamanan ini. Kami sedang melakukan pembaruan sistem untuk meningkatkan layanan kesehatan digital kami.'],
        ['notification_message', 'Selamat! Website Konsulku sudah kembali online. Silakan kunjungi kami di http://konsulku.sijabright.my.id']
    ];
    
    foreach ($settings as $setting) {
        $sql = "INSERT IGNORE INTO konsulku_settings (setting_name, setting_value) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $setting[0], $setting[1]);
        $stmt->execute();
    }
}

// Panggil fungsi untuk membuat tabel
createTables($conn);

// Fungsi untuk mendapatkan pengaturan
function getSetting($conn, $settingName) {
    $sql = "SELECT setting_value FROM konsulku_settings WHERE setting_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $settingName);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['setting_value'];
    }
    
    return null;
}

// Ambil pengaturan penting
$countdownDate = getSetting($conn, 'maintenance_end_date');
$maintenanceMessage = getSetting($conn, 'maintenance_message');
$notificationMessage = getSetting($conn, 'notification_message');

// Proses form subscriber
if (isset($_POST['action']) && $_POST['action'] == 'subscribe') {
    $email = $_POST['email'];
    $name = isset($_POST['name']) ? $_POST['name'] : "Guest";
    
    $sql = "INSERT INTO konsulku_email (email, name) VALUES (?, ?) ON DUPLICATE KEY UPDATE name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $email, $name, $name);
    
    $response = [];
    if ($stmt->execute()) {
        $response = ['status' => 'success', 'message' => 'Terima kasih! Kami akan memberi tahu Anda saat website kembali online.'];
    } else {
        $response = ['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $stmt->error];
    }
    
    echo json_encode($response);
    exit;
}

// Proses form feedback
if (isset($_POST['action']) && $_POST['action'] == 'feedback') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];
    
    $sql = "INSERT INTO konsulku_feedback (name, email, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $email, $message);
    
    $response = [];
    if ($stmt->execute()) {
        $response = ['status' => 'success', 'message' => 'Pesan Anda telah dikirim! Terima kasih atas masukan Anda.'];
    } else {
        $response = ['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $stmt->error];
    }
    
    echo json_encode($response);
    exit;
}

// Proses login admin
if (isset($_POST['action']) && $_POST['action'] == 'admin_login') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Verifikasi kredensial
    // Dalam kasus nyata, gunakan password_hash() dan password_verify()
    if ($username === 'admin@admin.net' && $password === '$goodgame123') {
        $_SESSION['admin_logged_in'] = true;
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Username atau password tidak valid']);
    }
    exit;
}

// Proses admin actions
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    // Get subscribers
    if (isset($_GET['action']) && $_GET['action'] == 'get_subscribers') {
        $sql = "SELECT id, email, name, subscribe_date FROM konsulku_email ORDER BY subscribe_date DESC";
        $result = $conn->query($sql);
        
        $subscribers = [];
        while ($row = $result->fetch_assoc()) {
            $subscribers[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $subscribers]);
        exit;
    }
    
    // Get feedback messages
    if (isset($_GET['action']) && $_GET['action'] == 'get_messages') {
        $sql = "SELECT id, name, email, message, read_status, submission_date FROM konsulku_feedback ORDER BY submission_date DESC";
        $result = $conn->query($sql);
        
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'data' => $messages]);
        exit;
    }
    
    // Mark message as read/unread
    if (isset($_POST['action']) && $_POST['action'] == 'toggle_read') {
        $id = $_POST['id'];
        $status = $_POST['status'];
        
        $sql = "UPDATE konsulku_feedback SET read_status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $status, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        exit;
    }
    
    // Delete message
    if (isset($_POST['action']) && $_POST['action'] == 'delete_message') {
        $id = $_POST['id'];
        
        $sql = "DELETE FROM konsulku_feedback WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        exit;
    }
    
    // Delete subscriber
    if (isset($_POST['action']) && $_POST['action'] == 'delete_subscriber') {
        $id = $_POST['id'];
        
        $sql = "DELETE FROM konsulku_email WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        exit;
    }
    
    // Save settings
    if (isset($_POST['action']) && $_POST['action'] == 'save_settings') {
        $endDate = $_POST['end_date'];
        $maintenanceMsg = $_POST['maintenance_message'];
        $notificationMsg = $_POST['notification_message'];
        
        $settings = [
            ['maintenance_end_date', $endDate],
            ['maintenance_message', $maintenanceMsg],
            ['notification_message', $notificationMsg]
        ];
        
        $hasError = false;
        foreach ($settings as $setting) {
            $sql = "UPDATE konsulku_settings SET setting_value = ? WHERE setting_name = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $setting[1], $setting[0]);
            
            if (!$stmt->execute()) {
                $hasError = true;
                break;
            }
        }
        
        if (!$hasError) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save settings']);
        }
        exit;
    }
    
    // Send email broadcast
    if (isset($_POST['action']) && $_POST['action'] == 'send_broadcast') {
        $subject = $_POST['subject'];
        $message = $_POST['message'];
        
        // Count subscribers
        $result = $conn->query("SELECT COUNT(*) as count FROM konsulku_email");
        $row = $result->fetch_assoc();
        $count = $row['count'];
        
        // In a real application, you would send emails here
        // For demonstration, we'll just log it
        $sql = "INSERT INTO konsulku_email_log (subject, message, recipients) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $subject, $message, $count);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => "Email terkirim ke $count subscriber"]);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        exit;
    }
    
    // End maintenance mode
    if (isset($_POST['action']) && $_POST['action'] == 'end_maintenance') {
        // In a real application, you would redirect to the live site
        // and send notifications to all subscribers
        
        // For demonstration, we'll just log it
        $message = getSetting($conn, 'notification_message');
        $subject = "Konsulku is back online!";
        
        // Count subscribers
        $result = $conn->query("SELECT COUNT(*) as count FROM konsulku_email");
        $row = $result->fetch_assoc();
        $count = $row['count'];
        
        $sql = "INSERT INTO konsulku_email_log (subject, message, recipients) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $subject, $message, $count);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => "Maintenance mode ended. Notifications sent to $count subscribers."]);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        exit;
    }
}

// Logout admin
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Fungsi untuk mengirim email (dalam aplikasi nyata)
function sendEmail($to, $subject, $message) {
    // Implementasi pengiriman email sesungguhnya akan di sini
    // Menggunakan PHPMailer atau fungsi mail() PHP
    
    // Contoh dengan mail() PHP
    $headers = 'From: noreply@konsulku.com' . "\r\n" .
               'Reply-To: info@konsulku.com' . "\r\n" .
               'X-Mailer: PHP/' . phpversion();
    
    return mail($to, $subject, $message, $headers);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konsulku - Under Maintenance</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f0f8ff;
            color: #333;
            height: 100vh;
            overflow-x: hidden;
            position: relative;
        }
        
        .bg-blur {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('/api/placeholder/1200/800');
            background-size: cover;
            background-position: center;
            filter: blur(8px);
            opacity: 0.2;
            z-index: -1;
        }
        
        .maintenance-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 40px 20px;
            text-align: center;
            z-index: 1;
        }
        
        .logo {
            margin-bottom: 30px;
        }
        
        .logo img {
            height: 80px;
        }
        
        .character {
            margin: 20px 0;
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
            100% {
                transform: translateY(0px);
            }
        }
        
        h1 {
            color: #2563eb;
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        
        p {
            font-size: 1.2rem;
            max-width: 600px;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .timer {
            font-size: 1.5rem;
            margin-bottom: 30px;
            color: #2563eb;
            font-weight: bold;
        }
        
        .notification-form {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            margin-bottom: 30px;
        }
        
        .form-title {
            margin-bottom: 15px;
            color: #2563eb;
        }
        
        input[type="email"],
        input[type="text"],
        input[type="password"],
        textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        button {
            background-color: #2563eb;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #1d4ed8;
        }
        
        button.secondary {
            background-color: #64748b;
        }
        
        button.secondary:hover {
            background-color: #475569;
        }
        
        .social-links {
            margin-top: 30px;
        }
        
        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: #2563eb;
            font-size: 1.5rem;
            text-decoration: none;
        }
        
        #notification-message,
        #feedback-message-status {
            margin-top: 15px;
            font-weight: bold;
        }
        
        .success {
            color: #10b981;
        }
        
        .error {
            color: #ef4444;
        }
        
        .admin-link {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: rgba(255, 255, 255, 0.7);
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            color: #64748b;
            font-size: 0.8rem;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 100;
            justify-content: center;
            align-items: center;
            overflow-y: auto;
            padding: 20px;
        }
        
        .modal-content {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 800px;
            position: relative;
        }
        
        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 1.5rem;
            cursor: pointer;
            color: #64748b;
        }
        
        .tab-buttons {
            display: flex;
            margin-bottom: 20px;
        }
        
        .tab-button {
            padding: 10px 20px;
            background-color: #f1f5f9;
            color: #2563eb;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            margin-right: 5px;
            border-radius: 5px 5px 0 0;
        }
        
        .tab-button.active {
            background-color: #2563eb;
            color: white;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .user-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 20px;
        }
        
        .user-list-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .user-list-item:last-child {
            border-bottom: none;
        }
        
        .message-box {
            margin-top: 20px;
        }
        
        .feedback-form {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }
        
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
            margin-left: 10px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .notification-section {
            background-color: #e0f2fe;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }
        
        @media (max-width: 768px) {
            h1 {
                font-size: 2rem;
            }
            
            p {
                font-size: 1rem;
            }
            
            .character svg {
                width: 200px;
            }
            
            .tab-buttons {
                flex-direction: column;
            }
            
            .tab-button {
                margin-right: 0;
                margin-bottom: 5px;
                border-radius: 5px;
            }
        }
    </style>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon web " href="../assets/konsulku-nobg.png">
</head>
<body>
    <div class="bg-blur"></div>
    <div class="maintenance-container">
        <div class="logo">
            <svg width="200" height="50" viewBox="0 0 200 50">
                <text x="10" y="35" fill="#2563eb" font-size="30" font-weight="bold">Konsulku. </text>
                <circle cx="180" cy="25" r="15" fill="#2563eb" opacity="0.2" />
                <circle cx="180" cy="25" r="10" fill="#2563eb" />
            </svg>
        </div>
        
        <h1>Website Sedang Dalam Perbaikan</h1>
        <p><?php echo htmlspecialchars($maintenanceMessage); ?></p>
        
        <div class="timer" id="countdown">00:00:00:00</div>
        
        <div class="character">
            <svg width="200" height="200" viewBox="0 0 200 200">
                <circle cx="100" cy="100" r="90" fill="#2563eb" opacity="0.1" />
                <circle cx="100" cy="100" r="80" stroke="#2563eb" stroke-width="4" fill="white" />
                <!-- Face -->
                <circle cx="70" cy="80" r="10" fill="#2563eb" opacity="0.7" />
                <circle cx="130" cy="80" r="10" fill="#2563eb" opacity="0.7" />
                <!-- Smile -->
                <path d="M 60 120 Q 100 150 140 120" stroke="#2563eb" stroke-width="4" fill="none" />
                <!-- Stethoscope -->
                <path d="M 40 50 C 60 30 140 30 160 50" stroke="#2563eb" stroke-width="3" fill="none" />
                <circle cx="40" cy="50" r="5" fill="#2563eb" />
                <circle cx="160" cy="50" r="5" fill="#2563eb" />
            </svg>
        </div>
        
        <div class="notification-form">
            <h3 class="form-title">Dapatkan Notifikasi Saat Website Kembali Online</h3>
            <form id="email-form">
                <input type="email" id="email" placeholder="Alamat Email Anda" required>
                <input type="text" id="name" placeholder="Nama Anda (Opsional)">
                <button type="submit" id="submit-btn">Beritahu Saya</button>
            </form>
            <div id="notification-message"></div>
        </div>
        
        <div class="feedback-form">
            <h3 class="form-title">Berikan Masukan atau Pertanyaan</h3>
            <form id="feedback-form">
                <input type="text" id="feedback-name" placeholder="Nama Anda" required>
                <input type="email" id="feedback-email" placeholder="Alamat Email Anda" required>
                <textarea id="feedback-message" placeholder="Pesan Anda" rows="4" required></textarea>
                <button type="submit" id="feedback-btn">Kirim Pesan</button>
            </form>
            <div id="feedback-message-status"></div>
        </div>
        
        <div class="social-links">
            <a href="#"><i class="fab fa-facebook"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-whatsapp"></i></a>
        </div>
    </div>
    
    <!-- Admin Link -->
    <a href="#" class="admin-link" id="admin-link">Admin</a>
    
    <!-- Admin Modal -->
    <div id="admin-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal" id="close-modal">&times;</span>
            <h2>Admin Panel</h2>
            
            <div id="login-section" style="<?php echo isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true ? 'display:none;' : ''; ?>">
                <form id="admin-login-form">
                    <input type="text" id="admin-username" placeholder="Username" required>
                    <input type="password" id="admin-password" placeholder="Password" required>
                    <button type="submit" id="login-btn">Login</button>
                </form>
                <div id="login-message"></div>
            </div>
            
            <div id="admin-panel" style="<?php echo isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true ? '' : 'display:none;'; ?>">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>Selamat datang, Admin</h3>
                    <a href="?action=logout" class="secondary" style="text-decoration: none; color: white; padding: 5px 10px; border-radius: 5px; background-color: #64748b;">Logout</a>
                </div>
                
                <div class="tab-buttons">
                    <button class="tab-button active" data-tab="dashboard">Dashboard</button>
                    <button class="tab-button" data-tab="subscribers">Subscribers</button>
                    <button class="tab-button" data-tab="messages">Messages</button>
                    <button class="tab-button" data-tab="notification_email">Notification Email</button>
                    <button class="tab-button" data-tab="settings">Settings</button>
                </div>
                
                <div class="tab-content active" id="dashboard-tab">
                    <h3>Maintenance Status</h3>
                    <p>Current status: <span id="maintenance-status">Active</span></p>
                    <p>Countdown ends: <span id="countdown-end-time"></span></p>
                    <p>Subscribers: <span id="subscriber-count">0</span></p>
                    <p>Messages: <span id="message-count">0</span></p>
                    <div style="margin-top: 20px;">
                        <button id="end-maintenance">End Maintenance Mode & Send Notifications</button>
                    </div>
                </div>
                
                <div class="tab-content" id="subscribers-tab">
                    <h3>Email Subscribers</h3>
                    <div class="user-list" id="subscriber-list">
                        <div class="user-list-item">Loading subscribers...</div>
                    </div>
                </div>
                
                <div class="tab-content" id="messages-tab">
                    <h3>User Messages</h3>
                    <div class="user-list" id="message-list">
                        <div class="user-list-item">Loading messages...</div>
                    </div>
                </div>
                
                <div class="tab-content" id="notification_email-tab">
                    <h3>Send Notification Email</h3>
                    <div class="notification-section">
                    <p>Send customized email to all subscribers in the konsulku.email database.</p>
                    </div>
                    <div class="message-box">
                        <input type="text" id="broadcast-subject" placeholder="Subject" required>
                        <textarea id="broadcast-message" placeholder="Your message to all subscribers" rows="4" required></textarea>
                        <button id="send-broadcast">Send to All Subscribers</button>
                        <div id="broadcast-result" style="margin-top: 15px;"></div>
                    </div>
                    
                    <h3 style="margin-top: 30px;">Email Log</h3>
                    <div class="user-list" id="email-log-list">
                        <div class="user-list-item">Loading email logs...</div>
                    </div>
                </div>
                
                <div class="tab-content" id="settings-tab">
                    <h3>Maintenance Settings</h3>
                    <form id="settings-form">
                        <label for="maintenance-end-date">Set End Date:</label>
                        <input type="datetime-local" id="maintenance-end-date" required>
                        <label for="maintenance-message">Maintenance Message:</label>
                        <textarea id="maintenance-message" rows="4" required><?php echo htmlspecialchars($maintenanceMessage); ?></textarea>
                        <label for="notify-message">Notification Message:</label>
                        <textarea id="notify-message" rows="4" required><?php echo htmlspecialchars($notificationMessage); ?></textarea>
                        <button type="submit" id="save-settings-btn">Save Settings</button>
                        <div id="settings-result" style="margin-top: 15px;"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Update the countdown based on server setting
        const countdownDate = new Date('<?php echo $countdownDate; ?>').getTime();
        const countdown = document.getElementById('countdown');
        
        // Update countdown time display
        function updateCountdown() {
            const now = new Date().getTime();
            const distance = countdownDate - now;
            
            if (distance < 0) {
                clearInterval(timer);
                countdown.innerHTML = "00:00:00:00";
                return;
            }
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            countdown.innerHTML = 
                (days < 10 ? "0" + days : days) + ":" +
                (hours < 10 ? "0" + hours : hours) + ":" +
                (minutes < 10 ? "0" + minutes : minutes) + ":" +
                (seconds < 10 ? "0" + seconds : seconds);
        }
        
        // Initial call and set interval
        updateCountdown();
        const timer = setInterval(updateCountdown, 1000);
        
        // Email form submission with AJAX
        const emailForm = document.getElementById('email-form');
        const notificationMessage = document.getElementById('notification-message');
        const submitBtn = document.getElementById('submit-btn');
        
        emailForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading indicator
            submitBtn.innerHTML = 'Mengirim... <span class="loading"></span>';
            submitBtn.disabled = true;
            
            const formData = new FormData();
            formData.append('action', 'subscribe');
            formData.append('email', document.getElementById('email').value);
            formData.append('name', document.getElementById('name').value || "Guest");
            
            fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                notificationMessage.textContent = data.message;
                notificationMessage.className = data.status;
                
                if (data.status === 'success') {
                    emailForm.reset();
                }
                
                // Reset button
                submitBtn.innerHTML = 'Beritahu Saya';
                submitBtn.disabled = false;
            })
            .catch(error => {
                notificationMessage.textContent = 'Terjadi kesalahan. Silakan coba lagi.';
                notificationMessage.className = 'error';
                
                // Reset button
                submitBtn.innerHTML = 'Beritahu Saya';
                submitBtn.disabled = false;
            });
        });
        
        // Feedback form submission with AJAX
        const feedbackForm = document.getElementById('feedback-form');
        const feedbackMessageStatus = document.getElementById('feedback-message-status');
        const feedbackBtn = document.getElementById('feedback-btn');
        
        feedbackForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading indicator
            feedbackBtn.innerHTML = 'Mengirim... <span class="loading"></span>';
            feedbackBtn.disabled = true;
            
            const formData = new FormData();
            formData.append('action', 'feedback');
            formData.append('name', document.getElementById('feedback-name').value);
            formData.append('email', document.getElementById('feedback-email').value);
            formData.append('message', document.getElementById('feedback-message').value);
            
            fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                feedbackMessageStatus.textContent = data.message;
                feedbackMessageStatus.className = data.status;
                
                if (data.status === 'success') {
                    feedbackForm.reset();
                }
                
                // Reset button
                feedbackBtn.innerHTML = 'Kirim Pesan';
                feedbackBtn.disabled = false;
            })
            .catch(error => {
                feedbackMessageStatus.textContent = 'Terjadi kesalahan. Silakan coba lagi.';
                feedbackMessageStatus.className = 'error';
                
                // Reset button
                feedbackBtn.innerHTML = 'Kirim Pesan';
                feedbackBtn.disabled = false;
            });
        });
        
        // Animate the doctor character
        const character = document.querySelector('.character svg');
        let rotation = 0;
        
        setInterval(() => {
            rotation = rotation === 5 ? -5 : 5;
            character.style.transform = `rotate(${rotation}deg)`;
        }, 3000);
        
        // Admin Panel
        const adminLink = document.getElementById('admin-link');
        const adminModal = document.getElementById('admin-modal');
        const closeModal = document.getElementById('close-modal');
        const adminLoginForm = document.getElementById('admin-login-form');
        const loginMessage = document.getElementById('login-message');
        const adminPanel = document.getElementById('admin-panel');
        const loginSection = document.getElementById('login-section');
        const loginBtn = document.getElementById('login-btn');
        
        // Open modal
        adminLink.addEventListener('click', function(e) {
            e.preventDefault();
            adminModal.style.display = 'flex';
        });
        
        // Close modal
        closeModal.addEventListener('click', function() {
            adminModal.style.display = 'none';
        });
        
        // Close modal if clicked outside
        window.addEventListener('click', function(e) {
            if (e.target === adminModal) {
                adminModal.style.display = 'none';
            }
        });
        
        // Admin login with AJAX
        adminLoginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading indicator
            loginBtn.innerHTML = 'Logging in... <span class="loading"></span>';
            loginBtn.disabled = true;
            
            const formData = new FormData();
            formData.append('action', 'admin_login');
            formData.append('username', document.getElementById('admin-username').value);
            formData.append('password', document.getElementById('admin-password').value);
            
            fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    loginSection.style.display = 'none';
                    adminPanel.style.display = 'block';
                    loadAdminData();
                } else {
                    loginMessage.textContent = data.message;
                    loginMessage.className = 'error';
                }
                
                // Reset button
                loginBtn.innerHTML = 'Login';
                loginBtn.disabled = false;
            })
            .catch(error => {
                loginMessage.textContent = 'Terjadi kesalahan saat login. Silakan coba lagi.';
                loginMessage.className = 'error';
                
                // Reset button
                loginBtn.innerHTML = 'Login';
                loginBtn.disabled = false;
            });
        });
        
        // Tab navigation
        const tabButtons = document.querySelectorAll('.tab-button');
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons and content
                tabButtons.forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                
                // Add active class to current button and content
                this.classList.add('active');
                document.getElementById(`${this.dataset.tab}-tab`).classList.add('active');
                
                // Load specific data for each tab if needed
                if (this.dataset.tab === 'subscribers') {
                    loadSubscribers();
                } else if (this.dataset.tab === 'messages') {
                    loadMessages();
                } else if (this.dataset.tab === 'notification_email') {
                    loadEmailLogs();
                }
            });
        });
        
        // Load admin data
        function loadAdminData() {
            // Set current date in maintenance end date input
            const dateTimeString = new Date(countdownDate).toISOString().slice(0, 16);
            document.getElementById('maintenance-end-date').value = dateTimeString;
            
            // Update dashboard info
            document.getElementById('countdown-end-time').textContent = new Date(countdownDate).toLocaleString();
            
            // Load initial data
            loadSubscribers();
            loadMessages();
        }
        
        // Load subscribers
        function loadSubscribers() {
            const subscriberList = document.getElementById('subscriber-list');
            subscriberList.innerHTML = '<div class="user-list-item">Loading subscribers...</div>';
            
            fetch('<?php echo $_SERVER['PHP_SELF']; ?>?action=get_subscribers')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const subscribers = data.data;
                        document.getElementById('subscriber-count').textContent = subscribers.length;
                        
                        if (subscribers.length === 0) {
                            subscriberList.innerHTML = '<div class="user-list-item">No subscribers yet</div>';
                        } else {
                            subscriberList.innerHTML = '';
                            subscribers.forEach(subscriber => {
                                const item = document.createElement('div');
                                item.className = 'user-list-item';
                                item.innerHTML = `
                                    <div>
                                        <strong>${subscriber.name}</strong><br>
                                        ${subscriber.email}<br>
                                        <small>Subscribed: ${subscriber.subscribe_date}</small>
                                    </div>
                                    <button class="secondary remove-subscriber" data-id="${subscriber.id}">Remove</button>
                                `;
                                subscriberList.appendChild(item);
                            });
                            
                            // Add event listeners to remove buttons
                            document.querySelectorAll('.remove-subscriber').forEach(button => {
                                button.addEventListener('click', function() {
                                    removeSubscriber(this.dataset.id);
                                });
                            });
                        }
                    } else {
                        subscriberList.innerHTML = '<div class="user-list-item error">Error loading subscribers</div>';
                    }
                })
                .catch(error => {
                    subscriberList.innerHTML = '<div class="user-list-item error">Error loading subscribers</div>';
                });
        }
        
        // Remove subscriber
        function removeSubscriber(id) {
            if (confirm('Are you sure you want to remove this subscriber?')) {
                const formData = new FormData();
                formData.append('action', 'delete_subscriber');
                formData.append('id', id);
                
                fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        loadSubscribers();
                    } else {
                        alert('Error removing subscriber: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error removing subscriber');
                });
            }
        }
        
        // Load messages
        function loadMessages() {
            const messageList = document.getElementById('message-list');
            messageList.innerHTML = '<div class="user-list-item">Loading messages...</div>';
            
            fetch('<?php echo $_SERVER['PHP_SELF']; ?>?action=get_messages')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const messages = data.data;
                        document.getElementById('message-count').textContent = messages.length;
                        
                        if (messages.length === 0) {
                            messageList.innerHTML = '<div class="user-list-item">No messages yet</div>';
                        } else {
                            messageList.innerHTML = '';
                            messages.forEach(message => {
                                const item = document.createElement('div');
                                item.className = 'user-list-item';
                                item.style.backgroundColor = message.read_status == 1 ? '#fff' : '#f0f9ff';
                                item.innerHTML = `
                                    <div>
                                        <strong>${message.name}</strong> (${message.email})<br>
                                        <small>${message.submission_date}</small><br>
                                        <p>${message.message}</p>
                                    </div>
                                    <div>
                                        <button class="secondary mark-read" data-id="${message.id}" data-status="${message.read_status == 1 ? '0' : '1'}">${message.read_status == 1 ? 'Mark Unread' : 'Mark Read'}</button>
                                        <button class="secondary remove-message" data-id="${message.id}">Delete</button>
                                    </div>
                                `;
                                messageList.appendChild(item);
                            });
                            
                            // Add event listeners to message buttons
                            document.querySelectorAll('.mark-read').forEach(button => {
                                button.addEventListener('click', function() {
                                    toggleReadStatus(this.dataset.id, this.dataset.status);
                                });
                            });
                            
                            document.querySelectorAll('.remove-message').forEach(button => {
                                button.addEventListener('click', function() {
                                    removeMessage(this.dataset.id);
                                });
                            });
                        }
                    } else {
                        messageList.innerHTML = '<div class="user-list-item error">Error loading messages</div>';
                    }
                })
                .catch(error => {
                    messageList.innerHTML = '<div class="user-list-item error">Error loading messages</div>';
                });
        }
        
        // Toggle read status
        function toggleReadStatus(id, status) {
            const formData = new FormData();
            formData.append('action', 'toggle_read');
            formData.append('id', id);
            formData.append('status', status);
            
            fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    loadMessages();
                } else {
                    alert('Error updating status: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error updating status');
            });
        }
        
        // Remove message
        function removeMessage(id) {
            if (confirm('Are you sure you want to delete this message?')) {
                const formData = new FormData();
                formData.append('action', 'delete_message');
                formData.append('id', id);
                
                fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        loadMessages();
                    } else {
                        alert('Error deleting message: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error deleting message');
                });
            }
        }
        
        // Load email logs
        function loadEmailLogs() {
            const logList = document.getElementById('email-log-list');
            
            // In a real application, you would fetch this from the server
            // For demonstration, we'll show a placeholder
            logList.innerHTML = '<div class="user-list-item">Email logs will appear here after sending broadcasts</div>';
        }
        
        // Send broadcast
        document.getElementById('send-broadcast').addEventListener('click', function() {
            const subject = document.getElementById('broadcast-subject').value;
            const message = document.getElementById('broadcast-message').value;
            
            if (!subject || !message) {
                alert('Please enter both subject and message');
                return;
            }
            
            const broadcastResult = document.getElementById('broadcast-result');
            broadcastResult.textContent = 'Sending emails...';
            broadcastResult.className = '';
            
            const formData = new FormData();
            formData.append('action', 'send_broadcast');
            formData.append('subject', subject);
            formData.append('message', message);
            
            fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                broadcastResult.textContent = data.message;
                broadcastResult.className = data.status;
                
                if (data.status === 'success') {
                    document.getElementById('broadcast-subject').value = '';
                    document.getElementById('broadcast-message').value = '';
                    loadEmailLogs();
                }
            })
            .catch(error => {
                broadcastResult.textContent = 'Error sending broadcast';
                broadcastResult.className = 'error';
            });
        });
        
        // End maintenance mode
        document.getElementById('end-maintenance').addEventListener('click', function() {
            if (confirm('Are you sure you want to end maintenance mode and notify all subscribers?')) {
                const formData = new FormData();
                formData.append('action', 'end_maintenance');
                
                fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('maintenance-status').textContent = 'Completed';
                        alert(data.message);
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error ending maintenance mode');
                });
            }
        });
        
        // Save settings
        document.getElementById('settings-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const settingsResult = document.getElementById('settings-result');
            const saveButton = document.getElementById('save-settings-btn');
            
            // Show loading indicator
            saveButton.innerHTML = 'Saving... <span class="loading"></span>';
            saveButton.disabled = true;
            
            const formData = new FormData();
            formData.append('action', 'save_settings');
            formData.append('end_date', document.getElementById('maintenance-end-date').value);
            formData.append('maintenance_message', document.getElementById('maintenance-message').value);
            formData.append('notification_message', document.getElementById('notify-message').value);
            
            fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    settingsResult.textContent = 'Settings saved successfully!';
                    settingsResult.className = 'success';
                    
                    // Update countdown
                    location.reload();
                } else {
                    settingsResult.textContent = 'Error: ' + data.message;
                    settingsResult.className = 'error';
                }
                
                // Reset button
                saveButton.innerHTML = 'Save Settings';
                saveButton.disabled = false;
            })
            .catch(error => {
                settingsResult.textContent = 'Error saving settings';
                settingsResult.className = 'error';
                
                // Reset button
                saveButton.innerHTML = 'Save Settings';
                saveButton.disabled = false;
            });
        });
        
        // Initialize admin panel if logged in
        <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
        document.addEventListener('DOMContentLoaded', function() {
            loadAdminData();
        });
        <?php endif; ?>
    </script>
</body>
</html>