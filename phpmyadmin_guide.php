<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup Guide</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .step {
            background-color: #f9f9f9;
            border-left: 4px solid #4CAF50;
            padding: 10px 15px;
            margin: 15px 0;
        }
        .important {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 10px 15px;
            margin: 15px 0;
        }
        code {
            background-color: #f0f0f0;
            padding: 2px 5px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <h1>Database Setup Guide</h1>
    
    <div class="step">
        <h2>1. Access phpMyAdmin</h2>
        <p>Open your web browser and go to: <a href="http://localhost/phpmyadmin" target="_blank">http://localhost/phpmyadmin</a></p>
    </div>
    
    <div class="step">
        <h2>2. Check if the Database Exists</h2>
        <p>In the left sidebar of phpMyAdmin, look for a database named <code>hearts_after_god_db</code>.</p>
        <p>If you don't see it, you'll need to create it:</p>
        <ol>
            <li>Click on "New" in the left sidebar</li>
            <li>Enter <code>hearts_after_god_db</code> as the database name</li>
            <li>Select <code>utf8mb4_general_ci</code> as the collation</li>
            <li>Click "Create"</li>
        </ol>
    </div>
    
    <div class="step">
        <h2>3. Import the Database (if you have a .sql file)</h2>
        <p>If you have a database backup (.sql file):</p>
        <ol>
            <li>Select the <code>hearts_after_god_db</code> database</li>
            <li>Click on the "Import" tab</li>
            <li>Click "Choose File" and select your .sql file</li>
            <li>Click "Go" to import the database</li>
        </ol>
    </div>
    
    <div class="step">
        <h2>4. Check Required Tables</h2>
        <p>After importing or creating the database, you should see these tables:</p>
        <ul>
            <li><code>blog_posts</code> - Stores blog posts</li>
            <li><code>blog_categories</code> - Blog post categories</li>
            <li><code>blog_post_categories</code> - Relationship between posts and categories</li>
            <li><code>users</code> - User accounts</li>
        </ul>
    </div>
    
    <div class="important">
        <h2>Important Notes</h2>
        <ul>
            <li>Make sure the MySQL service is running in XAMPP Control Panel</li>
            <li>Default MySQL credentials in XAMPP are usually:
                <ul>
                    <li>Username: <code>root</code></li>
                    <li>Password: <em>(empty)</em></li>
                </ul>
            </li>
            <li>If you're still having issues, check the XAMPP error logs at:
                <code>c:\xampp\mysql\data\mysql_error.log</code>
            </li>
        </ul>
    </div>
    
    <div class="step">
        <h2>5. Test the Connection Again</h2>
        <p>After setting up the database, test the connection again:</p>
        <p><a href="test_mysql.php">Test Database Connection</a></p>
    </div>
</body>
</html>
