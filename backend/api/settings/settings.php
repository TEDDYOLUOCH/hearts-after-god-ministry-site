<?php
require_once __DIR__ . '/../BaseApiHandler.php';

// Require admin authentication for all settings operations
requireAdminAuth();

$db = getDb();
$method = $_SERVER['REQUEST_METHOD'];

// Helper function to get setting value by key
function getSetting($key, $default = null) {
    global $db;
    $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? json_decode($result['setting_value'], true) : $default;
}

// Helper function to set setting value
function setSetting($key, $value) {
    global $db;
    $jsonValue = json_encode($value);
    $stmt = $db->prepare("
        INSERT INTO settings (setting_key, setting_value) 
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE setting_value = ?
    
    ");
    return $stmt->execute([$key, $jsonValue, $jsonValue]);
}

// Handle different HTTP methods
switch ($method) {
    case 'GET':
        // Get all settings or a specific setting
        if (isset($_GET['key'])) {
            $value = getSetting($_GET['key']);
            if ($value === null) {
                sendError('Setting not found', 404);
            }
            sendSuccess([$_GET['key'] => $value]);
        } else {
            // Get all settings
            $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
            $settings = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $settings[$row['setting_key']] = json_decode($row['setting_value'], true);
            }
            sendSuccess($settings);
        }
        break;
        
    case 'POST':
    case 'PUT':
        // Update settings
        $data = getJsonInput();
        
        if (empty($data) || !is_array($data)) {
            sendError('Invalid settings data', 400);
        }
        
        try {
            $db->beginTransaction();
            
            foreach ($data as $key => $value) {
                if (!setSetting($key, $value)) {
                    throw new Exception("Failed to update setting: $key");
                }
            }
            
            $db->commit();
            sendSuccess(null, 'Settings updated successfully');
            
        } catch (Exception $e) {
            $db->rollBack();
            sendError('Failed to update settings: ' . $e->getMessage(), 500);
        }
        break;
        
    case 'DELETE':
        // Delete a setting
        $data = getJsonInput();
        if (!isset($data['key'])) {
            sendError('Setting key is required', 400);
        }
        
        try {
            $stmt = $db->prepare("DELETE FROM settings WHERE setting_key = ?");
            $result = $stmt->execute([$data['key']]);
            
            if ($stmt->rowCount() === 0) {
                sendError('Setting not found', 404);
            }
            
            sendSuccess(null, 'Setting deleted successfully');
            
        } catch (Exception $e) {
            sendError('Failed to delete setting: ' . $e->getMessage(), 500);
        }
        break;
        
    default:
        sendError('Method not allowed', 405);
}

// Initialize default settings if they don't exist
function initializeDefaultSettings() {
    global $db;
    
    $defaultSettings = [
        'site_title' => 'Hearts After God Ministry',
        'site_description' => 'A ministry dedicated to serving God and spreading His word',
        'contact_email' => 'info@heartsaftergodministry.org',
        'contact_phone' => '',
        'contact_address' => '',
        'social_facebook' => '',
        'social_twitter' => '',
        'social_instagram' => '',
        'social_youtube' => '',
        'mailer_host' => '',
        'mailer_port' => '587',
        'mailer_username' => '',
        'mailer_password' => '',
        'mailer_encryption' => 'tls',
        'mailer_from_email' => 'noreply@heartsaftergodministry.org',
        'mailer_from_name' => 'Hearts After God Ministry',
        'maintenance_mode' => false,
        'registration_enabled' => true,
        'default_user_role' => 'user',
        'items_per_page' => 10,
        'date_format' => 'F j, Y',
        'time_format' => 'g:i A',
        'timezone' => 'UTC',
        'theme' => 'default',
        'logo_url' => '/assets/images/logo.png',
        'favicon_url' => '/favicon.ico',
        'google_analytics_id' => '',
        'seo_meta_description' => 'Hearts After God Ministry - Growing in faith, serving in love',
        'seo_meta_keywords' => 'ministry, church, god, jesus, faith, worship, bible',
        'seo_meta_author' => 'Hearts After God Ministry',
        'seo_og_image' => '/assets/images/og-image.jpg',
        'enable_comments' => true,
        'comment_moderation' => true,
        'enable_newsletter' => true,
        'homepage_slider' => [],
        'featured_events' => [],
        'featured_sermons' => [],
        'testimonials' => [],
        'team_members' => [],
        'custom_css' => '',
        'custom_js' => '',
        'custom_header' => '',
        'custom_footer' => '',
        'privacy_policy' => '',
        'terms_of_service' => '',
        'cookie_consent' => true,
        'cookie_message' => 'We use cookies to enhance your experience. By continuing to visit this site you agree to our use of cookies.',
        'cookie_learn_more_url' => '/privacy-policy',
        'backup_enabled' => true,
        'backup_frequency' => 'weekly',
        'backup_retention' => 30, // days
        'backup_last_run' => null,
        'backup_next_run' => date('Y-m-d H:i:s', strtotime('+1 week')),
        'storage_driver' => 'local',
        'storage_aws_key' => '',
        'storage_aws_secret' => '',
        'storage_aws_region' => 'us-east-1',
        'storage_aws_bucket' => '',
        'storage_aws_endpoint' => '',
        'storage_aws_url' => '',
        'storage_aws_use_path_style_endpoint' => false,
        'storage_aws_throw' => true,
        'storage_aws_visibility' => 'public',
        'storage_aws_options' => [],
        'storage_local_root' => 'storage/app/public',
        'storage_local_url' => '/storage',
        'storage_local_visibility' => 'public',
        'storage_local_throw' => false,
        'storage_local_options' => []
    ];
    
    try {
        $db->beginTransaction();
        
        foreach ($defaultSettings as $key => $value) {
            $stmt = $db->prepare("SELECT 1 FROM settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            
            if (!$stmt->fetch()) {
                setSetting($key, $value);
            }
        }
        
        $db->commit();
    } catch (Exception $e) {
        $db->rollBack();
        error_log('Failed to initialize default settings: ' . $e->getMessage());
    }
}

// Run initialization on first request
initializeDefaultSettings();
