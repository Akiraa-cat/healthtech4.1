<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Handle CORS
header('Access-Control-Allow-Methods: GET');

// Database configuration
$host = 'localhost';
$username = 'root'; // Replace with your DB username
$password = '12345678'; // Replace with your DB password
$database = 'doctor_db';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

// Get doctor ID from request
$doctor_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($doctor_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid doctor ID']);
    exit;
}

// Prepare and execute query
$stmt = $conn->prepare("SELECT 
    id, name, specialization, hospital, email, phone, 
    photo, bio, education, experience, awards, 
    consultation_fee, available_days, languages 
    FROM doctors WHERE id = ?");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Doctor not found']);
    exit;
}

// Fetch doctor data
$doctor = $result->fetch_assoc();

// Format response data
$response = [
    'id' => $doctor['id'],
    'name' => $doctor['name'],
    'specialization' => $doctor['specialization'],
    'hospital' => $doctor['hospital'],
    'email' => $doctor['email'],
    'phone' => $doctor['phone'],
    'photo' => $doctor['photo'] ?? '',
    'bio' => $doctor['bio'],
    'education' => $doctor['education'],
    'experience' => $doctor['experience'],
    'awards' => $doctor['awards'],
    'consultation_fee' => (float)$doctor['consultation_fee'],
    'available_days' => $doctor['available_days'],
    'languages' => $doctor['languages']
];

// Close connections
$stmt->close();
$conn->close();

// Return JSON response
echo json_encode($response);
?>