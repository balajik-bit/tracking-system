<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.html");
    exit();
}

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($user_id === 0 && $order_id === 0) {
    header("Location: admin_dashboard.html");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Get user information
    $user_sql = "SELECT id, fullname, email FROM users WHERE id = ?";
    $user_stmt = $conn->prepare($user_sql);
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user = $user_result->fetch_assoc();
    $user_stmt->close();

    // Get all orders for this user
    $orders_sql = "SELECT o.order_id, 
                          p.name as product_name,
                          p.price,
                          o.order_date,
                          o.status
                   FROM orders o 
                   JOIN products p ON o.product_id = p.id 
                   WHERE o.user_id = ? 
                   ORDER BY o.order_date DESC";
    
    $orders_stmt = $conn->prepare($orders_sql);
    $orders_stmt->bind_param("i", $user_id);
    $orders_stmt->execute();
    $orders_result = $orders_stmt->get_result();
    $orders = [];
    
    while ($row = $orders_result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    $orders_stmt->close();
    $conn->close();

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .header {
            background-color: #343a40;
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .nav-menu {
            display: flex;
            gap: 20px;
        }
        .nav-menu a {
            color: white;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
        }
        .nav-menu a:hover {
            background-color: #495057;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .user-info {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        .user-info h2 {
            margin-top: 0;
            color: #343a40;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        th {
            background-color: #f8f9fa;
            color: #495057;
        }
        .status-select {
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ced4da;
        }
        .back-btn {
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .back-btn:hover {
            background-color: #5a6268;
        }
        .highlight {
            background-color: #e9ecef;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Order Details</h1>
        <div class="nav-menu">
            <a href="admin_dashboard.html" class="back-btn">Back to Dashboard</a>
        </div>
    </div>

    <div class="container">
        <div class="user-info">
            <h2>Customer Information</h2>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($user['fullname']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        </div>

        <h2>Orders</h2>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Order Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr <?php echo $order['order_id'] == $order_id ? 'class="highlight"' : ''; ?>>
                    <td><?php echo $order['order_id']; ?></td>
                    <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                    <td>₹<?php echo number_format($order['price'], 2); ?></td>
                    <td><?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></td>
                    <td>
                        <button onclick="window.location.href='admin_order_status.php?order_id=<?php echo $order['order_id']; ?>'">View Details</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html> 