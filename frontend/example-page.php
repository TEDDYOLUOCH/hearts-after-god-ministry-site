<?php
// Set the page title
$page_title = 'Example Page';

// Include the header
require_once __DIR__ . '/includes/header-include.php';
?>

<!-- Page Content -->
<div class="container mx-auto px-4 py-12">
    <h1 class="text-4xl font-bold text-center mb-8 font-montserrat">Welcome to Our Example Page</h1>
    
    <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-lg p-8">
        <h2 class="text-2xl font-semibold mb-4 text-[#1E40AF]">This is an Example Page</h2>
        <p class="mb-4">
            This page demonstrates how to use the reusable header and footer includes. The header and footer are included 
            using PHP includes, making it easy to maintain a consistent look and feel across all pages of the website.
        </p>
        <p class="mb-6">
            The header includes the main navigation menu with active link highlighting, and the footer contains contact 
            information, quick links, and social media icons. Both are fully responsive and styled to match the 
            ministry's branding.
        </p>
        
        <div class="bg-gray-100 p-6 rounded-lg mb-6">
            <h3 class="text-lg font-semibold mb-2 text-[#7C3AED]">Key Features:</h3>
            <ul class="list-disc pl-5 space-y-2">
                <li>Responsive design that works on all devices</li>
                <li>Active link highlighting in the navigation</li>
                <li>Mobile-friendly hamburger menu</li>
                <li>Consistent styling using Tailwind CSS</li>
                <li>Easy to update across all pages</li>
            </ul>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-4 mt-8">
            <a href="<?php echo $base_url; ?>/index.html" 
               class="px-6 py-3 bg-gradient-to-r from-[#1E40AF] to-[#7C3AED] text-white font-semibold rounded-lg text-center hover:opacity-90 transition-opacity">
                Back to Home
            </a>
            <a href="#" 
               class="px-6 py-3 bg-gradient-to-r from-[#7C3AED] to-[#B91C1C] text-white font-semibold rounded-lg text-center hover:opacity-90 transition-opacity">
                Call to Action
            </a>
        </div>
    </div>
</div>

<?php
// Include the footer
require_once __DIR__ . '/includes/footer-include.php';
?>
