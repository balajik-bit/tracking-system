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

$name = $_POST['name'] ?? '';
$phone = $_POST['phone'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($name) || empty($phone) || empty($email) || empty($password) || empty($confirm_password)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
    exit;
}

if ($password !== $confirm_password) {
    echo json_encode(['status' => 'error', 'message' => 'Passwords do not match']);
    exit;
}

try {
    $conn = new mysqli('localhost', 'root', '', 'db');
    
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    // Check if email already exists
    $stmt = $conn->prepare('SELECT id FROM admins WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
        exit;
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new admin
    $stmt = $conn->prepare('INSERT INTO admins (name, phone, email, password) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssss', $name, $phone, $email, $hashed_password);
    
    if ($stmt->execute()) {
        // Set session variables
        $_SESSION['admin_id'] = $stmt->insert_id;
        $_SESSION['admin_name'] = $name;
        $_SESSION['admin_email'] = $email;

        echo json_encode([
            'status' => 'success',
            'message' => 'Account created successfully',
            'redirect' => 'admin_dashboard.html'
        ]);
    } else {
        throw new Exception('Failed to create account: ' . $stmt->error);
    }

} catch (Exception $e) {
    error_log("Signup Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while creating your account. Please try again.'
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
