<?php
// Informasi koneksi database
$host = 'localhost';
$dbname = 'healthcare';
$username = 'root';
$password = '12345678'; // Biasanya kosong untuk XAMPP/Laragon

// Membuat koneksi PDO
try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Fungsi helper untuk session
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Inisialisasi session jika belum
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}