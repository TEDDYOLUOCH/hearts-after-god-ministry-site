<?php
// Load database configuration from db.php
$dbConfig = require __DIR__ . '/db.php';

// Define constants if not already defined
if (!defined('DB_CONFIG_LOADED')) {
    define('DB_CONFIG_LOADED', true);
    
    // Define database constants from config array if not already defined
    !defined('DB_HOST') && define('DB_HOST', $dbConfig['host']);
    !defined('DB_NAME') && define('DB_NAME', $dbConfig['name']);
    !defined('DB_USER') && define('DB_USER', $dbConfig['user']);
    !defined('DB_PASS') && define('DB_PASS', $dbConfig['pass']);
    
    // Site configuration
    !defined('SITE_URL') && define('SITE_URL', '/hearts-after-god-ministry-site');
    !defined('ROOT_PATH') && define('ROOT_PATH', dirname(__DIR__));
    
    // Error logging configuration
    if (!file_exists(ROOT_PATH . '/logs')) {
        @mkdir(ROOT_PATH . '/logs', 0755, true);
    }
    
    @ini_set('log_errors', 1);
    @ini_set('error_log', ROOT_PATH . '/logs/error.log');
    @error_reporting(E_ALL);
    
    // Set error reporting based on environment
    if (getenv('APP_ENV') === 'development' || $_SERVER['SERVER_NAME'] === 'localhost') {
        @ini_set('display_errors', 1);
    } else {
        @ini_set('display_errors', 0);
    }
}