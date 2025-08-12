<?php
require_once '../includes/database.php';

// Fetch all active team members ordered by display_order
try {
    $stmt = $pdo->query("SELECT * FROM team_members WHERE is_active = 1 ORDER BY display_order, name");
    $teamMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching team members: " . $e->getMessage();
    $teamMembers = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Our Team | Hearts After God Ministry</title>
  <meta name="description" content="Meet the leadership and serving team of Hearts After God Ministry. Passionate, Spirit-filled, and dedicated to global revival and discipleship." />
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Google Fonts: Montserrat & Open Sans -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:400,700,900&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    body { font-family: 'Nunito', sans-serif; }
    .scripture { font-family: 'Georgia', serif; }
  </style>
</head>
<body class="bg-white text-gray-900">

  <!-- HEADER / NAVIGATION -->
  <div id="site-header" class="sticky top-0 z-50 w-full"></div>

  <!-- HERO SECTION -->
  <section class="relative min-h-[40vh] md:min-h-[60vh] flex items-center justify-center bg-gradient-to-br from-purple-900 via-blue-900 to-black overflow-hidden">
    <img src="assets/images/hero/IMG-20250705-WA0025.jpg" alt="Our Team" class="absolute inset-0 w-full h-full object-cover opacity-60" loading="lazy" />
    <div class="absolute inset-0 bg-gradient-to-br from-black/70 via-purple-900/60 to-blue-900/70"></div>
    <div class="relative z-10 flex flex-col items-center justify-center text-center px-4 py-16 w-full max-w-2xl mx-auto">
      <h1 class="font-bold text-white mb-4" style="font-family: 'Montserrat', Arial, sans-serif; font-size: 40px; line-height: 1.1;">
        <span class="block md:text-6xl text-4xl" style="font-size: 60px;">Our Team</span>
      </h1>
      <p class="text-white mb-8 md:text-xl text-base font-normal" style="font-family: 'Open Sans', Arial, sans-serif;">
        Meet the dedicated leaders and volunteers serving Hearts After God Ministry.
      </p>
      <a href="team.html#leadership" class="cta-btn inline-block px-8 py-4 rounded-full font-bold text-white text-lg shadow-lg transition bg-[#F59E0B] hover:bg-[#E0A615] focus:outline-none focus:ring-4 focus:ring-[#F59E0B]/50" style="font-family: 'Montserrat', Arial, sans-serif;">
        Meet Our Leaders
      </a>
    </div>
  </section>

  <!-- TEAM SECTION: Meet Our Ministry Team (Enhanced) -->
  <section class="relative py-20 px-6 bg-gradient-to-br from-gray-50 via-white to-purple-50 overflow-hidden">
    <!-- Decorative SVG/Shape -->
    <div class="absolute -top-24 -left-24 w-96 h-96 bg-gradient-to-br from-[#F59E0B]/20 to-[#7C3AED]/10 rounded-full blur-3xl opacity-50 pointer-events-none z-0"></div>
    <div class="max-w-6xl mx-auto relative z-10">
      <h2 class="text-4xl md:text-5xl font-montserrat font-extrabold text-center text-[#7C3AED] mb-4 tracking-tight flex items-center justify-center gap-2">
        <span>🙏</span> Meet Our Ministry Team
      </h2>
      <p class="text-xl text-center text-gray-600 font-opensans mb-10 max-w-2xl mx-auto">“And He gave some to be apostles, some prophets, some evangelists, and some pastors and teachers, for the equipping of the saints…” <span class="text-[#F59E0B]">Ephesians 4:11-12</span></p>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
        <?php if (!empty($teamMembers)): ?>
          <?php foreach ($teamMembers as $member): ?>
            <div class="backdrop-blur-xl bg-white/80 border border-[#7C3AED]/10 shadow-2xl rounded-2xl p-8 text-center transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl group relative">
              <div class="flex justify-center mb-4">
                <span class="inline-block rounded-full ring-4 ring-[#7C3AED] shadow-lg overflow-hidden">
                  <?php if (!empty($member['image_url'])): ?>
                    <img src="<?= htmlspecialchars($member['image_url']) ?>" alt="<?= htmlspecialchars($member['name']) ?>" class="rounded-full h-32 w-32 object-cover group-hover:scale-110 group-hover:shadow-[0_0_0_6px_rgba(124,58,237,0.15)] transition-transform duration-300" />
                  <?php else: ?>
                    <div class="h-32 w-32 bg-gradient-to-br from-purple-500 to-blue-600 flex items-center justify-center text-white text-4xl">
                      <?= mb_substr(htmlspecialchars($member['name']), 0, 1) ?>
                    </div>
                  <?php endif; ?>
                </span>
              </div>
              <h3 class="text-xl font-bold text-[#7C3AED] mb-1"><?= htmlspecialchars($member['name']) ?></h3>
              <p class="text-sm text-[#F59E0B] font-medium mb-4"><?= htmlspecialchars($member['role']) ?></p>
              <?php if (!empty($member['bio'])): ?>
                <p class="text-gray-600 text-sm mb-6"><?= nl2br(htmlspecialchars($member['bio'])) ?></p>
              <?php endif; ?>
              <div class="flex justify-center space-x-3">
                <?php if (!empty($member['facebook_url'])): ?>
                  <a href="<?= htmlspecialchars($member['facebook_url']) ?>" target="_blank" class="text-[#7C3AED] hover:text-[#5B23B4] transition-colors"><i class="fab fa-facebook-f"></i></a>
                <?php endif; ?>
                <?php if (!empty($member['twitter_url'])): ?>
                  <a href="<?= htmlspecialchars($member['twitter_url']) ?>" target="_blank" class="text-[#7C3AED] hover:text-[#5B23B4] transition-colors"><i class="fab fa-twitter"></i></a>
                <?php endif; ?>
                <?php if (!empty($member['instagram_url'])): ?>
                  <a href="<?= htmlspecialchars($member['instagram_url']) ?>" target="_blank" class="text-[#7C3AED] hover:text-[#5B23B4] transition-colors"><i class="fab fa-instagram"></i></a>
                <?php endif; ?>
                <?php if (!empty($member['linkedin_url'])): ?>
                  <a href="<?= htmlspecialchars($member['linkedin_url']) ?>" target="_blank" class="text-[#7C3AED] hover:text-[#5B23B4] transition-colors"><i class="fab fa-linkedin-in"></i></a>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-span-full text-center py-12">
            <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">No team members found. Please check back later.</p>
          </div>
        <?php endif; ?>
          </div>
      <!-- Team Member 6 (Optional) -->
      <div class="backdrop-blur-xl bg-white/80 border border-[#F59E0B]/10 shadow-2xl rounded-2xl p-8 text-center transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl group relative">
        <div class="flex justify-center mb-4">
          <span class="inline-block rounded-full ring-4 ring-[#F59E0B] shadow-lg overflow-hidden">
            <img src="assets/images/galleries/MINISTER LUTTA MUMIA.jpg" alt="MINISTER LUTTAH MUMIA" class="rounded-full h-32 w-32 object-cover group-hover:scale-110 group-hover:shadow-[0_0_0_6px_rgba(245,158,11,0.15)] transition-transform duration-300" />
          </span>
        </div>
        <h3 class="text-xl font-bold text-gray-900 font-montserrat mb-1">MINISTER LUTTAH MUMIA</h3>
        <p class="text-sm text-red-600 font-opensans mb-2">Spiritual Guidance</p>
        <p class="text-gray-600 text-sm font-opensans">Passionate servant of God, “speak My Lord for your servant is listening.”</p>
      </div>
        </div>
      </div>
    </section>
<!-- End Team Section -->

    <!-- CALL TO ACTION SECTION -->
    <section class="bg-gradient-to-br from-[#7C3AED]/10 to-[#F59E0B]/10 rounded-3xl p-12 mb-16 text-center">
      <h2 class="text-3xl md:text-4xl font-bold text-[#7C3AED] mb-6">Connect With Our Team</h2>
      <p class="text-lg text-gray-600 mb-8 max-w-2xl mx-auto">
        Ready to connect with our leadership team? Whether you have questions, want to join our ministry, or need prayer support, we're here for you.
      </p>
      <div class="flex flex-col md:flex-row gap-6 justify-center items-center">
      <a href="contact.html" class="group px-8 py-4 bg-[#F59E0B] text-white font-bold rounded-full shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105">
          <i class="fas fa-envelope mr-2"></i>Contact Us
        </a>
        <a href="https://chat.whatsapp.com/F1BIzqQTulA5t5XlUDLWhK" class="group px-8 py-4 bg-green-500 text-white font-bold rounded-full shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105">
          <i class="fab fa-whatsapp mr-2"></i>WhatsApp
        </a>
        <a href="discipleship.html" class="group px-8 py-4 bg-transparent text-[#7C3AED] font-bold rounded-full border-2 border-[#7C3AED] hover:bg-[#7C3AED] hover:text-white transition-all duration-300 transform hover:scale-105">
          <i class="fas fa-users mr-2"></i>Join Ministry
        </a>
      </div>
    </section>

  <div id="site-footer" ></div>
  
  <!-- Load dynamic header and footer -->
  <script src="../assets/main.js"></script>

  <script src="assets/js/main.js"></script>
</body>
</html> 