<?php
require_once '../config/database.php';
header('Content-Type: application/json');
try {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception('Product ID is required');
    }
    $db = db();
    $productId = (int)$_GET['id'];
    $product = $db->fetch("SELECT id, name, category, car_model, `condition`, price, image, availability, description FROM products WHERE id = ?", [$productId]);
    if (!$product) {
        throw new Exception('Product not found');
    }
    echo json_encode(['success' => true, 'product' => $product]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?> 