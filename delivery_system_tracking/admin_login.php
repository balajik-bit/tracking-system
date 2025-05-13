<?php
session_start();
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Email and password are required']);
    exit;
}

try {
    $conn = new mysqli('localhost', 'root', '', 'db');
    
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    $stmt = $conn->prepare('SELECT id, name, email, password FROM admins WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
        exit;
    }

    $admin = $result->fetch_assoc();
    
    if (!password_verify($password, $admin['password'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
        exit;
    }

    // Set session variables
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_name'] = $admin['name'];
    $_SESSION['admin_email'] = $admin['email'];

    // Return success response
    echo json_encode([
        'status' => 'success',
        'message' => 'Login successful',
        'redirect' => 'admin_dashboard.html'
    ]);

} catch (Exception $e) {
    error_log("Login Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while logging in. Please try again.'
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 