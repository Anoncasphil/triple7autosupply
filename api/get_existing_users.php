<?php
// Include database connection
require_once '../config/database.php';

// Set JSON header
header('Content-Type: application/json');

try {
    $db = db();
    
    // Get existing usernames
    $usernames = $db->fetchAll("SELECT username FROM users");
    $usernameList = array_column($usernames, 'username');
    
    // Get existing emails
    $emails = $db->fetchAll("SELECT email FROM users");
    $emailList = array_column($emails, 'email');
    
    // Return JSON response
    echo json_encode([
        'usernames' => $usernameList,
        'emails' => $emailList
    ]);
    
} catch (Exception $e) {
    // Return error response
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to fetch existing users',
        'message' => $e->getMessage()
    ]);
}
?> 