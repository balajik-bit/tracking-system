<?php
// Set headers for JSON response
header('Content-Type: application/json');

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Set character set to utf8
    $conn->set_charset("utf8");

    // Check if a specific product ID is requested
    if (isset($_GET['id'])) {
        $product_id = intval($_GET['id']);
        $sql = "SELECT id, name, price, rating, description, image FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
    } else {
        $sql = "SELECT id, name, price, rating, description, image FROM products";
        $stmt = $conn->prepare($sql);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];

    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    $stmt->close();
    $conn->close();

    echo json_encode([
        'status' => 'success',
        'products' => $products
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 