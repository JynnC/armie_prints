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
$user_id = $_SESSION['user_id'];
$logged_in = true;

$cart_count = 0;
$cartQuery = $db->prepare("
    SELECT COALESCE(SUM(quantity), 0) AS total
    FROM cart
    WHERE user_id = ?
");
$cartQuery->bind_param("i", $user_id);
$cartQuery->execute();
$cart_count = $cartQuery->get_result()->fetch_assoc()['total'] ?? 0;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['description'] ?? '');
    $quantity = (int) ($_POST['quantity'] ?? 1);

    if ($quantity < 1) {
        $quantity = 1;
    }

    $reference_image = null;

    if (!empty($_FILES['reference_image']['name'])) {
        $upload_dir = 'uploads/custom_orders/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = time() . '_' . basename($_FILES['reference_image']['name']);
        $target_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['reference_image']['tmp_name'], $target_path)) {
            $reference_image = $target_path;
        }
    }

    if ($description === '') {
        $error = 'Please add your custom order instructions.';
    } else {
        $stmt = $db->prepare("
            INSERT INTO custom_orders
            (user_id, description, reference_image, quantity, status)
            VALUES (?, ?, ?, ?, 'pending')
        ");

        $stmt->bind_param(
            "issi",
            $user_id,
            $description,
            $reference_image,
            $quantity
        );

        if ($stmt->execute()) {
            header("Location: custom-order-success.php");
            exit;
        } else {
            $error = 'Something went wrong. Please try again.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Custom Order | ArmiePrints</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="css/home.css">
  <link rel="stylesheet" href="css/customorder.css">
</head>
<body>

<nav class="navbar">
  <div class="nav-inner">
    <a href="index.php" class="nav-logo" aria-label="ArmiePrints Home">
      <img src="images/logo.png" alt="ArmiePrints"
           onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
      <span class="logo-fallback">Armie<span>Prints</span></span>
    </a>

    <ul class="nav-links">
      <li><a href="index.php">Home</a></li>
      <li><a href="products.php">Products</a></li>
      <li><a class="active" href="customorder.php">Custom Order</a></li>
      <li><a href="tracking.php">Tracking</a></li>
      <li><a href="about.php">About</a></li>
    </ul>

    <div class="nav-actions">
      <a href="cart.php" class="cart-btn" aria-label="Cart">
        🛒
        <span class="cart-count"><?= $cart_count ?></span>
      </a>

      <a href="profile.php" class="btn-signed-in">
        Hello, <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>!
      </a>

      <a href="logout.php" class="btn-logout-nav">Logout</a>
    </div>

    <button class="hamburger" id="hamburger" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>

<form method="POST" enctype="multipart/form-data">

  <main class="page">

    <aside class="sidebar-card stat-card">
      <div class="sidebar-white-pad">
        <ol class="stepper">
          <li class="step">
            <div class="dot done">✓</div>
            <div>
              <div class="step-title">Start</div>
              <div class="step-sub">Select Type</div>
            </div>
          </li>
          <li class="step">
            <div class="dot active">2</div>
            <div>
              <div class="step-title">Upload</div>
              <div class="step-sub">Your Design</div>
            </div>
          </li>
          <li class="step">
            <div class="dot">3</div>
            <div>
              <div class="step-title">Details</div>
              <div class="step-sub">Size and Quantity</div>
            </div>
          </li>
          <li class="step">
            <div class="dot">4</div>
            <div>
              <div class="step-title">Submit</div>
              <div class="step-sub">For Review</div>
            </div>
          </li>
        </ol>
      </div>
    </aside>

    <section class="stack">

      <?php if ($error): ?>
        <div class="card stat-card">
          <div class="card-white-pad" style="color:#d93030;font-weight:700;">
            <?= htmlspecialchars($error) ?>
          </div>
        </div>
      <?php endif; ?>

      <section class="card stat-card">
        <div class="card-head">
          <div class="card-icon">⬆</div>
          <div>
            <h2 class="card-title">Upload Your Design</h2>
            <p class="card-subtitle">
              Upload a reference image for your custom magnet design.
            </p>
          </div>
        </div>

        <div class="card-white-pad">
          <label class="drop" id="dropZone" for="designFile" tabindex="0">
            <div class="big">Drag and Drop your file here</div>
            <div class="small">or click to browse from your computer</div>
            <input id="designFile" name="reference_image" type="file" accept=".jpg,.jpeg,.png,.pdf">
          </label>
        </div>
      </section>

      <section class="card stat-card">
        <div class="card-head">
          <div class="card-icon">📋</div>
          <div>
            <h2 class="card-title">Order Details</h2>
            <p class="card-subtitle">Tell us how you want your custom order.</p>
          </div>
        </div>

        <div class="card-white-pad">
          <div class="form-grid">

            <div class="field">
              <label>Quantity</label>
              <div class="qty-wrap">
                <div class="qty-box">
                  <button type="button" id="qtyMinus">-</button>
                  <input id="qty" name="quantity" inputmode="numeric" value="1">
                  <button type="button" id="qtyPlus">+</button>
                </div>
              </div>
            </div>

            <div class="field chip-row">
              <label>Size Selection</label>
              <button class="chip" type="button">ATM Size</button>
            </div>

            <div class="field full">
              <label for="instructions">Special Instructions</label>
              <textarea
                id="instructions"
                name="description"
                class="textarea"
                placeholder="Describe your design, size, color, layout, character, text, or any specific request..."
                required
              ></textarea>
            </div>

          </div>
        </div>
      </section>

      <section class="card stat-card">
        <div class="card-head">
          <div class="card-icon">🧾</div>
          <div>
            <h2 class="card-title">Order Summary</h2>
          </div>
        </div>

        <div class="card-white-pad">
          <div class="summary">
            <div class="preview">🧲</div>

            <div class="breakdown">
              <h4>Custom Order Request</h4>
              <div class="row"><span>Status:</span><span>Pending Review</span></div>
              <div class="row"><span>Size:</span><span>ATM Size</span></div>
              <div class="row"><span>Price:</span><span>To be confirmed</span></div>
              <div class="total row">
                <span>Total Estimate</span>
                <span class="val">Pending</span>
              </div>
            </div>
          </div>

          <button type="submit" class="btn-checkout">
            Submit Custom Order
          </button>
        </div>
      </section>

    </section>
  </main>

</form>

<footer class="footer">
  <div class="container footer-grid">
    <div class="footer-brand">
      <div class="footer-logo">ArmiePrints</div>
      <p>Your favorite sticker-style shop bringing cute and creativity to every magnet surface.</p>
    </div>
  </div>
</footer>

<script src="js/home.js"></script>

<script>
const qty = document.getElementById('qty');
const minus = document.getElementById('qtyMinus');
const plus = document.getElementById('qtyPlus');

minus?.addEventListener('click', () => {
  let value = parseInt(qty.value || 1);
  if (value > 1) qty.value = value - 1;
});

plus?.addEventListener('click', () => {
  let value = parseInt(qty.value || 1);
  qty.value = value + 1;
});
</script>

</body>
</html>