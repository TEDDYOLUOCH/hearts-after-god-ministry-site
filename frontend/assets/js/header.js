// Mobile menu functionality
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileOverlay = document.getElementById('mobile-overlay');
    const closeBtn = document.querySelector('.close-btn');
    let lastFocusedElement = null;

    function openMobileMenu() {
        lastFocusedElement = document.activeElement;
        mobileMenu.classList.remove('translate-x-full');
        mobileMenu.classList.add('translate-x-0');
        mobileOverlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        mobileMenuToggle.setAttribute('aria-expanded', 'true');
        
        // Animate hamburger to X
        document.getElementById('bar1').style.transform = 'rotate(45deg) translateY(10px)';
        document.getElementById('bar2').style.opacity = '0';
        document.getElementById('bar3').style.transform = 'rotate(-45deg) translateY(-10px)';
        
        // Set focus to the first focusable element in the menu
        setTimeout(() => {
            const focusable = mobileMenu.querySelectorAll('a, button');
            if (focusable.length) focusable[0].focus();
        }, 200);
    }

    function closeMobileMenu() {
        mobileMenu.classList.add('translate-x-full');
        mobileMenu.classList.remove('translate-x-0');
        mobileOverlay.classList.add('hidden');
        document.body.style.overflow = '';
        mobileMenuToggle.setAttribute('aria-expanded', 'false');
        
        // Reset hamburger animation
        document.getElementById('bar1').style.transform = '';
        document.getElementById('bar2').style.opacity = '1';
        document.getElementById('bar3').style.transform = '';
        
        // Return focus to the last focused element
        if (lastFocusedElement) lastFocusedElement.focus();
    }

    // Event Listeners
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', openMobileMenu);
    }
    
    if (closeBtn) {
        closeBtn.addEventListener('click', closeMobileMenu);
    }
    
    if (mobileOverlay) {
        mobileOverlay.addEventListener('click', closeMobileMenu);
    }

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (mobileOverlay.classList.contains('hidden')) return;
        
        // Close on Escape key
        if (e.key === 'Escape') {
            closeMobileMenu();
            return;
        }
        
        // Handle tab key for keyboard navigation within the menu
        if (e.key === 'Tab') {
            const focusable = Array.from(mobileMenu.querySelectorAll('a, button'));
            if (!focusable.length) return;
            
            const firstFocusable = focusable[0];
            const lastFocusable = focusable[focusable.length - 1];
            
            if (e.shiftKey) {
                if (document.activeElement === firstFocusable) {
                    e.preventDefault();
                    lastFocusable.focus();
                }
            } else {
                if (document.activeElement === lastFocusable) {
                    e.preventDefault();
                    firstFocusable.focus();
                }
            }
        }
    });

    // Set active navigation link
    function setActiveLinks() {
        let path = window.location.pathname;
        let page = path.split('/').pop() || 'index.html';
        
        // Handle root path
        if (page === '' || page === 'hearts-after-god-ministry-site') {
            page = 'index.html';
        }
        
        document.querySelectorAll('.nav-link').forEach(link => {
            const href = link.getAttribute('href');
            const linkPage = href.split('/').pop();
            
            // Remove all active classes first
            link.classList.remove('active-nav');
            link.classList.remove('bg-[#FFF7CC]', 'text-[#DC2626]');
            
            // Check if current page matches the link
            if (linkPage === page || 
                (page === 'index.html' && (linkPage === '' || linkPage === 'index.html' || linkPage === 'hearts-after-god-ministry-site'))) {
                link.classList.add('active-nav');
                link.classList.add('bg-[#FFF7CC]', 'text-[#DC2626]');
            }
        });
    }
    
    // Initialize active links
    setActiveLinks();
    
    // Update active links when navigating with pushState
    window.addEventListener('popstate', setActiveLinks);
});
