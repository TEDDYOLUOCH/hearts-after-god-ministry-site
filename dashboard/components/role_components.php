<?php
// Role-based components that will be loaded dynamically

// Discipleship User Components
function renderDiscipleshipDashboard() {
    return '<?php include __DIR__ . "/discipleship_dashboard.php"; ?>';
}

// Ministry Leader Components
function renderMinistryLeaderDashboard() {
    return '<?php include __DIR__ . "/ministry_leader_dashboard.php"; ?>';
}

// Blogger Components
function renderBloggerDashboard() {
    return '<?php include __DIR__ . "/blogger_dashboard.php"; ?>';
}

// Media Team Components
function renderMediaTeamDashboard() {
    return '<?php include __DIR__ . "/media_team_dashboard.php"; ?>';
}

// Admin Components
function renderAdminDashboard() {
    return '<?php include __DIR__ . "/admin_dashboard.php"; ?>';
}
?>
