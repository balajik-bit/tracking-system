<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.html");
    exit();
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id === 0) {
    header("Location: admin_dashboard.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status Update - Admin Dashboard</title>
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
        .order-details {
            margin-bottom: 20px;
        }
        .order-details p {
            margin: 10px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .status-update {
            margin-top: 20px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        .status-select {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ced4da;
            margin-right: 10px;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .back-btn {
            background-color: #6c757d;
        }
        .back-btn:hover {
            background-color: #5a6268;
        }
        .error-message {
            color: #dc3545;
            margin-top: 10px;
        }
        .success-message {
            color: #28a745;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Order Status Update</h1>
        <div class="nav-menu">
            <a href="admin_dashboard.html" class="back-btn">Back to Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <div id="orderDetails" class="order-details">
            <!-- Order details will be loaded here -->
        </div>
        
        <div class="status-update">
            <h3>Update Order Status</h3>
            <select id="statusSelect" class="status-select">
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
            </select>
            <button onclick="updateOrderStatus()">Update Status</button>
            <div id="statusMessage"></div>
        </div>
    </div>

    <script>
        // Load order details
        function loadOrderDetails() {
            const orderId = new URLSearchParams(window.location.search).get('order_id');
            if (!orderId) {
                document.getElementById('orderDetails').innerHTML = `
                    <div class="error-message">No order ID provided</div>
                `;
                return;
            }

            fetch('get_orders.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const order = data.orders.find(o => o.order_id === parseInt(orderId));
                        if (order) {
                            document.getElementById('orderDetails').innerHTML = `
                                <h2>Order #${order.order_id}</h2>
                                <p><strong>Customer Name:</strong> ${order.customer_name}</p>
                                <p><strong>Customer Email:</strong> ${order.customer_email}</p>
                                <p><strong>Product Name:</strong> ${order.product_name}</p>
                                <p><strong>Product ID:</strong> ${order.product_id}</p>
                                <p><strong>Current Status:</strong> ${order.status}</p>
                                <p><strong>Order Date:</strong> ${order.order_date}</p>
                            `;
                            document.getElementById('statusSelect').value = order.status;
                        } else {
                            document.getElementById('orderDetails').innerHTML = `
                                <div class="error-message">Order not found</div>
                            `;
                        }
                    } else {
                        document.getElementById('orderDetails').innerHTML = `
                            <div class="error-message">Error loading order details: ${data.message}</div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('orderDetails').innerHTML = `
                        <div class="error-message">Error loading order details</div>
                    `;
                });
        }

        // Update order status
        function updateOrderStatus() {
            const orderId = new URLSearchParams(window.location.search).get('order_id');
            const newStatus = document.getElementById('statusSelect').value;
            const messageDiv = document.getElementById('statusMessage');

            fetch('update_order_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    order_id: parseInt(orderId),
                    status: newStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    messageDiv.className = 'success-message';
                    messageDiv.textContent = 'Order status updated successfully!';
                    loadOrderDetails(); // Refresh the order details
                } else {
                    messageDiv.className = 'error-message';
                    messageDiv.textContent = 'Error updating order status: ' + data.message;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                messageDiv.className = 'error-message';
                messageDiv.textContent = 'Error updating order status';
            });
        }

        // Initial load
        loadOrderDetails();
    </script>
</body>
</html> 