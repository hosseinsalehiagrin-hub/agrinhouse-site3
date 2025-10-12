<?php
header('Content-Type: application/json');
session_start();

// Log session configuration
error_log('[DEBUG] check_session.php: Session ID: ' . session_id());
error_log('[DEBUG] check_session.php: Session state: ' . print_r($_SESSION, true));

// Check login state
$loggedIn = isset($_SESSION['user_email']) 
    && isset($_SESSION['user_name']) 
    && !empty($_SESSION['user_email']) 
    && !empty($_SESSION['user_name']);

// Prepare response
$response = [
    'loggedIn' => $loggedIn,
    'name'     => $_SESSION['user_name'] ?? '',
    'email'    => $_SESSION['user_email'] ?? '',
    'avatar'   => $_SESSION['user_avatar'] ?? '',   // عکس گوگل اگر ست شده باشه
    'provider' => $_SESSION['provider'] ?? 'local' // نوع ورود (local/google)
];

// Log response
error_log('[DEBUG] check_session.php: Response: ' . json_encode($response));

// Send JSON response
echo json_encode($response);
