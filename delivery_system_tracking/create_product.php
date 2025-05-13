<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access'
    ]);
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db";

try {
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        throw new Exception("Invalid request method");
    }

    if (!isset($_POST['name']) || !isset($_POST['description']) || !isset($_POST['price'])) {
        throw new Exception("Missing required fields");
    }

    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);

    if (empty($name)) {
        throw new Exception("Product name is required");
    }

    if ($price <= 0) {
        throw new Exception("Price must be greater than 0");
    }

    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("INSERT INTO products (name, description, price) VALUES (?, ?, ?)");
    $stmt->bind_param("ssd", $name, $description, $price);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to create product: " . $stmt->error);
    }

    $product_id = $conn->insert_id;
    
    $stmt->close();
    $conn->close();

    echo json_encode([
        'status' => 'success',
        'message' => 'Product created successfully',
        'product_id' => $product_id
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 