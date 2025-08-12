// Navigation.js - Enhanced navigation for long pages
// Handles smooth scrolling, back-to-top button, and active section highlighting

document.addEventListener('DOMContentLoaded', function() {
  // Back to top button functionality
  const backToTopBtn = document.getElementById('back-to-top');
  
  if (backToTopBtn) {
    // Show/hide back to top button based on scroll position
    window.addEventListener('scroll', function() {
      if (window.pageYOffset > 300) {
        backToTopBtn.classList.remove('opacity-0', 'pointer-events-none');
        backToTopBtn.classList.add('opacity-100');
      } else {
        backToTopBtn.classList.add('opacity-0', 'pointer-events-none');
        backToTopBtn.classList.remove('opacity-100');
      }
    });

    // Smooth scroll to top when button is clicked
    backToTopBtn.addEventListener('click', function() {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  }

  // Smooth scrolling for anchor links
  const anchorLinks = document.querySelectorAll('a[href^="#"]');
  
  anchorLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      
      const targetId = this.getAttribute('href').substring(1);
      const targetElement = document.getElementById(targetId);
      
      if (targetElement) {
        // Calculate offset for sticky header
        const headerHeight = document.querySelector('header').offsetHeight;
        const tocHeight = document.querySelector('.sticky.top-20')?.offsetHeight || 0;
        const totalOffset = headerHeight + tocHeight + 20; // 20px extra padding
        
        const targetPosition = targetElement.offsetTop - totalOffset;
        
        window.scrollTo({
          top: targetPosition,
          behavior: 'smooth'
        });
        
        // Update active state in TOC
        updateActiveTOCLink(this);
      }
    });
  });

  // Active section highlighting using Intersection Observer
  const sections = document.querySelectorAll('section[id]');
  const tocLinks = document.querySelectorAll('.sticky.top-20 a[href^="#"]');
  
  if (sections.length > 0 && tocLinks.length > 0) {
    const observerOptions = {
      root: null,
      rootMargin: '-20% 0px -70% 0px', // Trigger when section is 20% from top
      threshold: 0
    };

    const sectionObserver = new IntersectionObserver(function(entries) {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const activeSectionId = entry.target.id;
          updateActiveTOCLink(activeSectionId);
        }
      });
    }, observerOptions);

    sections.forEach(section => {
      sectionObserver.observe(section);
    });
  }

  // Update active TOC link
  function updateActiveTOCLink(activeElement) {
    // Remove active class from all TOC links
    tocLinks.forEach(link => {
      link.classList.remove('bg-[#7C3AED]', 'text-white', 'bg-[#F59E0B]', 'text-[#1E40AF]', 'bg-[#1E40AF]');
      link.classList.add('bg-[#7C3AED]/10', 'text-[#7C3AED]', 'bg-[#F59E0B]/10', 'text-[#F59E0B]', 'bg-[#1E40AF]/10', 'text-[#1E40AF]');
    });

    // Add active class to current link
    if (typeof activeElement === 'string') {
      // If activeElement is a section ID
      const link = document.querySelector(`.sticky.top-20 a[href="#${activeElement}"]`);
      if (link) {
        link.classList.remove('bg-[#7C3AED]/10', 'text-[#7C3AED]', 'bg-[#F59E0B]/10', 'text-[#F59E0B]', 'bg-[#1E40AF]/10', 'text-[#1E40AF]');
        link.classList.add('bg-[#7C3AED]', 'text-white');
      }
    } else {
      // If activeElement is a link element
      activeElement.classList.remove('bg-[#7C3AED]/10', 'text-[#7C3AED]', 'bg-[#F59E0B]/10', 'text-[#F59E0B]', 'bg-[#1E40AF]/10', 'text-[#1E40AF]');
      activeElement.classList.add('bg-[#7C3AED]', 'text-white');
    }
  }

  // Mobile menu functionality
  const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
  const mobileMenu = document.getElementById('mobile-menu');
  const mobileOverlay = document.getElementById('mobile-overlay');
  const closeBtn = document.querySelector('.close-btn');

  if (mobileMenuToggle && mobileMenu && mobileOverlay) {
    // Open mobile menu
    mobileMenuToggle.addEventListener('click', function() {
      mobileMenu.classList.add('open');
      mobileOverlay.classList.add('open');
      this.setAttribute('aria-expanded', 'true');
      document.body.style.overflow = 'hidden';
    });

    // Close mobile menu
    function closeMobileMenu() {
      mobileMenu.classList.remove('open');
      mobileOverlay.classList.remove('open');
      mobileMenuToggle.setAttribute('aria-expanded', 'false');
      document.body.style.overflow = '';
    }

    // Close on overlay click
    mobileOverlay.addEventListener('click', closeMobileMenu);

    // Close on close button click
    if (closeBtn) {
      closeBtn.addEventListener('click', closeMobileMenu);
    }

    // Close on escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && mobileMenu.classList.contains('open')) {
        closeMobileMenu();
      }
    });

    // Close on mobile menu link click
    const mobileMenuLinks = mobileMenu.querySelectorAll('a');
    mobileMenuLinks.forEach(link => {
      link.addEventListener('click', closeMobileMenu);
    });
  }

  // Collapsible sections functionality
  const collapsibleSections = document.querySelectorAll('details');
  
  collapsibleSections.forEach(section => {
    const summary = section.querySelector('summary');
    if (summary) {
      summary.addEventListener('click', function(e) {
        // Add smooth animation
        const content = section.querySelector('div');
        if (content) {
          if (section.open) {
            content.style.maxHeight = content.scrollHeight + 'px';
            setTimeout(() => {
              content.style.maxHeight = '0px';
            }, 10);
          } else {
            content.style.maxHeight = '0px';
            setTimeout(() => {
              content.style.maxHeight = content.scrollHeight + 'px';
            }, 10);
          }
        }
      });
    }
  });

  // Search functionality for events
  const searchInput = document.querySelector('input[placeholder="Search events..."]');
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase();
      const eventCards = document.querySelectorAll('[id$="-events-list"] .bg-white');
      
      eventCards.forEach(card => {
        const title = card.querySelector('h3')?.textContent.toLowerCase() || '';
        const description = card.querySelector('p')?.textContent.toLowerCase() || '';
        const category = card.querySelector('.inline-flex')?.textContent.toLowerCase() || '';
        
        const matches = title.includes(searchTerm) || 
                       description.includes(searchTerm) || 
                       category.includes(searchTerm);
        
        card.style.display = matches ? 'flex' : 'none';
      });
    });
  }

  // Category filter functionality
  const categoryFilter = document.querySelector('select[aria-label="Filter by category"]');
  if (categoryFilter) {
    categoryFilter.addEventListener('change', function() {
      const selectedCategory = this.value;
      const eventCards = document.querySelectorAll('[id$="-events-list"] .bg-white');
      
      eventCards.forEach(card => {
        const category = card.querySelector('.inline-flex')?.textContent || '';
        
        if (selectedCategory === 'All Categories' || category.includes(selectedCategory)) {
          card.style.display = 'flex';
        } else {
          card.style.display = 'none';
        }
      });
    });
  }

  // Initialize current time display
  function updateCurrentTime() {
    const currentTimeElement = document.getElementById('current-time');
    if (currentTimeElement) {
      const now = new Date();
      const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit',
        timeZoneName: 'short'
      };
      currentTimeElement.textContent = now.toLocaleDateString('en-US', options);
    }
  }

  // Update time on page load and every minute
  updateCurrentTime();
  setInterval(updateCurrentTime, 60000);

  // Add loading states for better UX
  const loadingStates = document.querySelectorAll('.loading');
  loadingStates.forEach(element => {
    element.classList.add('animate-pulse');
    setTimeout(() => {
      element.classList.remove('animate-pulse');
    }, 1000);
  });

  // Keyboard navigation for accessibility
  document.addEventListener('keydown', function(e) {
    // Tab navigation for modals
    if (e.key === 'Tab') {
      const modal = document.querySelector('.fixed.inset-0:not(.hidden)');
      if (modal) {
        const focusableElements = modal.querySelectorAll('a, button, input, textarea, select, [tabindex]:not([tabindex="-1"])');
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        if (e.shiftKey) {
          if (document.activeElement === firstElement) {
            e.preventDefault();
            lastElement.focus();
          }
        } else {
          if (document.activeElement === lastElement) {
            e.preventDefault();
            firstElement.focus();
          }
        }
      }
    }
  });

  // Add focus indicators for better accessibility
  const focusableElements = document.querySelectorAll('a, button, input, textarea, select, [tabindex]:not([tabindex="-1"])');
  focusableElements.forEach(element => {
    element.addEventListener('focus', function() {
      this.classList.add('ring-2', 'ring-[#7C3AED]', 'ring-offset-2');
    });
    
    element.addEventListener('blur', function() {
      this.classList.remove('ring-2', 'ring-[#7C3AED]', 'ring-offset-2');
    });
  });
});

// Export functions for use in other scripts
window.navigationUtils = {
  scrollToSection: function(sectionId) {
    const element = document.getElementById(sectionId);
    if (element) {
      const headerHeight = document.querySelector('header').offsetHeight;
      const tocHeight = document.querySelector('.sticky.top-20')?.offsetHeight || 0;
      const totalOffset = headerHeight + tocHeight + 20;
      
      const targetPosition = element.offsetTop - totalOffset;
      
      window.scrollTo({
        top: targetPosition,
        behavior: 'smooth'
      });
    }
  },
  
  updateActiveSection: function(sectionId) {
    const tocLinks = document.querySelectorAll('.sticky.top-20 a[href^="#"]');
    tocLinks.forEach(link => {
      link.classList.remove('bg-[#7C3AED]', 'text-white');
      link.classList.add('bg-[#7C3AED]/10', 'text-[#7C3AED]');
    });
    
    const activeLink = document.querySelector(`.sticky.top-20 a[href="#${sectionId}"]`);
    if (activeLink) {
      activeLink.classList.remove('bg-[#7C3AED]/10', 'text-[#7C3AED]');
      activeLink.classList.add('bg-[#7C3AED]', 'text-white');
    }
  }
}; 