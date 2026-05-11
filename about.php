<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$logged_in = isLoggedIn();
<<<<<<< HEAD
=======
$open_modal = $open_modal ?? 'login';
$modal_error = $modal_error ?? '';
$modal_success = $modal_success ?? '';
>>>>>>> 6590370dbfe86524f3080b27008e455e1968401b
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Us — ArmiePrints</title>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,400;0,700;0,800;0,900;1,800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="css/home.css">
  <link rel="stylesheet" href="css/about.css">
</head>
<body>

<!-- ══ NAVBAR ══════════════════════════════════════════════════════════ -->
<nav class="navbar">
  <div class="nav-inner">
    <a href="index.php" class="nav-logo">
      <img src="images/logo.png" alt="ArmiePrints"
           onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
      <span class="logo-fallback">Armie<span>Prints</span></span>
    </a>

    <ul class="nav-links">
      <li><a href="index.php">Home</a></li>
      <li><a href="products.php">Products</a></li>
      <li><a href="customorder.php">Custom Order</a></li>
      <li><a href="tracking.php">Tracking</a></li>
      <li><a href="about.php" class="active">About</a></li>
    </ul>

    <div class="nav-actions">
      <a href="cart.php" class="cart-btn" aria-label="Cart">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
        </svg>
        <?php
          $cart_count = 0;
          if ($logged_in) {
              $uid = $_SESSION['user_id'];
              $cartQuery = getDB()->prepare("
                  SELECT SUM(quantity) as total
                  FROM cart
                  WHERE user_id = ?
              ");
              $cartQuery->bind_param("i", $uid);
              $cartQuery->execute();

              $cart_count = $cartQuery->get_result()->fetch_assoc()['total'] ?? 0;
          }
        ?>
        <span class="cart-count"><?= $cart_count ?></span>
      </a>
      <?php if ($logged_in): ?>
        <a href="profile.php" class="btn-signed-in">
          Hello, <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>!
        </a>
        <a href="logout.php" class="btn-logout-nav">Logout</a>
      <?php else: ?>
        <button class="btn-signin" id="openModal">Sign in / Sign Up</button>
      <?php endif; ?>
    </div>

    <button class="hamburger" id="hamburger" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
  </div>

  <!-- Mobile menu -->
  <div class="mobile-menu" id="mobileMenu">
    <a href="index.php">Home</a>
    <a href="products.php">Products</a>
<<<<<<< HEAD
    <a href="#">Custom Order</a>
    <a href="#">Tracking</a>
=======
    <a href="customorder.php">Custom Order</a>
    <a href="tracking.php">Tracking</a>
>>>>>>> 6590370dbfe86524f3080b27008e455e1968401b
    <a href="about.php">About</a>
    <?php if ($logged_in): ?>
      <a href="profile.php" class="btn-signed-in">
        Hello<?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>!
      </a>
      <a href="logout.php" class="btn-logout-nav">Logout</a>
    <?php else: ?>
      <button class="btn-signin" id="openModal">Sign in / Sign Up</button>
    <?php endif; ?>
  </div>


  <?php if (!$logged_in): ?>
    <div class="modal-overlay" id="authModal">
      <div class="modal-card">
        <button class="modal-close" id="closeModal">✕</button>

        <div class="modal-logo">
          <img src="images/logo.png" alt="ArmiePrints"
              onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
          <span class="logo-fallback" style="font-size:18px;">Armie<span>Prints</span></span>
        </div>

        <div class="modal-tabs">
          <button class="modal-tab <?= ($open_modal !== 'signup') ? 'active' : '' ?>" data-tab="login">Login</button>
          <button class="modal-tab <?= ($open_modal === 'signup') ? 'active' : '' ?>" data-tab="signup">Sign Up</button>
        </div>

        <?php if ($modal_error): ?>
          <div class="modal-alert modal-alert--error"><?= htmlspecialchars($modal_error) ?></div>
        <?php endif; ?>
        <?php if ($modal_success): ?>
          <div class="modal-alert modal-alert--success"><?= htmlspecialchars($modal_success) ?></div>
        <?php endif; ?>

        <div class="modal-pane <?= ($open_modal !== 'signup') ? 'active' : '' ?>" id="pane-login">
          <p class="modal-welcome">Welcome Back!</p>
          <form method="POST" action="index.php">
            <input type="hidden" name="action" value="login">
            <div class="mform-group">
              <label>Email</label>
              <input type="email" name="email" placeholder="juan@email.com" autocomplete="email" required>
            </div>
            <div class="mform-group">
              <label>Password</label>
              <div class="minput-wrap">
                <input type="password" name="password" id="loginPw" placeholder="Your password" required>
                <span class="mpw-toggle" onclick="toggleMPw('loginPw',this)">👁</span>
              </div>
            </div>
            <button type="submit" class="mbtn-primary">LOGIN</button>
          </form>
          <p class="modal-switch">No account yet? <a href="#" data-switch="signup">Sign up here</a></p>
        </div>

        <div class="modal-pane <?= ($open_modal === 'signup') ? 'active' : '' ?>" id="pane-signup">
          <p class="modal-welcome">Welcome!</p>
          <form method="POST" action="index.php">
            <input type="hidden" name="action" value="signup">
            <div class="mform-group">
              <label>Full Name</label>
              <input type="text" name="full_name" placeholder="Juan Dela Cruz" required>
            </div>
            <div class="mform-group">
              <label>Email</label>
              <input type="email" name="email" placeholder="juan@email.com" required>
            </div>
            <div class="mform-group">
              <label>Phone <span style="color:#888;font-weight:400;">(optional)</span></label>
              <input type="text" name="phone" placeholder="09xxxxxxxxx">
            </div>
            <div class="mform-group">
              <label>Password</label>
              <div class="minput-wrap">
                <input type="password" name="password" id="signupPw" placeholder="Min. 6 characters" required>
                <span class="mpw-toggle" onclick="toggleMPw('signupPw',this)">👁</span>
              </div>
            </div>
            <button type="submit" class="mbtn-primary">SIGN UP NOW</button>
          </form>
          <p class="modal-switch">Already have an account? <a href="#" data-switch="login">Login here</a></p>
        </div>

        <p class="modal-terms">
          By continuing you agree to ArmiePrints
          <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.
        </p>
      </div>
    </div>
    <?php endif; ?>
</nav>

<!-- ══ BREADCRUMB ══════════════════════════════════════════════════ -->
<div class="breadcrumb-bar">
  <div class="container">
    <a href="index.php">Home</a>
    <span class="sep">›</span>
    <span>About Us</span>
  </div>
</div>

<!-- ══ STATS STRIP ══════════════════════════════════════════════════ -->
<section class="stats-strip">
  <div class="container stats-grid">
    <div class="stat-item">
      <div class="stat-num">500+</div>
      <div class="stat-label">Happy Customers</div>
    </div>
    <div class="stat-item">
      <div class="stat-num">50+</div>
      <div class="stat-label">Unique Designs</div>
    </div>
    <div class="stat-item">
      <div class="stat-num">3+</div>
      <div class="stat-label">Years of Making</div>
    </div>
    <div class="stat-item">
      <div class="stat-num">100%</div>
      <div class="stat-label">Handcrafted</div>
    </div>
  </div>
</section>

<!-- ══ MISSION & VISION ════════════════════════════════════════════ -->
<section class="mv-section">
  <div class="container mv-grid">

    <div class="mv-card mv-card--mission">
      <i class="fa-solid fa-bullseye mv-icon"></i>
      <h2>Our Mission</h2>
      <p>
<<<<<<< HEAD
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
      </p>
      <p>
        Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident.
=======
        To transform precious memories and inspirations into high-quality, affordable keepsakes. As a family-run business, we are committed to providing exceptional personalized magnets and creative souvenirs—from cherished family photos to faith-based and anime-inspired designs—ensuring that every craft we produce brings joy and value to our customers across the Philippines.
>>>>>>> 6590370dbfe86524f3080b27008e455e1968401b
      </p>
    </div>

    <div class="mv-card mv-card--vision">
      <i class="fa-solid fa-eye mv-icon"></i>
      <h2>Our Vision</h2>
      <p>
<<<<<<< HEAD
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip.
      </p>
      <p>
        Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa.
      </p>
=======
        To be the Philippines’ leading online destination for personalized magnet crafts, recognized for our family-centered service, creative excellence, and unwavering commitment to quality. We envision ArmiePrints as the go-to shop on Shopee, Lazada, and TikTok for anyone looking to preserve memories or share meaningful gifts.
      </p>

>>>>>>> 6590370dbfe86524f3080b27008e455e1968401b
    </div>

  </div>
</section>

<!-- ══ OUR STORY ════════════════════════════════════════════════════ -->
<section class="story-section">
  <div class="container story-inner">
    <div class="story-img">
      <div class="story-img-stack">
        <div class="story-card s1"><i class="fa-solid fa-paintbrush"></i></div>
        <div class="story-card s2"><i class="fa-solid fa-magnet"></i></div>
        <div class="story-card s3"><i class="fa-solid fa-envelope"></i></div>
      </div>
    </div>
    <div class="story-text">
      <p class="about-eyebrow">How It Started</p>
      <h2 class="story-title">From a small hobby to a growing brand</h2>
      <p>
<<<<<<< HEAD
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat duis aute irure dolor.
      </p>
      <p>
        Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
      </p>
      <p>
        Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.
      </p>
=======
        ArmiePrints Printing Services is a family-owned business founded in September 2023 by a husband-and-wife team with the help of their children. Starting with one printer and a small Shopee shop, the business focused on creating affordable and high-quality personalized products. After discovering strong customer demand for ref magnets, ArmiePrints specialized in personalized photo magnets, anime collectible magnets, and Bible Verse souvenir magnets for special occasions.
      </p>
      <p>
        Through dedication, creativity, and commitment to customer satisfaction, ArmiePrints expanded to Lazada and TikTok in 2024. The business is proudly DTI, BIR, and Trustmark Registered, reflecting its professionalism and reliability. Today, ArmiePrints continues to grow as a trusted family business that creates meaningful products made with care and passion.
      </p>

>>>>>>> 6590370dbfe86524f3080b27008e455e1968401b
    </div>
  </div>
</section>

<!-- ══ VALUES ══════════════════════════════════════════════════════ -->
<section class="values-section">
  <div class="container">
    <div class="section-header">
      <h2 class="section-title">What We Stand For</h2>
      <p class="section-sub">The values that guide everything we create</p>
    </div>
    <div class="values-grid">
      <div class="value-card">
        <i class="fa-solid fa-star value-icon"></i>
        <h3>Quality First</h3>
<<<<<<< HEAD
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore.</p>
=======
        <p>We focus on delivering exceptional products and experiences with attention to every detail. Excellence and reliability guide everything we do.</p>
>>>>>>> 6590370dbfe86524f3080b27008e455e1968401b
      </div>
      <div class="value-card">
        <i class="fa-solid fa-heart value-icon"></i>
        <h3>Made with Love</h3>
<<<<<<< HEAD
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore.</p>
=======
        <p>Every project is crafted with passion, creativity, and care. We believe thoughtful design creates meaningful connections.</p>
>>>>>>> 6590370dbfe86524f3080b27008e455e1968401b
      </div>
      <div class="value-card">
        <i class="fa-solid fa-hands-holding-circle value-icon"></i>
        <h3>Community</h3>
<<<<<<< HEAD
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore.</p>
=======
        <p>We grow stronger together by supporting and empowering the people around us. Collaboration and inclusivity are at the heart of our mission.</p>
>>>>>>> 6590370dbfe86524f3080b27008e455e1968401b
      </div>
      <div class="value-card">
        <i class="fa-solid fa-leaf value-icon"></i>
        <h3>Sustainability</h3>
<<<<<<< HEAD
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore.</p>
=======
        <p>We are committed to sustainable practices in our operations, ensuring that we minimize our environmental impact while delivering high-quality products.</p>
>>>>>>> 6590370dbfe86524f3080b27008e455e1968401b
      </div>
    </div>
  </div>
</section>

<!-- ══ TEAM ════════════════════════════════════════════════════════ -->
<section class="team-section">
  <div class="container">
    <div class="section-header">
      <h2 class="section-title">Meet the Team</h2>
      <p class="section-sub">The people behind every magnet</p>
    </div>
    <div class="team-grid">
      <div class="team-card">
            <div class="team-avatar">
                <img src="images/team/member1.jpg" alt="Team Member"
                    onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
                <i class="fa-solid fa-user" style="display:none;font-size:32px;color:var(--teal);"></i>
            </div>
<<<<<<< HEAD
        <h3 class="team-name">Lorem Ipsum</h3>
        <p class="team-role">Founder & Designer</p>
        <p class="team-bio">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt.</p>
=======
        <h3 class="team-name">Allan Ramirez Empalmado</h3>
        <p class="team-role">Founder & Designer</p>
        <p class="team-bio">Leads the creative vision of Ref Magnets by designing unique and customized ATM-sized magnets that match customers’ preferences and ideas.</p>
>>>>>>> 6590370dbfe86524f3080b27008e455e1968401b
      </div>
      <div class="team-card">
        <div class="team-avatar">
            <img src="images/team/member1.jpg" alt="Team Member"
                onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
            <i class="fa-solid fa-user" style="display:none;font-size:32px;color:var(--teal);"></i>
        </div>
<<<<<<< HEAD
        <h3 class="team-name">Lorem Ipsum</h3>
        <p class="team-role">Production Lead</p>
        <p class="team-bio">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt.</p>
      </div>
      <div class="team-card">
        <div class="team-avatar">
            <img src="images/team/member1.jpg" alt="Team Member"
                onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
            <i class="fa-solid fa-user" style="display:none;font-size:32px;color:var(--teal);"></i>
        </div>
        <h3 class="team-name">Lorem Ipsum</h3>
        <p class="team-role">Customer Relations</p>
        <p class="team-bio">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt.</p>
=======
        <h3 class="team-name">Laarmi Caballes Empalmado</h3>
        <p class="team-role">Production Lead</p>
        <p class="team-bio">Oversees the production process to ensure every magnet is made with quality, consistency, and attention to detail.</p>
>>>>>>> 6590370dbfe86524f3080b27008e455e1968401b
      </div>
    </div>
  </div>
</section>

<!-- ══ CTA ════════════════════════════════════════════════════════ -->
<section class="about-cta">
  <div class="container about-cta-inner">
    <h2>Ready to Make It Yours?</h2>
    <p>Browse our collection or start a custom order today.</p>
    <div class="cta-btns">
      <a href="products.php" class="btn-shop">Shop Now →</a>
      <a href="#" class="btn-cta-outline">Custom Order</a>
    </div>
  </div>
</section>

<!-- ══ FOOTER ══════════════════════════════════════════════════════ -->
<footer class="footer">
  <div class="container footer-grid">
    <div class="footer-brand">
      <div class="footer-logo">ArmiePrints</div>
      <p>Your favorite sticker-style shop bringing cute and creativity to every magnet surface. Handcrafted with love.</p>
      <div class="footer-socials">
        <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
        <a href="#"><i class="fa-brands fa-instagram"></i></a>
        <a href="#"><i class="fa-brands fa-tiktok"></i></a>
        <a href="#"><i class="fa-brands fa-x-twitter"></i></a>
      </div>
    </div>
    <div class="footer-col"><h4>Shop</h4><ul>
      <li><a href="#">New Arrivals</a></li>
      <li><a href="#">Best Sellers</a></li>
      <li><a href="products.php">All Products</a></li>
      <li><a href="#">Sale</a></li>
    </ul></div>
    <div class="footer-col"><h4>Help</h4><ul>
      <li><a href="#">Track Order</a></li>
      <li><a href="#">Shipping Info</a></li>
      <li><a href="#">Returns</a></li>
      <li><a href="#">FAQ</a></li>
      <li><a href="#">Contact Us</a></li>
    </ul></div>
    <div class="footer-col footer-newsletter">
      <h4>Stay in the loop</h4>
      <p>Get notified with the latest items!</p>
      <div class="newsletter-form">
        <input type="email" placeholder="your@email.com">
        <button type="button">→</button>
      </div>
    </div>
  </div>
  <div class="footer-bottom">
    <p>© <?= date('Y') ?> ArmiePrints. All rights reserved.</p>
  </div>
</footer>

<script src="js/home.js"></script>
</body>
</html>