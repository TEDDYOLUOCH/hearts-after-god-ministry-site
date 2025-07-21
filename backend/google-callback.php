<?php
// Google OAuth2 callback handler
$client_id = 'YOUR_GOOGLE_CLIENT_ID';
$client_secret = 'YOUR_GOOGLE_CLIENT_SECRET';
$redirect_uri = 'http://localhost/hearts-after-god-ministry-site/backend/google-callback.php';

if (!isset($_GET['code'])) {
    exit('No code provided.');
}

// Exchange code for access token
$token_url = 'https://oauth2.googleapis.com/token';
$data = [
    'code' => $_GET['code'],
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'redirect_uri' => $redirect_uri,
    'grant_type' => 'authorization_code',
];
$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data),
    ],
];
$context  = stream_context_create($options);
$result = file_get_contents($token_url, false, $context);
if ($result === FALSE) {
    exit('Failed to get access token.');
}
$token = json_decode($result, true);

// Get user info
$userinfo_url = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $token['access_token'];
$userinfo = json_decode(file_get_contents($userinfo_url), true);

if (!empty($userinfo['email'])) {
    // Here you would check if the user exists in your DB, create if not, and log them in
    echo '<h2>Google Login Successful</h2>';
    echo '<pre>' . print_r($userinfo, true) . '</pre>';
    // Redirect or set session as needed
} else {
    echo 'Failed to retrieve user info.';
} 