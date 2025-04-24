<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database connection
include '../doctor_page/connect.php';

// Function to sanitize input data
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $specialization = ($_POST['specialization'] == 'other') ? sanitize($_POST['other_specialization']) : sanitize($_POST['specialization']);
    $hospital = sanitize($_POST['hospital']);
    $str_number = sanitize($_POST['str_number']);
    $bio = sanitize($_POST['bio']);
    $education = sanitize($_POST['education']);
    $experience = isset($_POST['experience']) ? sanitize($_POST['experience']) : null;
    $awards = isset($_POST['awards']) ? sanitize($_POST['awards']) : null;
    $consultation_fee = (float)$_POST['consultation_fee'];
    $available_days = sanitize($_POST['available_days']);
    $languages = isset($_POST['languages']) ? sanitize($_POST['languages']) : null;
    
    // Check if email already exists in applications or doctors table
    $email_check = $db->prepare("SELECT id FROM doctor_applications WHERE email = ? UNION SELECT id FROM doctors WHERE email = ?");
    $email_check->bind_param("ss", $email, $email);
    $email_check->execute();
    $result = $email_check->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['message'] = "Email sudah terdaftar. Gunakan email lain atau hubungi administrator.";
        $_SESSION['message_type'] = "error";
        header("Location: doctor_register.php");
        exit();
    }
    
    // Handle file uploads
    $target_dir = "../uploads/doctors/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    // Photo upload
    $photo_path = "";
    if(isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0) {
        $file_name = time() . '_' . basename($_FILES["photo"]["name"]);
        $target_file = $target_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Check file size (max 2MB)
        if ($_FILES["photo"]["size"] > 2 * 1024 * 1024) {
            $_SESSION['message'] = "Ukuran foto terlalu besar. Maksimal 2MB.";
            $_SESSION['message_type'] = "error";
            header("Location: doctor_register.php");
            exit();
        }
        
        // Check file type
        if($file_type != "jpg" && $file_type != "png" && $file_type != "jpeg") {
            $_SESSION['message'] = "Hanya file JPG, JPEG, PNG yang diperbolehkan untuk foto.";
            $_SESSION['message_type'] = "error";
            header("Location: doctor_register.php");
            exit();
        }
        
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            $photo_path = "uploads/doctors/" . $file_name;
        } else {
            $_SESSION['message'] = "Gagal mengunggah foto. Silakan coba lagi.";
            $_SESSION['message_type'] = "error";
            header("Location: doctor_register.php");
            exit();
        }
    } else {
        $_SESSION['message'] = "Foto profil wajib diunggah.";
        $_SESSION['message_type'] = "error";
        header("Location: doctor_register.php");
        exit();
    }
    
    // SIP/Practice license upload
    $license_path = "";
    if(isset($_FILES["practice_license"]) && $_FILES["practice_license"]["error"] == 0) {
        $file_name = time() . '_' . basename($_FILES["practice_license"]["name"]);
        $target_file = $target_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Check file size (max 2MB)
        if ($_FILES["practice_license"]["size"] > 2 * 1024 * 1024) {
            $_SESSION['message'] = "Ukuran file SIP terlalu besar. Maksimal 2MB.";
            $_SESSION['message_type'] = "error";
            header("Location: doctor_register.php");
            exit();
        }
        
        // Check file type
        if($file_type != "jpg" && $file_type != "png" && $file_type != "jpeg" && $file_type != "pdf") {
            $_SESSION['message'] = "Hanya file JPG, JPEG, PNG, PDF yang diperbolehkan untuk SIP.";
            $_SESSION['message_type'] = "error";
            header("Location: doctor_register.php");
            exit();
        }
        
        if (move_uploaded_file($_FILES["practice_license"]["tmp_name"], $target_file)) {
            $license_path = "uploads/documents/" . $file_name;
        } else {
            $_SESSION['message'] = "Gagal mengunggah SIP. Silakan coba lagi.";
            $_SESSION['message_type'] = "error";
            header("Location: doctor_register.php");
            exit();
        }
    } else {
        $_SESSION['message'] = "SIP wajib diunggah.";
        $_SESSION['message_type'] = "error";
        header("Location: doctor_register.php");
        exit();
    }
    
    // Insert into doctor_applications table
    $stmt = $db->prepare("INSERT INTO doctor_applications (name, email, phone, photo, specialization, 
                        hospital, str_number, practice_license, bio, education, experience, awards, 
                        consultation_fee, available_days, languages, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    
    $stmt->bind_param("ssssssssssssdss", 
                    $name, $email, $phone, $photo_path, $specialization, 
                    $hospital, $str_number, $license_path, $bio, $education, $experience, $awards, 
                    $consultation_fee, $available_days, $languages);
    
    if ($stmt->execute()) {
        $application_id = $stmt->insert_id;
        
        // Add notification for admin
        $message = "Pendaftaran dokter baru: $name ($specialization)";
        $notification_stmt = $db->prepare("INSERT INTO admin_notifications (type, message, reference_id) VALUES ('doctor_application', ?, ?)");
        $notification_stmt->bind_param("si", $message, $application_id);
        $notification_stmt->execute();
        
        $_SESSION['message'] = "Terima kasih! Pendaftaran Anda telah dikirim dan sedang dalam peninjauan. Kami akan menghubungi Anda melalui email setelah peninjauan selesai.";
        $_SESSION['message_type'] = "success";
        header("Location: doctor_register.php");
        exit();
    } else {
        $_SESSION['message'] = "Gagal mengirim pendaftaran. Error: " . $db->error;
        $_SESSION['message_type'] = "error";
        header("Location: doctor_register.php");
        exit();
    }
} else {
    // If not POST request, redirect to registration form
    header("Location: doctor_register.php");
    exit();
}
?>