<?php
require_once 'db.php';

// Set header content type to JSON
header('Content-Type: application/json');

// Fetch reviews from database
try {
    $stmt = $db->prepare("
        SELECT r.*, u.name as user_name, u.profile_image
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        ORDER BY r.created_at DESC
    ");
    $stmt->execute();
    $reviews = $stmt->fetchAll();
    
    // Format the reviews data
    $formattedReviews = [];
    foreach ($reviews as $review) {
        $profileImageData = null;
        if (!empty($review['profile_image'])) {
            $imageData = base64_encode($review['profile_image']);
            $profileImageData = "data:image/jpeg;base64," . $imageData;
        }
        
        $formattedReviews[] = [
            'id' => $review['id'],
            'user_name' => $review['user_name'],
            'profile_image' => $profileImageData,
            'rating' => $review['rating'],
            'review_text' => $review['review_text'],
            'created_at' => date('d M Y', strtotime($review['created_at']))
        ];
    }
    
    echo json_encode(['success' => true, 'reviews' => $formattedReviews]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat mengambil data review.']);
}