// Reusable Header Loader
(function() {
  const headerContainer = document.getElementById('site-header');
  if (headerContainer) {
    fetch('header.html')
      .then(res => res.text())
      .then(html => {
        headerContainer.innerHTML = html;
        // Optionally, re-initialize navigation logic here if needed
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