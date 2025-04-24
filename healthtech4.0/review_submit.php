<?php
require_once 'db.php';

// Set header content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login untuk memberikan review.']);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Get POST data
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$review = isset($_POST['review']) ? trim($_POST['review']) : '';

// Validate data
if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Rating harus antara 1-5.']);
    exit;
}

if (empty($review)) {
    echo json_encode(['success' => false, 'message' => 'Review tidak boleh kosong.']);
    exit;
}

// Insert review into database
try {
    $stmt = $db->prepare("INSERT INTO reviews (user_id, rating, review_text) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $rating, $review]);
    
    // Get the inserted review ID
    $reviewId = $db->lastInsertId();
    
    // Get the inserted review with user data
    $stmt = $db->prepare("
        SELECT r.*, u.name as user_name, u.profile_image
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        WHERE r.id = ?
    ");
    $stmt->execute([$reviewId]);
    $newReview = $stmt->fetch();

    // Format the review data for response
    $profileImageData = null;
    if (!empty($newReview['profile_image'])) {
        $imageData = base64_encode($newReview['profile_image']);
        $profileImageData = "data:image/jpeg;base64," . $imageData;
    }

    $reviewData = [
        'id' => $newReview['id'],
        'user_name' => $newReview['user_name'],
        'profile_image' => $profileImageData,
        'rating' => $newReview['rating'],
        'review_text' => $newReview['review_text'],
        'created_at' => date('d M Y', strtotime($newReview['created_at']))
    ];
    
    echo json_encode(['success' => true, 'message' => 'Review berhasil ditambahkan.', 'review' => $reviewData]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan. Silakan coba lagi.']);
}