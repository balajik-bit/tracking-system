<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit();
}


$name = $_POST['name'] ?? '';
$description = $_POST['description'] ?? '';
$price = $_POST['price'] ?? 0;

if (empty($name) || empty($price)) {
    echo json_encode(['status' => 'error', 'message' => 'Name and price are required']);
    exit();
}


$image = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_type = $_FILES['image']['type'];
    
    if (!in_array($file_type, $allowed_types)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Only JPG, PNG and GIF are allowed']);
        exit();
    }

    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $image = uniqid() . '.' . $file_extension;
    $upload_path = 'uploads/' . $image;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to upload image']);
        exit();
    }
}

try {
    $conn = new mysqli('localhost', 'root', '', 'db');
    
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    $stmt = $conn->prepare('INSERT INTO products (name, description, price, image) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssds', $name, $description, $price, $image);
    
    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Product added successfully',
            'product_id' => $stmt->insert_id
        ]);
    } else {
        throw new Exception('Failed to add product: ' . $stmt->error);
    }

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 