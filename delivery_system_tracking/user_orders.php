<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Delivery System</title>
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
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        .card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .section-title {
            color: #343a40;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #495057;
        }
        input, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            box-sizing: border-box;
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
        .error-message {
            color: #dc3545;
            margin-top: 10px;
        }
        .success-message {
            color: #28a745;
            margin-top: 10px;
        }
        .delete-btn {
            background-color: #dc3545;
            padding: 5px 10px;
            font-size: 14px;
        }
        .delete-btn:hover {
            background-color: #c82333;
        }
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            width: 60%;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .close-modal {
            float: right;
            cursor: pointer;
            font-size: 24px;
        }
        .order-details {
            margin-top: 20px;
        }
        .order-details p {
            margin: 10px 0;
        }
        .status-update {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Admin Dashboard</h1>
        <div class="nav-menu">
            <a href="#products">Products</a>
            <a href="#orders">Orders</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <!-- Products Section -->
        <div id="products" class="card">
            <h2 class="section-title">Product Management</h2>
            <form id="productForm">
                <div class="form-group">
                    <label for="productName">Product Name:</label>
                    <input type="text" id="productName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="productDescription">Description:</label>
                    <textarea id="productDescription" name="description" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label for="productPrice">Price:</label>
                    <input type="number" id="productPrice" name="price" step="0.01" required>
                </div>
                <button type="submit">Add Product</button>
            </form>
            <div id="productMessage"></div>

            <!-- Products List -->
            <h3 class="section-title">Existing Products</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="productsList">
                    <tr>
                        <td colspan="5">Loading products...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Orders Section -->
        <div id="orders" class="card">
            <h2 class="section-title">Order Management</h2>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Product Name</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="ordersList">
                    <tr>
                        <td colspan="5">Loading orders...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Order Details</h2>
            <div id="orderDetails" class="order-details">
                <!-- Order details will be loaded here -->
            </div>
            <div class="status-update">
                <h3>Update Status</h3>
                <select id="statusSelect" class="status-select">
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="shipped">Shipped</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <button onclick="updateOrderStatus()">Update Status</button>
            </div>
        </div>
    </div>

    <script>
        // Handle product form submission
        document.getElementById('productForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('create_product.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const messageDiv = document.getElementById('productMessage');
                if (data.status === 'success') {
                    messageDiv.className = 'success-message';
                    messageDiv.textContent = 'Product added successfully!';
                    this.reset();
                    fetchProducts(); // Refresh the products list
                } else {
                    messageDiv.className = 'error-message';
                    messageDiv.textContent = data.message || 'Error adding product';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('productMessage').className = 'error-message';
                document.getElementById('productMessage').textContent = 'Error adding product';
            });
        });

        // Fetch and display products
        function fetchProducts() {
            fetch('fetch_products.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('productsList').innerHTML = data.products.map(product => 
                            <tr>
                                <td>${product.id}</td>
                                <td>${product.name}</td>
                                <td>${product.description}</td>
                                <td>₹${product.price}</td>
                                <td>
                                    <button class="delete-btn" onclick="deleteProduct(${product.id})">Delete</button>
                                </td>
                            </tr>
                        ).join('');
                    } else {
                        document.getElementById('productsList').innerHTML = 
                            <tr><td colspan="5">Error loading products: ${data.message}</td></tr>
                        ;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('productsList').innerHTML = 
                        <tr><td colspan="5">Error loading products</td></tr>
                    ;
                });
        }

        // Delete product
        function deleteProduct(productId) {
            if (confirm('Are you sure you want to delete this product?')) {
                fetch('delete_product.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: productId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        fetchProducts(); // Refresh the products list
                    } else {
                        alert(data.message || 'Error deleting product');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting product');
                });
            }
        }

        // Fetch and display orders
        function fetchOrders() {
            console.log('Fetching orders...');
            const ordersList = document.getElementById('ordersList');
            ordersList.innerHTML = '<tr><td colspan="5">Loading orders...</td></tr>';

            const urlParams = new URLSearchParams(window.location.search);
            const userId = urlParams.get('user_id');
            const fetchUrl = userId ? `get_orders.php?user_id=${userId}` : 'get_orders.php';

            fetch(fetchUrl)
                .then(response => {
                    console.log('Response received:', response);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Data received:', data);
                    
                    if (data.status === 'success') {
                        if (!data.orders || data.orders.length === 0) {
                            ordersList.innerHTML = `
                                <tr><td colspan="5">No orders found</td></tr>
                            `;
                            return;
                        }

                        ordersList.innerHTML = data.orders.map(order => `
                            <tr>
                                <td>#${order.order_id}</td>
                                <td>${order.customer_name}</td>
                                <td>${order.product_name}</td>
                                <td>
                                    <select class="status-select" onchange="updateOrderStatus(${order.order_id}, this.value)">
                                        <option value="pending" ${order.status === 'pending' ? 'selected' : ''}>Pending</option>
                                        <option value="processing" ${order.status === 'processing' ? 'selected' : ''}>Processing</option>
                                        <option value="shipped" ${order.status === 'shipped' ? 'selected' : ''}>Shipped</option>
                                        <option value="delivered" ${order.status === 'delivered' ? 'selected' : ''}>Delivered</option>
                                        <option value="cancelled" ${order.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                                    </select>
                                </td>
                                <td>
                                    <button class="btn btn-primary" onclick="viewOrderDetails(${order.order_id})">View Details</button>
                                </td>
                            </tr>
                        `).join('');
                    } else {
                        console.error('Error in data structure:', data);
                        ordersList.innerHTML = `
                            <tr><td colspan="5">Error loading orders: ${data.message || 'Invalid data structure'}</td></tr>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error fetching orders:', error);
                    ordersList.innerHTML = `
                        <tr><td colspan="5">Error loading orders: ${error.message}</td></tr>
                    `;
                });
        }

        // View order details
        function viewOrderDetails(orderId) {
            if (!orderId) {
                console.error('No order ID provided');
                return;
            }
            
            // Get the current user_id from URL
            const urlParams = new URLSearchParams(window.location.search);
            const userId = urlParams.get('user_id');
            
            // Fetch orders for this user
            fetch(`get_orders.php?user_id=${userId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        // Find the specific order from the orders array
                        const order = data.orders.find(o => o.order_id === orderId);
                        if (!order) {
                            throw new Error('Order not found');
                        }

                        // Open modal with order details
                        const modal = document.getElementById('orderModal');
                        const orderDetails = document.getElementById('orderDetails');
                        const statusSelect = document.getElementById('statusSelect');
                        
                        // Set current status in the select
                        statusSelect.value = order.status;
                        
                        // Populate order details
                        orderDetails.innerHTML = `
                            <div class="order-info">
                                <p><strong>Order ID:</strong> #${order.order_id}</p>
                                <p><strong>Customer Name:</strong> ${order.customer_name}</p>
                                <p><strong>Customer Email:</strong> ${order.customer_email}</p>
                                <p><strong>Product Name:</strong> ${order.product_name}</p>
                                <p><strong>Price:</strong> ₹${order.price}</p>
                                <p><strong>Status:</strong> ${order.status}</p>
                                <p><strong>Order Date:</strong> ${order.order_date}</p>
                            </div>
                        `;
                        
                        // Show modal
                        modal.style.display = 'block';
                        
                        // Update the status update button to use the current order ID
                        const updateButton = document.querySelector('.status-update button');
                        updateButton.onclick = function() {
                            updateOrderStatus(orderId, statusSelect.value);
                        };
                    } else {
                        alert('Error fetching order details: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error fetching order details: ' + error.message);
                });
        }

        // Close modal when clicking the close button
        document.querySelector('.close-modal').onclick = function() {
            document.getElementById('orderModal').style.display = 'none';
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('orderModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Update order status
        function updateOrderStatus(orderId, newStatus) {
            if (!orderId || !newStatus) {
                console.error('Missing order ID or status');
                return;
            }

            fetch('update_order_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    order_id: orderId,
                    status: newStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Refresh the orders list
                    fetchOrders();
                } else {
                    alert('Error updating order status: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating order status');
            });
        }

        // Initial load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM Content Loaded');
            // Check admin authentication first
            fetch('check_admin_auth.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data.is_admin) {
                        window.location.href = 'admin_login.html';
                    } else {
                        // Load products and orders
                        fetchProducts();
                        fetchOrders();
                    }
                })
                .catch(error => {
                    console.error('Auth error:', error);
                    window.location.href = 'admin_login.html';
                });
        });
    </script>
</body>
</html>