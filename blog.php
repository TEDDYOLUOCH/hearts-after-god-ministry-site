<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if we're viewing a single post
$single_post_slug = $_GET['post'] ?? '';
$is_single_post = !empty($single_post_slug);

// Database configuration
$host = 'localhost';
$db   = 'hearts_after_god_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_STRINGIFY_FETCHES  => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Test the connection
    $pdo->query("SELECT 1");
    
    // Debug: Show database info
    echo "<!-- Database: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . " " . 
         $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . " -->\n";
    
    // Debug: Show all tables in the database
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<!-- Database tables: " . print_r($tables, true) . " -->\n";
    
    // If blog_posts exists, show its structure
    if (in_array('blog_posts', $tables)) {
        $columns = $pdo->query("SHOW COLUMNS FROM blog_posts")->fetchAll(PDO::FETCH_COLUMN);
        echo "<!-- blog_posts columns: " . print_r($columns, true) . " -->\n";
    }
    
    // Debug: Show that we're starting the script
    echo "<!-- Starting blog.php script -->\n";
    
    // Debug: Log request parameters
    echo "<!-- Request parameters: " . print_r($_GET, true) . " -->\n";
    
    // Get the requested category if any
    $category_slug = $_GET['category'] ?? '';
    $search_query = $_GET['s'] ?? '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $posts_per_page = 6;
    $offset = ($page - 1) * $posts_per_page;

    // Build the base query with explicit column names
    $query = "SELECT p.id, p.title, p.slug, p.excerpt, p.content, p.featured_image, 
                     p.status, p.published_at, p.created_at, p.updated_at, p.author_id,
                     u.name as author_name
              FROM blog_posts p
              LEFT JOIN users u ON p.author_id = u.id
              WHERE 1=1";
    
    $params = [];
    
    // Add status filter - handle case sensitivity
    $query .= " AND (";
    $query .= " (UPPER(p.status) = 'PUBLISHED' AND (p.published_at IS NULL OR p.published_at <= NOW()))";
    $query .= " OR (p.status = 'published' AND (p.published_at IS NULL OR p.published_at <= NOW()))";
    $query .= ")";
    
    // Add category filter if specified
    if (!empty($category_slug)) {
        $query .= " AND p.id IN (
            SELECT pc.post_id 
            FROM blog_post_categories pc
            JOIN blog_categories c ON pc.category_id = c.id
            WHERE c.slug = :category_slug
        )";
        $params[':category_slug'] = $category_slug;
    }
    
    // Add search filter if specified
    if (!empty($search_query)) {
        $query .= " AND (p.title LIKE :search OR p.content LIKE :search OR p.excerpt LIKE :search)";
        $params[':search'] = "%$search_query%";
    }
    
    // First, get the base query with author name but without categories
    $base_query = str_replace(
        "SELECT p.*",
        "SELECT p.id, p.title, p.slug, p.excerpt, p.content, p.featured_image, 
                     p.status, p.published_at, p.created_at, p.updated_at, p.author_id,
                     u.name as author_name",
        $query
    );
    
    // Debug: Show the query and parameters
    echo "<!-- Query: " . htmlspecialchars($query) . " -->\n";
    echo "<!-- Params: " . print_r($params, true) . " -->\n";
    
    // First, get the total count without pagination
    $count_query = "SELECT COUNT(DISTINCT p.id) as total 
                   FROM blog_posts p
                   LEFT JOIN users u ON p.author_id = u.id
                   WHERE " . substr($query, strpos($query, 'WHERE') + 6);
    
    // Remove any GROUP BY, ORDER BY, or LIMIT clauses from the count query
    $count_query = preg_replace('/\s+GROUP\s+BY\s+.*?(?=ORDER|LIMIT|$)/i', '', $count_query);
    $count_query = preg_replace('/\s+ORDER\s+BY\s+.*?(?=LIMIT|$)/i', '', $count_query);
    $count_query = preg_replace('/\s+LIMIT\s+.*$/i', '', $count_query);
    
    // Debug: Show the count query
    echo "<!-- Count Query: " . htmlspecialchars($count_query) . " -->\n";
    
    $stmt = $pdo->prepare($count_query);
    $stmt->execute($params);
    $total_posts = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_posts / $posts_per_page);
    
    // Debug: Show pagination info
    echo "<!-- Total posts: $total_posts, Page: $page, Posts per page: $posts_per_page -->\n";
    
    // Add ordering and pagination to the base query
    $query = $base_query . " ORDER BY COALESCE(p.published_at, p.created_at) DESC LIMIT :offset, :limit";
    
    // Debug: Show final query
    echo "<!-- Final Query: " . htmlspecialchars($query) . " -->\n";
    
    // Debug: Show final query with parameters
    $debug_query = $query;
    $debug_params = $params;
    $debug_params[':offset'] = $offset;
    $debug_params[':limit'] = $posts_per_page;
    
    foreach ($debug_params as $key => $value) {
        $debug_query = str_replace($key, "'" . $value . "'", $debug_query);
    }
    echo "<!-- Final Query with params: " . htmlspecialchars($debug_query) . " -->\n";
    
    // Prepare and execute the query with all parameters
    $stmt = $pdo->prepare($query);
    
    // Bind all parameters including pagination
    foreach ($params as $key => $value) {
        $param_type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindValue($key, $value, $param_type);
    }
    
    // Bind pagination parameters with explicit types
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $posts_per_page, PDO::PARAM_INT);
    
    // Debug: Show all bound parameters
    echo "<!-- All Bound Parameters: " . print_r(array_merge($params, [':offset' => $offset, ':limit' => $posts_per_page]), true) . " -->\n";
    
    // Execute the query
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug: Show number of posts found
    echo "<!-- Found " . count($posts) . " posts -->\n";
    
    // Get categories for each post in a separate query
    if (!empty($posts)) {
        $post_ids = array_column($posts, 'id');
        $placeholders = rtrim(str_repeat('?,', count($post_ids)), ',');
        
        $categories_query = "SELECT pc.post_id, GROUP_CONCAT(DISTINCT c.name ORDER BY c.name ASC SEPARATOR ', ') as categories
                           FROM blog_post_categories pc
                           JOIN blog_categories c ON pc.category_id = c.id
                           WHERE pc.post_id IN ($placeholders)
                           GROUP BY pc.post_id";
        
        $stmt = $pdo->prepare($categories_query);
        $stmt->execute($post_ids);
        $post_categories = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $post_categories[$row['post_id']] = $row['categories'];
        }
        
        // Add categories to each post
        foreach ($posts as &$post) {
            $post['categories'] = $post_categories[$post['id']] ?? '';
        }
        unset($post); // Unset reference
    }
    
    // Get categories for sidebar
    $categories = $pdo->query("SELECT c.*, COUNT(pc.post_id) as post_count 
                              FROM blog_categories c
                              LEFT JOIN blog_post_categories pc ON c.id = pc.category_id
                              LEFT JOIN blog_posts p ON pc.post_id = p.id AND p.status = 'published' 
                                AND (p.published_at IS NULL OR p.published_at <= NOW())
                              GROUP BY c.id")->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent posts for sidebar
    $recent_posts = $pdo->query("SELECT id, title, slug, created_at 
                                 FROM blog_posts 
                                 WHERE status = 'published' 
                                 AND (published_at IS NULL OR published_at <= NOW())
                                 ORDER BY created_at DESC 
                                 LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    // Log the full error for debugging
    $error_message = $e->getMessage();
    error_log("Database Error: " . $error_message);
    
    // Display detailed error for debugging
    if (ini_get('display_errors')) {
        $error = "<strong>Database Error:</strong> " . htmlspecialchars($error_message) . "<br><br>";
        $error .= "<strong>File:</strong> " . $e->getFile() . "<br>";
        $error .= "<strong>Line:</strong> " . $e->getLine() . "<br>";
        $error .= "<strong>Query:</strong> " . htmlspecialchars($query ?? 'N/A') . "<br>";
        $error .= "<strong>Parameters:</strong> " . print_r($params ?? [], true) . "<br>";
    } else {
        $error = "Sorry, we're experiencing technical difficulties. Please try again later.";
    }
    
    // Display error message
    echo "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 m-4' role='alert'>";
    echo "<p class='font-bold'>Error</p>";
    echo "<p>" . $error . "</p>";
    echo "</div>";
    
    // Set empty arrays for the rest of the page
    $posts = [];
    $categories = [];
    $recent_posts = [];
    $total_posts = 0;
    $total_pages = 1;
} // End of catch block
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Blog - Hearts After God Ministry</title>
  <meta name="description" content="Read inspiring blog posts and articles from Hearts After God Ministry. Get insights, devotionals, and spiritual growth resources.">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:400,700,900&display=swap">
  <style>
    body { font-family: 'Nunito', sans-serif; }
    h1, h2, h3, h4, h5, h6 { font-family: 'Montserrat', sans-serif; }
    .gradient-text { background: linear-gradient(135deg, #7C3AED, #F59E0B); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
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
    .post-content a:hover { color: #4338ca; }
  </style>
</head>
<body class="bg-gray-50 text-gray-900">

  <!-- HEADER / NAVIGATION -->
  <div id="site-header" class="sticky top-0 z-50 w-full">
    <?php include 'header.html'; ?>
  </div>
  
  <!-- Main Content -->
  <main class="container mx-auto px-4 py-12">
    <div class="flex flex-col lg:flex-row gap-8">
      <!-- Main Content Area -->
      <div class="w-full lg:w-2/3">
        <?php if ($is_single_post): ?>
          <?php 
          // Fetch the specific post
          $post = null;
          $post_query = "SELECT p.*, u.name as author_name 
                        FROM blog_posts p 
                        LEFT JOIN users u ON p.author_id = u.id 
                        WHERE p.slug = ? 
                        AND (p.status = 'published' OR p.status = 'PUBLISHED')
                        AND (p.published_at IS NULL OR p.published_at <= NOW())";
          
          $stmt = $pdo->prepare($post_query);
          $stmt->execute([$single_post_slug]);
          $post = $stmt->fetch(PDO::FETCH_ASSOC);
          
          if ($post): 
            $post_date = new DateTime($post['created_at']);
            $formatted_date = $post_date->format('F j, Y');
          ?>
            <a href="blog.php" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 mb-6">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
              </svg>
              Back to All Posts
            </a>
            
            <article class="bg-white rounded-xl shadow-md overflow-hidden">
              <?php if (!empty($post['featured_image'])): ?>
                <div class="h-96 overflow-hidden">
                  <img src="<?= htmlspecialchars($post['featured_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="w-full h-full object-cover">
                </div>
              <?php endif; ?>
              
              <div class="p-8">
                <div class="flex items-center text-sm text-gray-500 mb-4">
                  <span><?= $formatted_date ?></span>
                  <?php if (!empty($post['author_name'])): ?>
                    <span class="mx-2">•</span>
                    <span>By <?= htmlspecialchars($post['author_name']) ?></span>
                  <?php endif; ?>
                </div>
                
                <h1 class="text-4xl font-bold text-gray-900 mb-6"><?= htmlspecialchars($post['title']) ?></h1>
                
                <?php 
                // Get categories for this post
                $categories_query = "SELECT c.name 
                                   FROM blog_categories c 
                                   JOIN blog_post_categories pc ON c.id = pc.category_id 
                                   WHERE pc.post_id = ?";
                $stmt = $pdo->prepare($categories_query);
                $stmt->execute([$post['id']]);
                $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                if (!empty($categories)): 
                ?>
                  <div class="flex flex-wrap gap-2 mb-6">
                    <?php foreach ($categories as $category): 
                      $category_slug = strtolower(str_replace(' ', '-', $category));
                    ?>
                      <a href="blog.php?category=<?= $category_slug ?>" class="text-sm font-medium px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full hover:bg-indigo-200 transition-colors">
                        <?= htmlspecialchars($category) ?>
                      </a>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
                
                <div class="prose max-w-none">
                  <?= $post['content'] ?>
                </div>
              </div>
            </article>
            
          <?php else: ?>
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-8 rounded-lg text-center">
              <p class="text-lg">The requested blog post was not found.</p>
              <a href="blog.php" class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                Browse All Posts
              </a>
            </div>
          <?php endif; ?>
          
        <?php else: ?>
          <?php if (!empty($search_query)): ?>
            <div class="mb-8">
              <h1 class="text-3xl font-bold text-gray-900 mb-2">Search Results for "<?= htmlspecialchars($search_query) ?>"</h1>
              <p class="text-gray-600"><?= $total_posts ?> post<?= $total_posts != 1 ? 's' : '' ?> found</p>
            </div>
          <?php elseif (!empty($category_slug)): ?>
            <?php 
            $category_name = '';
            $cat_stmt = $pdo->prepare("SELECT name FROM blog_categories WHERE slug = ?");
            $cat_stmt->execute([$category_slug]);
            $category = $cat_stmt->fetch(PDO::FETCH_ASSOC);
            $category_name = $category ? $category['name'] : 'Unknown Category';
            ?>
            <div class="mb-8">
              <h1 class="text-3xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($category_name) ?></h1>
              <p class="text-gray-600"><?= $total_posts ?> post<?= $total_posts != 1 ? 's' : '' ?> in this category</p>
            </div>
          <?php else: ?>
            <h1 class="text-4xl font-bold text-gray-900 mb-8">Latest Blog Posts</h1>
          <?php endif; ?>
          
          <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
              <span class="block sm:inline"><?= $error ?></span>
            </div>
          <?php elseif (empty($posts)): ?>
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-8 rounded-lg text-center">
              <p class="text-lg">No blog posts found.</p>
              <?php if (!empty($search_query) || !empty($category_slug)): ?>
                <a href="blog.php" class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                  View All Posts
                </a>
              <?php endif; ?>
            </div>
          <?php else: ?>
            <div class="grid gap-8">
              <?php foreach ($posts as $post): 
                $post_date = new DateTime($post['created_at']);
                $formatted_date = $post_date->format('F j, Y');
                $excerpt = !empty($post['excerpt']) ? $post['excerpt'] : substr(strip_tags($post['content']), 0, 200) . '...';
              ?>
                <article class="bg-white rounded-xl shadow-md overflow-hidden transition-transform duration-300 hover:shadow-xl">
                  <?php if (!empty($post['featured_image'])): ?>
                    <div class="h-64 overflow-hidden">
                      <img src="<?= htmlspecialchars($post['featured_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="w-full h-full object-cover">
                    </div>
                  <?php endif; ?>
                  <div class="p-6">
                    <div class="flex items-center text-sm text-gray-500 mb-3">
                      <span><?= $formatted_date ?></span>
                      <?php if (!empty($post['author_name'])): ?>
                        <span class="mx-2">•</span>
                        <span>By <?= htmlspecialchars($post['author_name']) ?></span>
                      <?php endif; ?>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-3 hover:text-indigo-600 transition-colors">
                      <a href="blog.php?post=<?= $post['slug'] ?>"><?= htmlspecialchars($post['title']) ?></a>
                    </h2>
                    <?php if (!empty($post['categories'])): ?>
                      <div class="flex flex-wrap gap-2 mb-4">
                        <?php 
                        $category_list = explode(', ', $post['categories']);
                        foreach ($category_list as $category): 
                          $category_slug = strtolower(str_replace(' ', '-', $category));
                        ?>
                          <a href="/blog/category/<?= $category_slug ?>" class="text-xs font-medium px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full hover:bg-indigo-200 transition-colors">
                            <?= htmlspecialchars(trim($category)) ?>
                          </a>
                        <?php endforeach; ?>
                      </div>
                    <?php endif; ?>
                    <p class="text-gray-600 mb-4"><?= $excerpt ?></p>
                    <a href="blog.php?post=<?= $post['slug'] ?>" class="inline-flex items-center text-indigo-600 font-medium hover:text-indigo-800 transition-colors">
                      Read More
                      <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                      </svg>
                    </a>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
              <div class="mt-12 flex justify-center">
                <nav class="flex items-center space-x-2">
                  <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?><?= !empty($category_slug) ? '&category=' . urlencode($category_slug) : '' ?><?= !empty($search_query) ? '&s=' . urlencode($search_query) : '' ?>" 
                       class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                      Previous
                    </a>
                  <?php endif; ?>
                  
                  <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?><?= !empty($category_slug) ? '&category=' . urlencode($category_slug) : '' ?><?= !empty($search_query) ? '&s=' . urlencode($search_query) : '' ?>" 
                       class="px-4 py-2 border rounded-md text-sm font-medium <?= $i === $page ? 'bg-indigo-600 text-white border-indigo-600' : 'border-gray-300 text-gray-700 hover:bg-gray-50' ?>">
                      <?= $i ?>
                    </a>
                  <?php endfor; ?>
                  
                  <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?><?= !empty($category_slug) ? '&category=' . urlencode($category_slug) : '' ?><?= !empty($search_query) ? '&s=' . urlencode($search_query) : '' ?>" 
                       class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                      Next
                    </a>
                  <?php endif; ?>
                </nav>
              </div>
            <?php endif; ?>
          <?php endif; ?>
        <?php endif; ?>
      </div>
      
      <!-- Sidebar -->
      <aside class="w-full lg:w-1/3 space-y-8">
        <!-- Search Widget -->
        <div class="bg-white p-6 rounded-xl shadow-md">
          <h3 class="text-lg font-bold text-gray-900 mb-4">Search</h3>
          <form action="/blog.php" method="get" class="flex">
            <input type="text" name="s" value="<?= htmlspecialchars($search_query) ?>" 
                   placeholder="Search posts..." 
                   class="flex-1 px-4 py-2 border border-r-0 rounded-l-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-r-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              <i class="fas fa-search"></i>
            </button>
          </form>
        </div>
        
        <!-- Categories Widget -->
        <?php if (!empty($categories)): ?>
          <div class="bg-white p-6 rounded-xl shadow-md">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Categories</h3>
            <ul class="space-y-2">
              <?php foreach ($categories as $category): ?>
                <li>
                  <a href="/blog/category/<?= $category['slug'] ?>" 
                     class="flex items-center justify-between text-gray-700 hover:text-indigo-600 transition-colors">
                    <span><?= htmlspecialchars($category['name']) ?></span>
                    <span class="bg-gray-100 text-gray-600 text-xs font-medium px-2.5 py-0.5 rounded-full">
                      <?= $category['post_count'] ?: 0 ?>
                    </span>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
        
        <!-- Recent Posts Widget -->
        <?php if (!empty($recent_posts)): ?>
          <div class="bg-white p-6 rounded-xl shadow-md">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Recent Posts</h3>
            <ul class="space-y-4">
              <?php foreach ($recent_posts as $recent_post): 
                $post_date = new DateTime($recent_post['created_at']);
                $formatted_date = $post_date->format('M j, Y');
              ?>
                <li class="flex items-start space-x-3">
                  <?php if (!empty($recent_post['featured_image'])): ?>
                    <img src="<?= htmlspecialchars($recent_post['featured_image']) ?>" 
                         alt="<?= htmlspecialchars($recent_post['title']) ?>" 
                         class="w-16 h-16 object-cover rounded-md">
                  <?php else: ?>
                    <div class="w-16 h-16 bg-gray-100 rounded-md flex items-center justify-center text-gray-400">
                      <i class="fas fa-newspaper text-xl"></i>
                    </div>
                  <?php endif; ?>
                  <div class="flex-1">
                    <a href="/blog/<?= $recent_post['slug'] ?>" class="font-medium text-gray-900 hover:text-indigo-600 transition-colors">
                      <?= htmlspecialchars($recent_post['title']) ?>
                    </a>
                    <div class="text-sm text-gray-500"><?= $formatted_date ?></div>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
        
        <!-- Newsletter Signup -->
        <div class="bg-indigo-50 p-6 rounded-xl">
          <h3 class="text-lg font-bold text-gray-900 mb-2">Subscribe to our Newsletter</h3>
          <p class="text-gray-600 text-sm mb-4">Get the latest updates and news delivered to your inbox.</p>
          <form class="space-y-3">
            <input type="email" placeholder="Your email address" 
                   class="w-full px-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              Subscribe
            </button>
          </form>
        </div>
      </aside>
    </div>
  </main>

  <!-- Footer -->
  <footer class="bg-gradient-to-r from-[#1E40AF] to-[#7C3AED] text-white pt-2 pb-1 px-4">
    <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-10">
      <!-- Left: Logo, Name, Mission, Social -->
      <div class="flex flex-col items-start">
        <div class="flex items-center gap-3 mb-2">
          <img src="assets/images/logo.jpg" alt="Logo" class="h-14 w-14 rounded-full border-4 border-white object-cover shadow-xl"/>
          <div>
            <span class="text-2xl font-extrabold text-white leading-tight">Hearts After God</span><br/>
            <span class="text-sm text-white/80">Ministry</span>
          </div>
        </div>
        <p class="text-white/80 text-sm mb-4">A global online ministry advancing the gospel through digital platforms, prayer, and discipleship.</p>
        <div class="flex flex-wrap gap-3 mb-4">
          <a href="mailto:heartsaftergodministries254@gmail.com" target="_blank" aria-label="Gmail" class="bg-white/10 hover:bg-[#EA4335] text-white rounded-full p-3 transition flex items-center justify-center"><i class="fab fa-google"></i></a>
          <a href="https://chat.whatsapp.com/F1BIzqQTulA5t5XlUDLWhK" target="_blank" aria-label="WhatsApp" class="bg-white/10 hover:bg-[#25D366] text-white rounded-full p-3 transition flex items-center justify-center"><i class="fab fa-whatsapp"></i></a>
          <a href="https://www.facebook.com/share/g/16NwpW8sCB/" target="_blank" aria-label="Facebook" class="bg-white/10 hover:bg-[#1877F3] text-white rounded-full p-3 transition flex items-center justify-center"><i class="fab fa-facebook-f"></i></a>
          <a href="https://www.instagram.com/reel/DK2MTiVCR-k/?igsh=NngyM2p4anFmaG9l" target="_blank" aria-label="Instagram" class="bg-white/10 hover:bg-gradient-to-tr from-[#F58529] via-[#DD2A7B] to-[#515BD4] text-white rounded-full p-3 transition flex items-center justify-center"><i class="fab fa-instagram"></i></a>
          <a href="https://hearts-after-god-ministry-site.vercel.app/index.html" target="_blank" aria-label="Ministry Website" class="bg-white/10 hover:bg-[#7C3AED] text-white rounded-full p-3 transition flex items-center justify-center"><i class="fas fa-globe"></i></a>
        </div>
      </div>
      
      <!-- Quick Links -->
      <div>
        <h3 class="text-xl font-bold mb-4 flex items-center gap-2"><i class="fas fa-link text-[#F59E0B]"></i>Quick Links</h3>
        <ul class="space-y-2">
          <li><a href="index.html" class="text-white/80 hover:text-white transition">Home</a></li>
          <li><a href="about.html" class="text-white/80 hover:text-white transition">About Us</a></li>
          <li><a href="sermons.html" class="text-white/80 hover:text-white transition">Sermons</a></li>
          <li><a href="events.html" class="text-white/80 hover:text-white transition">Events</a></li>
          <li><a href="gallery.php" class="text-white/80 hover:text-white transition">Gallery</a></li>
          <li><a href="contact.html" class="text-white/80 hover:text-white transition">Contact Us</a></li>
        </ul>
      </div>
      
      <!-- Ministry Links -->
      <div>
        <h3 class="text-xl font-bold mb-4 flex items-center gap-2"><i class="fas fa-cross text-[#F59E0B]"></i>Ministry</h3>
        <ul class="space-y-2">
          <li><a href="discipleship.html" class="text-white/80 hover:text-white transition">Discipleship</a></li>
          <li><a href="prayer-request.html" class="text-white/80 hover:text-white transition">Prayer Request</a></li>
          <li><a href="testimonies.html" class="text-white/80 hover:text-white transition">Testimonies</a></li>
          <li><a href="give.html" class="text-white/80 hover:text-white transition">Give</a></li>
          <li><a href="volunteer.html" class="text-white/80 hover:text-white transition">Volunteer</a></li>
        </ul>
      </div>
      
      <!-- Contact Info -->
      <div>
        <h3 class="text-xl font-bold mb-4 flex items-center gap-2"><i class="fas fa-address-book text-[#F59E0B]"></i>Contact Info</h3>
        <div class="flex items-center gap-2 mb-2">
          <span class="bg-[#F59E0B] rounded-xl p-2"><i class="fas fa-map-marker-alt"></i></span>
          <div>
            <span class="font-bold">Location</span><br/>
            <span class="text-white/80 text-sm">PO.BOX 00100 NAIROBI, KENYA</span>
          </div>
        </div>
        <div class="flex items-center gap-2 mb-2">
          <span class="bg-[#F59E0B] rounded-xl p-2"><i class="fas fa-globe"></i></span>
          <div>
            <span class="font-bold">Ministry</span><br/>
            <span class="text-white/80 text-sm">Global online ministry</span>
          </div>
        </div>
        <div class="flex items-center gap-2 mb-2">
          <span class="bg-[#F59E0B] rounded-xl p-2"><i class="fas fa-phone"></i></span>
          <div>
            <span class="font-bold">Phone</span><br/>
            <span class="text-white/80 text-sm">0707529090</span>
          </div>
        </div>
        <div class="flex items-center gap-2 mb-2">
          <span class="bg-[#F59E0B] rounded-xl p-2"><i class="fas fa-envelope"></i></span>
          <div>
            <span class="font-bold">Email</span><br/>
            <span class="text-white/80 text-sm">heartsaftergodministries254@gmail.com</span>
          </div>
        </div>
      </div>
    </div>
    <div class="text-center text-white/70 text-sm mt-8">&copy; <?php echo date('Y'); ?> Hearts After God Ministry. All rights reserved.</div>
  </footer>
  
  <!-- Load dynamic header -->
  <script src="assets/js/main.js"></script>

  <script>
    // Mobile menu toggle
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuButton && mobileMenu) {
      mobileMenuButton.addEventListener('click', () => {
        const isExpanded = mobileMenuButton.getAttribute('aria-expanded') === 'true';
        mobileMenuButton.setAttribute('aria-expanded', !isExpanded);
        mobileMenu.classList.toggle('hidden');
      });
    }
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
      return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const targetId = this.getAttribute('href');
        if (targetId === '#') return;
        
        const targetElement = document.querySelector(targetId);
        if (targetElement) {
          window.scrollTo({
            top: targetElement.offsetTop - 80, // Adjust for fixed header
            behavior: 'smooth'
          });
        }
      });
    });
    
    // Add active class to current nav item
    const currentPage = window.location.pathname.split('/').pop() || 'index.php';
    document.querySelectorAll('nav a').forEach(link => {
      const href = link.getAttribute('href');
      if (href && (href.includes(currentPage) || 
          (currentPage === 'index.php' && (href === '/' || href === '' || href === 'index.php')))) {
        link.classList.add('active');
      }
    });
  </script>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/main.js"></script>
</body>
</html>
