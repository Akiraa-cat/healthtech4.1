<?php
// doctor_applications.php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// Database connection
include 'connect.php';

// Get filter value
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Prepare query based on filter
$where_clause = "";
if ($filter == 'pending') {
    $where_clause = "WHERE status = 'pending'";
} elseif ($filter == 'approved') {
    $where_clause = "WHERE status = 'approved'";
} elseif ($filter == 'rejected') {
    $where_clause = "WHERE status = 'rejected'";
}

// Get applications
$query = "SELECT * FROM doctor_applications $where_clause ORDER BY created_at DESC";
$applications = $db->query($query);

// Count applications by status
$count_query = "SELECT 
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
                COUNT(*) as total_count
                FROM doctor_applications";
$count_result = $db->query($count_query);
$counts = $count_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Applications</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .filter-container {
            display: flex;
            align-items: center;
        }
        
        .filter-label {
            margin-right: 10px;
        }
        
        .filter-select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: white;
            cursor: pointer;
        }
        
        .stats-container {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            flex: 1;
            background-color: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        .total-stat {
            background-color: #f0f4f8;
        }
        
        .pending-stat .stat-number {
            color: var(--warning-color);
        }
        
        .approved-stat .stat-number {
            color: var(--success-color);
        }
        
        .rejected-stat .stat-number {
            color: var(--danger-color);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        th {
            background-color: #f8fafc;
            font-weight: 600;
            color: #334155;
        }
        
        tr:hover {
            background-color: #f8fafc;
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
            <h1>Doctor Applications</h1>
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
        
        <div class="stats-container">
            <div class="stat-card total-stat">
                <div class="stat-number"><?php echo $counts['total_count']; ?></div>
                <div class="stat-label">Total Applications</div>
            </div>
            <div class="stat-card pending-stat">
                <div class="stat-number"><?php echo $counts['pending_count']; ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card approved-stat">
                <div class="stat-number"><?php echo $counts['approved_count']; ?></div>
                <div class="stat-label">Approved</div>
            </div>
            <div class="stat-card rejected-stat">
                <div class="stat-number"><?php echo $counts['rejected_count']; ?></div>
                <div class="stat-label">Rejected</div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <span>Applications</span>
                <div class="filter-container">
                    <span class="filter-label">Filter:</span>
                    <select class="filter-select" id="status-filter" onchange="window.location = 'doctor_applications.php?filter=' + this.value">
                        <option value="all" <?php echo $filter == 'all' ? 'selected' : ''; ?>>All Applications</option>
                        <option value="pending" <?php echo $filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo $filter == 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo $filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
            </div>
            
            <?php if ($applications->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Specialization</th>
                            <th>Hospital</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($application = $applications->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $application['id']; ?></td>
                                <td><?php echo htmlspecialchars($application['name']); ?></td>
                                <td><?php echo htmlspecialchars($application['specialization']); ?></td>
                                <td><?php echo htmlspecialchars($application['hospital']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $application['status']; ?>">
                                        <?php 
                                            if ($application['status'] == 'pending') echo 'Pending';
                                            elseif ($application['status'] == 'approved') echo 'Approved';
                                            else echo 'Rejected';
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo date('d M Y', strtotime($application['created_at'])); ?></td>
                                <td><a href="review_application.php?id=<?php echo $application['id']; ?>" class="btn btn-primary">View</a></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <p>No applications found with the selected filter.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>