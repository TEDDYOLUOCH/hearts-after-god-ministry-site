<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Gallery | Hearts After God Ministry</title>
  <meta name="description" content="View our photo gallery featuring ministry events, worship services, and community activities at Hearts After God Ministry.">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:400,700,900&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <!-- Google Fonts: Montserrat & Open Sans -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
  <style>
    .gallery-item {
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .gallery-item:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    .active-nav {
      background: linear-gradient(90deg, #7C3AED 0%, #F59E0B 100%);
      color: #fff !important;
      font-weight: bold;
      border-radius: 1rem;
      padding: 0.5rem 2.5rem;
      border: 3px solid #fff;
      box-shadow: 0 0 0 4px #F59E0B;
      position: relative;
      display: inline-block;
      transition: box-shadow 0.2s, border 0.2s;
    }
  </style>
</head>
<body class="bg-white text-gray-900 font-nunito">
  <!-- HEADER / NAVIGATION -->
  <div id="site-header" class="sticky top-0 z-50 w-full"></div>

  <!-- Hero Section -->
  <section class="relative min-h-[40vh] md:min-h-[60vh] flex items-center justify-center bg-gradient-to-br from-purple-900 via-blue-900 to-black overflow-hidden">
    <img src="assets/images/hero/IMG-20250705-WA0022.jpg" alt="Our Gallery" class="absolute inset-0 w-full h-full object-cover opacity-60" loading="lazy" />
    <div class="absolute inset-0 bg-gradient-to-br from-black/70 via-purple-900/60 to-blue-900/70"></div>
    <div class="relative z-10 flex flex-col items-center justify-center text-center px-4 py-16 w-full max-w-2xl mx-auto">
      <h1 class="font-bold text-white mb-4" style="font-family: 'Montserrat', Arial, sans-serif; font-size: 40px; line-height: 1.1;">
        <span class="block md:text-6xl text-4xl" style="font-size: 60px;">Our Gallery</span>
      </h1>
      <p class="text-white mb-8 md:text-xl text-base font-normal" style="font-family: 'Open Sans', Arial, sans-serif;">
        Captured moments from our ministry events, worship services, and community activities
      </p>
      <a href="#gallery-grid" class="cta-btn inline-block px-8 py-4 rounded-full font-bold text-white text-lg shadow-lg transition bg-[#F59E0B] hover:bg-[#E0A615] focus:outline-none focus:ring-4 focus:ring-[#F59E0B]/50" style="font-family: 'Montserrat', Arial, sans-serif;">
        View Gallery
      </a>
    </div>
  </section>

  <!-- Main Content -->
  <main class="py-12">
    <div class="container mx-auto px-4">
      <!-- Gallery Grid -->
      <div id="gallery-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6" id="gallery-grid">
        <!-- Images will be loaded here by JavaScript -->
        <div class="text-center py-8">
          <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-purple-500 mx-auto"></div>
          <p class="mt-2 text-gray-500">Loading gallery...</p>
        </div>
      </div>
    </div>
  </main>

  <div id="site-footer" ></div>
  
  <!-- Load dynamic header and footer -->
  <script src="../assets/main.js"></script>
  <!-- Load dynamic header and footer -->
  <script src="assets/js/main.js"></script>

  <!-- JavaScript -->
  <script>
 document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = window.location.hostname === 'localhost' 
        ? '/hearts-after-god-ministry-site' 
        : '';

    // Fetch gallery images from API
    fetch(`${baseUrl}/api/gallery.php`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            const galleryGrid = document.getElementById('gallery-grid');
            
            if (data.success && data.data && Array.isArray(data.data)) {
                galleryGrid.innerHTML = ''; // Clear loading message
                
                if (data.data.length === 0) {
                    galleryGrid.innerHTML = `
                        <div class="col-span-full text-center py-12">
                            <i class="fas fa-image text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500">No images found in the gallery.</p>
                        </div>
                    `;
                    return;
                }
                
                data.data.forEach(image => {
                    // Construct the full image URL
                    let imageUrl = image.image_url || '';
                    
                    // Ensure the URL is properly formatted
                    if (imageUrl) {
                        // Remove any leading slashes to prevent double slashes
                        imageUrl = imageUrl.replace(/^\/+/, '');
                        // Prepend the base URL if not already included
                        if (!imageUrl.startsWith('http') && !imageUrl.startsWith('//')) {
                            imageUrl = `${baseUrl}/${imageUrl}`;
                        }
                        
                        console.log('Loading image:', imageUrl);
                    }

                    const imageCard = `
                        <div class="gallery-item bg-white rounded-lg overflow-hidden shadow-md hover:shadow-xl transition-shadow duration-300">
                            <div class="relative overflow-hidden" style="padding-top: 75%;">
                                <img 
                                    src="${imageUrl}" 
                                    alt="${image.title || 'Gallery image'}" 
                                    class="absolute top-0 left-0 w-full h-full object-cover hover:scale-105 transition-transform duration-500"
                                    loading="lazy"
                                    onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MDAiIGhlaWdodD0iMzAwIiB2aWV3Qm94PSIwIDAgNDAwIDMwMCI+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0iI2YzZjRmNiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGRvbWluYW50LWJhc2VsaW5lPSJtaWRkbGUiIGZpbGw9IiM2YzcyODAiPk5vIGltYWdlIGF2YWlsYWJsZTwvdGV4dD48L3N2Zz4='; this.alt='Image not available'"
                                >
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-lg mb-1 truncate">${image.title || 'Untitled'}</h3>
                                ${image.description ? `<p class="text-gray-600 text-sm line-clamp-2">${image.description}</p>` : ''}
                            </div>
                        </div>
                    `;
                    galleryGrid.insertAdjacentHTML('beforeend', imageCard);
                });
            } else {
                throw new Error('Invalid response format from server');
            }
        })
        .catch(error => {
            console.error('Error loading gallery:', error);
            const galleryGrid = document.getElementById('gallery-grid');
            galleryGrid.innerHTML = `
                <div class="col-span-full text-center py-12">
                    <div class="inline-block p-4 bg-red-50 rounded-lg">
                        <i class="fas fa-exclamation-triangle text-4xl text-red-500 mb-2"></i>
                        <p class="text-gray-700">Error loading gallery. Please try again later.</p>
                        <p class="text-sm text-gray-500 mt-2">${error.message}</p>
                    </div>
                </div>
            `;
        });
});
  </script>
</body>
</html>