<?php
// Google OAuth2 login start
$client_id = 'YOUR_GOOGLE_CLIENT_ID';
$redirect_uri = 'http://localhost/hearts-after-god-ministry-site/backend/google-callback.php';
$scope = 'email profile';
$auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?response_type=code'
    . '&client_id=' . urlencode($client_id)
    . '&redirect_uri=' . urlencode($redirect_uri)
    . '&scope=' . urlencode($scope)
    . '&access_type=online'
    . '&prompt=select_account';
header('Location: ' . $auth_url);
exit; 