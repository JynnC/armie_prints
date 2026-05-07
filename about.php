<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$logged_in = isLoggedIn();
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

<!-- ══ NAVBAR ══════════════════════════════════════════════════════ -->
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
      <li><a href="#">Custom Order</a></li>
      <li><a href="#">Tracking</a></li>
      <li><a href="about.php" class="active">About</a></li>
    </ul>
    <div class="nav-actions">
      <a href="#" class="cart-btn" aria-label="Cart">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
        </svg>
        <span class="cart-count">0</span>
      </a>
      <?php if ($logged_in): ?>
        <a href="index.php" class="btn-signed-in">
          Hello, <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>!
        </a>
        <a href="logout.php" class="btn-logout-nav">Logout</a>
      <?php else: ?>
        <a href="index.php" class="btn-signin">Sign in / Sign Up</a>
      <?php endif; ?>
    </div>
    <button class="hamburger" id="hamburger"><span></span><span></span><span></span></button>
  </div>
  <div class="mobile-menu" id="mobileMenu">
    <a href="index.php">Home</a>
    <a href="products.php">Products</a>
    <a href="#">Custom Order</a>
    <a href="#">Tracking</a>
    <a href="about.php">About</a>
  </div>
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
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
      </p>
      <p>
        Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident.
      </p>
    </div>

    <div class="mv-card mv-card--vision">
      <i class="fa-solid fa-eye mv-icon"></i>
      <h2>Our Vision</h2>
      <p>
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip.
      </p>
      <p>
        Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa.
      </p>
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
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat duis aute irure dolor.
      </p>
      <p>
        Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
      </p>
      <p>
        Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.
      </p>
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
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore.</p>
      </div>
      <div class="value-card">
        <i class="fa-solid fa-heart value-icon"></i>
        <h3>Made with Love</h3>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore.</p>
      </div>
      <div class="value-card">
        <i class="fa-solid fa-hands-holding-circle value-icon"></i>
        <h3>Community</h3>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore.</p>
      </div>
      <div class="value-card">
        <i class="fa-solid fa-leaf value-icon"></i>
        <h3>Sustainability</h3>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore.</p>
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
        <h3 class="team-name">Lorem Ipsum</h3>
        <p class="team-role">Founder & Designer</p>
        <p class="team-bio">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt.</p>
      </div>
      <div class="team-card">
        <div class="team-avatar">
            <img src="images/team/member1.jpg" alt="Team Member"
                onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
            <i class="fa-solid fa-user" style="display:none;font-size:32px;color:var(--teal);"></i>
        </div>
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