<?php
// process_application.php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// Database connection
include 'connect.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $application_id = isset($_POST['application_id']) ? (int)$_POST['application_id'] : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $admin_notes = isset($_POST['admin_notes']) ? $_POST['admin_notes'] : '';
    
    // Validate application ID
    if ($application_id <= 0) {
        $_SESSION['message'] = "ID aplikasi tidak valid.";
        $_SESSION['message_type'] = "error";
        header("Location: admin_notification.php");
        exit();
    }
    
    // Get application data
    $app_query = "SELECT * FROM doctor_applications WHERE id = ?";
    $app_stmt = $db->prepare($app_query);
    $app_stmt->bind_param("i", $application_id);
    $app_stmt->execute();
    $app_result = $app_stmt->get_result();
    
    if ($app_result->num_rows == 0) {
        $_SESSION['message'] = "Aplikasi tidak ditemukan.";
        $_SESSION['message_type'] = "error";
        header("Location: admin_notification.php");
        exit();
    }
    
    $application = $app_result->fetch_assoc();
    
    // Check if application is already processed
    if ($application['status'] != 'pending') {
        $_SESSION['message'] = "Aplikasi ini sudah diproses sebelumnya.";
        $_SESSION['message_type'] = "error";
        header("Location: review_application.php?id=" . $application_id);
        exit();
    }
    
    // Process action
    if ($action == 'approve' || $action == 'reject') {
        $status = $action == 'approve' ? 'approved' : 'rejected';
        
        // Start transaction
        $db->begin_transaction();
        
        try {
            // Update application status
            $update_query = "UPDATE doctor_applications SET status = ?, admin_notes = ? WHERE id = ?";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->bind_param("ssi", $status, $admin_notes, $application_id);
            $update_stmt->execute();
            
            // If approved, create a doctor account
            if ($status == 'approved') {
                // Generate a temporary password
                $temp_password = "changepassword";
                $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
                
                // Insert into doctors table
                $doctor_query = "INSERT INTO doctors (name, email, password, phone, photo, specialization, hospital, 
                                str_number, bio, education, experience, awards, consultation_fee, available_days, languages) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $doctor_stmt = $db->prepare($doctor_query);
                $doctor_stmt->bind_param("ssssssssssssdss", 
                                $application['name'], 
                                $application['email'],
                                $hashed_password,
                                $application['phone'],
                                $application['photo'],
                                $application['specialization'],
                                $application['hospital'],
                                $application['str_number'],
                                $application['bio'],
                                $application['education'],
                                $application['experience'],
                                $application['awards'],
                                $application['consultation_fee'],
                                $application['available_days'],
                                $application['languages']);
                
                $doctor_stmt->execute();
            }
            
            // Send email notification to the doctor
            $email_sent = send_email_notification($application, $status, $temp_password ?? null);
            
            // Commit transaction
            $db->commit();
            
            $_SESSION['message'] = "Aplikasi dokter telah " . ($status == 'approved' ? 'disetujui' : 'ditolak') . " dan notifikasi email telah dikirim.";
            $_SESSION['message_type'] = "success";
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $db->rollback();
            
            $_SESSION['message'] = "Error: " . $e->getMessage();
            $_SESSION['message_type'] = "error";
        }
        
        header("Location: review_application.php?id=" . $application_id);
        exit();
    } else {
        $_SESSION['message'] = "Aksi tidak valid.";
        $_SESSION['message_type'] = "error";
        header("Location: review_application.php?id=" . $application_id);
        exit();
    }
} else {
    // If not POST request, redirect to notifications
    header("Location: admin_notification.php");
    exit();
}

// Function to send email notification
function send_email_notification($application, $status, $password = null) {
    $to = $application['email'];
    $subject = "Update Status Pendaftaran Dokter - " . ($status == 'approved' ? 'Disetujui' : 'Ditolak');
    
    if ($status == 'approved') {
        $message = "
        <html>
        <head>
            <title>Pendaftaran Dokter Disetujui</title>
        </head>
        <body>
            <h2>Selamat, Pendaftaran Dokter Anda Disetujui!</h2>
            <p>Halo Dr. " . htmlspecialchars($application['name']) . ",</p>
            <p>Kami dengan senang hati memberitahukan bahwa pendaftaran Anda sebagai dokter di platform Konsultasi Dokter Online kami telah disetujui.</p>
            <p>Berikut adalah detail akun Anda:</p>
            <ul>
                <li><strong>Email:</strong> " . htmlspecialchars($application['email']) . "</li>
                <li><strong>Password Sementara:</strong> " . htmlspecialchars($password) . "</li>
            </ul>
            <p>Untuk keamanan, silakan segera ganti password Anda setelah login pertama.</p>
            <p>Anda dapat login melalui: <a href='https://yourdomain.com/doctor_login.php'>https://yourdomain.com/doctor_login.php</a></p>
            <p>Terima kasih telah bergabung dengan platform kami.</p>
            <p>Salam Hormat,<br>Tim Konsultasi Dokter Online</p>
        </body>
        </html>
        ";
    } else {
        $message = "
        <html>
        <head>
            <title>Pendaftaran Dokter Ditolak</title>
        </head>
        <body>
            <h2>Pendaftaran Dokter Anda Ditolak</h2>
            <p>Halo Dr. " . htmlspecialchars($application['name']) . ",</p>
            <p>Mohon maaf, pendaftaran Anda sebagai dokter di platform Konsultasi Dokter Online kami belum dapat disetujui pada saat ini.</p>
            " . (!empty($application['admin_notes']) ? "<p><strong>Catatan dari Admin:</strong><br>" . nl2br(htmlspecialchars($application['admin_notes'])) . "</p>" : "") . "
            <p>Jika Anda memiliki pertanyaan atau ingin informasi lebih lanjut, silakan hubungi tim kami di support@yourdomain.com.</p>
            <p>Terima kasih atas minat Anda bergabung dengan platform kami.</p>
            <p>Salam Hormat,<br>Tim Konsultasi Dokter Online</p>
        </body>
        </html>
        ";
    }
    
    // Set email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Konsultasi Dokter Online <no-reply@konsulku.sijabright.my.id>" . "\r\n";
    
    // Send email
    return mail($to, $subject, $message, $headers);
    
    // Note: If using a local server like XAMPP, email might not be sent unless you configure a mail server
    // For production, consider using libraries like PHPMailer or services like SendGrid, Mailgun, etc.
    
    // Uncomment for testing/development without sending actual emails
    // return true;
}
?>