<?php
// Contact Form Handler for Hearts After God Ministry
// This script processes contact form submissions and sends emails

// Set headers to prevent caching and allow CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required_fields = ['name', 'email', 'subject', 'message'];
foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

// Sanitize inputs
$name = htmlspecialchars(trim($input['name']));
$email = filter_var(trim($input['email']), FILTER_SANITIZE_EMAIL);
$subject = htmlspecialchars(trim($input['subject']));
$message = htmlspecialchars(trim($input['message']));

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// Ministry email address
$to_email = 'heartsaftergodministries254@gmail.com';
$to_name = 'Hearts After God Ministry';

// Email headers
$headers = array(
    'From: ' . $name . ' <' . $email . '>',
    'Reply-To: ' . $email,
    'X-Mailer: PHP/' . phpversion(),
    'Content-Type: text/html; charset=UTF-8'
);

// Email subject
$email_subject = 'Website Contact: ' . $subject;

// Email body
$email_body = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #7C3AED, #2046B3); color: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .content { background: #f9f9f9; padding: 20px; border-radius: 10px; }
        .field { margin-bottom: 15px; }
        .label { font-weight: bold; color: #7C3AED; }
        .value { margin-top: 5px; }
        .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>Hearts After God Ministry</h2>
            <p>New Contact Form Submission</p>
        </div>
        
        <div class='content'>
            <div class='field'>
                <div class='label'>Name:</div>
                <div class='value'>$name</div>
            </div>
            
            <div class='field'>
                <div class='label'>Email:</div>
                <div class='value'><a href='mailto:$email'>$email</a></div>
            </div>
            
            <div class='field'>
                <div class='label'>Subject:</div>
                <div class='value'>$subject</div>
            </div>
            
            <div class='field'>
                <div class='label'>Message:</div>
                <div class='value'>" . nl2br($message) . "</div>
            </div>
        </div>
        
        <div class='footer'>
            <p><strong>Sent from:</strong> Hearts After God Ministry Website</p>
            <p><strong>Date:</strong> " . date('F j, Y \a\t g:i A') . "</p>
            <p><strong>IP Address:</strong> " . $_SERVER['REMOTE_ADDR'] . "</p>
        </div>
    </div>
</body>
</html>
";

// Send email
$mail_sent = mail($to_email, $email_subject, $email_body, implode("\r\n", $headers));

if ($mail_sent) {
    // Log the contact submission (optional)
    $log_entry = date('Y-m-d H:i:s') . " | Name: $name | Email: $email | Subject: $subject | IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
    file_put_contents('contact_log.txt', $log_entry, FILE_APPEND | LOCK_EX);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Thank you for your message! We will get back to you within 24 hours.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Sorry, there was an error sending your message. Please try again or contact us directly.'
    ]);
}
?> 