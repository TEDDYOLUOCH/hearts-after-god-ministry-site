#!/usr/bin/env php
<?php
/**
 * Database Migration Runner
 * 
 * Run this script from the command line to execute database migrations.
 * Usage: php run_migration.php [--force]
 */

// Only allow this script to be run from the command line
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from the command line.');
}

// Check for --force flag
$force = in_array('--force', $argv) || in_array('-f', $argv);

// Load environment and database configuration
require_once __DIR__ . '/../config/db.php';

// Display banner
echo "========================================\n";
echo "  Hearts After God Ministry - Database Setup\n";
echo "========================================\n\n";

if (!$force) {
    echo "WARNING: This will modify your database structure.\n";
    echo "Make sure you have a backup before proceeding.\n\n";
    
    echo "Do you want to continue? (yes/no) [no]: ";
    $handle = fopen('php://stdin', 'r');
    $response = strtolower(trim(fgets($handle)));
    
    if ($response !== 'yes' && $response !== 'y') {
        echo "\nMigration cancelled.\n";
        exit(0);
    }
}

try {
    // Include the migration script
    require_once __DIR__ . '/migrate.php';
    
    echo "\n\nDatabase setup completed successfully!\n";
    echo "You can now access the admin dashboard with the following credentials:\n";
    echo "Username: admin\n";
    echo "Password: admin123\n\n";
    echo "IMPORTANT: Change the default admin password after your first login.\n";
    
} catch (Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
    exit(1);
}
