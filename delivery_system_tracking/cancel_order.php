<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please login to cancel orders'
    ]);
    exit();
}

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Order ID is required'
    ]);
    exit();
}

$order_id = $_GET['order_id'];
$user_id = $_SESSION['user_id'];

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

    // Verify that the order belongs to the logged-in user
    $verify_sql = "SELECT order_id FROM orders WHERE order_id = ? AND user_id = ?";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("ii", $order_id, $user_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();

    if ($verify_result->num_rows === 0) {
        throw new Exception("Order not found or you don't have permission to cancel this order");
    }

    // Delete the order
    $delete_sql = "DELETE FROM orders WHERE order_id = ? AND user_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $order_id, $user_id);
    
    if ($delete_stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Order cancelled successfully'
        ]);
    } else {
        throw new Exception("Failed to cancel order");
    }

    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 