<?php
// Custom Order page (static layout + basic interactions)
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Custom Order | ArmiePrints</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/customorder.css">


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

  <main class="page">
    <aside class="sidebar-card" aria-label="Steps">
      <div class="sidebar-white-pad">
        <ol class="stepper">
          <li class="step">
            <div class="dot done" aria-hidden="true">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                <path d="m6 12 4 4 8-9" stroke="#fff" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
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
              <div class="step-sub">Size and Colors</div>
            </div>
          </li>
          <li class="step">
            <div class="dot">4</div>
            <div>
              <div class="step-title">Review</div>
              <div class="step-sub">Final Check</div>
            </div>
          </li>
        </ol>
      </div>
    </aside>

    <section class="stack" aria-label="Custom Order">
      <!-- Upload -->
      <section class="card">
        <div class="card-head">
          <div class="card-icon" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
              <path d="M12 14V4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              <path d="m8 8 4-4 4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M6 20h12a4 4 0 0 0 0-8h-1a5 5 0 0 0-9.8 1.5A3.5 3.5 0 0 0 6 20Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
            </svg>
          </div>
          <div>
            <h2 class="card-title">Upload Your Design</h2>
            <p class="card-subtitle">Please upload high-resolution image (300dpi recommended) for the best print quality.<br>Supported formats: JPG, PNG, PDF</p>
          </div>
        </div>

        <div class="card-white-pad">
          <label class="drop" id="dropZone" for="designFile" tabindex="0">
            <div class="big">Drag and Drop your files here</div>
            <div class="small">or click to browse from your computer</div>
            <input id="designFile" type="file" accept=".jpg,.jpeg,.png,.pdf" />
          </label>

          <div class="benefits" aria-label="Benefits">
            <div class="benefit">
              <div class="b-ico" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                  <path d="M4 18V6l8-3 8 3v12l-8 3-8-3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                  <path d="M8 9h8M8 12h8M8 15h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
              </div>
              <div class="b-title">High Quality</div>
              <div class="b-desc">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</div>
            </div>
            <div class="benefit">
              <div class="b-ico" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                  <path d="M12 3 4 7v6c0 5 8 8 8 8s8-3 8-8V7l-8-4Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                  <path d="m9 12 2 2 4-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>
              <div class="b-title">Secure Files</div>
              <div class="b-desc">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</div>
            </div>
            <div class="benefit">
              <div class="b-ico" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                  <path d="M12 6v6l4 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M21 12a9 9 0 1 1-3.2-6.9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                  <path d="M21 3v6h-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>
              <div class="b-title">Unlimited Edits</div>
              <div class="b-desc">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</div>
            </div>
          </div>
        </div>
      </section>

      <!-- Order details -->
      <section class="card">
        <div class="card-head">
          <div class="card-icon" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
              <path d="M7 2h10v4H7V2Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
              <path d="M6 6h12v16H6V6Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
              <path d="M9 10h6M9 14h6M9 18h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </div>
          <div>
            <h2 class="card-title">Order Details</h2>
            <p class="card-subtitle">Customize your print specifications below.</p>
          </div>
        </div>

        <div class="card-white-pad">
          <div class="form-grid" role="group" aria-label="Order details fields">
            <div class="field">
              <label for="material">Material Type</label>
              <select id="material" class="select">
                <option selected>Premium Glossy</option>
                <option>Matte</option>
                <option>Vinyl</option>
              </select>
            </div>

            <div class="field">
              <label>Quantity</label>
              <div class="qty-wrap">
                <div class="qty-box" aria-label="Quantity selector">
                  <button type="button" id="qtyMinus" aria-label="Decrease quantity">-</button>
                  <input id="qty" inputmode="numeric" value="100" aria-label="Quantity" />
                  <button type="button" id="qtyPlus" aria-label="Increase quantity">+</button>
                </div>
              </div>
            </div>

            <div class="field chip-row">
              <label>Size Selection</label>
              <button class="chip" type="button">ATM Size</button>
            </div>

            <div class="field full">
              <label for="instructions">Special instructions (Optional)</label>
              <input id="instructions" class="textarea" placeholder="Any specific requirements about cutting, color matching, or packaging?" />
            </div>
          </div>
        </div>
      </section>

      <!-- Order summary -->
      <section class="card">
        <div class="card-head">
          <div class="card-icon" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
              <path d="M7 2h10v4H7V2Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
              <path d="M6 6h12v16H6V6Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
              <path d="M9 10h6M9 14h6M9 18h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </div>
          <div>
            <h2 class="card-title">Order Summary</h2>
          </div>
        </div>

        <div class="card-white-pad">
          <div class="summary">
            <div class="preview" aria-label="Design preview">
              <svg width="54" height="54" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M5 5h14v14H5V5Z" stroke="#7e7e7e" stroke-width="1.8"/>
                <path d="m7 15 3-3 3 3 2-2 3 3" stroke="#7e7e7e" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M9 10a1.3 1.3 0 1 0 0-2.6A1.3 1.3 0 0 0 9 10Z" fill="#7e7e7e"/>
              </svg>
            </div>

            <div class="breakdown" aria-label="Cost breakdown">
              <h4>Cost Breakdown</h4>
              <div class="row"><span>Base Price:</span><span>P 0.00</span></div>
              <div class="row"><span>Material: Premium Glossy</span><span>P 0.00</span></div>
              <div class="row"><span>Size: ATM Size</span><span>P 0.00</span></div>
              <div class="total row"><span>Total Estimate</span><span class="val">P 0.00</span></div>
            </div>
          </div>

          <a href="#" class="btn-checkout">Proceed to Checkout</a>
        </div>
      </section>
    </section>
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