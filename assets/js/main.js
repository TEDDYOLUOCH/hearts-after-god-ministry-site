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
    link.classList.remove('font-bold', 'text-primary');
    // Highlight if matches current page
    const href = link.getAttribute('href');
    if ((path === '' && href === 'index.html') || href === path) {
      link.classList.add('font-bold', 'text-primary');
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