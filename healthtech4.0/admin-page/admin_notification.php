<?php
// admin_notification.php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}
// Database connection
include 'connect.php';

// Mark notification as read if requested
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $notification_id = $_GET['mark_read'];
    $update_stmt = $db->prepare("UPDATE admin_notifications SET is_read = 1 WHERE id = ?");
    $update_stmt->bind_param("i", $notification_id);
    $update_stmt->execute();
    
    // Redirect to avoid resubmission
    header("Location: admin_notification.php");
    exit();
}

// Get all notifications, ordered by newest first and unread first
$notifications_query = "SELECT * FROM admin_notifications ORDER BY is_read ASC, created_at DESC";
$notifications = $db->query($notifications_query);

// Count unread notifications
$unread_count_query = "SELECT COUNT(*) as count FROM admin_notifications WHERE is_read = 0";
$unread_result = $db->query($unread_count_query);
$unread_count = $unread_result->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Notifications</title>
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
        
        .badge {
            display: inline-block;
            background-color: var(--danger-color);
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            margin-left: 5px;
        }
        
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .card-header {
            background-color: #f0f4f8;
            padding: 15px 20px;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
        }
        
        .notification-list {
            list-style: none;
        }
        
        .notification-item {
            border-bottom: 1px solid #e2e8f0;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.2s;
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-item:hover {
            background-color: #f8fafc;
        }
        
        .notification-item.unread {
            background-color: #ebf5ff;
        }
        
        .notification-item.unread:hover {
            background-color: #e1efff;
        }
        
        .notification-content {
            flex: 1;
        }
        
        .notification-type {
            font-size: 0.8rem;
            color: #64748b;
            margin-bottom: 5px;
            text-transform: uppercase;
            font-weight: 600;
        }
        
        .notification-message {
            margin-bottom: 5px;
        }
        
        .notification-date {
            font-size: 0.8rem;
            color: #64748b;
        }
        
        .notification-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 5px;
            font-size: 0.9rem;
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
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #64748b;
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
    </style>
</head>
<body>
    <header>
        <div class="container header-content">
            <h1>Admin Notifications <?php if($unread_count > 0): ?><span class="badge"><?php echo $unread_count; ?></span><?php endif; ?></h1>
            <a href="index.php" class="btn btn-light">Dashboard</a>
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
        
        <div class="card">
            <div class="card-header">
                All Notifications
            </div>
            
            <?php if ($notifications->num_rows > 0): ?>
                <ul class="notification-list">
                    <?php while ($notification = $notifications->fetch_assoc()): ?>
                        <li class="notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?>">
                            <div class="notification-content">
                                <div class="notification-type"><?php echo htmlspecialchars($notification['type']); ?></div>
                                <div class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></div>
                                <div class="notification-date"><?php echo date('d M Y, H:i', strtotime($notification['created_at'])); ?></div>
                            </div>
                            <div class="notification-actions">
                                <?php if ($notification['type'] == 'doctor_application'): ?>
                                    <a href="review_application.php?id=<?php echo $notification['reference_id']; ?>" class="btn btn-primary">Review</a>
                                <?php endif; ?>
                                
                                <?php if (!$notification['is_read']): ?>
                                    <a href="admin_notification.php?mark_read=<?php echo $notification['id']; ?>" class="btn btn-light">Mark as Read</a>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <div class="empty-state">
                    <p>No notifications found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>