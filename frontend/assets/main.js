// Reusable Header Loader
(function() {
  document.addEventListener('DOMContentLoaded', function() {
    const headerContainer = document.getElementById('site-header');
    if (headerContainer) {
      fetch('includes/header.php')
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
          // Ensure active link highlighting runs after header is loaded
          if (typeof setActiveLinks === 'function') {
            setActiveLinks();
          } else {
            // Try to find the function in the loaded header
            const scriptTags = headerContainer.getElementsByTagName('script');
            for (let i = 0; i < scriptTags.length; i++) {
              try {
                eval(scriptTags[i].innerText);
              } catch (e) {}
            }
            if (typeof setActiveLinks === 'function') {
              setActiveLinks();
            }
          }
        });
    }
    const footerContainer = document.getElementById('site-footer');
    if (footerContainer) {
      fetch('includes/footer.php')
        .then(res => res.text())
        .then(html => {
          footerContainer.innerHTML = html;
        });
    }
  });
})();

document.addEventListener('scroll', function() {
  const header = document.querySelector('header');
  if (header) {
    if (window.scrollY > 10) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  }
});

// Dynamic Sermons Loader for sermons.html
(function() {
  const sermonsGrid = document.getElementById('sermons-grid');
  const loadMoreBtn = document.getElementById('load-more-sermons');
  const filterBtns = document.querySelectorAll('[data-category]');
  if (!sermonsGrid || !loadMoreBtn) return;

  let sermons = [];
  let filteredSermons = [];
  let renderedCount = 0;
  const INITIAL_COUNT = 6;
  const LOAD_COUNT = 3;
  let currentCategory = 'All';

  // Fetch sermons.json
  fetch('sermons.json')
    .then(res => res.json())
    .then(data => {
      sermons = data;
      filteredSermons = sermons;
      renderSermons(INITIAL_COUNT);
      if (filteredSermons.length <= INITIAL_COUNT) {
        loadMoreBtn.style.display = 'none';
      }
    });

  function renderSermons(count, reset = false) {
    if (reset) {
      sermonsGrid.innerHTML = '';
      renderedCount = 0;
    }
    const toRender = filteredSermons.slice(renderedCount, renderedCount + count);
    toRender.forEach(sermon => {
      const card = document.createElement('div');
      card.className = `bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 border-t-4 ${getBorderColor(sermon.category)}`;
      card.innerHTML = `
        <div class="mb-4">
          <span class="inline-block ${getBadgeBg(sermon.category)} text-white text-xs font-bold px-3 py-1 rounded-full mb-3">
            <i class="${getBadgeIcon(sermon.category)} mr-1"></i> ${sermon.category}
          </span>
          <h3 class="text-xl font-bold text-gray-900 mb-3" style="font-family: 'Montserrat', Arial, sans-serif;">
            ${sermon.title}
          </h3>
          <div class="flex items-center gap-2 text-gray-600 mb-2">
            <i class="fas fa-user ${getBadgeBg(sermon.category)}"></i>
            <span style="font-family: 'Open Sans', Arial, sans-serif;">${sermon.speaker}</span>
          </div>
          <div class="flex items-center gap-2 text-gray-500 text-sm mb-4">
            <i class="fas fa-calendar text-[#F59E0B]"></i>
            <span style="font-family: 'Open Sans', Arial, sans-serif;">${formatDate(sermon.date)}</span>
          </div>
          <p class="text-gray-700 text-sm mb-4" style="font-family: 'Open Sans', Arial, sans-serif;">
            ${sermon.description}
          </p>
        </div>
        <a href="${sermon.video}" target="_blank" class="inline-flex items-center gap-2 text-[#7C3AED] hover:text-[#DC2626] hover:underline font-semibold transition-colors duration-200" style="font-family: 'Open Sans', Arial, sans-serif;">
          <i class="fab fa-youtube"></i>
          Watch on YouTube
        </a>
      `;
      sermonsGrid.appendChild(card);
    });
    renderedCount += toRender.length;
    if (renderedCount >= filteredSermons.length) {
      loadMoreBtn.style.display = 'none';
    } else {
      loadMoreBtn.style.display = '';
    }
  }

  loadMoreBtn.addEventListener('click', function() {
    renderSermons(LOAD_COUNT);
  });

  filterBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      // Remove active style from all, add to clicked
      filterBtns.forEach(b => b.classList.remove('bg-[#7C3AED]', 'text-white', 'shadow'));
      this.classList.add('bg-[#7C3AED]', 'text-white', 'shadow');
      // Set filter
      currentCategory = this.getAttribute('data-category');
      if (currentCategory === 'All') {
        filteredSermons = sermons;
      } else {
        filteredSermons = sermons.filter(s => (s.category || '').toLowerCase() === currentCategory.toLowerCase());
      }
      renderSermons(INITIAL_COUNT, true);
      if (filteredSermons.length <= INITIAL_COUNT) {
        loadMoreBtn.style.display = 'none';
      }
    });
  });

  // Helpers for badge color/icon
  function getBorderColor(category) {
    switch (category) {
      case 'Faith': return 'border-[#F59E0B]';
      case 'Prayer': return 'border-[#7C3AED]';
      case 'Family': return 'border-[#1E40AF]';
      case 'Encouragement': return 'border-[#DC2626]';
      case 'Spiritual Growth': return 'border-[#7C3AED]';
      case 'Evangelism': return 'border-[#1E40AF]';
      case 'Grace': return 'border-[#F59E0B]';
      case 'Worship': return 'border-[#7C3AED]';
      case 'Victory': return 'border-[#DC2626]';
      case 'Character': return 'border-[#1E40AF]';
      case 'Service': return 'border-[#F59E0B]';
      case 'Trust': return 'border-[#7C3AED]';
      default: return 'border-[#7C3AED]';
    }
  }
  function getBadgeBg(category) {
    switch (category) {
      case 'Faith': return 'bg-[#F59E0B]';
      case 'Prayer': return 'bg-[#7C3AED]';
      case 'Family': return 'bg-[#1E40AF]';
      case 'Encouragement': return 'bg-[#DC2626]';
      case 'Spiritual Growth': return 'bg-[#7C3AED]';
      case 'Evangelism': return 'bg-[#1E40AF]';
      case 'Grace': return 'bg-[#F59E0B]';
      case 'Worship': return 'bg-[#7C3AED]';
      case 'Victory': return 'bg-[#DC2626]';
      case 'Character': return 'bg-[#1E40AF]';
      case 'Service': return 'bg-[#F59E0B]';
      case 'Trust': return 'bg-[#7C3AED]';
      default: return 'bg-[#7C3AED]';
    }
  }
  function getBadgeIcon(category) {
    switch (category) {
      case 'Faith': return 'fas fa-cross';
      case 'Prayer': return 'fas fa-pray';
      case 'Family': return 'fas fa-users';
      case 'Encouragement': return 'fas fa-bolt';
      case 'Spiritual Growth': return 'fas fa-seedling';
      case 'Evangelism': return 'fas fa-globe';
      case 'Grace': return 'fas fa-heart';
      case 'Worship': return 'fas fa-music';
      case 'Victory': return 'fas fa-trophy';
      case 'Character': return 'fas fa-user-shield';
      case 'Service': return 'fas fa-hands-helping';
      case 'Trust': return 'fas fa-shield-alt';
      default: return 'fas fa-bolt';
    }
  }
  function formatDate(dateStr) {
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
  }
})();

// Countdown Timer for Upcoming Event (Kapenguria Mission Trip)
(function() {
  const countdownEl = document.getElementById('event-countdown');
  if (!countdownEl) return;

  // Set event date (December 1st, next occurrence)
  const now = new Date();
  let eventYear = now.getFullYear();
  const eventMonth = 11; // December (0-indexed)
  const eventDay = 1;
  let eventDate = new Date(eventYear, eventMonth, eventDay, 0, 0, 0);
  if (eventDate < now) {
    eventDate = new Date(eventYear + 1, eventMonth, eventDay, 0, 0, 0);
  }

  function updateCountdown() {
    const now = new Date();
    const diff = eventDate - now;
    if (diff <= 0) {
      countdownEl.textContent = 'Event is ongoing!';
      countdownEl.classList.add('bg-[#7C3AED]', 'text-white');
      return;
    }
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
    const minutes = Math.floor((diff / (1000 * 60)) % 60);
    const seconds = Math.floor((diff / 1000) % 60);
    countdownEl.textContent = `${days}d ${hours}h ${minutes}m ${seconds}s left`;
  }

  updateCountdown();
  setInterval(updateCountdown, 1000);
})();

(function() {
  document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.querySelector('.gallery-sidebar');
    if (sidebarToggle && sidebar) {
      // Create overlay for mobile
      let overlay = document.getElementById('gallery-sidebar-overlay');
      if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'gallery-sidebar-overlay';
        overlay.className = 'fixed inset-0 bg-black/40 z-40 hidden';
        document.body.appendChild(overlay);
      }
      function openSidebar() {
        sidebar.classList.add('open');
        sidebar.classList.remove('-translate-x-full');
        sidebar.classList.add('translate-x-0');
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        setTimeout(() => { sidebar.focus(); }, 100);
      }
      function closeSidebar() {
        sidebar.classList.remove('open');
        sidebar.classList.remove('translate-x-0');
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        document.body.style.overflow = '';
      }
      sidebarToggle.addEventListener('click', openSidebar);
      overlay.addEventListener('click', closeSidebar);
      // Optional: close on escape
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar.classList.contains('open')) {
          closeSidebar();
        }
      });
      // Hide sidebar on desktop resize
      window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024) {
          closeSidebar();
        }
      });
    }
  });
})();

// BLOG CATEGORY FILTER FUNCTIONALITY
(function() {
  document.addEventListener('DOMContentLoaded', function() {
    // Find the filter bar (first one in blog-section)
    var blogSection = document.getElementById('blog-section');
    if (!blogSection) return;
    var filterBar = blogSection.querySelector('.flex.flex-wrap.justify-center.gap-3');
    if (!filterBar) return;
    var filterButtons = filterBar.querySelectorAll('button');
    var blogCards = blogSection.querySelectorAll('article');

    filterButtons.forEach(function(btn) {
      btn.addEventListener('click', function() {
        // Remove active style from all
        filterButtons.forEach(function(b) {
          b.classList.remove('bg-purple-700', 'text-white', 'shadow');
          b.classList.add('bg-gray-200', 'text-purple-700');
        });
        // Add active style to clicked
        btn.classList.remove('bg-gray-200', 'text-purple-700');
        btn.classList.add('bg-purple-700', 'text-white', 'shadow');

        var filter = btn.textContent.trim();
        blogCards.forEach(function(card) {
          var badge = card.querySelector('span');
          if (!badge) return;
          var category = badge.textContent.trim();
          if (filter === 'All' || category === filter || category + 's' === filter) {
            card.style.display = '';
          } else {
            card.style.display = 'none';
          }
        });
      });
    });

    // BLOG READ MORE BUTTONS FUNCTIONALITY
    var readMoreButtons = blogSection.querySelectorAll('a,button');
    readMoreButtons.forEach(function(btn) {
      if (btn.textContent.trim().toLowerCase().includes('read more')) {
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          var card = btn.closest('article');
          if (!card) return;
          var titleEl = card.querySelector('h3');
          if (!titleEl) return;
          var title = titleEl.textContent.trim();
          var url = 'blog-detail.html?title=' + encodeURIComponent(title);
          window.location.href = url;
        });
      }
    });

    // SUBSCRIBE BUTTON FUNCTIONALITY (YouTube)
    document.querySelectorAll('a,button').forEach(function(btn) {
      if (btn.textContent.trim().toLowerCase() === 'subscribe') {
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          window.open('https://youtube.com/@heartsaftergodministries?si=psY5wvKqNS_nls95', '_blank');
        });
      }
    });
  });
})();
