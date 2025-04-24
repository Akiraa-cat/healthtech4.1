<?php
session_start();

// Check if user is logged in as admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// Database connection
include 'connect.php';

// Get all doctors from database
$query = "SELECT * FROM doctors ORDER BY name ASC";
$result = $db->query($query);

// For notification messages
$message = '';
$messageType = '';

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['message_type'];
    
    // Clear the session message
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Handle doctor deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Get the doctor's photo to delete it
    $photoQuery = "SELECT photo FROM doctors WHERE id = ?";
    $stmt = $db->prepare($photoQuery);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $doctor = $result->fetch_assoc();
    
    // Delete the doctor from database
    $deleteQuery = "DELETE FROM doctors WHERE id = ?";
    $stmt = $db->prepare($deleteQuery);
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        // Delete the photo file if it exists
        if (!empty($doctor['photo']) && file_exists($doctor['photo'])) {
            unlink($doctor['photo']);
        }
        
        $_SESSION['message'] = 'Dokter berhasil dihapus';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Gagal menghapus dokter';
        $_SESSION['message_type'] = 'error';
    }
    
    header('Location: index.php');
    exit;
}

// Get fresh doctor data after any operations
$doctors = $db->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon web" href="../assets/konsulku-nobg.png">
    <title>Admin Dashboard - Konsultasi Dokter Online</title>
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
        
        .btn-warning {
            background-color: var(--warning-color);
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #d97706;
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
        
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        
        thead {
            background-color: var(--primary-color);
            color: white;
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        th {
            font-weight: 600;
        }
        
        tbody tr:hover {
            background-color: #f8fafc;
        }
        
        .search-container {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .search-input {
            flex-grow: 1;
            max-width: 500px;
            padding: 12px 20px;
            border: 2px solid #ddd;
            border-radius: 30px;
            font-size: 1rem;
            outline: none;
            transition: all 0.3s;
        }
        
        .search-input:focus {
            border-color: var(--primary-color);
        }
        
        .action-column {
            width: 200px;
            text-align: center;
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 8px;
        }
        
        .doctor-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }
        
        .pagination a {
            display: inline-block;
            padding: 8px 16px;
            background-color: white;
            color: var(--primary-color);
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .pagination a:hover, .pagination a.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .table-responsive {
            overflow-x: auto;
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
            
            .search-container {
                flex-direction: column;
                gap: 15px;
            }
            
            .search-input {
                max-width: 100%;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            table {
                font-size: 0.85rem;
            }
            
            th, td {
                padding: 10px;
            }
        }
        
        /* Delete Confirmation Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border-radius: 10px;
            width: 400px;
            max-width: 90%;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            animation: modalFadeIn 0.3s;
        }
        
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-header {
            margin-bottom: 15px;
        }
        
        .modal-title {
            font-size: 1.5rem;
            color: var(--danger-color);
            margin: 0;
        }
        
        .modal-body {
            margin-bottom: 20px;
        }
        
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .close-modal {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close-modal:hover {
            color: #555;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-flex">
                <div>
                    <h1>Admin Dashboard</h1>
                    <p>Kelola data dokter untuk konsultasi online</p>
                </div>
                <div class="header-actions">
                    <!-- <a href="admin_notification.php" class="btn btn-success">Pendaftaran Dokter</a> -->
                    <a href="../" class="btn btn-white">Lihat Halaman Utama</a>
                    <a href="admin_logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
        </div>
    </header>   
    
    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="search-container">
            <input type="text" id="searchInput" class="search-input" placeholder="Cari dokter berdasarkan nama atau spesialisasi...">
            <a href="admin_add_doctor.php" class="btn btn-primary">Tambah Dokter Baru</a>
        </div>
        
        <div class="table-responsive">
            <table id="doctorsTable">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>Spesialisasi</th>
                        <th>Rumah Sakit</th>
                        <th>Email</th>
                        <th class="action-column">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($doctor = $doctors->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <img src="<?php echo $doctor['photo'] ?: 'images/default-doctor.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($doctor['name']); ?>" 
                                     class="doctor-image">
                            </td>
                            <td><?php echo htmlspecialchars($doctor['name']); ?></td>
                            <td><?php echo htmlspecialchars($doctor['specialization']); ?></td>
                            <td><?php echo htmlspecialchars($doctor['hospital']); ?></td>
                            <td><?php echo htmlspecialchars($doctor['email']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="admin_view_doctor.php?id=<?php echo $doctor['id']; ?>" class="btn btn-primary">Lihat</a>
                                    <a href="admin_edit_doctor.php?id=<?php echo $doctor['id']; ?>" class="btn btn-warning">Edit</a>
                                    <button class="btn btn-danger delete-btn" data-id="<?php echo $doctor['id']; ?>" 
                                           data-name="<?php echo htmlspecialchars($doctor['name']); ?>">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    
                    <?php if ($doctors->num_rows === 0): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px;">
                                Tidak ada data dokter tersedia. <a href="admin_add_doctor.php">Tambah dokter baru</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close-modal">&times;</span>
                <h2 class="modal-title">Konfirmasi Hapus</h2>
            </div>
            <div class="modal-body">
                <p>Anda yakin ingin menghapus dokter <strong id="doctorToDelete"></strong>?</p>
                <p>Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer">
                <button id="cancelDelete" class="btn btn-primary">Batal</button>
                <a id="confirmDelete" href="#" class="btn btn-danger">Hapus</a>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Search functionality
            const searchInput = document.getElementById('searchInput');
            const table = document.getElementById('doctorsTable');
            const rows = table.getElementsByTagName('tr');
            
            searchInput.addEventListener('keyup', function() {
                const term = searchInput.value.toLowerCase();
                
                for (let i = 1; i < rows.length; i++) {
                    let found = false;
                    const cells = rows[i].getElementsByTagName('td');
                    
                    for (let j = 0; j < cells.length; j++) {
                        const cellText = cells[j].textContent.toLowerCase();
                        
                        if (cellText.indexOf(term) > -1) {
                            found = true;
                            break;
                        }
                    }
                    
                    if (found) {
                        rows[i].style.display = '';
                    } else {
                        rows[i].style.display = 'none';
                    }
                }
            });
            
            // Delete confirmation modal
            const modal = document.getElementById('deleteModal');
            const deleteButtons = document.querySelectorAll('.delete-btn');
            const closeModal = document.querySelector('.close-modal');
            const cancelDelete = document.getElementById('cancelDelete');
            const confirmDelete = document.getElementById('confirmDelete');
            const doctorToDelete = document.getElementById('doctorToDelete');
            
            // Show modal when delete button is clicked
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const doctorId = this.getAttribute('data-id');
                    const doctorName = this.getAttribute('data-name');
                    
                    doctorToDelete.textContent = doctorName;
                    confirmDelete.href = `index.php?delete=${doctorId}`;
                    modal.style.display = 'block';
                });
            });
            
            // Close modal events
            closeModal.addEventListener('click', function() {
                modal.style.display = 'none';
            });
            
            cancelDelete.addEventListener('click', function() {
                modal.style.display = 'none';
            });
            
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>