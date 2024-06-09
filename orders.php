<?php
include "update_last_seen.php";
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: /");
    exit;
}

$is_admin = false;

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $check_admin_query = "SELECT role FROM users WHERE id = ?";
    $check_admin_stmt = $conn->prepare($check_admin_query);
    $check_admin_stmt->bind_param("i", $user_id);
    $check_admin_stmt->execute();
    $check_admin_result = $check_admin_stmt->get_result();

    if ($check_admin_result->num_rows > 0) {
        $row = $check_admin_result->fetch_assoc();
        $is_admin = ($row['role'] === 'admin');
    }

    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $cart_count_query = "SELECT COUNT(*) as count FROM cart WHERE user_id = ?";
        $cart_count_stmt = $conn->prepare($cart_count_query);
        $cart_count_stmt->bind_param("i", $user_id);
        $cart_count_stmt->execute();
        $cart_count_result = $cart_count_stmt->get_result();

        if ($cart_count_result->num_rows > 0) {
            $row = $cart_count_result->fetch_assoc();
            $cart_count = $row['count'];
        }
    }
}

function displayOrderDetails($conn, $orderId) {
    $sql = "SELECT * FROM order_items WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<div class='order-box'>";
        echo "<h3>Order ID: #$orderId</h3>";
        while ($row = $result->fetch_assoc()) {
            $productId = $row['product_id'];
            $quantity = $row['quantity'];
            displayProductInfo($conn, $productId, $quantity);
        }
        echo "<div class='invoice-box'>";
        echo "<a href='#' onclick='openInvoiceModal($orderId)' ><i class='fas fa-receipt'></i></a>";
        echo "</div>";
        echo "</div>";
    } else {
        echo "No details found for this order.";
    }
}

function displayProductInfo($conn, $productId, $quantity) {
    $sql = "SELECT * FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $productName = $row['name'];
        $productPrice = $row['price'];

        echo "<div class='order-info'>";
        echo "<img class='small-image' src='{$row['image_url']}' alt='Product Image'>";
        echo "<div class='cart-item-details'>";
        echo "<div class='item-name-price'>";
        echo "<h3>$productName</h3>";
        echo "<p class='cart-item-price'>Price: $productPrice EUR</p>";
        echo "</div>";
        echo "<div>";
        echo "<p>Quantity: $quantity</p>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
    } else {
        echo "Product with ID $productId not found.<br>";
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha384-<correct_hash_here>" crossorigin="anonymous">
    <title>Orders - CityStore</title>
</head>
<body>
<div class="wrapper">
    <?php include 'header.php'; ?>
    <div class="container">
        <div class="main-page-wrapper">
            <div class="user-center">
                <div class="settings-buttons">
                    <a class="settings-btn" href="users_profile.php">Profile</a>
                    <a class="settings-btn active" href="orders.php">Orders</a>
                    <a class="settings-btn" href="settings.php">Settings</a>
                </div>
                <div class="user-orders">
                    <h1>My Orders</h1>
                    <?php
                    if (isset($_SESSION['user_id'])) {
                        $user_id = $_SESSION['user_id'];
                        $sql = "SELECT DISTINCT order_id FROM orders WHERE user_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $orderId = $row['order_id'];
                                displayOrderDetails($conn, $orderId);
                            }
                        } else {
                            echo "<p>You haven't ordered anything yet...</p>";
                        }
                    } else {
                        echo "<p>User not logged in.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</div>

<div id="invoiceModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeInvoiceModal()">&times;</span>
        <div id="invoiceContent">
        </div>
    </div>
</div>

<script>
    function openInvoiceModal(orderId) {
        const modal = document.getElementById("invoiceModal");
        modal.style.display = "block";
        document.body.style.overflow = 'hidden';

        loadInvoiceContent(orderId);
    }

    function closeInvoiceModal() {
        const modal = document.getElementById("invoiceModal");
        modal.style.display = "none";
        document.body.style.overflow = '';
    }

    function loadInvoiceContent(orderId) {
        const invoiceContent = document.getElementById("invoiceContent");
        const xhr = new XMLHttpRequest();
        xhr.open("GET", "load_invoice.php?order_id=" + orderId, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                invoiceContent.innerHTML = xhr.responseText;
            }
        };
        xhr.send();
    }
</script>

</body>
</html>