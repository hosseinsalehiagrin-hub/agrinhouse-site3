<?php
header('Content-Type: application/json');
require 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $name = trim($data['name'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = trim($data['password'] ?? '');

    if (empty($name) || empty($email) || empty($password)) {
        error_log('[ERROR] signup.php: Empty name, email, or password');
        echo json_encode(['success' => false, 'message' => 'Please complete all fields']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log('[ERROR] signup.php: Invalid email: ' . $email);
        echo json_encode(['success' => false, 'message' => 'Invalid email']);
        exit;
    }

    if (strlen($password) < 8) {
        error_log('[ERROR] signup.php: Password too short for email: ' . $email);
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters']);
        exit;
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        error_log('[ERROR] signup.php: Email already exists: ' . $email);
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit;
    }
    $stmt->close();

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Store user
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $hashed_password);
    if ($stmt->execute()) {
        // Set session for the user
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $name;
        $_SESSION['provider'] = 'local';
        $_SESSION['user_avatar'] = ''; // No avatar for local signup
        session_regenerate_id(true); // Regenerate session ID for security

        error_log('[DEBUG] signup.php: Signup successful, session set: ' . print_r($_SESSION, true));
        echo json_encode([
            'success' => true,
            'message' => 'Signup successful',
            'userId' => $conn->insert_id,
            'email' => $email,
            'name' => $name
        ]);
    } else {
        error_log('[ERROR] signup.php: Database error: ' . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Signup failed']);
    }
    $stmt->close();
} else {
    error_log('[ERROR] signup.php: Invalid request method');
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
$conn->close();
?>