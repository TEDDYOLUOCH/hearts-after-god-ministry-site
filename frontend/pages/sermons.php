<?php
require_once '../includes/database.php';

// Fetch all sermons from the database
$sermons = [];
$featuredSermon = null;

try {
    // Fetch all sermons ordered by date (newest first)
    $stmt = $pdo->query("SELECT * FROM sermons ORDER BY created_at DESC");
    $allSermons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get the most recent sermon as featured if available
    $sermons = $allSermons;
    $featuredSermon = !empty($allSermons) ? $allSermons[0] : null;
    
} catch (PDOException $e) {
    $error = "Error fetching sermons: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Sermons | Hearts After God Ministry</title>
  <meta name="description" content="Watch and listen to sermons from Hearts After God Ministry. Be inspired and equipped for your walk with Christ.">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:400,700,900&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Nunito', sans-serif; }
    .scripture { font-family: 'Georgia', serif; }
    .sermon-card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
    .sermon-card:hover { 
      transform: translateY(-5px);
      box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
  </style>
</head>
<body class="bg-white text-gray-900">

  <!-- HEADER / NAVIGATION -->
  <div id="site-header" class="sticky top-0 z-50 w-full"></div>

  <!-- HERO SECTION -->
  <section class="relative min-h-[40vh] md:min-h-[60vh] flex items-center justify-center bg-gradient-to-br from-purple-900 via-blue-900 to-black overflow-hidden">
    <img src="assets/images/hero/IMG-20250705-WA0024.jpg" alt="Sermons" class="absolute inset-0 w-full h-full object-cover opacity-60" loading="lazy" />
    <div class="absolute inset-0 bg-gradient-to-br from-black/70 via-purple-900/60 to-blue-900/70"></div>
    <div class="relative z-10 flex flex-col items-center justify-center text-center px-4 py-16 w-full max-w-2xl mx-auto">
      <h1 class="font-bold text-white mb-4" style="font-family: 'Montserrat', Arial, sans-serif; font-size: 40px; line-height: 1.1;">
        <span class="block md:text-6xl text-4xl" style="font-size: 60px;">Sermons & Teachings</span>
      </h1>
      <p class="text-white mb-8 md:text-xl text-base font-normal" style="font-family: 'Open Sans', Arial, sans-serif;">
        Be inspired and equipped for your walk with Christ through our collection of sermons and teachings.
        Be inspired by powerful messages from our ministry leaders. Watch, listen, or read to grow in your faith journey.
      </p>
      <a href="#sermon-list" class="cta-btn inline-block px-8 py-4 rounded-full font-bold text-white text-lg shadow-lg transition bg-[#F59E0B] hover:bg-[#E0A615] focus:outline-none focus:ring-4 focus:ring-[#F59E0B]/50" style="font-family: 'Montserrat', Arial, sans-serif;">
        Explore Sermons
      </a>
    </div>
  </section>

  <?php if ($featuredSermon): ?>
  <!-- FEATURED SERMON -->
  <section class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4">
      <div class="bg-gradient-to-br from-[#7C3AED]/5 to-[#F59E0B]/5 rounded-2xl p-8 border border-[#7C3AED]/10">
        <div class="flex flex-col lg:flex-row items-center gap-10">
          <div class="lg:w-1/2">
            <?php if (!empty($featuredSermon['thumbnail_url'])): ?>
              <img src="<?= htmlspecialchars($featuredSermon['thumbnail_url']) ?>" alt="<?= htmlspecialchars($featuredSermon['title']) ?>" class="w-full h-64 object-cover rounded-2xl shadow-2xl"/>
            <?php else: ?>
              <div class="w-full h-64 bg-gradient-to-br from-purple-500 to-blue-600 rounded-2xl shadow-2xl flex items-center justify-center">
                <i class="fas fa-bible text-white text-6xl"></i>
              </div>
            <?php endif; ?>
          </div>
          <div class="lg:w-1/2">
            <span class="inline-block bg-[#F59E0B] text-white text-xs font-bold px-4 py-2 rounded-full mb-4">
              <i class="fas fa-star mr-2"></i> Latest Sermon
            </span>
            <h2 class="text-3xl font-bold text-[#7C3AED] mb-4" style="font-family: 'Montserrat', Arial, sans-serif;">
              <?= htmlspecialchars($featuredSermon['title']) ?>
            </h2>
            <div class="flex items-center gap-4 mb-4 text-[#1E40AF] font-semibold">
              <?php if (!empty($featuredSermon['preacher'])): ?>
                <span><i class="fas fa-user"></i> <?= htmlspecialchars($featuredSermon['preacher']) ?></span>
              <?php endif; ?>
              <span><i class="fas fa-calendar-alt"></i> <?= date('F j, Y', strtotime($featuredSermon['sermon_date'] ?? $featuredSermon['created_at'])) ?></span>
            </div>
            <?php if (!empty($featuredSermon['bible_reference'])): ?>
              <p class="text-gray-700 mb-2"><i class="fas fa-book text-[#7C3AED] mr-2"></i> <?= htmlspecialchars($featuredSermon['bible_reference']) ?></p>
            <?php endif; ?>
            <?php if (!empty($featuredSermon['description'])): ?>
              <p class="text-gray-700 mb-6 text-lg" style="font-family: 'Open Sans', Arial, sans-serif;">
                <?= nl2br(htmlspecialchars(mb_substr($featuredSermon['description'], 0, 200) . (mb_strlen($featuredSermon['description']) > 200 ? '...' : ''))) ?>
              </p>
            <?php endif; ?>
            <div class="flex flex-col sm:flex-row gap-4">
              <?php if (!empty($featuredSermon['video_url'])): ?>
                <a href="<?= htmlspecialchars($featuredSermon['video_url']) ?>" target="_blank" class="inline-flex items-center px-6 py-3 bg-[#F59E0B] text-white font-bold rounded-full shadow hover:bg-[#E0A615] transition-all text-base">
                  <i class="fab fa-youtube mr-2"></i>Watch Now
                </a>
              <?php elseif (!empty($featuredSermon['audio_url'])): ?>
                <a href="<?= htmlspecialchars($featuredSermon['audio_url']) ?>" target="_blank" class="inline-flex items-center px-6 py-3 bg-[#F59E0B] text-white font-bold rounded-full shadow hover:bg-[#E0A615] transition-all text-base">
                  <i class="fas fa-headphones mr-2"></i>Listen Now
                </a>
              <?php endif; ?>
              <a href="#sermon-list" class="inline-flex items-center px-6 py-3 bg-transparent text-[#7C3AED] font-bold rounded-full border-2 border-[#7C3AED] hover:bg-[#7C3AED] hover:text-white transition-all text-base">
                <i class="fas fa-list mr-2"></i>View All Sermons
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- SERMON FILTER BAR -->
  <section class="py-8 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4">
      <div class="flex flex-wrap gap-4 justify-center">
        <button class="px-6 py-3 bg-[#7C3AED] text-white font-bold rounded-full shadow hover:bg-[#5B23B4] transition-all" data-category="All">
          <i class="fas fa-list mr-2"></i>All Sermons
        </button>
        <button class="px-6 py-3 bg-white text-[#7C3AED] font-bold rounded-full border-2 border-[#7C3AED] hover:bg-[#7C3AED] hover:text-white transition-all" data-category="Prayer">
          <i class="fas fa-pray mr-2"></i>Prayer
        </button>
        <button class="px-6 py-3 bg-white text-[#F59E0B] font-bold rounded-full border-2 border-[#F59E0B] hover:bg-[#F59E0B] hover:text-white transition-all" data-category="Faith">
          <i class="fas fa-cross mr-2"></i>Faith
        </button>
        <button class="px-6 py-3 bg-white text-[#1E40AF] font-bold rounded-full border-2 border-[#1E40AF] hover:bg-[#1E40AF] hover:text-white transition-all" data-category="Revival">
          <i class="fas fa-fire mr-2"></i>Revival
        </button>
        <button class="px-6 py-3 bg-white text-[#DC2626] font-bold rounded-full border-2 border-[#DC2626] hover:bg-[#DC2626] hover:text-white transition-all" data-category="Growth">
          <i class="fas fa-seedling mr-2"></i>Growth
        </button>
      </div>
    </div>
  </section>

  <!-- SERMONS GRID -->
  <section id="sermon-list" class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4">
      <div class="text-center mb-12">
        <h2 class="text-4xl md:text-5xl font-bold text-[#7C3AED] mb-4" style="font-family: 'Montserrat', Arial, sans-serif;">
          Recent Sermons
        </h2>
        <?php if (!empty($sermons)): ?>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto" style="font-family: 'Open Sans', Arial, sans-serif;">
            Be inspired and equipped by Spirit-filled messages from our ministry leaders
          </p>
        <?php endif; ?>
      </div>
      
      <div id="sermons-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php 
        // Skip the first sermon if it's the featured one
        $sermonsToShow = $featuredSermon ? array_slice($sermons, 1) : $sermons;
        if (!empty($sermonsToShow)): 
        ?>
          <?php foreach ($sermonsToShow as $sermon): ?>
            <div class="sermon-card bg-white rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300">
              <div class="relative h-48 overflow-hidden">
                <?php if (!empty($sermon['thumbnail_url'])): ?>
                  <img src="<?= htmlspecialchars($sermon['thumbnail_url']) ?>" alt="<?= htmlspecialchars($sermon['title']) ?>" class="w-full h-full object-cover">
                <?php else: ?>
                  <div class="w-full h-full bg-gradient-to-br from-purple-500 to-blue-600 flex items-center justify-center">
                    <i class="fas fa-bible text-white text-5xl"></i>
                  </div>
                <?php endif; ?>
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-4">
                  <span class="text-xs font-bold text-white bg-[#F59E0B] px-2 py-1 rounded">
                    <?= date('M j, Y', strtotime($sermon['sermon_date'] ?? $sermon['created_at'])) ?>
                  </span>
                </div>
              </div>
              <div class="p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-2" style="font-family: 'Montserrat', Arial, sans-serif;">
                  <?= htmlspecialchars($sermon['title']) ?>
                </h3>
                <?php if (!empty($sermon['preacher'])): ?>
                  <p class="text-sm text-gray-600 mb-3">
                    <i class="fas fa-user text-[#7C3AED] mr-1"></i>
                    <?= htmlspecialchars($sermon['preacher']) ?>
                  </p>
                <?php endif; ?>
                <?php if (!empty($sermon['bible_reference'])): ?>
                  <p class="text-sm text-gray-600 mb-4">
                    <i class="fas fa-book text-[#7C3AED] mr-1"></i>
                    <?= htmlspecialchars($sermon['bible_reference']) ?>
                  </p>
                <?php endif; ?>
                <?php if (!empty($sermon['description'])): ?>
                  <p class="text-gray-600 text-sm mb-4 line-clamp-3" style="font-family: 'Open Sans', Arial, sans-serif;">
                    <?= nl2br(htmlspecialchars($sermon['description'])) ?>
                  </p>
                <?php endif; ?>
                <div class="flex justify-between items-center">
                  <?php if (!empty($sermon['audio_url'])): ?>
                    <a href="<?= htmlspecialchars($sermon['audio_url']) ?>" target="_blank" class="text-sm text-[#7C3AED] hover:text-[#5B23B4] font-medium">
                      <i class="fas fa-headphones mr-1"></i> Listen
                    </a>
                  <?php endif; ?>
                  <?php if (!empty($sermon['video_url'])): ?>
                    <a href="<?= htmlspecialchars($sermon['video_url']) ?>" target="_blank" class="text-sm text-[#F59E0B] hover:text-[#D97706] font-medium">
                      <i class="fas fa-play-circle mr-1"></i> Watch
                    </a>
                  <?php endif; ?>
                  <a href="sermon-detail.php?id=<?= $sermon['id'] ?>" class="text-sm text-gray-600 hover:text-[#7C3AED] font-medium">
                    Read more <i class="fas fa-arrow-right ml-1"></i>
                  </a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-span-full text-center py-12">
            <i class="fas fa-bible text-4xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">No sermons available at the moment. Please check back later.</p>
          </div>
        <?php endif; ?>
      </div>

      <!-- Load More Button -->
      <div class="text-center mt-12">
        <button id="load-more-sermons" class="px-8 py-4 bg-[#F59E0B] text-white font-bold rounded-full shadow-lg hover:bg-[#E0A615] transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-[#F59E0B]/50" style="font-family: 'Montserrat', Arial, sans-serif;">
          <i class="fas fa-plus mr-2"></i>Load More Sermons
        </button>
      </div>
    </div>
  </section>

  <!-- CALL TO ACTION -->
  <section class="py-16 bg-gradient-to-br from-[#7C3AED]/5 to-[#F59E0B]/5">
    <div class="max-w-4xl mx-auto text-center px-4">
      <h2 class="text-4xl md:text-5xl font-bold text-[#7C3AED] mb-6" style="font-family: 'Montserrat', Arial, sans-serif;">
        Want More Teaching?
      </h2>
      <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto" style="font-family: 'Open Sans', Arial, sans-serif;">
        Subscribe to our YouTube channel or contact us for sermon resources and notes.
      </p>
      <div class="flex flex-col sm:flex-row gap-6 justify-center items-center">
        <a href="contact.html" class="group px-8 py-4 bg-[#F59E0B] text-white font-bold rounded-full shadow-xl hover:bg-[#E0A615] transition-all duration-300 transform hover:scale-105" style="font-family: 'Montserrat', Arial, sans-serif;">
          <i class="fas fa-envelope mr-2"></i>Contact Us
        </a>
        <a href="https://youtube.com/@heartsaftergodministries" target="_blank" class="group px-8 py-4 bg-transparent text-[#7C3AED] font-bold rounded-full border-2 border-[#7C3AED] hover:bg-[#7C3AED] hover:text-white transition-all duration-300 transform hover:scale-105" style="font-family: 'Montserrat', Arial, sans-serif;">
          <i class="fab fa-youtube mr-2"></i>Watch on YouTube
        </a>
      </div>
    </div>
  </section>

  <!-- Only one footer section at the bottom, matching footer.html -->
  <footer class="bg-gradient-to-r from-[#1E40AF] to-[#7C3AED] text-white pt-2 pb-1 px-4">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
      <div class="absolute inset-0" style="background-image: radial-gradient(circle at 25% 25%, rgba(245, 158, 11, 0.3) 0%, transparent 50%), radial-gradient(circle at 75% 75%, rgba(124, 58, 237, 0.3) 0%, transparent 50%);"></div>
    </div>
    
    <div class="relative z-10">
        <div id="site-footer" ></div>
  
  <!-- Load dynamic header and footer -->
  <script src="../assets/main.js"></script>

  <script src="assets/js/main.js"></script>
</body>
</html> 