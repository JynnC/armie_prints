<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) exit;

$db = getDB();
$user_id = $_SESSION['user_id'];
$cart_id = intval($_POST['cart_id']);
$action = $_POST['action'] ?? '';

$stmt = $db->prepare("SELECT quantity FROM cart WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $cart_id, $user_id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) exit;

$qty = $item['quantity'];

if ($action === 'plus') {
    $qty++;
} elseif ($action === 'minus') {
    $qty--;
}

if ($qty <= 0) {
    $delete = $db->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $delete->bind_param("ii", $cart_id, $user_id);
    $delete->execute();
} else {
    $update = $db->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
    $update->bind_param("iii", $qty, $cart_id, $user_id);
    $update->execute();
}