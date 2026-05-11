<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');

$db     = getDB();
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$uid    = $_SESSION['user_id'] ?? null;
$role   = $_SESSION['user_role'] ?? null;

if (!$uid) { echo json_encode(['status' => 'error', 'message' => 'Not logged in']); exit; }

// ── SEND MESSAGE
if ($action === 'send') {
    $message = trim($_POST['message'] ?? '');
    $user_id = ($role === 'admin') ? (int)$_POST['user_id'] : (int)$uid;

    if (empty($message) || !$user_id) {
        echo json_encode(['status' => 'error']); exit;
    }

    $stmt = $db->prepare("INSERT INTO messages (user_id, sender_role, message) VALUES (?, ?, ?)");
    $stmt->bind_param('iss', $user_id, $role, $message);
    $stmt->execute();
    echo json_encode(['status' => 'success']);
    exit;
}

// ── FETCH MESSAGES
if ($action === 'fetch') {
    $user_id = ($role === 'admin') ? (int)$_GET['user_id'] : (int)$uid;

    if (!$user_id) { echo json_encode([]); exit; }

    // Mark as read
    $mark = $db->prepare("UPDATE messages SET is_read = 1 WHERE user_id = ? AND sender_role != ?");
    $mark->bind_param('is', $user_id, $role);
    $mark->execute();

    $stmt = $db->prepare("SELECT sender_role, message, created_at FROM messages WHERE user_id = ? ORDER BY created_at ASC");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $msgs = [];
    while ($row = $result->fetch_assoc()) $msgs[] = $row;
    echo json_encode($msgs);
    exit;
}

// ── UNREAD COUNT (for badge)
if ($action === 'unread') {
    $user_id = ($role === 'admin') ? null : (int)$uid;
    if ($role === 'admin') {
        $res = $db->query("SELECT COUNT(*) AS c FROM messages WHERE sender_role = 'customer' AND is_read = 0");
    } else {
        $stmt = $db->prepare("SELECT COUNT(*) AS c FROM messages WHERE user_id = ? AND sender_role = 'admin' AND is_read = 0");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
    }
    echo json_encode(['count' => $res->fetch_assoc()['c']]);
    exit;
}

// ── FETCH USERS WITH MESSAGES (admin only)
if ($action === 'users' && $role === 'admin') {
    $res = $db->query("
        SELECT u.id, u.full_name,
               (SELECT message FROM messages WHERE user_id = u.id ORDER BY created_at DESC LIMIT 1) AS last_message,
               (SELECT COUNT(*) FROM messages WHERE user_id = u.id AND sender_role = 'customer' AND is_read = 0) AS unread
        FROM users u
        WHERE u.role = 'customer'
        AND EXISTS (SELECT 1 FROM messages WHERE user_id = u.id)
        ORDER BY (SELECT created_at FROM messages WHERE user_id = u.id ORDER BY created_at DESC LIMIT 1) DESC
    ");
    $users = [];
    while ($row = $res->fetch_assoc()) $users[] = $row;
    echo json_encode($users);
    exit;
}