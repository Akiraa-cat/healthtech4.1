<?php
// process_doctor_login.php
session_start();
require_once 'connect.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = $db->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    
    // Check if the email exists in the database
    $query = "SELECT * FROM doctors WHERE email = '$email'";
    $result = $db->query($query);
    
    if ($result->num_rows == 1) {
        $doctor = $result->fetch_assoc();
        
        // Check if the doctor is approved
        if ($doctor['is_approved'] == 0) {
            $_SESSION['login_error'] = "Akun Anda masih dalam proses verifikasi. Silakan tunggu konfirmasi dari admin.";
            header("Location: doctor_login.php");
            exit();
        }
        
        // Verify password
        if (password_verify($password, $doctor['password'])) {
            // Password is correct, set session variables
            $_SESSION['doctor_id'] = $doctor['id'];
            $_SESSION['doctor_name'] = $doctor['name'];
            $_SESSION['doctor_email'] = $doctor['email'];
            
            // Check if it's the default password ("changepassword")
            if (password_verify("changepassword", $doctor['password'])) {
                // Redirect to change password page
                $_SESSION['force_password_change'] = true;
                header("Location: doctor_change_password.php");
                exit();
            }
            
            // Redirect to doctor dashboard
            header("Location: doctor_dashboard.php");
            exit();
        } else {
            // Incorrect password
            $_SESSION['login_error'] = "Email atau password salah.";
            header("Location: doctor_login.php");
            exit();
        }
    } else {
        // Email not found
        $_SESSION['login_error'] = "Email atau password salah.";
        header("Location: doctor_login.php");
        exit();
    }
} else {
    // If someone tries to access this page directly
    header("Location: doctor_login.php");
    exit();
}
?>