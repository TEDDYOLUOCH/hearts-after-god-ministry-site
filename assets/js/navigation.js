// Navigation Enhancement Script
// Provides smooth scrolling, back-to-top button, and table of contents functionality

class NavigationEnhancer {
  constructor() {
    this.backToTopBtn = document.getElementById('back-to-top');
    this.init();
  }

  init() {
    this.setupBackToTop();
    this.setupSmoothScrolling();
    this.setupTableOfContents();
  }

  setupBackToTop() {
    if (!this.backToTopBtn) return;

    window.addEventListener('scroll', () => {
      if (window.pageYOffset > 300) {
        this.backToTopBtn.classList.remove('opacity-0', 'invisible');
        this.backToTopBtn.classList.add('opacity-100', 'visible');
      } else {
        this.backToTopBtn.classList.add('opacity-0', 'invisible');
        this.backToTopBtn.classList.remove('opacity-100', 'visible');
      }
    });

    this.backToTopBtn.addEventListener('click', () => {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  }

  setupSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        }
      });
    });
  }

  setupTableOfContents() {
    const observerOptions = {
      threshold: 0.1,
      rootMargin: '-20% 0px -80% 0px'
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        const id = entry.target.getAttribute('id');
        const tocLink = document.querySelector(`a[href="#${id}"]`);
        if (entry.isIntersecting) {
          tocLink?.classList.add('bg-[#7C3AED]', 'text-white');
          tocLink?.classList.remove('bg-[#7C3AED]/10', 'text-[#7C3AED]');
        } else {
          tocLink?.classList.remove('bg-[#7C3AED]', 'text-white');
          tocLink?.classList.add('bg-[#7C3AED]/10', 'text-[#7C3AED]');
        }
      });
    }, observerOptions);

    // Observe all sections with IDs
    document.querySelectorAll('section[id]').forEach(section => {
      observer.observe(section);
    });
  }
}

// Initialize navigation enhancements when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  new NavigationEnhancer();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
  module.exports = NavigationEnhancer;
} 