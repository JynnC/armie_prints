<?php
// Tracking page (static mock to match design screenshot)
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Tracking | ArmiePrints</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,700;0,800;0,900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/tracking.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" referrerpolicy="no-referrer" />
</head>
<body>

  <!-- ══ NAVBAR (matches css/customorder.css selectors) ═══════════════ -->
  <nav class="navbar">
    <div class="nav-inner">
      <a href="index.php" class="nav-logo" aria-label="ArmiePrints Home">
        <img src="images/logo.png" alt="ArmiePrints"
             onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
        <span class="logo-fallback">Armie<span>Prints</span></span>
      </a>

      <ul class="nav-links" aria-label="Primary">
        <li><a href="index.php">Home</a></li>
        <li><a href="products.php">Products</a></li>
        <li><a class="active" href="customorder.php">Custom Order</a></li>
        <li><a href="tracking.php">Tracking</a></li>
        <li><a href="about.php">About</a></li>
      </ul>

      <div class="nav-actions" aria-label="Actions">
        <a href="#" class="cart-btn" aria-label="Cart">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
          </svg>
          <span class="cart-count">0</span>
        </a>
        <button class="btn-signin" type="button">Sign in / Sign Up</button>
      </div>

      <button class="hamburger" id="hamburger" aria-label="Menu">
        <span></span><span></span><span></span>
      </button>
    </div>

    <div class="mobile-menu" id="mobileMenu" aria-label="Mobile menu">
      <a href="index.php">Home</a>
      <a href="products.php">Products</a>
      <a href="customorder.php">Custom Order</a>
      <a href="tracking.php">Tracking</a>
      <a href="about.php">About</a>
    </div>
  </nav>

<!-- ══ TRACKING ═════════════════════════════════════════════════════ -->
<main class="tracking-wrap">
  <div class="container">

    <section class="track-card stat-card">
      <div class="track-top">
        <div class="track-meta">
          <div class="order-id">ORDER ID. 12389DESAGH3DTT</div>
        </div>
        <div class="order-badge">ORDER COMPLETED</div>
      </div>

      <div class="progress">
        <div class="progress-line" aria-hidden="true"></div>

        <div class="pstep done">
          <div class="picon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M7 2h10v4H7V2Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
              <path d="M6 6h12v16H6V6Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
              <path d="M9 10h6M9 14h6M9 18h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span class="check">✓</span>
          </div>
          <div class="plabel">Order Placed</div>
          <div class="pdate">01/23/2026 21:35</div>
        </div>

        <div class="pstep done">
          <div class="picon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M12 3 4 7v6c0 5 8 8 8 8s8-3 8-8V7l-8-4Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
              <path d="m9 12 2 2 4-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span class="check">✓</span>
          </div>
          <div class="plabel">Payment info<br>Confirmed</div>
          <div class="pdate">01/23/2026 21:46</div>
        </div>

        <div class="pstep done">
          <div class="picon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M3 7h13v10H3V7Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
              <path d="M16 10h5l-2 3h-3v-3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
              <path d="M7 20a1.2 1.2 0 1 0 0-2.4A1.2 1.2 0 0 0 7 20Z" fill="currentColor"/>
              <path d="M17 20a1.2 1.2 0 1 0 0-2.4A1.2 1.2 0 0 0 17 20Z" fill="currentColor"/>
            </svg>
            <span class="check">✓</span>
          </div>
          <div class="plabel">Order Shipped<br>Out</div>
          <div class="pdate">01/24/2026 11:10</div>
        </div>

        <div class="pstep done">
          <div class="picon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M4 8h16v10H4V8Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
              <path d="m4 9 8 6 8-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span class="check">✓</span>
          </div>
          <div class="plabel">Order Received</div>
          <div class="pdate">01/26/2026 8:05</div>
        </div>

        <div class="pstep done">
          <div class="picon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M7 2h10v4H7V2Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
              <path d="M6 6h12v16H6V6Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
              <path d="M9 10h6M9 14h6M9 18h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span class="check">✓</span>
          </div>
          <div class="plabel">Order Completed</div>
          <div class="pdate">01/26/2026 8:05</div>
        </div>
      </div>
    </section>

    <section class="actions-row stat-card">
      <button class="btn-outline" type="button">View Shop Rating</button>
      <button class="btn-outline" type="button">Contact Seller</button>
      <button class="btn-solid" type="button">Buy Again</button>
    </section>

    <section class="delivery-card stat-card">
      <div class="delivery-grid">
        <div class="delivery-left">
          <div class="delivery-title">Delivery Address</div>
          <div class="delivery-name">John Doe</div>
          <div class="delivery-phone">(63+) 918 658 9104</div>
          <div class="delivery-addr">Hilario T. Apolinario St.,<br>Malinta, South Luzon, Laguna</div>
        </div>

        <div class="delivery-right">
          <div class="timeline">
            <div class="titem done">
              <div class="tdot">
                <span class="ticon">✓</span>
              </div>
              <div class="tdate">01/25/2026 16:00</div>
              <div class="ttext">
                <div class="tstatus">Delivered</div>
                <div>Parcel has been delivered</div>
                <a href="#" class="tlink">View Proof of Delivery</a>
              </div>
            </div>

            <div class="titem">
              <div class="tdot icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <path d="M3 7h13v10H3V7Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                  <path d="M16 10h5l-2 3h-3v-3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                </svg>
              </div>
              <div class="tdate">01/25/2026 10:51</div>
              <div class="ttext">
                <div class="tstatus">In transit</div>
                <div>Parcel is out for delivery.</div>
              </div>
            </div>

            <div class="titem">
              <div class="tdot"></div>
              <div class="tdate">01/25/2026 09:35</div>
              <div class="ttext">
                <div class="tstatus">Delivery driver has been assigned</div>
              </div>
            </div>

            <div class="titem">
              <div class="tdot"></div>
              <div class="tdate">01/25/2026 07:43</div>
              <div class="ttext">
                <div class="tstatus">Your parcel has arrived at the delivery</div>
                <div>hub : Bay Hub</div>
              </div>
            </div>

            <div class="titem">
              <div class="tdot"></div>
              <div class="tdate">01/24/2026 22:09</div>
              <div class="ttext">
                <div class="tstatus">Parcel has arrived and to be received by</div>
                <div>the delivery hub</div>
              </div>
            </div>

            <div class="titem">
              <div class="tdot icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <path d="M7 2h10v4H7V2Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                  <path d="M6 6h12v16H6V6Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                  <path d="M9 10h6M9 14h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
              </div>
              <div class="tdate">01/24/2026 08:31</div>
              <div class="ttext">
                <div class="tstatus">Preparing to ship</div>
                <div>Sender is preparing to ship your parcel</div>
              </div>
            </div>

            <div class="titem">
              <div class="tdot icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <path d="M12 2 4 6v6c0 5 8 10 8 10s8-5 8-10V6l-8-4Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                  <path d="M8 11h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
              </div>
              <div class="tdate">01/23/2026 21:35</div>
              <div class="ttext">
                <div class="tstatus">Order</div>
                <div>placed</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="reco stat-card">
      <div class="reco-title">You Might Also Like</div>
      <div class="reco-line" aria-hidden="true"></div>

      <div class="reco-grid">
        <a class="reco-card" href="#">
          <div class="reco-img"></div>
          <div class="reco-name">Premium Quality Meets Art</div>
          <div class="reco-price">P 0.00</div>
        </a>
        <a class="reco-card" href="#">
          <div class="reco-img"></div>
          <div class="reco-name">Premium Quality Meets Art</div>
          <div class="reco-price">P 0.00</div>
        </a>
        <a class="reco-card" href="#">
          <div class="reco-img"></div>
          <div class="reco-name">Premium Quality Meets Art</div>
          <div class="reco-price">P 0.00</div>
        </a>
        <a class="reco-card" href="#">
          <div class="reco-img"></div>
          <div class="reco-name">Premium Quality Meets Art</div>
          <div class="reco-price">P 0.00</div>
        </a>
      </div>
    </section>
  </div>
</main>

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

    <div class="footer-col">
      <h4>Shop</h4>
      <ul>
        <li><a href="#">New Arrivals</a></li>
        <li><a href="#">Best Sellers</a></li>
        <li><a href="#">Custom Orders</a></li>
        <li><a href="#">Sale</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <h4>Help</h4>
      <ul>
        <li><a href="#">Track Order</a></li>
        <li><a href="#">Shipping Info</a></li>
        <li><a href="#">Returns</a></li>
        <li><a href="#">FAQ</a></li>
        <li><a href="#">Contact Us</a></li>
      </ul>
    </div>

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