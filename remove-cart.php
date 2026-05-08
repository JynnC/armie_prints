<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) exit;

$db = getDB();
$user_id = $_SESSION['user_id'];
$cart_id = intval($_POST['cart_id']);

$stmt = $db->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $cart_id, $user_id);
$stmt->execute();