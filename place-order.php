<?php
require_once 'includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$db = getDB();
$user_id = (int) $_SESSION['user_id'];

$address = trim($_POST['address'] ?? '');
$city = trim($_POST['city'] ?? '');
$postal_code = trim($_POST['postal_code'] ?? '');

$shipping_address = $address . ', ' . $city . ', ' . $postal_code;

$total_amount = 0;
$items = [];
$is_buy_now = isset($_POST['buy_now']) && $_POST['buy_now'] == '1';

if ($is_buy_now) {

    $product_id = (int) ($_POST['product_id'] ?? 0);
    $qty = (int) ($_POST['qty'] ?? 1);

    if ($qty < 1) {
        $qty = 1;
    }

    $stmt = $db->prepare("
        SELECT id AS product_id, price
        FROM products
        WHERE id = ? AND is_active = 1
    ");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();

    $product = $stmt->get_result()->fetch_assoc();

    if (!$product) {
        header("Location: products.php");
        exit;
    }

    $total_amount = $product['price'] * $qty;

    $items[] = [
        'product_id' => $product['product_id'],
        'quantity' => $qty,
        'unit_price' => $product['price']
    ];

} else {

    $stmt = $db->prepare("
        SELECT 
            cart.quantity,
            products.id AS product_id,
            products.price
        FROM cart
        JOIN products ON cart.product_id = products.id
        WHERE cart.user_id = ?
    ");

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $cart_items = $stmt->get_result();

    if ($cart_items->num_rows === 0) {
        header("Location: cart.php");
        exit;
    }

    while ($item = $cart_items->fetch_assoc()) {
        $total_amount += $item['price'] * $item['quantity'];

        $items[] = [
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['price']
        ];
    }
}

$order = $db->prepare("
    INSERT INTO orders
    (user_id, total_amount, shipping_address)
    VALUES (?, ?, ?)
");

$order->bind_param(
    "ids",
    $user_id,
    $total_amount,
    $shipping_address
);

$order->execute();

$order_id = $db->insert_id;

$item_stmt = $db->prepare("
    INSERT INTO order_items
    (order_id, product_id, quantity, unit_price)
    VALUES (?, ?, ?, ?)
");

foreach ($items as $item) {
    $item_stmt->bind_param(
        "iiid",
        $order_id,
        $item['product_id'],
        $item['quantity'],
        $item['unit_price']
    );

    $item_stmt->execute();
}

/* Clear cart ONLY if checkout came from cart */
if (!$is_buy_now) {
    $clear = $db->prepare("DELETE FROM cart WHERE user_id = ?");
    $clear->bind_param("i", $user_id);
    $clear->execute();
}

header("Location: checkout.php?success=1");
exit;