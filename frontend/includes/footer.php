<?php
// includes/footer.php - Enhanced Reusable Footer Component
?>

  </main>
  <!-- End Main Content -->

  <!-- Enhanced Footer -->
  <footer class="bg-gradient-to-br from-gray-900 via-purple-900 to-blue-900 text-white pt-20 pb-8 relative overflow-hidden" role="contentinfo">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
      <div class="absolute inset-0" style="background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.15) 1px, transparent 0); background-size: 20px 20px;"></div>
    </div>
    
    <div class="relative max-w-7xl mx-auto px-4">
      <!-- Main Footer Content -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
        
        <!-- Ministry Info -->
        <div class="lg:col-span-1 space-y-6">
          <div class="flex items-center mb-6">
            <div class="h-20 w-20 rounded-full bg-gradient-to-br from-purple-600 to-amber-500 flex items-center justify-center text-4xl text-white border-4 border-amber-400 mr-4 shadow-2xl">⛪</div>
            <div>
              <h3 class="text-2xl font-bold font-display">Hearts After God</h3>
              <p class="text-amber-400 font-semibold text-sm tracking-widest">MINISTRY</p>
            </div>
          </div>
          
          <p class="text-gray-300 leading-relaxed text-lg">
            Leading revival ministry for soul-winning, discipleship, and global outreach. Join our mission to reach the world for Christ and experience His transforming power.
          </p>
          
          <!-- Ministry Stats -->
          <div class="grid grid-cols-2 gap-4 py-4">
            <div class="text-center bg-white/10 rounded-lg p-3 backdrop-blur-sm">
              <div class="text-2xl font-bold text-amber-400">500+</div>
              <div class="text-xs text-gray-300">Lives Transformed</div>
            </div>
            <div class="text-center bg-white/10 rounded-lg p-3 backdrop-blur-sm">
              <div class="text-2xl font-bold text-amber-400">50+</div>
              <div class="text-xs text-gray-300">Countries Reached</div>
            </div>
          </div>
          
          <!-- Social Media Links with enhanced design -->
          <div class="space-y-3">
            <h4 class="font-semibold text-amber-400">Connect With Us</h4>
            <div class="flex flex-wrap gap-3">
              <?php
              $social_links = [
                ['url' => 'https://www.facebook.com/share/g/16NwpW8sCB/', 'icon' => 'fab fa-facebook-f', 'name' => 'Facebook', 'color' => 'hover:bg-blue-600'],
                ['url' => 'https://www.instagram.com/reel/DK2MTiVCR-k/?igsh=NngyM2p4anFmaG9l', 'icon' => 'fab fa-instagram', 'name' => 'Instagram', 'color' => 'hover:bg-gradient-to-tr from-purple-500 to-pink-500'],
                ['url' => 'https://youtube.com/@heartsaftergodministries', 'icon' => 'fab fa-youtube', 'name' => 'YouTube', 'color' => 'hover:bg-red-600'],
                ['url' => 'https://chat.whatsapp.com/F1BIzqQTulA5t5XlUDLWhK', 'icon' => 'fab fa-whatsapp', 'name' => 'WhatsApp', 'color' => 'hover:bg-green-600'],
                ['url' => 'https://www.tiktok.com/@heartsaftergodmin7', 'icon' => 'fab fa-tiktok', 'name' => 'TikTok', 'color' => 'hover:bg-gray-800'],
                ['url' => 'https://t.me/+ZnRxd1gF7AcwMzY0', 'icon' => 'fab fa-telegram', 'name' => 'Telegram', 'color' => 'hover:bg-blue-500'],
                ['url' => 'mailto:heartsaftergodministries254@gmail.com', 'icon' => 'fas fa-envelope', 'name' => 'Email', 'color' => 'hover:bg-red-500']
              ];
              
              foreach($social_links as $link): ?>
                <a href="<?= $link['url'] ?>" 
                   target="<?= strpos($link['url'], 'mailto:') === 0 ? '_self' : '_blank' ?>" 
                   rel="<?= strpos($link['url'], 'mailto:') === 0 ? '' : 'noopener noreferrer' ?>"
                   aria-label="<?= $link['name'] ?>" 
                   class="bg-white/10 <?= $link['color'] ?> p-4 rounded-full transition-all duration-300 transform hover:scale-110 group backdrop-blur-sm border border-white/20 hover:border-white/40">
                  <i class="<?= $link['icon'] ?> text-lg group-hover:text-white"></i>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Quick Links -->
        <div class="space-y-6">
          <h4 class="text-2xl font-bold text-amber-400 flex items-center font-display">
            <i class="fas fa-link mr-3 text-xl"></i>Quick Links
          </h4>
          <ul class="space-y-3">
            <?php
            $footer_links = [
              ['url' => 'index.php', 'label' => 'Home', 'icon' => 'fas fa-home'],
              ['url' => 'about.php', 'label' => 'About Us', 'icon' => 'fas fa-info-circle'],
              ['url' => 'ministries.php', 'label' => 'Ministries', 'icon' => 'fas fa-church'],
              ['url' => 'programmes.php', 'label' => 'Programmes', 'icon' => 'fas fa-users'],
              ['url' => 'events.php', 'label' => 'Events', 'icon' => 'fas fa-calendar'],
              ['url' => 'sermons.php', 'label' => 'Sermons', 'icon' => 'fas fa-play-circle'],
              ['url' => 'blog.php', 'label' => 'Blog', 'icon' => 'fas fa-blog'],
              ['url' => 'team.php', 'label' => 'Team', 'icon' => 'fas fa-user-friends'],
              ['url' => 'contact.php', 'label' => 'Contact', 'icon' => 'fas fa-envelope']
            ];
            
            foreach($footer_links as $link): ?>
              <li>
                <a href="<?= $link['url'] ?>" class="text-gray-300 hover:text-amber-400 transition-colors duration-200 flex items-center group py-2 px-3 rounded-lg hover:bg-white/5">
                  <i class="<?= $link['icon'] ?> mr-3 text-amber-400 group-hover:translate-x-1 transition-transform w-4"></i>
                  <span><?= $link['label'] ?></span>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <!-- Contact Info -->
        <div class="space-y-6">
          <h4 class="text-2xl font-bold text-amber-400 flex items-center font-display">
            <i class="fas fa-address-book mr-3 text-xl"></i>Contact Info
          </h4>
          <div class="space-y-6">
            <div class="flex items-start group hover:bg-white/5 p-3 rounded-lg transition-colors">
              <div class="bg-amber-500 p-3 rounded-xl mr-4 group-hover:bg-amber-400 transition-colors shadow-lg">
                <i class="fas fa-map-marker-alt text-white text-lg"></i>
              </div>
              <div>
                <p class="font-semibold text-white text-lg">Location</p>
                <p class="text-gray-300">Nairobi, Kenya</p>
                <p class="text-gray-400 text-sm">Serving globally online</p>
              </div>
            </div>
            
            <div class="flex items-start group hover:bg-white/5 p-3 rounded-lg transition-colors">
              <div class="bg-amber-500 p-3 rounded-xl mr-4 group-hover:bg-amber-400 transition-colors shadow-lg">
                <i class="fas fa-phone text-white text-lg"></i>
              </div>
              <div>
                <p class="font-semibold text-white text-lg">Phone</p>
                <a href="tel:+254707529090" class="text-gray-300 hover:text-amber-400 transition-colors block">
                  +254 707 529 090
                </a>
                <p class="text-gray-400 text-sm">Available 24/7 for prayers</p>
              </div>
            </div>
            
            <div class="flex items-start group hover:bg-white/5 p-3 rounded-lg transition-colors">
              <div class="bg-amber-500 p-3 rounded-xl mr-4 group-hover:bg-amber-400 transition-colors shadow-lg">
                <i class="fas fa-envelope text-white text-lg"></i>
              </div>
              <div>
                <p class="font-semibold text-white text-lg">Email</p>
                <a href="mailto:heartsaftergodministries254@gmail.com" class="text-gray-300 hover:text-amber-400 transition-colors block break-words">
                  heartsaftergodministries254@gmail.com
                </a>
                <p class="text-gray-400 text-sm">Response within 24 hours</p>
              </div>
            </div>
            
            <div class="flex items-start group hover:bg-white/5 p-3 rounded-lg transition-colors">
              <div class="bg-amber-500 p-3 rounded-xl mr-4 group-hover:bg-amber-400 transition-colors shadow-lg">
                <i class="fas fa-clock text-white text-lg"></i>
              </div>
              <div>
                <p class="font-semibold text-white text-lg">Service Times</p>
                <div class="text-gray-300 space-y-1">
                  <p>Monday: 8:00 PM (Prayer)</p>
                  <p>Wednesday: 8:00 PM (Tongues)</p>
                  <p>Friday: 8:00 PM (Bible Study)</p>
                  <p>Sunday: 10:00 AM (Worship)</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Our Services -->
        <div class="space-y-6">
          <h4 class="text-2xl font-bold text-amber-400 flex items-center font-display">
            <i class="fas fa-church mr-3 text-xl"></i>Our Services
          </h4>
          <ul class="space-y-4">
            <?php
            $services = [
              ['icon' => 'fas fa-pray', 'label' => 'Prayer Support', 'desc' => '24/7 prayer line'],
              ['icon' => 'fas fa-graduation-cap', 'label' => 'Discipleship Training', 'desc' => 'Spiritual growth programs'],
              ['icon' => 'fas fa-hands-helping', 'label' => 'Community Outreach', 'desc' => 'Serving those in need'],
              ['icon' => 'fas fa-music', 'label' => 'Worship Services', 'desc' => 'Spirit-filled gatherings'],
              ['icon' => 'fas fa-book-open', 'label' => 'Bible Study', 'desc' => 'Deep scriptural learning'],
              ['icon' => 'fas fa-globe', 'label' => 'Global Missions', 'desc' => 'Worldwide evangelism'],
              ['icon' => 'fas fa-heart', 'label' => 'Counseling & Care', 'desc' => 'Pastoral support'],
              ['icon' => 'fas fa-child', 'label' => 'Youth Ministry', 'desc' => 'Next generation focus']
            ];
            
            foreach($services as $service): ?>
              <li class="flex items-start text-gray-300 group cursor-pointer hover:bg-white/5 p-3 rounded-lg transition-colors">
                <div class="bg-amber-500 p-2 rounded-lg mr-3 group-hover:bg-amber-400 transition-colors">
                  <i class="<?= $service['icon'] ?> text-white text-sm"></i>
                </div>
                <div>
                  <div class="font-medium group-hover:text-white transition-colors"><?= $service['label'] ?></div>
                  <div class="text-xs text-gray-400"><?= $service['desc'] ?></div>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>

      <!-- Newsletter Subscription -->
      <div class="border-t border-gray-700 pt-12 mb-12">
        <div class="max-w-2xl mx-auto text-center">
          <div class="bg-gradient-to-r from-purple-600 to-blue-600 rounded-2xl p-8 shadow-2xl">
            <h4 class="text-3xl font-bold mb-4 text-white font-display">📧 Stay Connected</h4>
            <p class="text-white/90 mb-6 text-lg leading-relaxed">
              Subscribe to receive ministry updates, prayer requests, and inspirational content delivered straight to your inbox.
            </p>
            <form id="footer-newsletter-form" class="flex flex-col sm:flex-row gap-4 max-w-lg mx-auto">
              <div class="flex-1 relative">
                <input 
                  type="email" 
                  name="email" 
                  required 
                  placeholder="Enter your email address"
                  class="w-full px-6 py-4 rounded-full border-2 border-white/20 bg-white/10 text-white placeholder-white/70 focus:outline-none focus:ring-4 focus:ring-amber-400 focus:border-amber-400 backdrop-blur-sm text-lg"
                >
                <i class="fas fa-envelope absolute right-4 top-1/2 transform -translate-y-1/2 text-white/50"></i>
              </div>
              <button 
                type="submit" 
                class="px-8 py-4 bg-gradient-to-r from-amber-500 to-orange-600 text-white font-bold rounded-full hover:from-amber-600 hover:to-orange-700 transition-all duration-300 transform hover:scale-105 shadow-lg text-lg whitespace-nowrap"
              >
                <i class="fas fa-paper-plane mr-2"></i>
                Subscribe
              </button>
            </form>
            <p class="text-white/60 text-sm mt-4">
              Join 1,000+ believers receiving weekly inspiration. Unsubscribe anytime.
            </p>
          </div>
        </div>
      </div>

      <!-- Ministry Partners & Affiliations -->
      <div class="border-t border-gray-700 pt-12 mb-12">
        <div class="text-center mb-8">
          <h4 class="text-2xl font-bold text-amber-400 mb-4 font-display">Our Ministry Partners</h4>
          <p class="text-gray-300">Working together to spread God's love worldwide</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
          <?php
          $partners = [
            ['name' => 'Global Revival Network', 'logo' => 'assets/images/partners/partner1.jpg'],
            ['name' => 'Africa Ministry Alliance', 'logo' => 'assets/images/partners/partner2.jpg'],
            ['name' => 'Youth for Christ Kenya', 'logo' => 'assets/images/partners/partner3.jpg'],
            ['name' => 'International Prayer Network', 'logo' => 'assets/images/partners/partner4.jpg']
          ];
          
          foreach($partners as $partner): ?>
            <div class="bg-white/5 rounded-lg p-4 text-center hover:bg-white/10 transition-colors">
              <div class="w-16 h-16 bg-gray-600 rounded-full mx-auto mb-2 flex items-center justify-center">
                <i class="fas fa-handshake text-amber-400 text-2xl"></i>
              </div>
              <p class="text-gray-300 text-sm"><?= $partner['name'] ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Bottom Bar -->
      <div class="border-t border-gray-700 pt-8 flex flex-col lg:flex-row justify-between items-center text-center lg:text-left space-y-6 lg:space-y-0">
        <div class="text-gray-300 space-y-2">
          <p class="text-lg">&copy; <?= date('Y') ?> Hearts After God Ministry. All rights reserved.</p>
          <p class="text-sm text-gray-400">
            Built with ❤️ for the Kingdom of God | Designed to inspire and connect souls worldwide
          </p>
          <p class="text-xs text-gray-500">
            "For where your treasure is, there your heart will be also." - Matthew 6:21
          </p>
        </div>
        
        <div class="flex flex-wrap justify-center lg:justify-end gap-6 text-sm">
          <a href="privacy-policy.php" class="text-gray-400 hover:text-amber-400 transition-colors hover:underline">Privacy Policy</a>
          <a href="terms-of-service.php" class="text-gray-400 hover:text-amber-400 transition-colors hover:underline">Terms of Service</a>
          <a href="sitemap.php" class="text-gray-400 hover:text-amber-400 transition-colors hover:underline">Sitemap</a>
          <a href="accessibility.php" class="text-gray-400 hover:text-amber-400 transition-colors hover:underline">Accessibility</a>
          <a href="cookie-policy.php" class="text-gray-400 hover:text-amber-400 transition-colors hover:underline">Cookie Policy</a>
        </div>
      </div>

      <!-- Ministry Statement -->
      <div class="mt-8 pt-8 border-t border-gray-700/50 text-center">
        <div class="max-w-3xl mx-auto">
          <p class="text-amber-400 font-semibold text-lg mb-2 font-display">Our Mission</p>
          <p class="text-gray-300 italic leading-relaxed">
            "To raise disciples who will impact their generation through the power of God, spreading the Gospel to the ends of the earth, and establishing communities of believers who walk in love, truth, and the supernatural power of the Holy Spirit."
          </p>
          <div class="mt-6 flex justify-center items-center space-x-2">
            <div class="w-12 h-0.5 bg-gradient-to-r from-transparent to-amber-400"></div>
            <i class="fas fa-heart text-amber-400 text-xl"></i>
            <div class="w-12 h-0.5 bg-gradient-to-l from-transparent to-amber-400"></div>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <!-- Back to Top Button -->
  <button id="back-to-top" 
          class="fixed bottom-8 right-8 bg-gradient-to-r from-purple-600 to-amber-500 text-white p-4 rounded-full shadow-2xl hover:shadow-3xl transform hover:scale-110 transition-all duration-300 z-50 opacity-0 invisible group"
          aria-label="Back to top">
    <i class="fas fa-arrow-up text-xl group-hover:translate-y-1 transition-transform"></i>
  </button>

  <!-- Cookie Consent Banner -->
  <div id="cookie-banner" class="fixed bottom-0 left-0 right-0 bg-gray-900 text-white p-4 shadow-2xl z-40 transform translate-y-full transition-transform duration-300">
    <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <i class="fas fa-cookie-bite text-amber-400 text-2xl"></i>
        <div>
          <p class="font-semibold">We use cookies to enhance your experience</p>
          <p class="text-sm text-gray-300">By continuing, you agree to our use of cookies for analytics and personalization.</p>
        </div>
      </div>
      <div class="flex gap-3">
        <button id="cookie-accept" class="px-6 py-2 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-full transition-colors">
          Accept All
        </button>
        <button id="cookie-decline" class="px-6 py-2 border border-gray-600 hover:bg-gray-800 text-white font-semibold rounded-full transition-colors">
          Decline
        </button>
      </div>
    </div>
  </div>

  <!-- Enhanced Footer JavaScript -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Back to Top Button with smooth animation
      const backToTopButton = document.getElementById('back-to-top');
      let scrollTimeout;
      
      function toggleBackToTop() {
        if (window.pageYOffset > 500) {
          backToTopButton.classList.remove('opacity-0', 'invisible');
          backToTopButton.classList.add('opacity-100', 'visible');
        } else {
          backToTopButton.classList.add('opacity-0', 'invisible');
          backToTopButton.classList.remove('opacity-100', 'visible');
        }
      }

      // Throttled scroll listener
      window.addEventListener('scroll', () => {
        if (scrollTimeout) {
          clearTimeout(scrollTimeout);
        }
        scrollTimeout = setTimeout(toggleBackToTop, 10);
      });

      backToTopButton.addEventListener('click', () => {
        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        });
      });

      // Enhanced Newsletter Form
      const newsletterForm = document.getElementById('footer-newsletter-form');
      if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
          e.preventDefault();
          const formData = new FormData(this);
          const email = formData.get('email');
          const button = this.querySelector('button[type="submit"]');
          const originalContent = button.innerHTML;
          
          if (!Hearts.validateEmail(email)) {
            Hearts.showNotification('Please enter a valid email address', 'error');
            return;
          }

          // Show loading state
          button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Subscribing...';
          button.disabled = true;

          // Simulate API call
          setTimeout(() => {
            Hearts.showNotification('🎉 Welcome to our community! Check your email for confirmation.', 'success', 7000);
            this.reset();
            button.innerHTML = originalContent;
            button.disabled = false;
            
            // Add subscriber count animation
            const statsElements = document.querySelectorAll('.counter');
            statsElements.forEach(el => {
              const currentValue = parseInt(el.textContent.replace(/[^\d]/g, ''));
              el.textContent = (currentValue + 1).toLocaleString() + '+';
            });
          }, 2000);
        });
      }

      // Cookie Consent Management
      const cookieBanner = document.getElementById('cookie-banner');
      const cookieAccept = document.getElementById('cookie-accept');
      const cookieDecline = document.getElementById('cookie-decline');
      
      // Show cookie banner if not already accepted/declined
      if (!localStorage.getItem('cookieConsent')) {
        setTimeout(() => {
          cookieBanner.classList.remove('translate-y-full');
        }, 2000);
      }

      cookieAccept?.addEventListener('click', () => {
        localStorage.setItem('cookieConsent', 'accepted');
        cookieBanner.classList.add('translate-y-full');
        Hearts.showNotification('Cookie preferences saved. Thank you!', 'success');
      });

      cookieDecline?.addEventListener('click', () => {
        localStorage.setItem('cookieConsent', 'declined');
        cookieBanner.classList.add('translate-y-full');
        Hearts.showNotification('Your privacy preferences have been saved.', 'info');
      });

      // Footer animations on scroll
      const footerObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
          }
        });
      }, { 
        threshold: 0.1,
        rootMargin: '50px 0px'
      });

      // Observe footer sections for animation
      document.querySelectorAll('footer > div > div > div').forEach((section, index) => {
        section.style.opacity = '0';
        section.style.transform = 'translateY(30px)';
        section.style.transition = `all 0.6s ease-out ${index * 0.1}s`;
        footerObserver.observe(section);
      });

      // Enhanced social media tracking (for analytics)
      const socialLinks = document.querySelectorAll('footer a[target="_blank"]');
      socialLinks.forEach(link => {
        link.addEventListener('click', function(e) {
          const platform = this.href.includes('facebook') ? 'Facebook' :
                         this.href.includes('instagram') ? 'Instagram' :
                         this.href.includes('youtube') ? 'YouTube' :
                         this.href.includes('whatsapp') ? 'WhatsApp' :
                         this.href.includes('tiktok') ? 'TikTok' :
                         this.href.includes('telegram') ? 'Telegram' : 'Other';
          
          // Analytics tracking (replace with your analytics code)
          console.log(`Social media click: ${platform}`);
          
          // Optional: Add a small delay for analytics
          setTimeout(() => {
            // Analytics code here
          }, 100);
        });
      });

      // Service links interaction
      const serviceItems = document.querySelectorAll('footer ul li');
      serviceItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
          this.style.transform = 'scale(1.02)';
        });
        
        item.addEventListener('mouseleave', function() {
          this.style.transform = 'scale(1)';
        });
      });

      // Contact info click handlers
      const phoneLink = document.querySelector('a[href^="tel:"]');
      const emailLink = document.querySelector('a[href^="mailto:"]');
      
      phoneLink?.addEventListener('click', () => {
        Hearts.showNotification('📞 Calling Hearts After God Ministry...', 'info');
      });
      
      emailLink?.addEventListener('click', () => {
        Hearts.showNotification('📧 Opening your email client...', 'info');
      });

      // Newsletter input enhancements
      const newsletterInput = document.querySelector('#footer-newsletter-form input[type="email"]');
      if (newsletterInput) {
        newsletterInput.addEventListener('focus', function() {
          this.parentElement.style.transform = 'scale(1.02)';
        });
        
        newsletterInput.addEventListener('blur', function() {
          this.parentElement.style.transform = 'scale(1)';
        });
        
        // Add email validation on blur
        newsletterInput.addEventListener('blur', function() {
          const email = this.value;
          if (email && !Hearts.validateEmail(email)) {
            this.style.borderColor = '#ef4444';
            this.setCustomValidity('Please enter a valid email address');
          } else {
            this.style.borderColor = '';
            this.setCustomValidity('');
          }
        });
      }

      // Add loading states for external links
      const externalLinks = document.querySelectorAll('a[target="_blank"]:not([href*="mailto"]):not([href*="tel"])');
      externalLinks.forEach(link => {
        link.addEventListener('click', function() {
          const icon = this.querySelector('i');
          if (icon) {
            const originalClass = icon.className;
            icon.className = 'fas fa-spinner fa-spin';
            setTimeout(() => {
              icon.className = originalClass;
            }, 1000);
          }
        });
      });

      // Initialize tooltips for service items
      const createTooltip = (element, text) => {
        let tooltip;
        
        element.addEventListener('mouseenter', function(e) {
          tooltip = document.createElement('div');
          tooltip.className = 'absolute bg-gray-800 text-white text-sm px-3 py-2 rounded-lg shadow-lg z-50 pointer-events-none';
          tooltip.textContent = text;
          tooltip.style.bottom = '100%';
          tooltip.style.left = '50%';
          tooltip.style.transform = 'translateX(-50%)';
          tooltip.style.marginBottom = '8px';
          this.style.position = 'relative';
          this.appendChild(tooltip);
        });
        
        element.addEventListener('mouseleave', function() {
          if (tooltip) {
            tooltip.remove();
          }
        });
      };

      // Add tooltips to service items
      serviceItems.forEach((item, index) => {
        const serviceTexts = [
          'Available 24/7 for urgent prayer requests',
          'Comprehensive spiritual growth programs',
          'Community service and outreach programs',
          'Weekly worship and praise services',
          'In-depth biblical studies and discussions',
          'Worldwide evangelism and mission work',
          'Pastoral care and spiritual counseling',
          'Programs for children, teens, and young adults'
        ];
        
        if (serviceTexts[index]) {
          createTooltip(item, serviceTexts[index]);
        }
      });
    });

    // Extend Hearts global object with footer-specific functions
    window.Hearts = window.Hearts || {};
    Object.assign(window.Hearts, {
      // Smooth scroll to footer sections
      scrollToFooter: function(sectionId) {
        const section = document.querySelector(`#${sectionId}`);
        if (section) {
          section.scrollIntoView({ 
            behavior: 'smooth',
            block: 'start'
          });
        }
      },
      
      // Newsletter subscription with enhanced validation
      subscribeNewsletter: async function(email, additionalData = {}) {
        if (!this.validateEmail(email)) {
          this.showNotification('Please enter a valid email address', 'error');
          return false;
        }
        
        try {
          // Simulate API call - replace with actual endpoint
          const response = await fetch('/api/newsletter/subscribe', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              email,
              ...additionalData,
              timestamp: new Date().toISOString()
            })
          });
          
          if (response.ok) {
            this.showNotification('Successfully subscribed to our newsletter!', 'success');
            return true;
          } else {
            throw new Error('Subscription failed');
          }
        } catch (error) {
          this.showNotification('There was an error subscribing. Please try again.', 'error');
          return false;
        }
      }
    });
  </script>

  <!-- Service Worker Registration -->
  <script>
    // Register Service Worker
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', function() {
        // Use relative path from the site root
        const swPath = '/sw.js';
        const scope = '/';
        
        navigator.serviceWorker.register(swPath, { scope: scope })
          .then(function(registration) {
            console.log('ServiceWorker registration successful with scope: ', registration.scope);
            // Check if service worker is controlling the page
            if (navigator.serviceWorker.controller) {
              console.log('Service worker is controlling the page.');
            } else {
              console.log('Service worker is registered but not controlling the page.');
            }
          })
          .catch(function(error) {
            console.log('ServiceWorker registration failed: ', error);
          });
      });
    }
  </script>

  <!-- Initialize Swiper -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize Hero Swiper
      if (document.querySelector('.hero-swiper')) {
        const heroSwiper = new Swiper('.hero-swiper', {
          // Optional parameters
          direction: 'horizontal',
          loop: true,
          effect: 'fade',
          speed: 1000,
          parallax: true,
          grabCursor: true,
          
          // Autoplay settings
          autoplay: {
            delay: 5000,
            disableOnInteraction: false,
            pauseOnMouseEnter: true
          },
          
          // Pagination
          pagination: {
            el: '.swiper-pagination',
            clickable: true,
            renderBullet: function (index, className) {
              return '<span class="' + className + '" style="background-color: #D4AF37"></span>';
            }
          },
          
          // Navigation arrows
          navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
          },
          
          // Keyboard control
          keyboard: {
            enabled: true,
            onlyInViewport: true,
          },
          
          // Disable mousewheel to prevent scroll issues
          mousewheel: false,
          
          // Performance optimizations
          watchOverflow: true,
          updateOnWindowResize: true,
          
          // Disable unused modules
          a11y: false,
          
          // Fade effect
          fadeEffect: {
            crossFade: true
          },
          
          // Events
          on: {
            init: function() {
              // Add active class to first slide
              this.slides[this.activeIndex].classList.add('swiper-slide-active');
            },
            slideChange: function() {
              // Update active slide class
              this.slides.forEach(slide => slide.classList.remove('swiper-slide-active'));
              this.slides[this.activeIndex].classList.add('swiper-slide-active');
            }
          }
        });
      }
    });
  </script>
  
  <!-- Custom JavaScript for specific pages -->
  <?php if(isset($custom_js)): ?>
    <script><?= $custom_js ?></script>
  <?php endif; ?>

  <!-- Performance and SEO Scripts -->
  <script>
    // Performance monitoring
    window.addEventListener('load', function() {
      setTimeout(function() {
        const perfData = performance.timing;
        const pageLoadTime = perfData.loadEventEnd - perfData.navigationStart;
        console.log('Page Load Time:', pageLoadTime + 'ms');
        
        // Send to analytics if needed
        if (pageLoadTime > 3000) {
          console.warn('Page load time is slower than recommended (>3s)');
        }
      }, 0);
    });
  </script>

</body>
</html>