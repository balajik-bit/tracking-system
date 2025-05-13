<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Delivery Tracking System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #007bff;
            padding: 15px;
            text-align: center;
        }
        .nav-bar a {
            color: white;
            margin: 0 15px;
            text-decoration: none;
            font-weight: bold;
        }
        .nav-bar a:hover {
            background-color: #0056b3;
            border-radius: 5px;
        }
        #logout {
            color: white;
            background-color: #dc3545;
            padding: 10px;
            font-weight: bold;
            border-radius: 5px;
        }
        .orders-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .order-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .order-id {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        .order-date {
            color: #666;
        }
        .order-status {
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            text-transform: capitalize;
        }

        /* Status styles */
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        .status-processing {
            background-color: #e2e3e5;
            color: #383d41;
            border: 1px solid #d6d8db;
        }
        .status-shipped {
            background-color: #cce5ff;
            color: #004085;
            border: 1px solid #b8daff;
        }
        .status-delivered {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .order-details {
            margin-top: 15px;
        }
        .product-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .product-name {
            font-weight: bold;
        }
        .product-price {
            color: #28a745;
        }
        .cancel-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        .cancel-btn:hover {
            background-color: #c82333;
        }
        .error-message {
            color: #dc3545;
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            background-color: #f8d7da;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <header>
        <div class="nav-bar">
            <a href="product.html">Home</a>
            <a href="view_orders.php">Orders</a>
            <a href="login.html">Login</a>
            <a id="logout" href="logout.php">Logout</a>
        </div>
    </header>

    <div class="orders-container">
        <h1>My Orders</h1>
        <div id="ordersList">
            <div class="loading">Loading orders...</div>
        </div>
    </div>

    <script>
        function fetchOrders() {
            fetch('fetch_orders.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('ordersList');
                    
                    if (data.status === 'success') {
                        if (data.orders.length === 0) {
                            container.innerHTML = '<div class="error-message">No orders found.</div>';
                            return;
                        }

                        container.innerHTML = data.orders.map(order => {
                            const statusClass = order.status.toLowerCase().replace(/\s+/g, '');
                            return `
                                <div class="order-card">
                                    <div class="order-header">
                                        <div>
                                            <span class="order-id">Order #${order.id}</span>
                                            <span class="order-date">${order.order_date}</span>
                                        </div>
                                        <span class="order-status status-${statusClass}">${order.status}</span>
                                    </div>
                                    <div class="order-details">
                                        <div class="product-info">
                                            <span class="product-name">${order.product_name}</span>
                                            <span class="product-price">₹${order.price}</span>
                                        </div>
                                        ${(order.status.toLowerCase() === 'pending' || order.status.toLowerCase() === 'processing') ? `
                                            <button class="cancel-btn" onclick="cancelOrder(${order.id})">Cancel Order</button>
                                        ` : ''}
                                    </div>
                                </div>
                            `;
                        }).join('');
                    } else {
                        container.innerHTML = `
                            <div class="error-message">
                                Error: ${data.message || 'Failed to load orders'}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    document.getElementById('ordersList').innerHTML = `
                        <div class="error-message">
                            Error: ${error.message || 'Failed to fetch orders'}
                        </div>
                    `;
                });
        }

        function cancelOrder(orderId) {
            if (confirm('Are you sure you want to cancel this order?')) {
                fetch(`cancel_order.php?order_id=${orderId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert('Order cancelled successfully');
                            fetchOrders(); // Refresh the orders list
                        } else {
                            alert(data.message || 'Failed to cancel order');
                        }
                    })
                    .catch(error => {
                        alert('Error cancelling order. Please try again.');
                    });
            }
        }

        document.addEventListener('DOMContentLoaded', fetchOrders);
    </script>
</body>
</html>
