// js/home.js

document.addEventListener('DOMContentLoaded', () => {

  // ── Mobile menu toggle
  const hamburger   = document.getElementById('hamburger');
  const mobileMenu  = document.getElementById('mobileMenu');

  if (hamburger && mobileMenu) {
    hamburger.addEventListener('click', () => {
      mobileMenu.classList.toggle('open');
    });
  }

  // ── Filter tabs
  const tabs    = document.querySelectorAll('.tab');
  const cards   = document.querySelectorAll('.product-card');

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');

      const filter = tab.dataset.filter;

      cards.forEach(card => {
        if (filter === 'all' || card.dataset.category === filter) {
          card.style.display = '';
          card.style.animation = 'none';
          card.offsetHeight; // reflow
          card.style.animation = '';
        } else {
          card.style.display = 'none';
        }
      });
    });
  });

  // ── Cart counter (simple local state)
  let cartCount = 0;
  const cartBadge = document.querySelector('.cart-count');

  window.addToCart = function(productId) {
    cartCount++;
    if (cartBadge) cartBadge.textContent = cartCount;

    // Quick toast feedback
    showToast('Added to cart! 🛒');
  };

  window.buyNow = function(productId) {
    window.location.href = `login.php?redirect=checkout&id=${productId}`;
  };

  // ── Toast notification
  function showToast(msg) {
    const toast = document.createElement('div');
    toast.textContent = msg;
    toast.style.cssText = `
      position: fixed; bottom: 28px; right: 28px;
      background: #1a1a1a; color: #fff;
      padding: 12px 20px; border-radius: 10px;
      font-size: 13px; font-weight: 600;
      font-family: 'Poppins', sans-serif;
      box-shadow: 0 8px 24px rgba(0,0,0,0.2);
      z-index: 9999;
      animation: slideIn 0.3s ease;
    `;

    const style = document.createElement('style');
    style.textContent = `
      @keyframes slideIn {
        from { opacity: 0; transform: translateY(10px); }
        to   { opacity: 1; transform: translateY(0); }
      }
    `;
    document.head.appendChild(style);
    document.body.appendChild(toast);

    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transition = 'opacity 0.4s';
      setTimeout(() => toast.remove(), 400);
    }, 2500);
  }

  // ── Scroll-reveal for feature cards and sections
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });

  document.querySelectorAll('.feature-card, .stat-card').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    observer.observe(el);
  });

  // Modal
  const modal      = document.getElementById('authModal');
  const openBtn    = document.getElementById('openModal');
  const closeBtn   = document.getElementById('closeModal');

  function openModal(tab) {
    if (!modal) return;
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
    if (tab) switchTab(tab);
  }
  function closeModal() {
    if (!modal) return;
    modal.classList.remove('open');
    document.body.style.overflow = '';
  }

  openBtn?.addEventListener('click', () => openModal('login'));
  closeBtn?.addEventListener('click', closeModal);
  modal?.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

  function switchTab(name) {
    document.querySelectorAll('.modal-tab').forEach(t => t.classList.toggle('active', t.dataset.tab === name));
    document.querySelectorAll('.modal-pane').forEach(p => p.classList.toggle('active', p.id === 'pane-' + name));
  }

  document.querySelectorAll('.modal-tab').forEach(tab => tab.addEventListener('click', () => switchTab(tab.dataset.tab)));
  document.querySelectorAll('[data-switch]').forEach(link => {
    link.addEventListener('click', (e) => { e.preventDefault(); switchTab(link.dataset.switch); });
  });

  // Auto-open if PHP returned an error
  if (document.querySelector('.modal-alert')) openModal();

  window.toggleMPw = function(inputId, icon) {
    const input = document.getElementById(inputId);
    if (!input) return;
    input.type = input.type === 'password' ? 'text' : 'password';
    icon.textContent = input.type === 'password' ? '👁' : '-';
  };

});