<?php
// Database configuration
$host = 'localhost';
$dbname = 'hearts_after_god_db';
$username = 'root';
$password = '';

// Create connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // SQL to insert a test blog post
    $sql = "INSERT INTO blog_posts (title, content) VALUES (:title, :content)";
    
    // Prepare statement
    $stmt = $pdo->prepare($sql);
    
    // Bind parameters
    $title = "Test Blog Post";
    $content = "This is a test blog post content. It should appear in the blog posts list after creation.";
    
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':content', $content);
    
    // Execute the query
    $stmt->execute();
    
    echo "Test blog post created successfully! ID: " . $pdo->lastInsertId();
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
