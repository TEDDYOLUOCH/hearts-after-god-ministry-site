<?php
/**
 * Helper functions for the Hearts After God Ministry dashboard
 * @package HearthsAfterGod
 */

// Time and Date Helpers
function time_ago($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' minutes ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' hours ago';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . ' days ago';
    } else {
        return date('M j, Y', $time);
    }
}

// User Related Helpers
function get_user_avatar($user_id) {
    $avatar_path = "/assets/avatars/user_{$user_id}.jpg";
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . $avatar_path)) {
        return $avatar_path;
    }
    return '/assets/default-avatar.png';
}

function get_user_role($user_id) {
    global $db;
    try {
        $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn() ?: 'user';
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return 'user';
    }
}

// Formatting Helpers
function format_number($number) {
    if (!is_numeric($number)) return 0;
    
    if ($number >= 1000000) {
        return round($number / 1000000, 1) . 'M';
    } elseif ($number >= 1000) {
        return round($number / 1000, 1) . 'K';
    }
    return number_format($number);
}

// Security Helpers
function sanitize_output($value) {
    if (is_array($value)) {
        return array_map('sanitize_output', $value);
    }
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function validate_csrf_token() {
    if (!isset($_SESSION['csrf_token']) || 
        !isset($_POST['csrf_token']) || 
        $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
        http_response_code(403);
        die('Invalid CSRF token');
    }
}

// Dashboard Specific Helpers
function get_dashboard_stats() {
    global $db;
    try {
        return [
            'total_posts' => $db->query("SELECT COUNT(*) FROM posts")->fetchColumn(),
            'total_events' => $db->query("SELECT COUNT(*) FROM events")->fetchColumn(),
            'total_members' => $db->query("SELECT COUNT(*) FROM members")->fetchColumn(),
            'recent_activities' => $db->query("SELECT * FROM activities ORDER BY created_at DESC LIMIT 5")->fetchAll()
        ];
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return [
            'total_posts' => 0,
            'total_events' => 0,
            'total_members' => 0,
            'recent_activities' => []
        ];
    }
}

// Error Handling Helper
function handle_error($error_message) {
    error_log($error_message);
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        throw new Exception($error_message);
    }
    return false;
}

// Remove the npm commands from this file as they should be in a separate build process