<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db";

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT id, name, price, description FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        header("Location: product.html");
        exit();
    }

    $conn->close();
} catch (Exception $e) {
    header("Location: product.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Product Details</title>
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
        #signup {
            color: white;
            background-color: red;
            padding: 10px;
            font-weight: bold;
            border-radius: 5px;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .product-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .product-name {
            font-size: 32px;
            color: #333;
            margin-bottom: 10px;
        }
        .product-price {
            font-size: 36px;
            color: #28a745;
            font-weight: bold;
            margin: 20px 0;
        }
        .product-description {
            font-size: 18px;
            line-height: 1.6;
            color: #666;
            margin: 20px 0;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .action-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 30px;
        }
        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            transition: background-color 0.2s;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background-color: #545b62;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <div class="nav-bar">
            <a href="product.html">Home</a>
            <a href="order.html">Order</a>
            <a href="logout.php">Logout</a>
        </div>
    </header>

    <div class="container">
        <div class="product-header">
            <h1 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h1>
            <div class="product-price">₹<?php echo number_format($product['price'], 2); ?></div>
        </div>

        <div class="product-description">
            <?php echo nl2br(htmlspecialchars($product['description'])); ?>
        </div>

        <div class="action-buttons">
            <button class="btn btn-primary" onclick="orderProduct(<?php echo $product['id']; ?>)">Order Now</button>
            <a href="product.html" class="btn btn-secondary">Back to Products</a>
        </div>
    </div>

    <script>
        function orderProduct(productId) {
            // Get product details from the current page
            const productName = document.querySelector('.product-name').textContent;
            const productPrice = document.querySelector('.product-price').textContent.replace('₹', '').trim();
            
            // Redirect to order_now.html with product details
            const url = `order_now.html?id=${productId}&name=${encodeURIComponent(productName)}&price=${encodeURIComponent(productPrice)}`;
            window.location.href = url;
        }
    </script>
</body>
</html> 