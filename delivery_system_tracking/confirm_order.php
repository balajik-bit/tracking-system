<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please login first!'
    ]);
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Get product details
    $product_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];
    $product_name = $_GET['name'];
    $product_price = $_GET['price'];

    // Get user's address
    $address_sql = "SELECT address FROM users WHERE id = ?";
    $address_stmt = $conn->prepare($address_sql);
    $address_stmt->bind_param("i", $user_id);
    $address_stmt->execute();
    $address_result = $address_stmt->get_result();
    $user_address = $address_result->fetch_assoc()['address'];
    $address_stmt->close();

    // Insert the order into the database
    $insert_sql = "INSERT INTO orders (user_id, product_id, order_date, status) VALUES (?, ?, NOW(), 'pending')";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("ii", $user_id, $product_id);
    
    if (!$insert_stmt->execute()) {
        throw new Exception("Failed to place order: " . $insert_stmt->error);
    }
    
    $order_id = $insert_stmt->insert_id;
    $insert_stmt->close();
    $conn->close();

    echo json_encode([
        'status' => 'success',
        'message' => 'Order placed successfully',
        'order_id' => $order_id,
        'product_id' => $product_id,
        'product_name' => $product_name,
        'order_date' => date('Y-m-d H:i:s'),
        'delivery_address' => $user_address
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 