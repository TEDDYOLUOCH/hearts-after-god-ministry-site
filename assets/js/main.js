// Reusable Header Loader
(function() {
  document.addEventListener('DOMContentLoaded', function() {
    const headerContainer = document.getElementById('site-header');
    if (headerContainer) {
      fetch('header.html')
        .then(res => res.text())
        .then(html => {
          headerContainer.innerHTML = html;
          // Re-initialize mobile menu toggle after header is loaded
          const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
          const mobileMenu = document.getElementById('mobile-menu');
          const mobileOverlay = document.getElementById('mobile-overlay');
          const closeBtn = mobileMenu ? mobileMenu.querySelector('.close-btn') : null;
          if (mobileMenuToggle && mobileMenu && mobileOverlay) {
            mobileMenuToggle.addEventListener('click', function() {
              mobileMenu.classList.add('open');
              mobileOverlay.classList.add('open');
              this.setAttribute('aria-expanded', 'true');
              document.body.style.overflow = 'hidden';
            });
            function closeMobileMenu() {
              mobileMenu.classList.remove('open');
              mobileOverlay.classList.remove('open');
              mobileMenuToggle.setAttribute('aria-expanded', 'false');
              document.body.style.overflow = '';
            }
            mobileOverlay.addEventListener('click', closeMobileMenu);
            if (closeBtn) closeBtn.addEventListener('click', closeMobileMenu);
            document.addEventListener('keydown', function(e) {
              if (e.key === 'Escape' && mobileMenu.classList.contains('open')) {
                closeMobileMenu();
              }
            });
            mobileMenu.querySelectorAll('a').forEach(link => {
              link.addEventListener('click', closeMobileMenu);
            });
          }
        });
    }
  });
})();
