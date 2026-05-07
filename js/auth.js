// js/auth.js

function togglePw(inputId, icon) {
  const input = document.getElementById(inputId);
  if (!input) return;
  if (input.type === 'password') {
    input.type = 'text';
    icon.textContent = '-';
  } else {
    input.type = 'password';
    icon.textContent = '👁';
  }
}

// Auto-dismiss alerts after 5s
document.addEventListener('DOMContentLoaded', () => {
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(a => {
    setTimeout(() => {
      a.style.transition = 'opacity 0.5s';
      a.style.opacity = '0';
      setTimeout(() => a.remove(), 500);
    }, 5000);
  });

  // Input focus animation
  document.querySelectorAll('input').forEach(input => {
    input.addEventListener('focus', () => {
      input.closest('.form-group')?.classList.add('focused');
    });
    input.addEventListener('blur', () => {
      input.closest('.form-group')?.classList.remove('focused');
    });
  });
});