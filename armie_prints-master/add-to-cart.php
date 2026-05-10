<?php
require_once 'includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'login_required']);
    exit;
}

if (!isset($_POST['product_id'])) {
    echo json_encode(['status' => 'error']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$product_id = (int) $_POST['product_id'];
$quantity = (int) ($_POST['quantity'] ?? 1);

if ($quantity < 1) {
    $quantity = 1;
}

$db = getDB();

$stmt = $db->prepare("
    INSERT INTO cart (user_id, product_id, quantity)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
");

$stmt->bind_param("iii", $user_id, $product_id, $quantity);
$stmt->execute();

$count = $db->prepare("
    SELECT COALESCE(SUM(quantity), 0) AS total 
    FROM cart 
    WHERE user_id = ?
");
$count->bind_param("i", $user_id);
$count->execute();

$total = $count->get_result()->fetch_assoc()['total'];

echo json_encode([
    'status' => 'success',
    'count' => $total
]);