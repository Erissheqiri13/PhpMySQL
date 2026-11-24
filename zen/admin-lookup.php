<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_id'] !== 1) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo json_encode(['ok' => false, 'message' => 'Invalid Product ID.']);
    exit;
}

$allowed_categories = [
    'pc'       => 'Gaming PCs',
    'gpu'      => 'GPUs',
    'monitor'  => 'Monitors',
    'keyboard' => 'Keyboards',
    'mouse'    => 'Mice',
];

$stmt = $pdo->prepare(
    "SELECT products.*, users.username AS seller_name 
     FROM products 
     LEFT JOIN users ON products.userid = users.id 
     WHERE products.id = ?"
);
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo json_encode(['ok' => false, 'message' => 'No listing found with ID #' . $id . '.']);
    exit;
}

$product['category_label'] = $allowed_categories[$product['category']] ?? strtoupper($product['category']);

echo json_encode(['ok' => true, 'product' => $product]);