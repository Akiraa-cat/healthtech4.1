<?php
// doctor_dashboard.php
session_start();
include 'connect.php';

// Check if doctor is logged in
if (!isset($_SESSION['doctor_id'])) {
    header("Location: doctor_login.php");
    exit();
}

// Get doctor information
$doctor_id = $_SESSION['doctor_id'];
$query = "SELECT * FROM doctors WHERE id = $doctor_id";
$result = $db->query($query);
$doctor = $result->fetch_assoc();

// Get doctor's consultation bookings
$bookings_query = "SELECT c.*, p.name as patient_name, p.email as patient_email, p.phone as patient_phone 
                  FROM consultations c 
                  JOIN patients p ON c.patient_id = p.id 
                  WHERE c.doctor_id = $doctor_id 
                  ORDER BY c.consultation_date DESC, c.consultation_time DESC";
$bookings_result = $db->query($bookings_query);

// Count bookings by status
$pending_count = 0;
$confirmed_count = 0;
$completed_count = 0;
$cancelled_count = 0;

// Check if the query was successful
if ($bookings_result) {
    // Store all bookings in an array
    $all_bookings = [];
    while ($booking = $bookings_result->fetch_assoc()) {
        $all_bookings[] = $booking;
        
        // Count by status
        switch ($booking['status']) {
            case 'pending':
                $pending_count++;
                break;
            case 'confirmed':
                $confirmed_count++;
                break;
            case 'completed':
                $completed_count++;
                break;
            case 'cancelled':
                $cancelled_count++;
                break;
        }
    }
    
    // Free the result set for reuse
    $bookings_result->free();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Dokter - Konsultasi Online</title>
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #06b6d4;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
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
            min-height: 100vh;
        }
        
        .container {
            width: 95%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px 0;
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
        
        .header-left h1 {
            font-size: 1.8rem;
            margin-bottom: 5px;
        }
        
        .header-right {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-block;
            cursor: pointer;
            border: none;
        }
        
        .btn-light {
            background-color: white;
            color: var(--primary-color);
        }
        
        .btn-light:hover {
            background-color: #f1f1f1;
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #dc2626;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 30px;
        }
        
        .profile-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            height: fit-content;
        }
        
        .profile-header {
            padding: 20px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            text-align: center;
        }
        
        .profile-img-container {
            width: 120px;
            height: 120px;
            margin: 0 auto 15px;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .profile-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .profile-name {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        
        .profile-specialty {
            opacity: 0.9;
            font-size: 1rem;
        }
        
        .profile-body {
            padding: 20px;
        }
        
        .info-item {
            margin-bottom: 15px;
        }
        
        .info-label {
            font-weight: 600;
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 5px;
            display: block;
        }
        
        .info-value {
            color: var(--dark-color);
        }
        
        .dashboard-content {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 10px 0;
        }
        
        .stat-label {
            color: #64748b;
            font-size: 1rem;
        }
        
        .stat-pending .stat-value { color: var(--warning-color); }
        .stat-confirmed .stat-value { color: var(--primary-color); }
        .stat-completed .stat-value { color: var(--success-color); }
        .stat-cancelled .stat-value { color: var(--danger-color); }
        
        .panel {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        
        .panel-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .panel-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .panel-body {
            padding: 0;
        }
        
        .filter-bar {
            display: flex;
            padding: 15px 20px;
            background-color: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .filter-select {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            font-size: 0.9rem;
            background-color: white;
            min-width: 150px;
        }
        
        .consultation-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .consultation-table th, 
        .consultation-table td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .consultation-table th {
            background-color: #f8fafc;
            font-weight: 600;
            color: #64748b;
            font-size: 0.9rem;
        }
        
        .consultation-table tbody tr:hover {
            background-color: #f9fafb;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-align: center;
        }
        
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .status-confirmed {
            background-color: #dbeafe;
            color: #1e40af;
        }
        
        .status-completed {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .status-cancelled {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-sm {
            padding: 6px 10px;
            font-size: 0.8rem;
            border-radius: 4px;
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
        
        .btn-warning {
            background-color: var(--warning-color);
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #d97706;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            padding: 20px;
            border-top: 1px solid #e5e7eb;
        }
        
        .page-btn {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            background-color: white;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .page-btn.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .page-btn:hover:not(.active) {
            background-color: #f1f5f9;
        }
        
        .btn-edit-profile {
            display: block;
            width: 100%;
            text-align: center;
            margin-top: 15px;
            padding: 10px;
            background-color: #f1f5f9;
            color: var(--primary-color);
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .btn-edit-profile:hover {
            background-color: #e2e8f0;
        }
        
        @media (max-width: 1024px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .profile-card {
                max-width: 600px;
                margin: 0 auto;
            }
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .header-content {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .consultation-table {
                display: block;
                overflow-x: auto;
            }
        }
        
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-bar {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="header-left">
                    <h1>Dashboard Dokter</h1>
                    <p>Selamat datang, Dr. <?php echo htmlspecialchars($doctor['name']); ?></p>
                </div>
                <div class="header-right">
                    <a href="change_password.php" class="btn btn-light">Ubah Password</a>
                    <a href="logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="dashboard-grid">
            <!-- Profile Section -->
            <aside class="profile-card">
                <div class="profile-header">
                    <div class="profile-img-container">
                        <img src="<?php echo htmlspecialchars($doctor['photo'] ?: 'https://via.placeholder.com/150?text=Doctor'); ?>" alt="Profile" class="profile-img">
                    </div>
                    <h2 class="profile-name">Dr. <?php echo htmlspecialchars($doctor['name']); ?></h2>
                    <p class="profile-specialty"><?php echo htmlspecialchars($doctor['specialization']); ?></p>
                </div>
                <div class="profile-body">
                    <div class="info-item">
                        <span class="info-label">Email</span>
                        <span class="info-value"><?php echo htmlspecialchars($doctor['email']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">No. Telepon</span>
                        <span class="info-value"><?php echo htmlspecialchars($doctor['phone']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Rumah Sakit/Klinik</span>
                        <span class="info-value"><?php echo htmlspecialchars($doctor['hospital']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Biaya Konsultasi</span>
                        <span class="info-value">Rp<?php echo number_format($doctor['consultation_fee'], 0, ',', '.'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Hari Praktik</span>
                        <span class="info-value"><?php echo htmlspecialchars($doctor['available_days']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Bahasa</span>
                        <span class="info-value"><?php echo htmlspecialchars($doctor['languages'] ?: 'Indonesia'); ?></span>
                    </div>
                    
                    <a href="edit_profile.php" class="btn-edit-profile">Edit Profil</a>
                </div>
            </aside>
            
            <!-- Main Content Section -->
            <main class="dashboard-content">
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card stat-pending">
                        <span class="stat-value"><?php echo $pending_count; ?></span>
                        <span class="stat-label">Menunggu Konfirmasi</span>
                    </div>
                    <div class="stat-card stat-confirmed">
                        <span class="stat-value"><?php echo $confirmed_count; ?></span>
                        <span class="stat-label">Terkonfirmasi</span>
                    </div>
                    <div class="stat-card stat-completed">
                        <span class="stat-value"><?php echo $completed_count; ?></span>
                        <span class="stat-label">Selesai</span>
                    </div>
                    <div class="stat-card stat-cancelled">
                        <span class="stat-value"><?php echo $cancelled_count; ?></span>
                        <span class="stat-label">Dibatalkan</span>
                    </div>
                </div>
                
                <!-- Consultations Panel -->
                <div class="panel">
                    <div class="panel-header">
                        <h2 class="panel-title">Daftar Pemesanan Konsultasi</h2>
                    </div>
                    
                    <div class="filter-bar">
                        <select id="statusFilter" class="filter-select">
                            <option value="all">Semua Status</option>
                            <option value="pending">Menunggu Konfirmasi</option>
                            <option value="confirmed">Terkonfirmasi</option>
                            <option value="completed">Selesai</option>
                            <option value="cancelled">Dibatalkan</option>
                        </select>
                        
                        <select id="dateFilter" class="filter-select">
                            <option value="all">Semua Tanggal</option>
                            <option value="today">Hari Ini</option>
                            <option value="tomorrow">Besok</option>
                            <option value="week">Minggu Ini</option>
                            <option value="month">Bulan Ini</option>
                        </select>
                        
                        <input type="text" id="searchInput" placeholder="Cari nama pasien..." class="filter-select">
                    </div>
                    
                    <div class="panel-body">
                        <table class="consultation-table">
                            <thead>
                                <tr>
                                    <th>Tanggal & Waktu</th>
                                    <th>Nama Pasien</th>
                                    <th>Keluhan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($all_bookings) && !empty($all_bookings)): ?>
                                    <?php foreach ($all_bookings as $booking): ?>
                                        <tr>
                                            <td>
                                                <?php 
                                                $date = date('d/m/Y', strtotime($booking['consultation_date']));
                                                $time = $booking['consultation_time'];
                                                echo "$date - $time"; 
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($booking['patient_name']); ?></td>
                                            <td><?php echo htmlspecialchars(substr($booking['symptoms'], 0, 50) . (strlen($booking['symptoms']) > 50 ? '...' : '')); ?></td>
                                            <td>
                                                <?php 
                                                $status_class = '';
                                                $status_text = '';
                                                
                                                switch ($booking['status']) {
                                                    case 'pending':
                                                        $status_class = 'status-pending';
                                                        $status_text = 'Menunggu Konfirmasi';
                                                        break;
                                                    case 'confirmed':
                                                        $status_class = 'status-confirmed';
                                                        $status_text = 'Terkonfirmasi';
                                                        break;
                                                    case 'completed':
                                                        $status_class = 'status-completed';
                                                        $status_text = 'Selesai';
                                                        break;
                                                    case 'cancelled':
                                                        $status_class = 'status-cancelled';
                                                        $status_text = 'Dibatalkan';
                                                        break;
                                                }
                                                ?>
                                                <span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="view_consultation.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-primary">Detail</a>
                                                    
                                                    <?php if ($booking['status'] == 'pending'): ?>
                                                        <a href="update_consultation.php?id=<?php echo $booking['id']; ?>&action=confirm" class="btn btn-sm btn-success">Konfirmasi</a>
                                                        <a href="update_consultation.php?id=<?php echo $booking['id']; ?>&action=cancel" class="btn btn-sm btn-danger">Tolak</a>
                                                    <?php elseif ($booking['status'] == 'confirmed'): ?>
                                                        <a href="update_consultation.php?id=<?php echo $booking['id']; ?>&action=complete" class="btn btn-sm btn-success">Selesai</a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 30px;">Belum ada pemesanan konsultasi</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        
                        <div class="pagination">
                            <button class="page-btn active">1</button>
                            <button class="page-btn">2</button>
                            <button class="page-btn">3</button>
                            <button class="page-btn">»</button>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusFilter = document.getElementById('statusFilter');
            const dateFilter = document.getElementById('dateFilter');
            const searchInput = document.getElementById('searchInput');
            const tableRows = document.querySelectorAll('.consultation-table tbody tr');
            
            // Function to filter table rows
            function filterTable() {
                const statusValue = statusFilter.value;
                const dateValue = dateFilter.value;
                const searchValue = searchInput.value.toLowerCase();
                
                // Get current date info for date filtering
                const now = new Date();
                const today = new Date(now.getFullYear(), now.getMonth(), now.getDate()).getTime();
                const tomorrow = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1).getTime();
                const weekEnd = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 7).getTime();
                const monthEnd = new Date(now.getFullYear(), now.getMonth() + 1, now.getDate()).getTime();
                
                tableRows.forEach(row => {
                    // Get cell values
                    const statusCell = row.querySelector('td:nth-child(4)');
                    const statusText = statusCell ? statusCell.textContent.trim() : '';
                    const patientCell = row.querySelector('td:nth-child(2)');
                    const patientText = patientCell ? patientCell.textContent.toLowerCase() : '';
                    const dateCell = row.querySelector('td:nth-child(1)');
                    const dateText = dateCell ? dateCell.textContent.trim() : '';
                    
                    // Status filtering
                    let showByStatus = statusValue === 'all';
                    if (statusValue === 'pending' && statusText === 'Menunggu Konfirmasi') showByStatus = true;
                    if (statusValue === 'confirmed' && statusText === 'Terkonfirmasi') showByStatus = true;
                    if (statusValue === 'completed' && statusText === 'Selesai') showByStatus = true;
                    if (statusValue === 'cancelled' && statusText === 'Dibatalkan') showByStatus = true;
                    
                    // Date filtering (simplified for demonstration)
                    let showByDate = dateValue === 'all';
                    
                    // Very basic date extraction - in real app, use proper date parsing
                    if (dateText && dateValue !== 'all') {
                        const dateMatch = dateText.match(/(\d{2})\/(\d{2})\/(\d{4})/);
                        if (dateMatch) {
                            const [_, day, month, year] = dateMatch;
                            const rowDate = new Date(year, month - 1, day).getTime();
                            
                            if (dateValue === 'today' && rowDate === today) showByDate = true;
                            if (dateValue === 'tomorrow' && rowDate === tomorrow) showByDate = true;
                            if (dateValue === 'week' && rowDate >= today && rowDate <= weekEnd) showByDate = true;
                            if (dateValue === 'month' && rowDate >= today && rowDate <= monthEnd) showByDate = true;
                        }
                    }
                    
                    const showBySearch = !searchValue || patientText.includes(searchValue);
                    
                    if (showByStatus && showByDate && showBySearch) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
            
            // Add event listeners
            statusFilter.addEventListener('change', filterTable);
            dateFilter.addEventListener('change', filterTable);
            searchInput.addEventListener('input', filterTable);
        });
    </script>
</body>
</html>