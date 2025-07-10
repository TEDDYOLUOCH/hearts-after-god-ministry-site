// Reusable Header Loader
(function() {
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
})();

// Reusable Footer Loader
(function() {
  const footerContainer = document.getElementById('site-footer');
  if (footerContainer) {
    fetch('footer.html')
      .then(res => res.text())
      .then(html => {
        footerContainer.innerHTML = html;
      });
  }
})();

// Remove video background and glassmorphism code
// Navigation: Mobile menu toggle
const mobileMenuBtn = document.getElementById('mobile-menu-btn');
const mobileMenu = document.getElementById('mobile-menu');
const mobileMenuOverlay = document.getElementById('mobile-menu-overlay');
const mobileMenuClose = document.getElementById('mobile-menu-close');

function openMobileMenu() {
  mobileMenuOverlay.classList.remove('hidden');
  mobileMenuOverlay.setAttribute('aria-hidden', 'false');
  mobileMenu.classList.remove('translate-x-full');
  mobileMenu.setAttribute('aria-hidden', 'false');
  mobileMenuBtn.setAttribute('aria-expanded', 'true');
  // Focus first link in menu
  setTimeout(() => {
    const firstLink = mobileMenu.querySelector('a, button');
    if (firstLink) firstLink.focus();
  }, 100);
  document.body.style.overflow = 'hidden';
}
function closeMobileMenu() {
  mobileMenuOverlay.classList.add('hidden');
  mobileMenuOverlay.setAttribute('aria-hidden', 'true');
  mobileMenu.classList.add('translate-x-full');
  mobileMenu.setAttribute('aria-hidden', 'true');
  mobileMenuBtn.setAttribute('aria-expanded', 'false');
  mobileMenuBtn.focus();
  document.body.style.overflow = '';
}
if (mobileMenuBtn && mobileMenu && mobileMenuOverlay && mobileMenuClose) {
  mobileMenuBtn.addEventListener('click', openMobileMenu);
  mobileMenuClose.addEventListener('click', closeMobileMenu);
  mobileMenuOverlay.addEventListener('click', closeMobileMenu);
  // Trap focus inside menu
  mobileMenu.addEventListener('keydown', function(e) {
    if (e.key === 'Tab') {
      const focusable = mobileMenu.querySelectorAll('a, button:not([disabled])');
      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      if (e.shiftKey) {
        if (document.activeElement === first) {
          e.preventDefault();
          last.focus();
        }
      } else {
        if (document.activeElement === last) {
          e.preventDefault();
          first.focus();
        }
      }
    }
    if (e.key === 'Escape') {
      closeMobileMenu();
    }
  });
  // ESC key closes menu from anywhere
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && mobileMenuBtn.getAttribute('aria-expanded') === 'true') {
      closeMobileMenu();
    }
  });
  // Close menu on link click
  mobileMenu.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', closeMobileMenu);
  });
}

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    const targetId = this.getAttribute('href').slice(1);
    const target = document.getElementById(targetId);
    if (target) {
      e.preventDefault();
      target.scrollIntoView({ behavior: 'smooth' });
      target.tabIndex = -1;
      target.focus();
    }
  });
});

// Dynamic Breadcrumbs
function updateBreadcrumbs() {
  const breadcrumbNav = document.getElementById('breadcrumbs');
  if (!breadcrumbNav) return;
  const path = window.location.pathname.split('/').pop();
  const pageMap = {
    'about.html': 'About',
    'vision.html': 'Vision',
    'events.html': 'Events',
    'sermons.html': 'Sermons',
    'blog.html': 'Blog',
    'gallery.html': 'Gallery',
    'contact.html': 'Contact',
    'discipleship.html': 'Discipleship',
    'missions.html': 'Missions',
  };
  if (!path || path === '' || path === 'index.html') {
    breadcrumbNav.classList.add('hidden');
    breadcrumbNav.innerHTML = '';
    return;
  }
  const pageName = pageMap[path] || document.title;
  breadcrumbNav.innerHTML = `<ol class="flex gap-2" role="list"><li><a href="index.html" class="text-primary underline">Home</a></li><li aria-hidden="true">/</li><li aria-current="page" class="text-gray-700">${pageName}</li></ol>`;
  breadcrumbNav.classList.remove('hidden');
}
document.addEventListener('DOMContentLoaded', updateBreadcrumbs);

// Active Link Highlighting
function highlightActiveNavLinks() {
  const path = window.location.pathname.split('/').pop() || 'index.html';
  const navLinks = document.querySelectorAll('nav ul li a, #mobile-menu a');
  navLinks.forEach(link => {
    // Remove previous highlight
    link.classList.remove('active');
    link.removeAttribute('aria-current');
    // Highlight if matches current page
    const href = link.getAttribute('href');
    if ((path === '' && href === 'index.html') || href === path) {
      link.classList.add('active');
      link.setAttribute('aria-current', 'page');
    }
  });
}
document.addEventListener('DOMContentLoaded', () => {
  highlightActiveNavLinks();
});

// Page Transition Effects (Fade)
function supportsReducedMotion() {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}
function fadeOutAndNavigate(href) {
  const main = document.querySelector('main');
  if (main && !supportsReducedMotion()) {
    main.classList.add('fade-out');
    setTimeout(() => { window.location.href = href; }, 300);
  } else {
    window.location.href = href;
  }
}
document.querySelectorAll('a').forEach(link => {
  const href = link.getAttribute('href');
  if (href && !href.startsWith('http') && !href.startsWith('mailto:') && !href.startsWith('tel:') && !href.startsWith('#')) {
    link.addEventListener('click', function(e) {
      if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey || link.target === '_blank') return;
      e.preventDefault();
      fadeOutAndNavigate(href);
    });
  }
});
document.addEventListener('DOMContentLoaded', function() {
  const main = document.querySelector('main');
  if (main && !supportsReducedMotion()) {
    main.classList.add('fade-in');
    setTimeout(() => { main.classList.remove('fade-in'); }, 400);
  }
});

// Dynamic Content Loader for Home Page
async function fetchAndRenderContent(section, url, renderFn) {
  try {
    const res = await fetch(url);
    if (!res.ok) throw new Error('Not found');
    const data = await res.json();
    renderFn(data);
  } catch (e) {
    // Demo fallback
    if (section === 'blog') renderFn([{title:'The Power of Daily Devotion',author:'Jane Doe',date:'2024-04-10',tags:['Devotion'],excerpt:'Discover how daily time with God transforms your life.'}]);
    if (section === 'sermons') renderFn([{title:'Living by Faith',speaker:'Pastor John Doe',date:'2025-04-01',tags:['Faith'],duration:'42:00',series:'Faith Series'}]);
    if (section === 'events') renderFn([{title:'Spring Worship Night',date:'2025-04-28',location:'123 Revival Avenue',tags:['Worship'],description:'An evening of worship, prayer, and community.'}]);
  }
}
function renderBlogPosts(posts) {
  const container = document.getElementById('home-blog-list') || document.getElementById('blog-list');
  if (!container) return;
  // If rendering for home page, use image and modern card style
  if (container.id === 'home-blog-list') {
    container.innerHTML = posts.map(post => `
      <div class="bg-white rounded-2xl shadow-xl p-0 flex flex-col group blog-card overflow-hidden">
        <a href="blog-detail.html?id=${encodeURIComponent(post.id||post.title)}" class="block">
          <img src="${post.image||'assets/images/hero/IMG-20250705-WA0023.jpg'}" alt="${post.title}" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy" />
        </a>
        <div class="p-6 flex flex-col flex-1">
          <div class="flex gap-2 mb-2">${(post.tags||[]).map(tag=>`<span class='tag'>${tag}</span>`).join('')}</div>
          <a href="blog-detail.html?id=${encodeURIComponent(post.id||post.title)}" class="block group-hover:text-primary transition-colors font-bold text-lg mb-1 line-clamp-2">${post.title}</a>
          <div class="flex items-center gap-2 mb-2 text-sm text-gray-500">
            <span>${post.date}</span> &bull; <span>${post.author || ''}</span>
          </div>
          <p class="text-gray-700 mb-4 line-clamp-3">${post.excerpt||''}</p>
          <a href="blog-detail.html?id=${encodeURIComponent(post.id||post.title)}" class="mt-auto px-4 py-2 bg-[#7C3AED] text-white rounded-lg shadow hover:bg-[#FDBA17] hover:text-[#2046B3] focus:outline-none focus:ring-2 focus:ring-[#7C3AED] transition">Read More</a>
        </div>
      </div>
    `).join('');
    return;
  }
  // Default rendering (blog page)
  container.innerHTML = posts.map(post => `
    <div class="bg-white rounded-xl shadow p-6 flex flex-col group">
      <a href="blog-detail.html?id=${encodeURIComponent(post.id||post.title)}" class="block group-hover:text-primary transition-colors font-semibold text-lg mb-2">${post.title}</a>
      <div class="flex items-center gap-2 mb-2 text-sm text-gray-500">
        <span>${post.date}</span> &bull; <span>${post.author || ''}</span>
      </div>
      <div class="flex gap-2 mb-2">${(post.tags||[]).map(tag=>`<a href='blog.html?tag=${encodeURIComponent(tag)}' class='bg-gold/20 text-gold px-2 py-0.5 rounded text-xs hover:underline focus:underline'>${tag}</a>`).join('')}</div>
      <p class="text-gray-700 mb-4">${post.excerpt||''}</p>
      <a href="blog-detail.html?id=${encodeURIComponent(post.id||post.title)}" class="mt-auto px-4 py-2 bg-primary text-white rounded hover:bg-secondary hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary transition">Read More</a>
    </div>
  `).join('');
}
function renderSermons(sermons) {
  const container = document.getElementById('home-sermon-list') || document.getElementById('sermon-list');
  if (!container) return;
  container.innerHTML = sermons.map(sermon => `
    <div class="bg-white rounded-xl shadow p-6 flex flex-col group">
      <a href="sermon-detail.html?id=${encodeURIComponent(sermon.id||sermon.title)}" class="block group-hover:text-primary transition-colors font-semibold text-lg mb-2">${sermon.title}</a>
      <div class="flex items-center gap-2 mb-2 text-sm text-gray-500">
        <span>${sermon.date}</span> &bull; <span>${sermon.speaker || ''}</span>
      </div>
      <div class="flex gap-2 mb-2">${(sermon.tags||[]).map(tag=>`<a href='sermons.html?tag=${encodeURIComponent(tag)}' class='bg-blue/20 text-blue px-2 py-0.5 rounded text-xs hover:underline focus:underline'>${tag}</a>`).join('')}</div>
      <span class="text-xs text-gray-400 mb-2">${sermon.duration||''} ${sermon.series?('&bull; '+sermon.series):''}</span>
      <a href="sermon-detail.html?id=${encodeURIComponent(sermon.id||sermon.title)}" class="mt-auto px-4 py-2 bg-primary text-white rounded hover:bg-secondary hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary transition">Listen</a>
    </div>
  `).join('');
}
function renderEvents(events) {
  const container = document.getElementById('home-event-list') || document.getElementById('event-list');
  if (!container) return;
  container.innerHTML = events.map(event => `
    <div class="bg-white rounded-xl shadow p-6 flex flex-col group">
      <a href="event-detail.html?id=${encodeURIComponent(event.id||event.title)}" class="block group-hover:text-primary transition-colors font-semibold text-lg mb-2">${event.title}</a>
      <div class="flex items-center gap-2 mb-2 text-sm text-gray-500">
        <span>${event.date}</span> &bull; <span>${event.location||''}</span>
      </div>
      <div class="flex gap-2 mb-2">${(event.tags||[]).map(tag=>`<a href='events.html?tag=${encodeURIComponent(tag)}' class='bg-purple/20 text-purple px-2 py-0.5 rounded text-xs hover:underline focus:underline'>${tag}</a>`).join('')}</div>
      <p class="text-gray-700 mb-4">${event.description||''}</p>
      ${event.files && event.files.length ? `
        <div class='mb-4'>
          <div class='font-semibold text-[#7C3AED] mb-1 flex items-center gap-2'><i class='fas fa-paperclip'></i>Files/Resources:</div>
          <ul class='space-y-1'>
            ${event.files.map(file => `
              <li class='flex items-center gap-2 text-sm'>
                <a href='${file.dataUrl}' download='${file.name}' target='_blank' class='text-blue-700 underline hover:text-[#7C3AED]'>${file.name}</a>
                <span class='text-gray-400'>${file.type.replace('application/','').replace('image/','').toUpperCase()||''}</span>
                ${file.description ? `<span class='text-gray-500 italic'>${file.description}</span>` : ''}
              </li>
            `).join('')}
          </ul>
        </div>
      ` : ''}
      <a href="event-detail.html?id=${encodeURIComponent(event.id||event.title)}" class="mt-auto px-4 py-2 bg-primary text-white rounded hover:bg-secondary hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary transition">Details</a>
    </div>
  `).join('');
}

// --- Home Page Search, Filter, Pagination Logic ---
function uniqueTags(items) {
  const tags = new Set();
  items.forEach(item => (item.tags||[]).forEach(tag => tags.add(tag)));
  return Array.from(tags);
}
function paginate(items, page, perPage) {
  const total = items.length;
  const totalPages = Math.ceil(total / perPage);
  const start = (page - 1) * perPage;
  return {
    items: items.slice(start, start + perPage),
    total,
    totalPages,
    page
  };
}
function sortItems(items, sortValue) {
  if (sortValue === 'oldest') {
    return items.slice().sort((a, b) => new Date(a.date) - new Date(b.date));
  }
  // Default: newest
  return items.slice().sort((a, b) => new Date(b.date) - new Date(a.date));
}
setupSection = function(section, fetchFn, renderFn, filterId, searchId, listId, paginationId, perPage) {
  let allItems = [];
  let filtered = [];
  let page = 1;
  perPage = perPage || 6;
  const filter = document.getElementById(filterId);
  const search = document.getElementById(searchId);
  const list = document.getElementById(listId);
  const pagination = document.getElementById(paginationId);
  const sort = document.getElementById(listId.replace('list', 'sort'));
  function updateFilterDropdown() {
    if (!filter) return;
    const tags = uniqueTags(allItems);
    filter.innerHTML = '<option value="">All Tags</option>' + tags.map(tag => `<option value="${tag}">${tag}</option>`).join('');
  }
  function filterAndRender() {
    const q = search ? search.value.trim().toLowerCase() : '';
    const tag = filter ? filter.value : '';
    const sortValue = sort ? sort.value : 'newest';
    filtered = allItems.filter(item => {
      const matchesQ = !q || Object.values(item).join(' ').toLowerCase().includes(q);
      const matchesTag = !tag || (item.tags||[]).includes(tag);
      return matchesQ && matchesTag;
    });
    filtered = sortItems(filtered, sortValue);
    page = 1;
    renderPage();
  }
  function renderPage() {
    const {items, total, totalPages} = paginate(filtered, page, perPage);
    renderFn(items);
    if (!pagination) return;
    pagination.innerHTML = '';
    if (totalPages <= 1) return;
    if (page > 1) {
      const prev = document.createElement('button');
      prev.textContent = 'Prev';
      prev.className = 'px-3 py-1 rounded bg-soft hover:bg-gold/20 focus:bg-gold/20 focus:outline-none';
      prev.onclick = () => { page--; renderPage(); };
      pagination.appendChild(prev);
    }
    for (let i = 1; i <= totalPages; i++) {
      const btn = document.createElement('button');
      btn.textContent = i;
      btn.className = 'px-3 py-1 rounded ' + (i === page ? 'bg-gold text-white font-bold' : 'bg-soft hover:bg-gold/20 focus:bg-gold/20');
      btn.setAttribute('aria-current', i === page ? 'page' : 'false');
      btn.onclick = () => { page = i; renderPage(); };
      pagination.appendChild(btn);
    }
    if (page < totalPages) {
      const next = document.createElement('button');
      next.textContent = 'Next';
      next.className = 'px-3 py-1 rounded bg-soft hover:bg-gold/20 focus:bg-gold/20 focus:outline-none';
      next.onclick = () => { page++; renderPage(); };
      pagination.appendChild(next);
    }
  }
  if (filter) filter.onchange = filterAndRender;
  if (search) search.oninput = filterAndRender;
  if (sort) sort.onchange = filterAndRender;
  fetchFn(function(items) {
    allItems = items;
    updateFilterDropdown();
    filterAndRender();
  });
};
// Blog
if (document.getElementById('blog-list')) {
  setupSection(
    'blog',
    cb => fetchAndRenderContent('blog', 'blog.json', cb),
    renderBlogPosts,
    'blog-filter',
    'blog-search',
    'blog-list',
    'blog-pagination',
    8 // items per page for blog
  );
}
// Sermons
if (document.getElementById('sermon-list')) {
  setupSection(
    'sermons',
    cb => fetchAndRenderContent('sermons', 'sermons.json', cb),
    renderSermons,
    'sermon-filter',
    'sermon-search',
    'sermon-list',
    'sermon-pagination',
    8 // items per page for sermons
  );
}
// Events
if (document.getElementById('event-list')) {
  setupSection(
    'events',
    cb => fetchAndRenderContent('events', 'events.json', cb),
    renderEvents,
    'event-filter',
    'event-search',
    'event-list',
    'event-pagination',
    8 // items per page for events
  );
}

// Tag/Category Filtered Views and Sorting via URL
function getQueryParam(name) {
  const params = new URLSearchParams(window.location.search);
  return params.get(name);
}
function setQueryParam(name, value) {
  const params = new URLSearchParams(window.location.search);
  if (value) {
    params.set(name, value);
  } else {
    params.delete(name);
  }
  const newUrl = window.location.pathname + '?' + params.toString();
  window.history.replaceState({}, '', newUrl);
}
function patchFilterSortDropdowns(filterId, sortId) {
  const filter = document.getElementById(filterId);
  const sort = document.getElementById(sortId);
  if (filter) {
    const tag = getQueryParam('tag');
    if (tag) filter.value = tag;
    filter.addEventListener('change', function() {
      setQueryParam('tag', filter.value);
    });
  }
  if (sort) {
    const sortVal = getQueryParam('sort');
    if (sortVal) sort.value = sortVal;
    sort.addEventListener('change', function() {
      setQueryParam('sort', sort.value);
    });
  }
}
document.addEventListener('DOMContentLoaded', function() {
  patchFilterSortDropdowns('blog-filter', 'blog-sort');
  patchFilterSortDropdowns('sermon-filter', 'sermon-sort');
  patchFilterSortDropdowns('event-filter', 'event-sort');
  patchFilterSortDropdowns('home-blog-filter', 'home-blog-sort');
  patchFilterSortDropdowns('home-sermon-filter', 'home-sermon-sort');
  patchFilterSortDropdowns('home-event-filter', 'home-event-sort');
});

// Modern Mobile Menu Functionality
(function() {
  const menuBtn = document.getElementById('mobile-menu-btn');
  const menu = document.getElementById('mobile-menu');
  const overlay = document.getElementById('mobile-menu-overlay');
  const closeBtn = document.getElementById('mobile-menu-close');
  let lastFocused = null;

  function openMenu() {
    lastFocused = document.activeElement;
    menu.classList.remove('translate-x-full');
    menu.classList.add('translate-x-0');
    overlay.classList.remove('hidden');
    overlay.classList.add('opacity-100');
    menu.setAttribute('aria-hidden', 'false');
    menuBtn.setAttribute('aria-expanded', 'true');
    // Focus first link
    const firstLink = menu.querySelector('a, button');
    if (firstLink) firstLink.focus();
    document.body.style.overflow = 'hidden';
  }

  function closeMenu() {
    menu.classList.add('translate-x-full');
    menu.classList.remove('translate-x-0');
    overlay.classList.add('hidden');
    overlay.classList.remove('opacity-100');
    menu.setAttribute('aria-hidden', 'true');
    menuBtn.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
    if (lastFocused) lastFocused.focus();
  }

  menuBtn && menuBtn.addEventListener('click', openMenu);
  closeBtn && closeBtn.addEventListener('click', closeMenu);
  overlay && overlay.addEventListener('click', closeMenu);

  // Trap focus inside menu
  menu && menu.addEventListener('keydown', function(e) {
    if (e.key === 'Tab') {
      const focusable = menu.querySelectorAll('a, button, [tabindex]:not([tabindex="-1"])');
      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      if (e.shiftKey) {
        if (document.activeElement === first) {
          e.preventDefault();
          last.focus();
        }
      } else {
        if (document.activeElement === last) {
          e.preventDefault();
          first.focus();
        }
      }
    } else if (e.key === 'Escape') {
      closeMenu();
    }
  });
})();

// === Dynamic HTML Includes for Header & Footer ===
function includeHTML(selector, url, callback) {
  fetch(url)
    .then(res => res.text())
    .then(html => {
      document.querySelector(selector).innerHTML = html;
      if (callback) callback();
    });
}

function initHeaderFooter() {
  includeHTML('#site-header', 'header.html', () => {
    // Re-initialize nav underline and mobile menu after header is loaded
    initNavUnderline();
    initMobileMenu();
  });
  includeHTML('#site-footer', 'footer.html');
}

document.addEventListener('DOMContentLoaded', initHeaderFooter);

// === Nav Underline Animation ===
function initNavUnderline() {
  // Remove underline animation: do nothing
}

// === Mobile Menu Logic ===
function initMobileMenu() {
  const menuToggle = document.getElementById('mobile-menu-toggle');
  const mobileMenu = document.getElementById('mobile-menu');
  const overlay = document.getElementById('mobile-overlay');
  const closeBtn = mobileMenu ? mobileMenu.querySelector('.close-btn') : null;
  if (!menuToggle || !mobileMenu || !overlay) return;
  function openMenu() {
    mobileMenu.classList.add('open');
    overlay.classList.add('open');
    menuToggle.setAttribute('aria-expanded', 'true');
    document.body.classList.add('overflow-hidden');
  }
  function closeMenu() {
    mobileMenu.classList.remove('open');
    overlay.classList.remove('open');
    menuToggle.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('overflow-hidden');
  }
  menuToggle.addEventListener('click', openMenu);
  overlay.addEventListener('click', closeMenu);
  if (closeBtn) closeBtn.addEventListener('click', closeMenu);
  // Close on nav link click (mobile)
  mobileMenu.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', closeMenu);
  });
}

// === Gallery Page Logic ===
function initGalleryPage() {
  // Only run on gallery.html
  if (!window.location.pathname.endsWith('gallery.html')) return;
  console.log('[Gallery] initGalleryPage running');
  let allImages = [];
  let activeCategory = '';
  fetch('gallery.json')
    .then(res => {
      console.log('[Gallery] Fetched gallery.json:', res.status);
      if (!res.ok) throw new Error('Failed to fetch gallery.json');
      return res.json();
    })
    .then(images => {
      console.log('[Gallery] Parsed images:', images.length, images);
      allImages = images;
      renderSidebar(images);
      renderGalleryFeaturedAndStats(images);
      filterAndRender();
    })
    .catch(err => {
      console.error('[Gallery] Error loading gallery.json:', err);
      const grid = document.getElementById('gallery-list');
      if (grid) grid.innerHTML = '<div class="text-red-600">Failed to load gallery images.</div>';
    });
  // Render category filter buttons (future-proof)
  function renderCategories(images) {
    const categories = [...new Set(images.map(img => img.category).filter(Boolean))];
    const container = document.getElementById('gallery-categories');
    if (!container) return;
    if (!categories.length) { container.innerHTML = ''; return; }
    container.innerHTML = `<button class="px-4 py-2 rounded-full bg-[#7C3AED] text-white font-semibold shadow hover:bg-[#FDBA17] hover:text-[#2046B3] transition" data-cat="">All</button>` +
      categories.map(cat => `<button class="px-4 py-2 rounded-full bg-gray-100 text-[#7C3AED] font-semibold shadow hover:bg-[#7C3AED] hover:text-white transition" data-cat="${cat}">${cat}</button>`).join('');
    container.querySelectorAll('button').forEach(btn => {
      btn.onclick = () => filterCategory(btn.getAttribute('data-cat'));
    });
  }
  // Render gallery grid
  function renderGallery(images) {
    const grid = document.getElementById('gallery-list');
    if (!grid) return;
    if (!images.length) {
      grid.innerHTML = '<div class="col-span-full text-center text-gray-400 text-xl py-16">No images found.</div>';
      return;
    }
    grid.innerHTML = images.map(img => {
      // Escape single quotes for HTML attributes
      const url = (img.url || '').replace(/'/g, '&#39;');
      const caption = (img.caption || '').replace(/'/g, '&#39;');
      return `
        <div class="relative group cursor-pointer rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition" data-url='${url}' data-caption='${caption}'>
          <img src="${img.url}" alt="${img.caption||'Gallery Image'}" class="w-full h-48 object-cover group-hover:scale-105 transition duration-300" />
          <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent px-3 py-2 text-white text-sm font-semibold opacity-0 group-hover:opacity-100 transition">${img.caption||''}</div>
        </div>
      `;
    }).join('');
    // Add click listeners for modal
    grid.querySelectorAll('.group').forEach(item => {
      item.onclick = () => openModal(item.getAttribute('data-url'), item.getAttribute('data-caption'));
    });
  }
  // Category filter logic
  function filterCategory(cat) {
    activeCategory = cat;
    if (!cat) return renderGallery(allImages);
    renderGallery(allImages.filter(img => img.category === cat));
  }
  // Modal logic
  function openModal(url, caption) {
    document.getElementById('modal-img').src = url;
    document.getElementById('modal-caption').textContent = caption;
    document.getElementById('gallery-modal').classList.remove('hidden');
  }
  const closeModalBtn = document.getElementById('close-modal');
  if (closeModalBtn) {
    closeModalBtn.onclick = function() {
      document.getElementById('gallery-modal').classList.add('hidden');
      document.getElementById('modal-img').src = '';
      document.getElementById('modal-caption').textContent = '';
    };
  }
}
document.addEventListener('DOMContentLoaded', initGalleryPage);

// === Gallery Featured Section & Stats ===
function renderGalleryFeaturedAndStats(images) {
  // Featured: use first album or first image
  const albums = [...new Set(images.map(img => img.album).filter(Boolean))];
  let featuredAlbum = albums[0];
  let featuredImg = images.find(img => img.album === featuredAlbum) || images[0];
  if (featuredImg) {
    document.getElementById('featured-img').src = featuredImg.url;
    document.getElementById('featured-title').textContent = featuredImg.album || 'Featured';
    document.getElementById('featured-caption').textContent = featuredImg.caption || '';
    const viewBtn = document.getElementById('view-featured');
    if (viewBtn) {
      viewBtn.onclick = () => {
        console.log('[Gallery] View Album button clicked');
        if (featuredImg.album) {
          activeAlbum = featuredImg.album;
          filterAndRender();
          window.scrollTo({ top: document.getElementById('gallery-list').offsetTop - 80, behavior: 'smooth' });
        }
      };
    } else {
      console.warn('[Gallery] View Album button not found in DOM');
    }
  }
  // Stats
  document.getElementById('stat-images').textContent = images.length;
  document.getElementById('stat-albums').textContent = albums.length;
  const tags = images.flatMap(img => img.tags||[]);
  const uniqueTags = [...new Set(tags)];
  document.getElementById('stat-tags').textContent = uniqueTags.length;
  // Top tags
  const tagCounts = {};
  tags.forEach(tag => { tagCounts[tag] = (tagCounts[tag]||0)+1; });
  const topTags = Object.entries(tagCounts).sort((a,b)=>b[1]-a[1]).slice(0,3).map(([tag])=>tag);
  document.getElementById('stat-top-tags').textContent = topTags.join(', ');
}

// Remove Masonry Layout for Gallery and related functions
// Restore renderGallery to use standard grid
function renderGallery(images) {
  console.log('[Gallery] Rendering gallery. Images:', images.length, images);
  const grid = document.getElementById('gallery-list');
  const empty = document.getElementById('gallery-empty');
  if (!images.length) {
    grid.innerHTML = '';
    empty.classList.remove('hidden');
    console.log('[Gallery] No images to display.');
    return;
  }
  empty.classList.add('hidden');
  grid.innerHTML = images.map(img => renderGalleryItem(img)).join('');
}

// --- Album Download Button Logic ---
function updateDownloadAlbumButton(selectedAlbum, images) {
  const section = document.getElementById('download-album-section');
  const btn = document.getElementById('download-album-btn');
  if (selectedAlbum && images.length) {
    section.classList.remove('hidden');
    btn.onclick = () => showAlbumDownloadModal(selectedAlbum, images);
  } else {
    section.classList.add('hidden');
    btn.onclick = null;
  }
}
function showAlbumDownloadModal(album, images) {
  // Simple modal with download links for each image
  const modal = document.createElement('div');
  modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black/60';
  modal.innerHTML = `
    <div class="bg-white rounded-xl shadow-lg p-6 max-w-lg w-full relative">
      <button class="absolute top-2 right-2 text-gray-400 hover:text-[#7C3AED] text-2xl font-bold" aria-label="Close">&times;</button>
      <h2 class="text-xl font-bold mb-4 text-[#7C3AED]">Download Album: ${album}</h2>
      <ul class="space-y-2 max-h-64 overflow-y-auto">
        ${images.map(img => `<li><a href="${img.src}" download class="text-[#2046B3] hover:underline">${img.caption || img.filename || 'Image'}</a></li>`).join('')}
      </ul>
      <div class="mt-4 text-xs text-gray-500">Tip: Right-click and 'Save as' if download does not start automatically.</div>
    </div>
  `;
  document.body.appendChild(modal);
  modal.querySelector('button').onclick = () => modal.remove();
  modal.addEventListener('click', e => { if (e.target === modal) modal.remove(); });
}
// Call updateDownloadAlbumButton(selectedAlbum, filteredImages) when album changes

// --- Gallery Analytics ---
function updateGalleryAnalytics(images, albums) {
  // Most viewed: from localStorage
  const views = JSON.parse(localStorage.getItem('galleryViews') || '{}');
  let mostViewed = null, mostViewedCount = 0;
  images.forEach(img => {
    const count = views[img.id] || 0;
    if (count > mostViewedCount) {
      mostViewed = img;
      mostViewedCount = count;
    }
  });
  // Most favorited
  const favs = JSON.parse(localStorage.getItem('galleryFavorites') || '[]');
  let mostFav = null, mostFavCount = 0;
  const favCounts = {};
  favs.forEach(id => { favCounts[id] = (favCounts[id] || 0) + 1; });
  images.forEach(img => {
    const count = favCounts[img.id] || 0;
    if (count > mostFavCount) {
      mostFav = img;
      mostFavCount = count;
    }
  });
  // Largest album
  let largestAlbum = null, largestCount = 0;
  Object.entries(albums).forEach(([album, imgs]) => {
    if (imgs.length > largestCount) {
      largestAlbum = album;
      largestCount = imgs.length;
    }
  });
  document.getElementById('stat-most-viewed').textContent = mostViewed ? (mostViewed.caption || mostViewed.filename || mostViewed.id) : '—';
  document.getElementById('stat-most-favorited').textContent = mostFav ? (mostFav.caption || mostFav.filename || mostFav.id) : '—';
  document.getElementById('stat-largest-album').textContent = largestAlbum ? `${largestAlbum} (${largestCount})` : '—';
}
// Call updateGalleryAnalytics(filteredImages, albums) after filtering or on load

// --- Track Views ---
function trackImageView(imgId) {
  const views = JSON.parse(localStorage.getItem('galleryViews') || '{}');
  views[imgId] = (views[imgId] || 0) + 1;
  localStorage.setItem('galleryViews', JSON.stringify(views));
}
// Call trackImageView(image.id) when modal is opened

// === Infinite Scroll for Gallery ===
let imagesPerPage = 12;
let currentPage = 1;
let infiniteScrollActive = false;
let infiniteScrollLoading = false;

function resetInfiniteScroll() {
  currentPage = 1;
  infiniteScrollActive = true;
  infiniteScrollLoading = false;
  document.getElementById('gallery-list').innerHTML = '';
  document.getElementById('gallery-empty').classList.add('hidden');
}

function renderGallery(images, append = false, skipScroll) {
  console.log('[Gallery] Rendering gallery. Images:', images.length, images);
  const grid = document.getElementById('gallery-list');
  const empty = document.getElementById('gallery-empty');
  let pagedImages = images.slice(0, imagesPerPage * currentPage);
  if (!pagedImages.length) {
    grid.innerHTML = '';
    empty.classList.remove('hidden');
    if (showFavoritesOnly) {
      empty.textContent = 'You have no favorite images yet. Click the star on any image to add it to your favorites!';
    } else {
      empty.textContent = 'No images to display.';
    }
    console.log('[Gallery] No images to display.');
    return;
  }
  empty.classList.add('hidden');
  if (!append) grid.innerHTML = '';
  grid.innerHTML += pagedImages.slice(append ? (imagesPerPage * (currentPage - 1)) : 0).map((img, i) => `
    <div class="gallery-card relative group cursor-pointer rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition bg-white">
      <button class="absolute top-2 right-2 z-10 p-1 rounded-full bg-white/80 hover:bg-[#FDBA17]/80 transition star-btn" data-id="${img.id}" tabindex="0" aria-label="Favorite">
        <svg class="w-6 h-6 ${isFavorite(img.id) ? 'text-[#FDBA17] fill-[#FDBA17]' : 'text-gray-300'}" fill="${isFavorite(img.id) ? '#FDBA17' : 'none'}" stroke="currentColor" stroke-width="2" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.18c.969 0 1.371 1.24.588 1.81l-3.388 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.388-2.46a1 1 0 00-1.175 0l-3.388 2.46c-.784.57-1.838-.196-1.54-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.388-2.46c-.783-.57-.38-1.81.588-1.81h4.18a1 1 0 00.95-.69l1.286-3.967z"/></svg>
      </button>
      <img src="${img.url}" alt="${img.caption||'Gallery Image'}" class="w-full h-56 object-cover group-hover:scale-105 transition duration-300 fade-img" loading="lazy" />
      <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent px-3 py-2 text-white text-sm font-semibold opacity-0 group-hover:opacity-100 transition">${img.caption||''}</div>
      <div class="absolute top-2 left-2 flex gap-1 flex-wrap">
        ${(img.tags||[]).map(tag=>`<span class='bg-[#FDBA17]/90 text-[#2046B3] px-2 py-0.5 rounded-full text-xs font-bold mr-1 mb-1'>${tag}</span>`).join('')}
      </div>
    </div>
  `).join('');
  // Modal triggers
  grid.querySelectorAll('.gallery-card').forEach((item, idx) => {
    const img = pagedImages[idx];
    item.onclick = (e) => {
      if (e.target.closest('.star-btn')) return;
      openModalByAlbum(img.id);
    };
  });
  // Star button logic
  grid.querySelectorAll('.star-btn').forEach(btn => {
    btn.onclick = (e) => {
      e.stopPropagation();
      toggleFavorite(btn.getAttribute('data-id'));
    };
  });
  setTimeout(() => {
    grid.querySelectorAll('.gallery-card').forEach(card => {
      card.classList.remove('opacity-0');
      card.classList.add('opacity-100');
    });
  }, 50);
}

function handleInfiniteScroll() {
  if (!infiniteScrollActive || infiniteScrollLoading) return;
  const grid = document.getElementById('gallery-list');
  if (!grid) return;
  if ((window.innerHeight + window.scrollY) >= (grid.offsetTop + grid.offsetHeight - 400)) {
    // Near bottom, load more
    infiniteScrollLoading = true;
    showGalleryLoadingSpinner();
    setTimeout(() => {
      currentPage++;
      renderGallery(filteredImages, true);
      infiniteScrollLoading = false;
      hideGalleryLoadingSpinner();
      // If all images loaded, deactivate infinite scroll
      if (filteredImages.length <= imagesPerPage * currentPage) infiniteScrollActive = false;
    }, 600);
  }
}
function showGalleryLoadingSpinner() {
  let spinner = document.getElementById('gallery-loading');
  if (!spinner) {
    spinner = document.createElement('div');
    spinner.id = 'gallery-loading';
    spinner.className = 'flex justify-center py-8';
    spinner.innerHTML = '<svg class="animate-spin h-8 w-8 text-[#7C3AED]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>';
    document.getElementById('gallery-list').after(spinner);
  }
  spinner.style.display = 'flex';
}
function hideGalleryLoadingSpinner() {
  let spinner = document.getElementById('gallery-loading');
  if (spinner) spinner.style.display = 'none';
}
window.addEventListener('scroll', handleInfiniteScroll);

// === Favorites (Starred Images) ===
function getFavorites() {
  return JSON.parse(localStorage.getItem('galleryFavorites') || '[]');
}
function setFavorites(favs) {
  localStorage.setItem('galleryFavorites', JSON.stringify(favs));
}
function isFavorite(id) {
  return getFavorites().includes(id);
}
function toggleFavorite(id) {
  let favs = getFavorites();
  if (favs.includes(id)) {
    favs = favs.filter(f => f !== id);
  } else {
    favs.push(id);
  }
  setFavorites(favs);
  renderGallery(filteredImages, false, true);
  // Always update sidebar and analytics after toggling favorite
  console.log('[Gallery] Updating sidebar and analytics after favorite toggle');
  renderSidebar(filteredImages);
  // Recompute albums for analytics
  const albums = {};
  filteredImages.forEach(img => {
    if (!albums[img.album]) albums[img.album] = [];
    albums[img.album].push(img);
  });
  updateGalleryAnalytics(filteredImages, albums);
}
let showFavoritesOnly = false;

// Patch sidebar to add Show Favorites toggle
function renderSidebar(images) {
  // Albums
  const albumBtns = document.querySelectorAll('.album-filter-btn');
  albumBtns.forEach(btn => {
    btn.onclick = () => {
      activeAlbum = btn.getAttribute('data-value');
      setActiveFilterButton('.album-filter-btn', activeAlbum);
      filterAndRender();
    };
    btn.setAttribute('aria-label', btn.textContent + (btn.getAttribute('data-value') ? ' album' : ' all albums'));
    btn.setAttribute('tabindex', '0');
  });
  setActiveFilterButton('.album-filter-btn', activeAlbum);
  // Tags
  const tagBtns = document.querySelectorAll('.tag-filter-btn');
  tagBtns.forEach(btn => {
    btn.onclick = () => {
      activeTag = btn.getAttribute('data-value');
      setActiveFilterButton('.tag-filter-btn', activeTag);
      filterAndRender();
    };
    btn.setAttribute('aria-label', btn.textContent + (btn.getAttribute('data-value') ? ' tag' : ' all tags'));
    btn.setAttribute('tabindex', '0');
  });
  setActiveFilterButton('.tag-filter-btn', activeTag);
  // Years
  const yearBtns = document.querySelectorAll('.year-filter-btn');
  yearBtns.forEach(btn => {
    btn.onclick = () => {
      activeYear = btn.getAttribute('data-value');
      setActiveFilterButton('.year-filter-btn', activeYear);
      filterAndRender();
    };
    btn.setAttribute('aria-label', btn.textContent + (btn.getAttribute('data-value') ? ' year' : ' all years'));
    btn.setAttribute('tabindex', '0');
  });
  setActiveFilterButton('.year-filter-btn', activeYear);
  // Favorites toggle
  let favToggle = document.getElementById('gallery-fav-toggle');
  if (favToggle) {
    const favCount = getFavorites().length;
    favToggle.innerHTML = `<svg class="w-5 h-5 text-[#FDBA17]" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.18c.969 0 1.371 1.24.588 1.81l-3.388 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.388-2.46a1 1 0 00-1.175 0l-3.388 2.46c-.784.57-1.838-.196-1.54-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.388-2.46c-.783-.57-.38-1.81.588-1.81h4.18a1 1 0 00.95-.69l1.286-3.967z"/></svg> Show Favorites${favCount > 0 ? ` (${favCount})` : ''}`;
    favToggle.setAttribute('aria-pressed', showFavoritesOnly ? 'true' : 'false');
    favToggle.setAttribute('tabindex', '0');
    favToggle.setAttribute('aria-label', `Show Favorites${favCount > 0 ? ` (${favCount})` : ''}`);
    favToggle.onclick = function() {
      showFavoritesOnly = !showFavoritesOnly;
      favToggle.classList.toggle('bg-[#FDBA17]', showFavoritesOnly);
      favToggle.classList.toggle('text-white', showFavoritesOnly);
      favToggle.setAttribute('aria-pressed', showFavoritesOnly ? 'true' : 'false');
      filterAndRender();
    };
    // Keyboard accessibility
    favToggle.onkeydown = function(e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        favToggle.click();
      }
    };
  }
}

// Patch filterAndRender to filter by favorites
function filterAndRender() {
  resetInfiniteScroll();
  let favs = getFavorites();
  filteredImages = allImages.filter(img => {
    if (showFavoritesOnly && !favs.includes(img.id)) return false;
    const matchesAlbum = !activeAlbum || img.album === activeAlbum;
    const matchesTag = !activeTag || (img.tags && img.tags.includes(activeTag));
    const matchesYear = !activeYear || String(img.year) === String(activeYear);
    const matchesSearch = !searchTerm || (img.caption && img.caption.toLowerCase().includes(searchTerm)) || (img.album && img.album.toLowerCase().includes(searchTerm)) || (img.tags && img.tags.join(' ').toLowerCase().includes(searchTerm));
    return matchesAlbum && matchesTag && matchesYear && matchesSearch;
  });
  console.log('[Gallery] filterAndRender: activeAlbum =', activeAlbum, '| images after filter =', filteredImages.length);
  renderGallery(filteredImages);
  // Update analytics for the current filtered set
  const albums = {};
  filteredImages.forEach(img => {
    if (!albums[img.album]) albums[img.album] = [];
    albums[img.album].push(img);
  });
  updateGalleryAnalytics(filteredImages, albums);
  infiniteScrollActive = filteredImages.length > imagesPerPage;
}

// Patch renderGallery to show star icon and favorite state
function renderGallery(images, append = false, skipScroll) {
  const grid = document.getElementById('gallery-list');
  const empty = document.getElementById('gallery-empty');
  let pagedImages = images.slice(0, imagesPerPage * currentPage);
  if (!pagedImages.length) {
    grid.innerHTML = '';
    empty.classList.remove('hidden');
    return;
  }
  empty.classList.add('hidden');
  if (!append) grid.innerHTML = '';
  grid.innerHTML += pagedImages.slice(append ? (imagesPerPage * (currentPage - 1)) : 0).map((img, i) => `
    <div class="gallery-card relative group cursor-pointer rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition bg-white">
      <button class="absolute top-2 right-2 z-10 p-1 rounded-full bg-white/80 hover:bg-[#FDBA17]/80 transition star-btn" data-id="${img.id}" tabindex="0" aria-label="Favorite">
        <svg class="w-6 h-6 ${isFavorite(img.id) ? 'text-[#FDBA17] fill-[#FDBA17]' : 'text-gray-300'}" fill="${isFavorite(img.id) ? '#FDBA17' : 'none'}" stroke="currentColor" stroke-width="2" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.18c.969 0 1.371 1.24.588 1.81l-3.388 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.388-2.46a1 1 0 00-1.175 0l-3.388 2.46c-.784.57-1.838-.196-1.54-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.388-2.46c-.783-.57-.38-1.81.588-1.81h4.18a1 1 0 00.95-.69l1.286-3.967z"/></svg>
      </button>
      <img src="${img.url}" alt="${img.caption||'Gallery Image'}" class="w-full h-56 object-cover group-hover:scale-105 transition duration-300 fade-img" loading="lazy" />
      <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent px-3 py-2 text-white text-sm font-semibold opacity-0 group-hover:opacity-100 transition">${img.caption||''}</div>
      <div class="absolute top-2 left-2 flex gap-1 flex-wrap">
        ${(img.tags||[]).map(tag=>`<span class='bg-[#FDBA17]/90 text-[#2046B3] px-2 py-0.5 rounded-full text-xs font-bold mr-1 mb-1'>${tag}</span>`).join('')}
      </div>
    </div>
  `).join('');
  // Modal triggers
  grid.querySelectorAll('.gallery-card').forEach((item, idx) => {
    item.onclick = (e) => {
      if (e.target.closest('.star-btn')) return;
      openModal(idx);
    };
  });
  // Star button logic
  grid.querySelectorAll('.star-btn').forEach(btn => {
    btn.onclick = (e) => {
      e.stopPropagation();
      toggleFavorite(btn.getAttribute('data-id'));
    };
  });
  setTimeout(() => {
    grid.querySelectorAll('.gallery-card').forEach(card => {
      card.classList.remove('opacity-0');
      card.classList.add('opacity-100');
    });
  }, 50);
}
// Patch modal to show favorite state
function showModalImage() {
  const img = filteredImages[currentIndex];
  if (!img) return;
  
  // Track image view for analytics
  if (typeof trackImageView === 'function') {
    trackImageView(img.id);
  }
  
  document.getElementById('modal-img').src = img.url;
  document.getElementById('modal-caption').textContent = img.caption || '';
  document.getElementById('modal-album').textContent = img.album || '';
  document.getElementById('modal-year').textContent = img.year || '';
  document.getElementById('modal-tags').innerHTML = '';
  if (img.tags && img.tags.length > 0) {
    img.tags.forEach(tag => {
      const tagSpan = document.createElement('span');
      tagSpan.classList.add('px-2', 'py-0.5', 'rounded-full', 'bg-gray-200', 'text-xs', 'font-medium', 'text-gray-800');
      tagSpan.textContent = tag;
      document.getElementById('modal-tags').appendChild(tagSpan);
    });
  }
  // Favorite star in modal
  let modalStar = document.getElementById('modal-fav-star');
  if (!modalStar) {
    modalStar = document.createElement('button');
    modalStar.id = 'modal-fav-star';
    modalStar.className = 'absolute top-2 left-2 z-10 p-1 rounded-full bg-white/80 hover:bg-[#FDBA17]/80 transition';
    modalStar.innerHTML = '<svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.18c.969 0 1.371 1.24.588 1.81l-3.388 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.388-2.46a1 1 0 00-1.175 0l-3.388 2.46c-.784.57-1.838-.196-1.54-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.388-2.46c-.783-.57-.38-1.81.588-1.81h4.18a1 1 0 00.95-.69l1.286-3.967z"/></svg>';
    document.querySelector('.modal-content').appendChild(modalStar);
  }
  modalStar.querySelector('svg').setAttribute('fill', isFavorite(img.id) ? '#FDBA17' : 'none');
  modalStar.querySelector('svg').classList.toggle('text-[#FDBA17]', isFavorite(img.id));
  modalStar.onclick = function(e) {
    e.stopPropagation();
    toggleFavorite(img.id);
    showModalImage();
  };
}

// === Modal Download & Share ===
function setupModalDownloadShare() {
  const downloadBtn = document.getElementById('modal-download');
  const shareBtn = document.getElementById('modal-share');
  if (!downloadBtn || !shareBtn) return;
  downloadBtn.onclick = function(e) {
    e.preventDefault();
    const img = filteredImages[currentIndex];
    if (img) {
      downloadBtn.href = img.url;
      downloadBtn.setAttribute('download', img.url.split('/').pop());
    }
  };
  shareBtn.onclick = function(e) {
    e.preventDefault();
    const img = filteredImages[currentIndex];
    if (!img) return;
    const shareData = {
      title: img.caption || 'Gallery Image',
      text: img.caption || 'Check out this photo from Hearts After God Ministry!',
      url: window.location.origin + '/' + img.url
    };
    if (navigator.share) {
      navigator.share(shareData).catch(() => {});
    } else {
      showShareFallback(shareData.url);
    }
  };
}
function showShareFallback(url) {
  let fallback = document.getElementById('modal-share-fallback');
  if (!fallback) {
    fallback = document.createElement('div');
    fallback.id = 'modal-share-fallback';
    fallback.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black/40';
    fallback.innerHTML = `
      <div class="bg-white rounded-xl shadow-lg p-6 max-w-xs w-full text-center relative">
        <button id="close-share-fallback" class="absolute top-2 right-2 text-gray-400 hover:text-[#7C3AED] text-2xl font-bold">&times;</button>
        <h3 class="text-lg font-bold mb-2 text-[#7C3AED]">Share Image</h3>
        <input id="share-link" type="text" class="w-full px-2 py-1 border rounded mb-3 text-sm" readonly value="${url}" />
        <button id="copy-share-link" class="px-3 py-1 bg-[#FDBA17] text-[#2046B3] font-bold rounded mb-3 w-full">Copy Link</button>
        <div class="flex gap-2 justify-center">
          <a href="https://wa.me/?text=${encodeURIComponent(url)}" target="_blank" class="p-2 bg-green-500 rounded-full" aria-label="Share on WhatsApp"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/></svg></a>
          <a href="https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}" target="_blank" class="p-2 bg-blue-600 rounded-full" aria-label="Share on Facebook"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a>
        </div>
      </div>
    `;
    document.body.appendChild(fallback);
    document.getElementById('close-share-fallback').onclick = () => fallback.remove();
    document.getElementById('copy-share-link').onclick = () => {
      const input = document.getElementById('share-link');
      input.select();
      document.execCommand('copy');
      document.getElementById('copy-share-link').textContent = 'Copied!';
      setTimeout(() => { document.getElementById('copy-share-link').textContent = 'Copy Link'; }, 1200);
    };
  } else {
    fallback.style.display = 'flex';
  }
}
// Call setupModalDownloadShare after modal is shown
const origShowModalImage = showModalImage;
showModalImage = function() {
  origShowModalImage.apply(this, arguments);
  setupModalDownloadShare();
}; 

// === Modal Swipe Gestures & Autoplay ===
let autoplayInterval = null;
let autoplayActive = false;
function startAutoplay() {
  if (autoplayInterval) clearInterval(autoplayInterval);
  autoplayActive = true;
  autoplayInterval = setInterval(() => {
    if (currentIndex < filteredImages.length - 1) {
      currentIndex++;
      showModalImage();
    } else {
      currentIndex = 0;
      showModalImage();
    }
  }, 2500);
  updateAutoplayBtn();
}
function stopAutoplay() {
  autoplayActive = false;
  if (autoplayInterval) clearInterval(autoplayInterval);
  updateAutoplayBtn();
}
function updateAutoplayBtn() {
  const btn = document.getElementById('modal-autoplay');
  if (btn) {
    btn.innerHTML = autoplayActive
      ? '<svg class="w-5 h-5 inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 12L6 6V18Z"/></svg> Pause'
      : '<svg class="w-5 h-5 inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-6.518-3.759A1 1 0 007 8.118v7.764a1 1 0 001.234.97l6.518-1.757A1 1 0 0016 14.882V9.118a1 1 0 00-1.248-.95z"/></svg> Play';
    btn.setAttribute('aria-pressed', autoplayActive ? 'true' : 'false');
  }
}
// Add autoplay button to modal
function setupModalAutoplayBtn() {
  let btn = document.getElementById('modal-autoplay');
  if (!btn) {
    btn = document.createElement('button');
    btn.id = 'modal-autoplay';
    btn.className = 'px-3 py-1 rounded bg-[#2046B3] text-white font-bold hover:bg-[#FDBA17] hover:text-[#2046B3] transition ml-2';
    btn.setAttribute('aria-label', 'Toggle slideshow autoplay');
    btn.onclick = function(e) {
      e.stopPropagation();
      if (autoplayActive) stopAutoplay(); else startAutoplay();
    };
    document.querySelector('.modal-content .flex').appendChild(btn);
  }
  updateAutoplayBtn();
}
// Swipe gestures for modal
function setupModalSwipe() {
  const modal = document.getElementById('gallery-modal');
  let startX = null;
  modal.addEventListener('touchstart', function(e) {
    if (e.touches.length === 1) startX = e.touches[0].clientX;
  });
  modal.addEventListener('touchend', function(e) {
    if (startX === null) return;
    const endX = e.changedTouches[0].clientX;
    if (endX - startX > 60) document.getElementById('modal-prev').click();
    if (startX - endX > 60) document.getElementById('modal-next').click();
    startX = null;
  });
}
// Trap focus in modal
function trapModalFocus() {
  const modal = document.getElementById('gallery-modal');
  if (!modal) return;
  const focusable = modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
  const first = focusable[0];
  const last = focusable[focusable.length - 1];
  modal.addEventListener('keydown', function(e) {
    if (e.key === 'Tab') {
      if (e.shiftKey) {
        if (document.activeElement === first) {
          e.preventDefault();
          last.focus();
        }
      } else {
        if (document.activeElement === last) {
          e.preventDefault();
          first.focus();
        }
      }
    }
    if (e.key === 'Escape') {
      document.getElementById('close-modal').click();
    }
  });
}
// Patch showModalImage to setup autoplay, swipe, and focus trap
const origShowModalImage2 = showModalImage;
showModalImage = function() {
  origShowModalImage2.apply(this, arguments);
  setupModalDownloadShare();
  setupModalAutoplayBtn();
  setupModalSwipe();
  trapModalFocus();
};
// Stop autoplay when modal closes
const closeModalBtn = document.getElementById('close-modal');
if (closeModalBtn) {
  closeModalBtn.addEventListener('click', stopAutoplay);
}

// === Sidebar Collapsible on Mobile ===
document.addEventListener('DOMContentLoaded', function() {
  const sidebar = document.getElementById('sidebar');
  const sidebarToggle = document.getElementById('sidebar-toggle');
  const sidebarContent = document.getElementById('sidebar-content');
  if (sidebar && sidebarToggle && sidebarContent) {
    let open = false;
    function setSidebar(openState) {
      open = openState;
      sidebarContent.style.display = open ? 'block' : 'none';
      sidebarToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (open) sidebarContent.focus();
    }
    setSidebar(window.innerWidth >= 768); // Show by default on desktop
    sidebarToggle.onclick = function() {
      setSidebar(!open);
    };
    window.addEventListener('resize', () => {
      setSidebar(window.innerWidth >= 768);
    });
    // Trap focus in sidebar when open on mobile
    sidebarContent.setAttribute('tabindex', '-1');
    sidebarContent.addEventListener('keydown', function(e) {
      if (!open) return;
      if (e.key === 'Tab') {
        const focusable = sidebarContent.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (e.shiftKey) {
          if (document.activeElement === first) {
            e.preventDefault();
            last.focus();
          }
        } else {
          if (document.activeElement === last) {
            e.preventDefault();
            first.focus();
          }
        }
      }
      if (e.key === 'Escape') {
        setSidebar(false);
        sidebarToggle.focus();
      }
    });
  }
}); 

// --- Filter Button Utility ---
function setActiveFilterButton(groupSelector, activeValue) {
  document.querySelectorAll(groupSelector).forEach(btn => {
    const val = btn.getAttribute('data-value');
    const isActive = val === activeValue || (!val && !activeValue);
    btn.classList.toggle('bg-[#7C3AED]', isActive);
    btn.classList.toggle('text-white', isActive);
    btn.classList.toggle('font-bold', isActive);
    btn.setAttribute('aria-current', isActive ? 'true' : 'false');
    btn.setAttribute('tabindex', '0');
  });
} 

// === Real-Time Polling for Gallery Updates ===
let lastGalleryData = null;
let pollingInterval = null;
function startGalleryPolling() {
  if (pollingInterval) clearInterval(pollingInterval);
  pollingInterval = setInterval(async () => {
    try {
      const res = await fetch('gallery.json', { cache: 'no-store' });
      if (!res.ok) return;
      const data = await res.json();
      if (!lastGalleryData) {
        lastGalleryData = JSON.stringify(data);
        return;
      }
      const newData = JSON.stringify(data);
      if (newData !== lastGalleryData) {
        lastGalleryData = newData;
        // Update UI
        allImages = data;
        renderSidebar(allImages);
        renderGalleryFeaturedAndStats(allImages);
        filterAndRender();
        showGalleryToast('Gallery updated with new images!');
      }
    } catch (e) {}
  }, 15000);
}
function showGalleryToast(msg) {
  let toast = document.getElementById('gallery-toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'gallery-toast';
    toast.className = 'fixed top-6 left-1/2 -translate-x-1/2 z-50 bg-[#7C3AED] text-white px-6 py-3 rounded-xl shadow-lg text-lg font-bold opacity-0 pointer-events-none transition';
    document.body.appendChild(toast);
  }
  toast.textContent = msg;
  toast.style.opacity = '1';
  setTimeout(() => { toast.style.opacity = '0'; }, 2500);
}
document.addEventListener('DOMContentLoaded', () => {
  if (window.location.pathname.endsWith('gallery.html')) {
    startGalleryPolling();
  }
}); 

// === Album-Scoped Modal Logic ===
let modalAlbumImages = [];
let modalAlbumIndex = 0;

function openModalByAlbum(imageId) {
  // Find the clicked image
  const img = allImages.find(img => img.id === imageId);
  if (!img) return;
  // Get all images in the same album
  modalAlbumImages = allImages.filter(i => i.album === img.album);
  modalAlbumIndex = modalAlbumImages.findIndex(i => i.id === imageId);
  showModalImageByAlbum();
  document.getElementById('gallery-modal').classList.remove('hidden');
}

function showModalImageByAlbum() {
  const img = modalAlbumImages[modalAlbumIndex];
  if (!img) return;
  document.getElementById('modal-img').src = img.url;
  document.getElementById('modal-caption').textContent = img.caption || '';
  document.getElementById('modal-album').textContent = img.album || '';
  document.getElementById('modal-year').textContent = img.year || '';
  document.getElementById('modal-tags').innerHTML = '';
  if (img.tags && img.tags.length > 0) {
    img.tags.forEach(tag => {
      const tagSpan = document.createElement('span');
      tagSpan.classList.add('px-2', 'py-0.5', 'rounded-full', 'bg-gray-200', 'text-xs', 'font-medium', 'text-gray-800');
      tagSpan.textContent = tag;
      document.getElementById('modal-tags').appendChild(tagSpan);
    });
  }
  // Favorite star in modal
  let modalStar = document.getElementById('modal-fav-star');
  if (!modalStar) {
    modalStar = document.createElement('button');
    modalStar.id = 'modal-fav-star';
    modalStar.className = 'absolute top-2 left-2 z-10 p-1 rounded-full bg-white/80 hover:bg-[#FDBA17]/80 transition';
    modalStar.innerHTML = '<svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.18c.969 0 1.371 1.24.588 1.81l-3.388 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.388-2.46a1 1 0 00-1.175 0l-3.388 2.46c-.784.57-1.838-.196-1.54-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.388-2.46c-.783-.57-.38-1.81.588-1.81h4.18a1 1 0 00.95-.69l1.286-3.967z"/></svg>';
    document.querySelector('.modal-content').appendChild(modalStar);
  }
  modalStar.querySelector('svg').setAttribute('fill', isFavorite(img.id) ? '#FDBA17' : 'none');
  modalStar.querySelector('svg').classList.toggle('text-[#FDBA17]', isFavorite(img.id));
  modalStar.onclick = function(e) {
    e.stopPropagation();
    toggleFavorite(img.id);
    showModalImageByAlbum();
  };
}

// Modal next/prev for album
function modalNextAlbum() {
  if (modalAlbumImages.length === 0) return;
  modalAlbumIndex = (modalAlbumIndex + 1) % modalAlbumImages.length;
  showModalImageByAlbum();
}
function modalPrevAlbum() {
  if (modalAlbumImages.length === 0) return;
  modalAlbumIndex = (modalAlbumIndex - 1 + modalAlbumImages.length) % modalAlbumImages.length;
  showModalImageByAlbum();
}
// Attach to modal next/prev buttons only once
(function() {
  const modalNextBtn = document.getElementById('modal-next');
  const modalPrevBtn = document.getElementById('modal-prev');
  if (modalNextBtn && modalPrevBtn) {
    modalNextBtn.onclick = modalNextAlbum;
    modalPrevBtn.onclick = modalPrevAlbum;
  }
})();
// Update gallery grid click logic to use openModalByAlbum
function renderGallery(images, append = false, skipScroll) {
  const grid = document.getElementById('gallery-list');
  const empty = document.getElementById('gallery-empty');
  let pagedImages = images.slice(0, imagesPerPage * currentPage);
  if (!pagedImages.length) {
    grid.innerHTML = '';
    empty.classList.remove('hidden');
    return;
  }
  empty.classList.add('hidden');
  if (!append) grid.innerHTML = '';
  grid.innerHTML += pagedImages.slice(append ? (imagesPerPage * (currentPage - 1)) : 0).map((img, i) => `
    <div class="gallery-card relative group cursor-pointer rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition bg-white">
      <button class="absolute top-2 right-2 z-10 p-1 rounded-full bg-white/80 hover:bg-[#FDBA17]/80 transition star-btn" data-id="${img.id}" tabindex="0" aria-label="Favorite">
        <svg class="w-6 h-6 ${isFavorite(img.id) ? 'text-[#FDBA17] fill-[#FDBA17]' : 'text-gray-300'}" fill="${isFavorite(img.id) ? '#FDBA17' : 'none'}" stroke="currentColor" stroke-width="2" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.18c.969 0 1.371 1.24.588 1.81l-3.388 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.388-2.46a1 1 0 00-1.175 0l-3.388 2.46c-.784.57-1.838-.196-1.54-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.388-2.46c-.783-.57-.38-1.81.588-1.81h4.18a1 1 0 00.95-.69l1.286-3.967z"/></svg>
      </button>
      <img src="${img.url}" alt="${img.caption||'Gallery Image'}" class="w-full h-56 object-cover group-hover:scale-105 transition duration-300 fade-img" loading="lazy" />
      <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent px-3 py-2 text-white text-sm font-semibold opacity-0 group-hover:opacity-100 transition">${img.caption||''}</div>
      <div class="absolute top-2 left-2 flex gap-1 flex-wrap">
        ${(img.tags||[]).map(tag=>`<span class='bg-[#FDBA17]/90 text-[#2046B3] px-2 py-0.5 rounded-full text-xs font-bold mr-1 mb-1'>${tag}</span>`).join('')}
      </div>
    </div>
  `).join('');
  // Modal triggers
  grid.querySelectorAll('.gallery-card').forEach((item, idx) => {
    const img = pagedImages[idx];
    item.onclick = (e) => {
      if (e.target.closest('.star-btn')) return;
      openModalByAlbum(img.id);
    };
  });
  // Star button logic
  grid.querySelectorAll('.star-btn').forEach(btn => {
    btn.onclick = (e) => {
      e.stopPropagation();
      toggleFavorite(btn.getAttribute('data-id'));
    };
  });
  setTimeout(() => {
    grid.querySelectorAll('.gallery-card').forEach(card => {
      card.classList.remove('opacity-0');
      card.classList.add('opacity-100');
    });
  }, 50);
} 

document.addEventListener('DOMContentLoaded', function () {
  // Only run on user-dashboard.html
  if (!document.getElementById('modules-list')) return;

  // Helper: Toast
  function showModuleToast(msg) {
    let toast = document.getElementById('module-toast');
    if (!toast) {
      toast = document.createElement('div');
      toast.id = 'module-toast';
      toast.className = 'fixed top-6 left-1/2 transform -translate-x-1/2 z-50 bg-[#7C3AED] text-white px-6 py-3 rounded-lg shadow-lg opacity-0 transition-opacity duration-300 font-bold';
      document.body.appendChild(toast);
    }
    toast.textContent = msg;
    toast.classList.remove('opacity-0');
    toast.classList.add('opacity-100');
    setTimeout(() => {
      toast.classList.remove('opacity-100');
      toast.classList.add('opacity-0');
    }, 1800);
  }

  // Module state persistence
  const MODULE_KEY = 'discipleship_module_status';
  function getModuleStatus() {
    try {
      return JSON.parse(localStorage.getItem(MODULE_KEY)) || {};
    } catch { return {}; }
  }
  function setModuleStatus(status) {
    localStorage.setItem(MODULE_KEY, JSON.stringify(status));
  }

  // Setup module cards
  document.querySelectorAll('#modules-list > div[data-module]').forEach(card => {
    const moduleIdx = card.getAttribute('data-module');
    const startBtn = card.querySelector('.module-action');
    const completeBtn = card.querySelector('.complete-module-btn');
    const statusSpan = card.querySelector('.module-status');
    let status = getModuleStatus();
    let state = status[moduleIdx] || 'Not Started';

    // Initial UI state
    if (state === 'Completed') {
      statusSpan.textContent = 'Completed';
      statusSpan.className = 'text-xs text-green-600 font-bold mb-2 module-status';
      startBtn.textContent = 'Review';
      startBtn.disabled = false;
      startBtn.classList.remove('bg-[#FDBA17]', 'text-[#2046B3]');
      startBtn.classList.add('bg-[#7C3AED]', 'text-white');
      completeBtn.style.display = 'none';
    } else if (state === 'In Progress') {
      statusSpan.textContent = 'In Progress';
      statusSpan.className = 'text-xs text-yellow-600 font-bold mb-2 module-status';
      startBtn.textContent = 'Continue';
      startBtn.disabled = true;
      startBtn.classList.remove('bg-[#7C3AED]', 'text-white');
      startBtn.classList.add('bg-[#FDBA17]', 'text-[#2046B3]');
      completeBtn.style.display = '';
      completeBtn.disabled = false;
    } else {
      statusSpan.textContent = 'Not Started';
      statusSpan.className = 'text-xs text-gray-400 font-bold mb-2 module-status';
      startBtn.textContent = 'Start';
      startBtn.disabled = false;
      startBtn.classList.remove('bg-[#7C3AED]', 'text-white');
      startBtn.classList.add('bg-[#FDBA17]', 'text-[#2046B3]');
      completeBtn.style.display = '';
      completeBtn.disabled = true;
    }

    // Start/Continue/Review button
    startBtn.addEventListener('click', function () {
      let status = getModuleStatus();
      let state = status[moduleIdx] || 'Not Started';
      if (state === 'Completed') {
        showModuleToast('Reviewing completed module!');
        return;
      }
      status[moduleIdx] = 'In Progress';
      setModuleStatus(status);
      statusSpan.textContent = 'In Progress';
      statusSpan.className = 'text-xs text-yellow-600 font-bold mb-2 module-status';
      startBtn.textContent = 'Continue';
      startBtn.disabled = true;
      startBtn.classList.remove('bg-[#7C3AED]', 'text-white');
      startBtn.classList.add('bg-[#FDBA17]', 'text-[#2046B3]');
      completeBtn.style.display = '';
      completeBtn.disabled = false;
      showModuleToast('Module started!');
    });

    // Complete button
    completeBtn.addEventListener('click', function () {
      let status = getModuleStatus();
      status[moduleIdx] = 'Completed';
      setModuleStatus(status);
      statusSpan.textContent = 'Completed';
      statusSpan.className = 'text-xs text-green-600 font-bold mb-2 module-status';
      startBtn.textContent = 'Review';
      startBtn.disabled = false;
      startBtn.classList.remove('bg-[#FDBA17]', 'text-[#2046B3]');
      startBtn.classList.add('bg-[#7C3AED]', 'text-white');
      completeBtn.style.display = 'none';
      showModuleToast('Module completed!');
    });
  });

  // Modal logic for module review
  const reviewModal = document.getElementById('module-review-modal');
  const reviewModalTitle = document.getElementById('review-modal-title');
  const reviewModalContent = document.getElementById('review-modal-content');
  const closeReviewModalBtn = document.getElementById('close-review-modal');

  // Day 1 content (HTML)
  const day1Title = 'Day 1: I Am a Child of God';
  const day1Content = `
    <ol class='list-decimal list-inside mb-2'>
      <li><span class='font-semibold'>Get to the knowledge that I am now a child of God.</span></li>
    </ol>
    <div class='text-sm text-[#2046B3] mb-2'>(John 1:12-13 ESV)</div>
    <ol class='list-decimal list-inside mb-2' start='2'>
      <li><span class='font-semibold'>Embrace the new beginnings from my thoughts to my actions that I have been made a new creation in Christ Jesus.</span></li>
    </ol>
    <div class='text-sm text-[#2046B3]'>(2nd Corinthians 5:17)</div>
  `;

  // Only attach to Day 1 Review button (robust)
  const day1Card = document.querySelector('#modules-list > div[data-module="0"]');
  if (day1Card) {
    const reviewBtn = day1Card.querySelector('.module-action');
    // Remove all previous click listeners by replacing the element
    const newReviewBtn = reviewBtn.cloneNode(true);
    reviewBtn.parentNode.replaceChild(newReviewBtn, reviewBtn);
    newReviewBtn.addEventListener('click', function () {
      if (newReviewBtn.textContent.trim().toLowerCase() === 'review') {
        reviewModalTitle.textContent = day1Title;
        reviewModalContent.innerHTML = day1Content;
        reviewModal.classList.remove('hidden');
      }
    });
  }

  // Close modal logic
  if (closeReviewModalBtn) {
    closeReviewModalBtn.addEventListener('click', function () {
      reviewModal.classList.add('hidden');
    });
  }
  if (reviewModal) {
    reviewModal.addEventListener('click', function (e) {
      if (e.target === reviewModal) {
        reviewModal.classList.add('hidden');
      }
    });
  }
}); 

// === Modal Keyboard Navigation & Swipe ===
(function setupGalleryModalNavigation() {
  const modal = document.getElementById('gallery-modal');
  if (!modal) return;
  // Keyboard navigation
  document.addEventListener('keydown', function(e) {
    if (modal.classList.contains('hidden')) return;
    if (e.key === 'ArrowLeft') {
      if (currentIndex > 0) { currentIndex--; showModalImage(); }
      e.preventDefault();
    } else if (e.key === 'ArrowRight') {
      if (currentIndex < filteredImages.length - 1) { currentIndex++; showModalImage(); }
      e.preventDefault();
    } else if (e.key === 'Escape') {
      modal.classList.add('hidden');
      e.preventDefault();
    }
  });
  // Swipe support
  let startX = null;
  modal.addEventListener('touchstart', function(e) {
    if (e.touches.length === 1) startX = e.touches[0].clientX;
  });
  modal.addEventListener('touchend', function(e) {
    if (startX === null) return;
    const endX = e.changedTouches[0].clientX;
    const diff = endX - startX;
    if (Math.abs(diff) > 50) {
      if (diff > 0 && currentIndex > 0) { currentIndex--; showModalImage(); }
      else if (diff < 0 && currentIndex < filteredImages.length - 1) { currentIndex++; showModalImage(); }
    }
    startX = null;
  });
})(); 

// === Gallery Infinite Scroll ===
(function setupGalleryInfiniteScroll() {
  let allImages = [];
  let displayedImages = [];
  let currentBatch = 0;
  const batchSize = 12;
  let isLoading = false;
  
  // Load initial batch
  function loadInitialImages() {
    fetch('gallery.json')
      .then(res => res.json())
      .then(data => {
        allImages = data.images || [];
        loadNextBatch();
      })
      .catch(err => {
        console.error('Failed to load gallery images:', err);
        document.getElementById('gallery-list').innerHTML = '<div class="text-center py-12"><p class="text-gray-500">Failed to load images. Please refresh the page.</p></div>';
      });
  }
  
  // Load next batch of images
  function loadNextBatch() {
    if (isLoading) return;
    
    const startIndex = currentBatch * batchSize;
    const endIndex = startIndex + batchSize;
    const newImages = allImages.slice(startIndex, endIndex);
    
    if (newImages.length === 0) {
      // No more images to load
      const loadMoreBtn = document.getElementById('load-more-btn');
      if (loadMoreBtn) loadMoreBtn.style.display = 'none';
      return;
    }
    
    isLoading = true;
    showLoadingIndicator();
    
    // Simulate loading delay for better UX
    setTimeout(() => {
      displayedImages = [...displayedImages, ...newImages];
      renderGalleryImages(displayedImages);
      currentBatch++;
      isLoading = false;
      hideLoadingIndicator();
      
      // Check if we need to load more automatically
      if (shouldAutoLoadMore()) {
        loadNextBatch();
      }
    }, 500);
  }
  
  // Check if we should auto-load more images
  function shouldAutoLoadMore() {
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const windowHeight = window.innerHeight;
    const documentHeight = document.documentElement.scrollHeight;
    
    // Load more when user is 200px from bottom
    return (scrollTop + windowHeight + 200) >= documentHeight;
  }
  
  // Show loading indicator
  function showLoadingIndicator() {
    let indicator = document.getElementById('gallery-loading');
    if (!indicator) {
      indicator = document.createElement('div');
      indicator.id = 'gallery-loading';
      indicator.className = 'flex justify-center items-center py-8';
      indicator.innerHTML = `
        <div class="flex items-center gap-3 text-[#7C3AED]">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#7C3AED]"></div>
          <span class="text-lg font-semibold">Loading more images...</span>
        </div>
      `;
      document.getElementById('gallery-list').appendChild(indicator);
    }
    indicator.style.display = 'flex';
  }
  
  // Hide loading indicator
  function hideLoadingIndicator() {
    const indicator = document.getElementById('gallery-loading');
    if (indicator) {
      indicator.style.display = 'none';
    }
  }
  
  // Render gallery images with infinite scroll support
  function renderGalleryImages(images) {
    const galleryList = document.getElementById('gallery-list');
    if (!galleryList) return;
    
    // Remove loading indicator temporarily
    const loadingIndicator = document.getElementById('gallery-loading');
    if (loadingIndicator) loadingIndicator.remove();
    
    galleryList.innerHTML = images.map((image, index) => `
      <div class="group relative overflow-hidden rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-105 cursor-pointer" 
           onclick="openGalleryModal(${index})" 
           data-album="${image.album || 'General'}" 
           data-tags="${(image.tags || []).join(' ')}"
           data-year="${image.year || ''}">
        <img src="${image.src}" alt="${image.alt || 'Gallery Image'}" 
             class="w-full h-64 object-cover transition-transform duration-300 group-hover:scale-110" 
             loading="lazy" />
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <div class="absolute bottom-0 left-0 right-0 p-4 text-white transform translate-y-full group-hover:translate-y-0 transition-transform duration-300">
          <h3 class="font-bold text-lg mb-1">${image.title || 'Untitled'}</h3>
          <p class="text-sm opacity-90">${image.description || ''}</p>
          <div class="flex gap-2 mt-2">
            ${(image.tags || []).map(tag => `<span class="px-2 py-1 bg-white/20 rounded-full text-xs">${tag}</span>`).join('')}
          </div>
        </div>
      </div>
    `).join('');
    
    // Re-add loading indicator if it existed
    if (loadingIndicator) {
      galleryList.appendChild(loadingIndicator);
    }
  }
  
  // Scroll event listener for infinite scroll
  function handleScroll() {
    if (shouldAutoLoadMore() && !isLoading && allImages.length > displayedImages.length) {
      loadNextBatch();
    }
  }
  
  // Initialize infinite scroll
  function initInfiniteScroll() {
    loadInitialImages();
    window.addEventListener('scroll', handleScroll);
    
    // Add manual load more button for accessibility
    const loadMoreBtn = document.createElement('button');
    loadMoreBtn.id = 'load-more-btn';
    loadMoreBtn.className = 'w-full py-4 bg-[#7C3AED] text-white font-bold rounded-xl hover:bg-[#FDBA17] hover:text-[#2046B3] transition-colors mt-8';
    loadMoreBtn.textContent = 'Load More Images';
    loadMoreBtn.onclick = loadNextBatch;
    
    const galleryContainer = document.getElementById('gallery-list');
    if (galleryContainer) {
      galleryContainer.parentNode.insertBefore(loadMoreBtn, galleryContainer.nextSibling);
    }
  }
  
  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initInfiniteScroll);
  } else {
    initInfiniteScroll();
  }
})(); 

 

// === Advanced Filtering & Analytics ===
(function setupAdvancedFilters() {
  let currentFilters = {
    album: '',
    tags: [],
    year: '',
    dateFrom: '',
    dateTo: '',
    size: '',
    uploadDate: '',
    views: ''
  };
  
  // Toggle filter sections
  window.toggleFilterSection = function(sectionId) {
    const section = document.getElementById(sectionId);
    const icon = document.getElementById(sectionId + '-icon');
    
    if (section && icon) {
      const isHidden = section.classList.contains('hidden');
      section.classList.toggle('hidden');
      icon.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
    }
  };
  
  // Apply filters
  function applyFilters() {
    const filteredImages = allImages.filter(image => {
      // Album filter
      if (currentFilters.album && image.album !== currentFilters.album) return false;
      
      // Tags filter
      if (currentFilters.tags.length > 0) {
        const imageTags = image.tags || [];
        if (!currentFilters.tags.some(tag => imageTags.includes(tag))) return false;
      }
      
      // Year filter
      if (currentFilters.year && image.year !== currentFilters.year) return false;
      
      // Date range filter
      if (currentFilters.dateFrom || currentFilters.dateTo) {
        const imageDate = new Date(image.uploadDate || image.date);
        if (currentFilters.dateFrom && imageDate < new Date(currentFilters.dateFrom)) return false;
        if (currentFilters.dateTo && imageDate > new Date(currentFilters.dateTo)) return false;
      }
      
      // Size filter (simulated)
      if (currentFilters.size) {
        const size = getImageSize(image);
        if (currentFilters.size === 'small' && size >= 1024 * 1024) return false;
        if (currentFilters.size === 'medium' && (size < 1024 * 1024 || size > 5 * 1024 * 1024)) return false;
        if (currentFilters.size === 'large' && size <= 5 * 1024 * 1024) return false;
      }
      
      // Upload date filter
      if (currentFilters.uploadDate) {
        const imageDate = new Date(image.uploadDate || image.date);
        const now = new Date();
        const diffDays = Math.floor((now - imageDate) / (1000 * 60 * 60 * 24));
        
        if (currentFilters.uploadDate === 'today' && diffDays > 0) return false;
        if (currentFilters.uploadDate === 'week' && diffDays > 7) return false;
        if (currentFilters.uploadDate === 'month' && diffDays > 30) return false;
        if (currentFilters.uploadDate === 'year' && diffDays > 365) return false;
      }
      
      // Views filter
      if (currentFilters.views) {
        const views = image.views || 0;
        if (currentFilters.views === 'popular' && views < 100) return false;
        if (currentFilters.views === 'trending' && views < 50) return false;
        if (currentFilters.views === 'new' && views >= 10) return false;
      }
      
      return true;
    });
    
    // Reset infinite scroll with filtered results
    displayedImages = [];
    currentBatch = 0;
    allImages = filteredImages;
    loadNextBatch();
  }
  
  // Get simulated image size
  function getImageSize(image) {
    // Simulate file size based on image properties
    return Math.random() * 5 * 1024 * 1024 + 1024 * 1024; // 1-6MB
  }
  
  // Initialize filter event listeners
  function initFilters() {
    // Album filter
    const albumSelect = document.getElementById('album-filter');
    if (albumSelect) {
      albumSelect.addEventListener('change', (e) => {
        currentFilters.album = e.target.value;
        applyFilters();
      });
    }
    
    // Tags filter
    const tagButtons = document.querySelectorAll('.tag-filter');
    tagButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        const tag = btn.dataset.tag;
        if (currentFilters.tags.includes(tag)) {
          currentFilters.tags = currentFilters.tags.filter(t => t !== tag);
          btn.classList.remove('bg-[#7C3AED]', 'text-white');
          btn.classList.add('bg-gray-200', 'text-gray-700');
        } else {
          currentFilters.tags.push(tag);
          btn.classList.add('bg-[#7C3AED]', 'text-white');
          btn.classList.remove('bg-gray-200', 'text-gray-700');
        }
        applyFilters();
      });
    });
    
    // Year filter
    const yearSelect = document.getElementById('year-filter');
    if (yearSelect) {
      yearSelect.addEventListener('change', (e) => {
        currentFilters.year = e.target.value;
        applyFilters();
      });
    }
    
    // Advanced filters
    const dateFrom = document.getElementById('filter-date-from');
    const dateTo = document.getElementById('filter-date-to');
    const sizeFilter = document.getElementById('filter-size');
    const uploadDateFilter = document.getElementById('filter-upload-date');
    const viewsFilter = document.getElementById('filter-views');
    const clearFiltersBtn = document.getElementById('clear-filters');
    
    if (dateFrom) {
      dateFrom.addEventListener('change', (e) => {
        currentFilters.dateFrom = e.target.value;
        applyFilters();
      });
    }
    
    if (dateTo) {
      dateTo.addEventListener('change', (e) => {
        currentFilters.dateTo = e.target.value;
        applyFilters();
      });
    }
    
    if (sizeFilter) {
      sizeFilter.addEventListener('change', (e) => {
        currentFilters.size = e.target.value;
        applyFilters();
      });
    }
    
    if (uploadDateFilter) {
      uploadDateFilter.addEventListener('change', (e) => {
        currentFilters.uploadDate = e.target.value;
        applyFilters();
      });
    }
    
    if (viewsFilter) {
      viewsFilter.addEventListener('change', (e) => {
        currentFilters.views = e.target.value;
        applyFilters();
      });
    }
    
    if (clearFiltersBtn) {
      clearFiltersBtn.addEventListener('click', () => {
        // Reset all filters
        currentFilters = {
          album: '',
          tags: [],
          year: '',
          dateFrom: '',
          dateTo: '',
          size: '',
          uploadDate: '',
          views: ''
        };
        
        // Reset UI
        if (albumSelect) albumSelect.value = '';
        if (yearSelect) yearSelect.value = '';
        if (dateFrom) dateFrom.value = '';
        if (dateTo) dateTo.value = '';
        if (sizeFilter) sizeFilter.value = '';
        if (uploadDateFilter) uploadDateFilter.value = '';
        if (viewsFilter) viewsFilter.value = '';
        
        // Reset tag buttons
        tagButtons.forEach(btn => {
          btn.classList.remove('bg-[#7C3AED]', 'text-white');
          btn.classList.add('bg-gray-200', 'text-gray-700');
        });
        
        // Reload original images
        loadInitialImages();
      });
    }
  }
  
  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFilters);
  } else {
    initFilters();
  }
})();

// === Gallery Analytics ===
(function setupGalleryAnalytics() {
  // Track image views
  function trackImageView(imageId) {
    const image = allImages.find(img => img.id === imageId);
    if (image) {
      image.views = (image.views || 0) + 1;
      updateAnalytics();
    }
  }
  
  // Track favorites
  function toggleFavorite(imageId) {
    const image = allImages.find(img => img.id === imageId);
    if (image) {
      image.favorited = !image.favorited;
      image.favorites = image.favorited ? (image.favorites || 0) + 1 : Math.max(0, (image.favorites || 0) - 1);
      updateAnalytics();
    }
  }
  
  // Update analytics display
  function updateAnalytics() {
    const totalViews = allImages.reduce((sum, img) => sum + (img.views || 0), 0);
    const totalFavorites = allImages.reduce((sum, img) => sum + (img.favorites || 0), 0);
    const totalImages = allImages.length;
    
    // Update stats in sidebar
    const statsContainer = document.querySelector('.gallery-stats');
    if (statsContainer) {
      const viewsElement = statsContainer.querySelector('#stat-total-views');
      const favoritesElement = statsContainer.querySelector('#stat-total-favorites');
      const imagesElement = statsContainer.querySelector('#stat-total-images');
      
      if (viewsElement) viewsElement.textContent = totalViews.toLocaleString();
      if (favoritesElement) favoritesElement.textContent = totalFavorites.toLocaleString();
      if (imagesElement) imagesElement.textContent = totalImages.toLocaleString();
    }
  }
  
  // Make analytics functions global
  window.trackImageView = trackImageView;
  window.toggleFavorite = toggleFavorite;
  
  // Initialize analytics
  function initAnalytics() {
    updateAnalytics();
  }
  
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAnalytics);
  } else {
    initAnalytics();
  }
})();

// === Image Download ===
(function setupImageDownload() {
  // Download image function
  function downloadImage(imageSrc, imageTitle) {
    const link = document.createElement('a');
    link.href = imageSrc;
    link.download = imageTitle || 'gallery-image';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
  
  // Add download button to modal
  function addDownloadButton() {
    const modal = document.getElementById('gallery-modal');
    if (modal) {
      const downloadBtn = document.createElement('button');
      downloadBtn.className = 'absolute top-4 right-16 bg-[#7C3AED] text-white p-2 rounded-full hover:bg-[#FDBA17] hover:text-[#2046B3] transition';
      downloadBtn.innerHTML = `
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/>
        </svg>
      `;
      downloadBtn.onclick = () => {
        const currentImage = displayedImages[currentIndex];
        if (currentImage) {
          downloadImage(currentImage.src, currentImage.title || 'gallery-image');
        }
      };
      modal.appendChild(downloadBtn);
    }
  }
  
  // Initialize download functionality
  function initDownload() {
    // Add download button when modal opens
    const originalOpenModal = window.openGalleryModal;
    window.openGalleryModal = function(index) {
      originalOpenModal(index);
      setTimeout(addDownloadButton, 100);
    };
  }
  
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDownload);
  } else {
    initDownload();
  }
})();

// Render album filter buttons dynamically
function renderAlbumFilters(albums) {
  const albumContainer = document.getElementById('gallery-albums');
  if (!albumContainer) return;
  albumContainer.innerHTML = '';
  // Add 'All' button
  const allBtn = document.createElement('button');
  allBtn.className = 'album-filter-btn px-3 py-1 rounded-lg bg-gray-100 text-[#7C3AED] font-bold hover:bg-[#7C3AED] hover:text-white transition';
  allBtn.setAttribute('data-value', '');
  allBtn.textContent = 'All';
  albumContainer.appendChild(allBtn);
  // Add 'Favorites' album button
  const favBtn = document.createElement('button');
  favBtn.className = 'album-filter-btn px-3 py-1 rounded-lg bg-[#FDBA17]/80 text-[#2046B3] font-bold hover:bg-[#FDBA17] hover:text-[#2046B3] transition flex items-center gap-1';
  favBtn.setAttribute('data-value', '__favorites__');
  favBtn.innerHTML = '<svg class="w-4 h-4 text-[#FDBA17]" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.967a1 1 0 00.95.69h4.18c.969 0 1.371 1.24.588 1.81l-3.388 2.46a1 1 0 00-.364 1.118l1.287 3.966c.3.922-.755 1.688-1.54 1.118l-3.388-2.46a1 1 0 00-1.175 0l-3.388 2.46c-.784.57-1.838-.196-1.54-1.118l1.287-3.966a1 1 0 00-.364-1.118l-3.388-2.46c-.783-.57-.38-1.81.588-1.81h4.18a1 1 0 00.95-.69l1.286-3.967z"/></svg>Favorites';
  albumContainer.appendChild(favBtn);
  // Add album buttons
  albums.forEach(album => {
    const btn = document.createElement('button');
    btn.className = 'album-filter-btn px-3 py-1 rounded-lg bg-gray-100 text-[#7C3AED] font-bold hover:bg-[#7C3AED] hover:text-white transition';
    btn.setAttribute('data-value', album);
    btn.textContent = album;
    albumContainer.appendChild(btn);
  });
}
// Call this function after loading images:
// renderAlbumFilters([...new Set(images.map(img => img.album).filter(Boolean))]);

// Render tag filter buttons dynamically
function renderTagFilters(tags) {
  const tagContainer = document.getElementById('gallery-tags');
  if (!tagContainer) return;
  tagContainer.innerHTML = '';
  // Add 'All' button
  const allBtn = document.createElement('button');
  allBtn.className = 'tag-filter-btn px-3 py-1 rounded-lg bg-gray-100 text-[#FDBA17] font-bold hover:bg-[#FDBA17] hover:text-white transition';
  allBtn.setAttribute('data-value', '');
  allBtn.textContent = 'All';
  tagContainer.appendChild(allBtn);
  // Add tag buttons
  tags.forEach(tag => {
    const btn = document.createElement('button');
    btn.className = 'tag-filter-btn px-3 py-1 rounded-lg bg-gray-100 text-[#FDBA17] font-bold hover:bg-[#FDBA17] hover:text-white transition';
    btn.setAttribute('data-value', tag);
    btn.textContent = tag;
    tagContainer.appendChild(btn);
  });
}
// Call this function after loading images:
// renderTagFilters([...new Set(images.flatMap(img => img.tags || []).filter(Boolean))]);

// Render year filter buttons dynamically
function renderYearFilters(years) {
  const yearContainer = document.getElementById('gallery-years');
  if (!yearContainer) return;
  yearContainer.innerHTML = '';
  // Add 'All' button
  const allBtn = document.createElement('button');
  allBtn.className = 'year-filter-btn px-3 py-1 rounded-lg bg-gray-100 text-[#2046B3] font-bold hover:bg-[#2046B3] hover:text-white transition';
  allBtn.setAttribute('data-value', '');
  allBtn.textContent = 'All';
  yearContainer.appendChild(allBtn);
  // Add year buttons
  years.forEach(year => {
    const btn = document.createElement('button');
    btn.className = 'year-filter-btn px-3 py-1 rounded-lg bg-gray-100 text-[#2046B3] font-bold hover:bg-[#2046B3] hover:text-white transition';
    btn.setAttribute('data-value', year);
    btn.textContent = year;
    yearContainer.appendChild(btn);
  });
}
// Call this function after loading images:
// renderYearFilters([...new Set(images.map(img => img.year).filter(Boolean))]);

// Add visual feedback for favoriting
function animateFavoriteStar(starBtn) {
  if (!starBtn) return;
  starBtn.classList.add('animate-fav-pop');
  setTimeout(() => starBtn.classList.remove('animate-fav-pop'), 400);
}
function showFavoriteToast(msg) {
  const toast = document.createElement('div');
  toast.className = 'fixed top-6 right-6 z-50 bg-[#FDBA17] text-[#2046B3] font-bold px-6 py-3 rounded-lg shadow-lg animate-fade-in';
  toast.textContent = msg;
  document.body.appendChild(toast);
  setTimeout(() => { toast.classList.add('opacity-0'); }, 1200);
  setTimeout(() => { toast.remove(); }, 1600);
}
// Add CSS for pop animation
const favPopStyle = document.createElement('style');
favPopStyle.innerHTML = `
@keyframes fav-pop { 0% { transform: scale(1); } 50% { transform: scale(1.4) rotate(-10deg); } 70% { transform: scale(0.9) rotate(8deg); } 100% { transform: scale(1); } }
.animate-fav-pop { animation: fav-pop 0.4s cubic-bezier(.4,0,.2,1); }
@keyframes fade-in { from { opacity: 0; transform: translateY(-10px);} to { opacity: 1; transform: none; } }
.animate-fade-in { animation: fade-in 0.3s; }
`;
document.head.appendChild(favPopStyle);
// Patch favorite toggle logic in gallery
const origToggleFavorite = window.toggleFavorite;
window.toggleFavorite = function(imageId) {
  origToggleFavorite(imageId);
  // Animate star in grid
  const starBtn = document.querySelector(`.star-btn[data-id='${imageId}']`);
  animateFavoriteStar(starBtn);
  // Animate star in modal
  const modalStar = document.getElementById('modal-fav-star');
  animateFavoriteStar(modalStar);
  // Show toast
  const isNowFav = isFavorite(imageId);
  showFavoriteToast(isNowFav ? 'Added to Favorites!' : 'Removed from Favorites');
};

// === Enhanced Advanced Filtering System ===
(function setupAdvancedFilters() {
  let advancedFilters = {
    dateFrom: '',
    dateTo: '',
    size: '',
    uploadDate: '',
    views: '',
    orientation: '',
    colorFilter: '',
    resolution: '',
    favoritesOnly: false
  };
  
  // Toggle filter sections with smooth animation
  window.toggleFilterSection = function(sectionId) {
    const section = document.getElementById(sectionId);
    const icon = document.getElementById(sectionId + '-icon');
    
    if (section && icon) {
      const isHidden = section.classList.contains('hidden');
      section.classList.toggle('hidden');
      icon.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
      
      // Add visual feedback
      if (!isHidden) {
        section.style.maxHeight = '0';
        section.style.overflow = 'hidden';
        section.style.transition = 'max-height 0.3s ease-out';
        setTimeout(() => {
          section.style.maxHeight = '';
          section.style.overflow = '';
          section.style.transition = '';
        }, 300);
      }
    }
  };
  
  // Enhanced apply filters function
  function applyAdvancedFilters() {
    console.log('[Advanced Filters] Applying filters:', advancedFilters);
    
    // Get current filtered images from the main gallery system
    let imagesToFilter = filteredImages || allImages;
    
    const filteredResults = imagesToFilter.filter(image => {
      // Date range filter
      if (advancedFilters.dateFrom || advancedFilters.dateTo) {
        const imageDate = new Date(image.uploadDate || image.date || image.timestamp);
        if (advancedFilters.dateFrom && imageDate < new Date(advancedFilters.dateFrom)) return false;
        if (advancedFilters.dateTo && imageDate > new Date(advancedFilters.dateTo)) return false;
      }
      
      // File size filter (simulated)
      if (advancedFilters.size) {
        const size = getImageSize(image);
        if (advancedFilters.size === 'small' && size >= 1024 * 1024) return false;
        if (advancedFilters.size === 'medium' && (size < 1024 * 1024 || size > 5 * 1024 * 1024)) return false;
        if (advancedFilters.size === 'large' && size <= 5 * 1024 * 1024) return false;
      }
      
      // Upload date filter
      if (advancedFilters.uploadDate) {
        const imageDate = new Date(image.uploadDate || image.date || image.timestamp);
        const now = new Date();
        const diffDays = Math.floor((now - imageDate) / (1000 * 60 * 60 * 24));
        
        if (advancedFilters.uploadDate === 'today' && diffDays > 0) return false;
        if (advancedFilters.uploadDate === 'week' && diffDays > 7) return false;
        if (advancedFilters.uploadDate === 'month' && diffDays > 30) return false;
        if (advancedFilters.uploadDate === 'year' && diffDays > 365) return false;
      }
      
      // Views filter
      if (advancedFilters.views) {
        const views = image.views || 0;
        if (advancedFilters.views === 'popular' && views < 100) return false;
        if (advancedFilters.views === 'trending' && views < 50) return false;
        if (advancedFilters.views === 'new' && views >= 10) return false;
      }
      
      // Orientation filter
      if (advancedFilters.orientation) {
        const aspectRatio = image.width && image.height ? image.width / image.height : 1;
        if (advancedFilters.orientation === 'landscape' && aspectRatio <= 1) return false;
        if (advancedFilters.orientation === 'portrait' && aspectRatio >= 1) return false;
        if (advancedFilters.orientation === 'square' && Math.abs(aspectRatio - 1) > 0.1) return false;
      }
      
      // Color filter (simulated based on tags or metadata)
      if (advancedFilters.colorFilter) {
        const imageColors = image.colors || image.tags || [];
        const colorMatch = imageColors.some(color => 
          color.toLowerCase().includes(advancedFilters.colorFilter.toLowerCase())
        );
        if (!colorMatch) return false;
      }
      
      // Resolution filter
      if (advancedFilters.resolution) {
        const resolution = image.width && image.height ? image.width * image.height : 0;
        if (advancedFilters.resolution === 'hd' && resolution < 1280 * 720) return false;
        if (advancedFilters.resolution === 'fullhd' && resolution < 1920 * 1080) return false;
        if (advancedFilters.resolution === '4k' && resolution < 3840 * 2160) return false;
      }
      
      // Favorites only filter
      if (advancedFilters.favoritesOnly) {
        const favorites = getFavorites();
        if (!favorites.includes(image.id)) return false;
      }
      
      return true;
    });
    
    console.log('[Advanced Filters] Filtered results:', filteredResults.length);
    
    // Update the gallery with filtered results
    if (typeof renderGallery === 'function') {
      renderGallery(filteredResults);
    }
    
    // Update analytics with filtered results
    if (typeof updateGalleryAnalytics === 'function') {
      const albums = {};
      filteredResults.forEach(img => {
        if (!albums[img.album]) albums[img.album] = [];
        albums[img.album].push(img);
      });
      updateGalleryAnalytics(filteredResults, albums);
    }
    
    // Show filter status
    showFilterStatus(filteredResults.length, imagesToFilter.length);
  }
  
  // Get simulated image size
  function getImageSize(image) {
    // Simulate file size based on image properties
    const baseSize = 1024 * 1024; // 1MB base
    const randomFactor = 0.5 + Math.random() * 4; // 0.5x to 4.5x
    return Math.floor(baseSize * randomFactor);
  }
  
  // Show filter status with visual feedback
  function showFilterStatus(filteredCount, totalCount) {
    let statusElement = document.getElementById('filter-status');
    if (!statusElement) {
      statusElement = document.createElement('div');
      statusElement.id = 'filter-status';
      statusElement.className = 'fixed top-4 right-4 bg-[#7C3AED] text-white px-4 py-2 rounded-lg shadow-lg z-50 transform transition-all duration-300 flex items-center gap-2';
      document.body.appendChild(statusElement);
    }
    
    const hasActiveFilters = Object.values(advancedFilters).some(value => 
      value !== '' && value !== false && (Array.isArray(value) ? value.length > 0 : true)
    );
    
    if (hasActiveFilters) {
      statusElement.innerHTML = `
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
        </svg>
        <span>${filteredCount} of ${totalCount} images</span>
        <button onclick="clearAllAdvancedFilters()" class="ml-2 text-xs bg-white/20 hover:bg-white/30 rounded px-2 py-1 transition">
          Clear
        </button>
      `;
      statusElement.style.display = 'flex';
    } else {
      statusElement.style.display = 'none';
    }
    
    // Auto-hide after 5 seconds if no active filters
    if (!hasActiveFilters) {
      setTimeout(() => {
        statusElement.style.transform = 'translateX(100%)';
        setTimeout(() => {
          statusElement.style.display = 'none';
          statusElement.style.transform = 'translateX(0)';
        }, 300);
      }, 5000);
    }
  }
  
  // Initialize enhanced filter event listeners
  function initAdvancedFilters() {
    console.log('[Advanced Filters] Initializing...');
    
    // Date range filters
    const dateFrom = document.getElementById('filter-date-from');
    const dateTo = document.getElementById('filter-date-to');
    
    if (dateFrom) {
      dateFrom.addEventListener('change', (e) => {
        advancedFilters.dateFrom = e.target.value;
        applyAdvancedFilters();
      });
    }
    
    if (dateTo) {
      dateTo.addEventListener('change', (e) => {
        advancedFilters.dateTo = e.target.value;
        applyAdvancedFilters();
      });
    }
    
    // File size filter
    const sizeFilter = document.getElementById('filter-size');
    if (sizeFilter) {
      sizeFilter.addEventListener('change', (e) => {
        advancedFilters.size = e.target.value;
        applyAdvancedFilters();
      });
    }
    
    // Upload date filter
    const uploadDateFilter = document.getElementById('filter-upload-date');
    if (uploadDateFilter) {
      uploadDateFilter.addEventListener('change', (e) => {
        advancedFilters.uploadDate = e.target.value;
        applyAdvancedFilters();
      });
    }
    
    // Views filter
    const viewsFilter = document.getElementById('filter-views');
    if (viewsFilter) {
      viewsFilter.addEventListener('change', (e) => {
        advancedFilters.views = e.target.value;
        applyAdvancedFilters();
      });
    }
    
    // Quick filter buttons
    setupQuickFilters();
    
    // Add new filter options
    addNewFilterOptions();
    
    // Clear filters button
    const clearFiltersBtn = document.getElementById('clear-filters');
    if (clearFiltersBtn) {
      clearFiltersBtn.addEventListener('click', clearAllAdvancedFilters);
    }
    
    console.log('[Advanced Filters] Initialized successfully');
  }
  
  // Setup quick filter buttons
  function setupQuickFilters() {
    const quickFilters = {
      'filter-recent': () => {
        // Set upload date to this month
        advancedFilters.uploadDate = 'month';
        document.getElementById('filter-upload-date').value = 'month';
        applyAdvancedFilters();
      },
      'filter-popular': () => {
        // Set views to popular
        advancedFilters.views = 'popular';
        document.getElementById('filter-views').value = 'popular';
        applyAdvancedFilters();
      },
      'filter-favorites': () => {
        // Set favorites only
        advancedFilters.favoritesOnly = true;
        document.getElementById('filter-favorites-only').checked = true;
        applyAdvancedFilters();
      },
      'filter-large': () => {
        // Set size to large
        advancedFilters.size = 'large';
        document.getElementById('filter-size').value = 'large';
        applyAdvancedFilters();
      }
    };
    
    Object.entries(quickFilters).forEach(([id, handler]) => {
      const button = document.getElementById(id);
      if (button) {
        button.addEventListener('click', handler);
      }
    });
  }
  
  // Add new filter options to the HTML
  function addNewFilterOptions() {
    const advancedFiltersSection = document.getElementById('advanced-filters');
    if (!advancedFiltersSection) return;
    
    // Add orientation filter
    const orientationDiv = document.createElement('div');
    orientationDiv.innerHTML = `
      <label class="block text-sm font-semibold text-gray-700 mb-2">Orientation</label>
      <select id="filter-orientation" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#7C3AED] focus:outline-none">
        <option value="">Any Orientation</option>
        <option value="landscape">Landscape</option>
        <option value="portrait">Portrait</option>
        <option value="square">Square</option>
      </select>
    `;
    advancedFiltersSection.appendChild(orientationDiv);
    
    // Add color filter
    const colorDiv = document.createElement('div');
    colorDiv.innerHTML = `
      <label class="block text-sm font-semibold text-gray-700 mb-2">Color Theme</label>
      <select id="filter-color" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#7C3AED] focus:outline-none">
        <option value="">Any Color</option>
        <option value="warm">Warm Colors</option>
        <option value="cool">Cool Colors</option>
        <option value="bright">Bright Colors</option>
        <option value="dark">Dark Colors</option>
      </select>
    `;
    advancedFiltersSection.appendChild(colorDiv);
    
    // Add resolution filter
    const resolutionDiv = document.createElement('div');
    resolutionDiv.innerHTML = `
      <label class="block text-sm font-semibold text-gray-700 mb-2">Resolution</label>
      <select id="filter-resolution" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#7C3AED] focus:outline-none">
        <option value="">Any Resolution</option>
        <option value="hd">HD (720p+)</option>
        <option value="fullhd">Full HD (1080p+)</option>
        <option value="4k">4K (2160p+)</option>
      </select>
    `;
    advancedFiltersSection.appendChild(resolutionDiv);
    
    // Add favorites only checkbox
    const favoritesDiv = document.createElement('div');
    favoritesDiv.innerHTML = `
      <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
        <input type="checkbox" id="filter-favorites-only" class="rounded border-gray-300 text-[#7C3AED] focus:ring-[#7C3AED]">
        Show Favorites Only
      </label>
    `;
    advancedFiltersSection.appendChild(favoritesDiv);
    
    // Add event listeners for new filters
    const orientationFilter = document.getElementById('filter-orientation');
    const colorFilter = document.getElementById('filter-color');
    const resolutionFilter = document.getElementById('filter-resolution');
    const favoritesOnlyFilter = document.getElementById('filter-favorites-only');
    
    if (orientationFilter) {
      orientationFilter.addEventListener('change', (e) => {
        advancedFilters.orientation = e.target.value;
        applyAdvancedFilters();
      });
    }
    
    if (colorFilter) {
      colorFilter.addEventListener('change', (e) => {
        advancedFilters.colorFilter = e.target.value;
        applyAdvancedFilters();
      });
    }
    
    if (resolutionFilter) {
      resolutionFilter.addEventListener('change', (e) => {
        advancedFilters.resolution = e.target.value;
        applyAdvancedFilters();
      });
    }
    
    if (favoritesOnlyFilter) {
      favoritesOnlyFilter.addEventListener('change', (e) => {
        advancedFilters.favoritesOnly = e.target.checked;
        applyAdvancedFilters();
      });
    }
  }
  
  // Clear all advanced filters
  function clearAllAdvancedFilters() {
    console.log('[Advanced Filters] Clearing all filters');
    
    // Reset filter object
    advancedFilters = {
      dateFrom: '',
      dateTo: '',
      size: '',
      uploadDate: '',
      views: '',
      orientation: '',
      colorFilter: '',
      resolution: '',
      favoritesOnly: false
    };
    
    // Reset all form elements
    const filterElements = [
      'filter-date-from', 'filter-date-to', 'filter-size', 
      'filter-upload-date', 'filter-views', 'filter-orientation',
      'filter-color', 'filter-resolution', 'filter-favorites-only'
    ];
    
    filterElements.forEach(id => {
      const element = document.getElementById(id);
      if (element) {
        if (element.type === 'checkbox') {
          element.checked = false;
        } else {
          element.value = '';
        }
      }
    });
    
    // Reload original gallery
    if (typeof filterAndRender === 'function') {
      filterAndRender();
    }
    
    // Show cleared status
    showFilterStatus(allImages.length, allImages.length);
  }
  
  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdvancedFilters);
  } else {
    initAdvancedFilters();
  }
  
  // Make functions globally available
  window.applyAdvancedFilters = applyAdvancedFilters;
  window.clearAllAdvancedFilters = clearAllAdvancedFilters;
})();

// === Real-Time Gallery System ===
(function setupRealTimeGallery() {
  let realTimeInterval = null;
  let lastUpdateTime = Date.now();
  let notificationCount = 0;
  
  // Real-time update intervals (in milliseconds)
  const UPDATE_INTERVALS = {
    events: 30000,    // 30 seconds
    analytics: 60000,  // 1 minute
    notifications: 15000, // 15 seconds
    liveStats: 10000   // 10 seconds
  };
  
  // Initialize real-time system
  function initRealTimeSystem() {
    console.log('[Real-Time] Initializing gallery real-time system...');
    
    // Start real-time updates
    startRealTimeUpdates();
    
    // Setup live notifications
    setupLiveNotifications();
    
    // Setup live analytics
    setupLiveAnalytics();
    
    // Setup live user activity
    setupLiveUserActivity();
    
    // Setup WebSocket-like polling for real-time updates
    setupRealTimePolling();
    
    console.log('[Real-Time] Gallery real-time system initialized');
  }
  
  // Start real-time updates
  function startRealTimeUpdates() {
    // Update current time every minute
    setInterval(updateCurrentTime, 60000);
    
    // Update live stats every 10 seconds
    setInterval(updateLiveStats, UPDATE_INTERVALS.liveStats);
    
    // Check for new events every 30 seconds
    setInterval(checkForNewEvents, UPDATE_INTERVALS.events);
    
    // Update analytics every minute
    setInterval(updateRealTimeAnalytics, UPDATE_INTERVALS.analytics);
    
    // Check for notifications every 15 seconds
    setInterval(checkForNotifications, UPDATE_INTERVALS.notifications);
  }
  
  // Update current time display
  function updateCurrentTime() {
    const now = new Date();
    const options = { 
      year: 'numeric', 
      month: 'long', 
      day: 'numeric', 
      hour: '2-digit', 
      minute: '2-digit',
      timeZoneName: 'short'
    };
    const timeElement = document.getElementById('current-time');
    if (timeElement) {
      timeElement.textContent = now.toLocaleDateString('en-US', options);
    }
  }
  
  // Update live statistics
  function updateLiveStats() {
    const stats = {
      totalImages: allImages.length,
      totalViews: getTotalViews(),
      totalFavorites: getFavorites().length,
      activeUsers: Math.floor(Math.random() * 50) + 10, // Simulated
      recentActivity: getRecentActivity()
    };
    
    // Update stats display if elements exist
    updateStatsDisplay(stats);
    
    // Update live user count
    updateLiveUserCount(stats.activeUsers);
  }
  
  // Get total views from localStorage
  function getTotalViews() {
    const views = JSON.parse(localStorage.getItem('galleryViews') || '{}');
    return Object.values(views).reduce((sum, count) => sum + count, 0);
  }
  
  // Get recent activity
  function getRecentActivity() {
    const activities = JSON.parse(localStorage.getItem('galleryActivity') || '[]');
    return activities.slice(-5); // Last 5 activities
  }
  
  // Update stats display
  function updateStatsDisplay(stats) {
    const elements = {
      'stat-images': stats.totalImages,
      'stat-albums': new Set(allImages.map(img => img.album)).size,
      'stat-tags': new Set(allImages.flatMap(img => img.tags || [])).size,
      'live-views': stats.totalViews,
      'live-favorites': stats.totalFavorites,
      'live-users': stats.activeUsers
    };
    
    Object.entries(elements).forEach(([id, value]) => {
      const element = document.getElementById(id);
      if (element) {
        element.textContent = value.toLocaleString();
      }
    });
  }
  
  // Update live user count
  function updateLiveUserCount(count) {
    let userCountElement = document.getElementById('live-user-count');
    if (!userCountElement) {
      userCountElement = document.createElement('div');
      userCountElement.id = 'live-user-count';
      userCountElement.className = 'fixed bottom-4 left-4 bg-[#7C3AED] text-white px-3 py-2 rounded-lg shadow-lg z-50 flex items-center gap-2 text-sm';
      document.body.appendChild(userCountElement);
    }
    
    userCountElement.innerHTML = `
      <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
      <span>${count} users online</span>
    `;
  }
  
  // Check for new events
  function checkForNewEvents() {
    // Simulate checking for new events
    const hasNewEvents = Math.random() > 0.8; // 20% chance of new event
    
    if (hasNewEvents) {
      showRealTimeNotification('New event added!', 'info');
      updateEventCount();
    }
  }
  
  // Update real-time analytics
  function updateRealTimeAnalytics() {
    if (typeof updateGalleryAnalytics === 'function') {
      const albums = {};
      (filteredImages || allImages).forEach(img => {
        if (!albums[img.album]) albums[img.album] = [];
        albums[img.album].push(img);
      });
      updateGalleryAnalytics(filteredImages || allImages, albums);
    }
  }
  
  // Check for notifications
  function checkForNotifications() {
    // Simulate various notifications
    const notifications = [
      { type: 'view', message: 'Someone viewed your favorite image', probability: 0.3 },
      { type: 'favorite', message: 'New image added to favorites', probability: 0.2 },
      { type: 'upload', message: 'New images uploaded to gallery', probability: 0.1 },
      { type: 'trending', message: 'An image is trending!', probability: 0.15 }
    ];
    
    notifications.forEach(notification => {
      if (Math.random() < notification.probability) {
        showRealTimeNotification(notification.message, notification.type);
      }
    });
  }
  
  // Setup live notifications
  function setupLiveNotifications() {
    // Create notification container
    let notificationContainer = document.getElementById('real-time-notifications');
    if (!notificationContainer) {
      notificationContainer = document.createElement('div');
      notificationContainer.id = 'real-time-notifications';
      notificationContainer.className = 'fixed top-4 left-4 z-50 space-y-2';
      document.body.appendChild(notificationContainer);
    }
  }
  
  // Show real-time notification
  function showRealTimeNotification(message, type = 'info') {
    const notificationId = `notification-${Date.now()}-${notificationCount++}`;
    const notification = document.createElement('div');
    notification.id = notificationId;
    notification.className = `bg-white border-l-4 border-${getNotificationColor(type)} shadow-lg rounded-lg p-4 max-w-sm transform transition-all duration-300 translate-x-full`;
    
    notification.innerHTML = `
      <div class="flex items-start gap-3">
        <div class="flex-shrink-0">
          <svg class="w-5 h-5 text-${getNotificationColor(type)}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            ${getNotificationIcon(type)}
          </svg>
        </div>
        <div class="flex-1">
          <p class="text-sm font-medium text-gray-900">${message}</p>
          <p class="text-xs text-gray-500 mt-1">${new Date().toLocaleTimeString()}</p>
        </div>
        <button onclick="removeNotification('${notificationId}')" class="text-gray-400 hover:text-gray-600">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
    `;
    
    const container = document.getElementById('real-time-notifications');
    if (container) {
      container.appendChild(notification);
      
      // Animate in
      setTimeout(() => {
        notification.classList.remove('translate-x-full');
      }, 100);
      
      // Auto-remove after 5 seconds
      setTimeout(() => {
        removeNotification(notificationId);
      }, 5000);
    }
  }
  
  // Get notification color based on type
  function getNotificationColor(type) {
    const colors = {
      info: '[#7C3AED]',
      success: '[#10B981]',
      warning: '[#F59E0B]',
      error: '[#EF4444]',
      view: '[#3B82F6]',
      favorite: '[#FDBA17]',
      upload: '[#10B981]',
      trending: '[#8B5CF6]'
    };
    return colors[type] || colors.info;
  }
  
  // Get notification icon based on type
  function getNotificationIcon(type) {
    const icons = {
      info: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
      success: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
      warning: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>',
      error: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
      view: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>',
      favorite: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>',
      upload: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>',
      trending: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>'
    };
    return icons[type] || icons.info;
  }
  
  // Remove notification
  window.removeNotification = function(notificationId) {
    const notification = document.getElementById(notificationId);
    if (notification) {
      notification.classList.add('translate-x-full');
      setTimeout(() => {
        notification.remove();
      }, 300);
    }
  };
  
  // Setup live analytics
  function setupLiveAnalytics() {
    // Create live analytics display
    let analyticsDisplay = document.getElementById('live-analytics');
    if (!analyticsDisplay) {
      analyticsDisplay = document.createElement('div');
      analyticsDisplay.id = 'live-analytics';
      analyticsDisplay.className = 'fixed top-4 right-4 bg-white shadow-lg rounded-lg p-4 z-40 max-w-xs';
      analyticsDisplay.innerHTML = `
        <h3 class="text-sm font-bold text-[#7C3AED] mb-2">Live Analytics</h3>
        <div class="space-y-1 text-xs">
          <div class="flex justify-between">
            <span>Total Views:</span>
            <span id="live-total-views">0</span>
          </div>
          <div class="flex justify-between">
            <span>Total Favorites:</span>
            <span id="live-total-favorites">0</span>
          </div>
          <div class="flex justify-between">
            <span>Active Users:</span>
            <span id="live-active-users">0</span>
          </div>
        </div>
      `;
      document.body.appendChild(analyticsDisplay);
    }
    
    // Setup activity feed
    setupActivityFeed();
    
    // Setup live updates
    setupLiveUpdates();
  }
  
  // Setup activity feed
  function setupActivityFeed() {
    const activityFeed = document.getElementById('activity-feed');
    if (!activityFeed) return;
    
    // Generate sample activities
    const activities = [
      { type: 'view', message: 'Someone viewed "Ministry Event 2024"', time: '2 minutes ago', icon: '👁️' },
      { type: 'favorite', message: 'New favorite added to "Youth Ministry"', time: '5 minutes ago', icon: '⭐' },
      { type: 'upload', message: 'New images uploaded to "Events"', time: '8 minutes ago', icon: '📸' },
      { type: 'share', message: 'Gallery shared on social media', time: '12 minutes ago', icon: '📤' },
      { type: 'trending', message: '"Revival Night" is trending!', time: '15 minutes ago', icon: '🔥' }
    ];
    
    // Populate activity feed
    activityFeed.innerHTML = activities.map(activity => `
      <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
        <div class="text-2xl">${activity.icon}</div>
        <div class="flex-1">
          <p class="text-sm font-medium text-gray-900">${activity.message}</p>
          <p class="text-xs text-gray-500">${activity.time}</p>
        </div>
        <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
      </div>
    `).join('');
    
    // Add new activities periodically
    setInterval(() => {
      addNewActivity();
    }, 15000); // Every 15 seconds
  }
  
  // Add new activity to feed
  function addNewActivity() {
    const activityFeed = document.getElementById('activity-feed');
    if (!activityFeed) return;
    
    const newActivities = [
      { type: 'view', message: 'Someone viewed a gallery image', time: 'Just now', icon: '👁️' },
      { type: 'favorite', message: 'New image favorited', time: 'Just now', icon: '⭐' },
      { type: 'share', message: 'Image shared on WhatsApp', time: 'Just now', icon: '📱' },
      { type: 'download', message: 'Image downloaded', time: 'Just now', icon: '⬇️' },
      { type: 'comment', message: 'New comment on gallery', time: 'Just now', icon: '💬' }
    ];
    
    const randomActivity = newActivities[Math.floor(Math.random() * newActivities.length)];
    
    const activityElement = document.createElement('div');
    activityElement.className = 'flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition transform translate-x-full';
    activityElement.innerHTML = `
      <div class="text-2xl">${randomActivity.icon}</div>
      <div class="flex-1">
        <p class="text-sm font-medium text-gray-900">${randomActivity.message}</p>
        <p class="text-xs text-gray-500">${randomActivity.time}</p>
      </div>
      <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
    `;
    
    activityFeed.insertBefore(activityElement, activityFeed.firstChild);
    
    // Animate in
    setTimeout(() => {
      activityElement.classList.remove('translate-x-full');
    }, 100);
    
    // Remove old activities if too many
    const activities = activityFeed.children;
    if (activities.length > 10) {
      activities[activities.length - 1].remove();
    }
  }
  
  // Setup live updates
  function setupLiveUpdates() {
    const liveUpdates = document.getElementById('live-updates');
    if (!liveUpdates) return;
    
    // Initial updates
    const initialUpdates = [
      'Gallery analytics updated',
      'New user joined the gallery',
      'Image view count increased',
      'Favorites system active',
      'Real-time notifications enabled'
    ];
    
    liveUpdates.innerHTML = initialUpdates.map(update => `
      <div class="flex items-center gap-2 text-gray-600">
        <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
        <span>${update}</span>
      </div>
    `).join('');
    
    // Add new updates periodically
    setInterval(() => {
      addNewUpdate();
    }, 10000); // Every 10 seconds
  }
  
  // Add new live update
  function addNewUpdate() {
    const liveUpdates = document.getElementById('live-updates');
    if (!liveUpdates) return;
    
    const updates = [
      'Gallery performance optimized',
      'New filter applied',
      'Image metadata updated',
      'User activity tracked',
      'Analytics refreshed',
      'Cache updated',
      'Search index updated',
      'Mobile view optimized'
    ];
    
    const randomUpdate = updates[Math.floor(Math.random() * updates.length)];
    
    const updateElement = document.createElement('div');
    updateElement.className = 'flex items-center gap-2 text-gray-600 transform translate-x-full';
    updateElement.innerHTML = `
      <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
      <span>${randomUpdate}</span>
    `;
    
    liveUpdates.insertBefore(updateElement, liveUpdates.firstChild);
    
    // Animate in
    setTimeout(() => {
      updateElement.classList.remove('translate-x-full');
    }, 100);
    
    // Remove old updates if too many
    const updatesList = liveUpdates.children;
    if (updatesList.length > 8) {
      updatesList[updatesList.length - 1].remove();
    }
  }
  
  // Setup live user activity
  function setupLiveUserActivity() {
    // Track user activity
    const activityEvents = ['click', 'scroll', 'mousemove'];
    let lastActivity = Date.now();
    
    activityEvents.forEach(event => {
      document.addEventListener(event, () => {
        lastActivity = Date.now();
        // Store activity in localStorage
        const activities = JSON.parse(localStorage.getItem('galleryActivity') || '[]');
        activities.push({
          type: event,
          timestamp: Date.now(),
          page: 'gallery'
        });
        
        // Keep only last 100 activities
        if (activities.length > 100) {
          activities.splice(0, activities.length - 100);
        }
        
        localStorage.setItem('galleryActivity', JSON.stringify(activities));
      });
    });
  }
  
  // Setup real-time polling (simulate WebSocket)
  function setupRealTimePolling() {
    // Poll for updates every 30 seconds
    setInterval(() => {
      // Simulate checking for new content
      const hasUpdates = Math.random() > 0.7; // 30% chance of updates
      
      if (hasUpdates) {
        // Update last update time
        lastUpdateTime = Date.now();
        
        // Show update notification
        showRealTimeNotification('Gallery updated with new content!', 'info');
        
        // Refresh analytics
        updateRealTimeAnalytics();
      }
    }, 30000);
  }
  
  // Update event count
  function updateEventCount() {
    const eventCountElement = document.getElementById('event-count');
    if (eventCountElement) {
      const currentCount = parseInt(eventCountElement.textContent) || 0;
      eventCountElement.textContent = currentCount + 1;
    }
  }
  
  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initRealTimeSystem);
  } else {
    initRealTimeSystem();
  }
  
  // Make functions globally available
  window.showRealTimeNotification = showRealTimeNotification;
  window.updateLiveStats = updateLiveStats;
})();