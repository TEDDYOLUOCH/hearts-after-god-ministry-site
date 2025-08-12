<?php
<?php
require_once __DIR__ . '/../config/database.php';

try {
    // Read SQL file
    $sql = file_get_contents(__DIR__ . '/setup.sql');
    
    // Split into individual statements
    $statements = array_filter(
        array_map(
            function($sql) {
                return trim(preg_replace(['/^--.*$/m', '/\/\*.*?\*\//s'], '', $sql));
            },
            explode(';', $sql)
        )
    );
    
    // Execute each statement
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $db->exec($statement);
                echo "Success: " . substr($statement, 0, 50) . "...\n";
            } catch (PDOException $e) {
                echo "Error executing: " . substr($statement, 0, 50) . "...\n";
                echo "Error message: " . $e->getMessage() . "\n";
                throw $e;
            }
        }
    }
    
    echo "\nDatabase setup completed successfully!\n";
    
} catch (PDOException $e) {
    die("Setup failed: " . $e->getMessage() . "\n");
}