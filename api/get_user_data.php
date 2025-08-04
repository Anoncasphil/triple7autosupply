<?php
// Suppress error output to prevent HTML from corrupting JSON
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering to catch any unexpected output
ob_start();

require_once __DIR__ . '/../config/database.php';

// Clear any output that might have been generated
ob_clean();

header('Content-Type: application/json');

try {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception('User ID is required');
    }
    
    // Try to get database connection
    try {
        $db = db();
    } catch (Exception $dbError) {
        throw new Exception('Database connection failed: ' . $dbError->getMessage());
    }
    
    $userId = (int)$_GET['id'];
    
    $user = $db->fetch("SELECT id, username, first_name, last_name, email, role, status FROM users WHERE id = ?", [$userId]);
    
    if (!$user) {
        throw new Exception('User not found with ID: ' . $userId);
    }
    
    echo json_encode([
        'success' => true,
        'user' => $user
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

// End output buffering
ob_end_flush();
?> 