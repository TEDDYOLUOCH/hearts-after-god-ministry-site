<?php
require_once '../includes/database.php';
$events = $pdo->query("SELECT * FROM events ORDER BY event_date DESC")->fetchAll();

// Debug output
// echo '<pre>';
// print_r($events);
// echo '</pre>';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Events | Hearts After God Ministry</title>
  <meta name="description" content="See upcoming events and weekly services at Hearts After God Ministry."/>
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Google Fonts: Montserrat & Open Sans -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:400,700,900&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
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
  <img src="assets/images/hero/IMG-20250705-WA0023.jpg" alt="Events" class="absolute inset-0 w-full h-full object-cover opacity-60" loading="lazy" />
  <div class="absolute inset-0 bg-gradient-to-br from-black/70 via-purple-900/60 to-blue-900/70"></div>
  <div class="relative z-10 flex flex-col items-center justify-center text-center px-4 py-16 w-full max-w-2xl mx-auto">
    <h1 class="font-bold text-white mb-4" style="font-family: 'Montserrat', Arial, sans-serif; font-size: 40px; line-height: 1.1;">
      <span class="block md:text-6xl text-4xl" style="font-size: 60px;">Events</span>
    </h1>
    <p class="text-white mb-8 md:text-xl text-base font-normal" style="font-family: 'Open Sans', Arial, sans-serif;">
      Discover upcoming and past events at Hearts After God Ministry. Join us and be part of our journey!
    </p>
    <a href="events.html#upcoming-events" class="cta-btn inline-block px-8 py-4 rounded-full font-bold text-white text-lg shadow-lg transition bg-[#F59E0B] hover:bg-[#E0A615] focus:outline-none focus:ring-4 focus:ring-[#F59E0B]/50" style="font-family: 'Montserrat', Arial, sans-serif;">
      View Events
    </a>
  </div>
</section>
<!-- End Hero Section -->

<!-- Events Section -->
<section class="bg-gray-50 py-16 px-4">
  <div class="max-w-screen-lg mx-auto">
    <h2 id="upcoming-events" class="text-3xl md:text-4xl font-bold text-[#7C3AED] mb-8 text-center flex items-center justify-center gap-3" style="font-family: 'Montserrat', Arial, sans-serif;">
      <span class="text-2xl">📅</span> Upcoming Events
    </h2>
    
    <!-- Main Event Card -->
  <!-- Main Event Card -->
<div class="bg-white rounded-xl shadow-md p-8 mb-8 hover:shadow-lg transition-shadow duration-300">
<?php foreach ($events as $event): ?>
<div class="bg-white rounded-xl shadow-md p-8 mb-8 hover:shadow-lg transition-shadow duration-300">
  <div class="grid md:grid-cols-2 gap-8">
    <!-- Event Details -->
    <div class="space-y-4">
      <h3 class="text-2xl font-bold text-[#F59E0B] mb-4"><?= htmlspecialchars($event['title']) ?></h3>
      
      <div class="space-y-3">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 bg-[#7C3AED] rounded-full flex items-center justify-center">
            <i class="fas fa-calendar text-white text-sm"></i>
          </div>
          <div>
            <p class="font-semibold text-gray-900">Date</p>
            <p class="text-gray-600"><?= date('F j, Y', strtotime($event['event_date'])) ?></p>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <div class="w-8 h-8 bg-[#1E40AF] rounded-full flex items-center justify-center">
            <i class="fas fa-user text-white text-sm"></i>
          </div>
          <div>
            <p class="font-semibold text-gray-900">Coordinator</p>
            <p class="text-gray-600"><?= htmlspecialchars($event['coordinator']) ?: 'Not Assigned' ?></p>
          </div>
        </div>

        <?php if (!empty($event['description'])): ?>
        <div class="text-gray-600">
          <?= nl2br(htmlspecialchars($event['description'])) ?>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Event Photo -->
    <?php if (!empty($event['photo'])): ?>
    <div class="relative h-64">
      <?php 
      // Remove any existing 'uploads/' prefix to prevent duplication
      $cleanPhotoPath = str_replace('uploads/', '', $event['photo']);
      $imagePath = 'uploads/' . $cleanPhotoPath;
      $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/hearts-after-god-ministry-site/' . $imagePath;
      $imageExists = file_exists($fullPath);
      ?>
      <?php if ($imageExists): ?>
      <img src="/hearts-after-god-ministry-site/<?= $imagePath ?>" 
           alt="<?= htmlspecialchars($event['title']) ?>" 
           class="rounded-xl w-full h-full object-cover shadow-lg" 
           loading="lazy">
      <?php else: ?>
      <div class="bg-gray-200 w-full h-full rounded-xl flex items-center justify-center text-gray-400">
        <i class="fas fa-image text-4xl"></i>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php endforeach; ?>

        <!-- Scripture Section -->
        <div class="bg-gradient-to-br from-[#F3E8FF] to-white rounded-xl p-6 border-l-4 border-[#7C3AED]">
          <div class="mb-4">
            <h4 class="text-lg font-bold text-[#7C3AED] mb-2" style="font-family: 'Montserrat', Arial, sans-serif;">Scripture Reference</h4>
            <p class="text-sm font-semibold text-[#7C3AED]">Matthew 5:14-16</p>
          </div>
          
          <blockquote class="italic text-gray-700 leading-relaxed" style="font-family: 'Open Sans', Arial, sans-serif;">
            "You are the light of the world. A town built on a hill cannot be hidden. Neither do people light a lamp and put it under a bowl. Instead they put it on its stand, and it gives light to everyone in the house. In the same way, let your light shine before others, that they may see your good deeds and glorify your Father in heaven."
          </blockquote>
        </div>
      </div>
      
      <!-- Call to Action -->
      <div class="mt-8 pt-6 border-t border-gray-200">
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <a href="contact.html" class="inline-block px-6 py-3 bg-[#F59E0B] text-white font-bold rounded-full hover:bg-[#E0A615] transition-colors duration-300 text-center">
            <i class="fas fa-envelope mr-2"></i>Register for Mission Trip
          </a>
          <a href="https://chat.whatsapp.com/F1BIzqQTulA5t5XlUDLWhK" class="inline-block px-6 py-3 bg-green-500 text-white font-bold rounded-full hover:bg-green-600 transition-colors duration-300 text-center">
            <i class="fab fa-whatsapp mr-2"></i>Contact Coordinator
          </a>
        </div>
      </div>
    </div>
    
    <!-- Weekly Services Section -->
    <div class="bg-white rounded-xl shadow-md p-8">
      <h3 class="text-2xl font-bold text-[#1E40AF] mb-6 text-center" style="font-family: 'Montserrat', Arial, sans-serif;">Our Weekly Services</h3>
      <div class="grid md:grid-cols-3 gap-6">
        <div class="bg-[#F3E8FF] rounded-xl p-6 shadow text-center hover:shadow-lg transition-shadow duration-300">
          <div class="text-lg font-bold text-[#7C3AED] mb-2 flex items-center justify-center gap-2">
            <i class="fas fa-book-bible"></i>Monday
          </div>
          <div class="text-gray-700 font-semibold">8PM - 9PM</div>
          <div class="text-gray-500 text-sm" style="font-family: 'Open Sans', Arial, sans-serif;">Word & Prayer</div>
        </div>
        <div class="bg-[#FFF7ED] rounded-xl p-6 shadow text-center hover:shadow-lg transition-shadow duration-300">
          <div class="text-lg font-bold text-[#F59E0B] mb-2 flex items-center justify-center gap-2">
            <i class="fas fa-fire"></i>Wednesday
          </div>
          <div class="text-gray-700 font-semibold">8PM - 9PM</div>
          <div class="text-gray-500 text-sm" style="font-family: 'Open Sans', Arial, sans-serif;">Tongues Session</div>
        </div>
        <div class="bg-[#F3E8FF] rounded-xl p-6 shadow text-center hover:shadow-lg transition-shadow duration-300">
          <div class="text-lg font-bold text-[#1E40AF] mb-2 flex items-center justify-center gap-2">
            <i class="fas fa-book-open"></i>Friday
          </div>
          <div class="text-gray-700 font-semibold">8PM - 9PM</div>
          <div class="text-gray-500 text-sm" style="font-family: 'Open Sans', Arial, sans-serif;">Bible Study</div>
        </div>
      </div>
    </div>
  </div>
</section>

  <div id="site-footer" ></div>
  
  <!-- Load dynamic header and footer -->
  <script src="../assets/main.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>