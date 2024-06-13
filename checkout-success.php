<?php
session_start();
include 'db.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    try {
        // Start a transaction
        $conn->begin_transaction();

        // Get the cart items
        $get_cart_items_query = "SELECT * FROM cart WHERE user_id = ?";
        $get_cart_items_stmt = $conn->prepare($get_cart_items_query);
        $get_cart_items_stmt->bind_param("i", $user_id);
        if (!$get_cart_items_stmt->execute()) {
            throw new Exception("Error fetching cart items: " . $conn->error);
        }
        $cart_items_result = $get_cart_items_stmt->get_result();

        // Insert a new order
        $insert_order_query = "INSERT INTO orders (user_id, status) VALUES (?, 'successful')";
        $insert_order_stmt = $conn->prepare($insert_order_query);
        $insert_order_stmt->bind_param("i", $user_id);
        if (!$insert_order_stmt->execute()) {
            throw new Exception("Error inserting into orders table: " . $conn->error);
        }
        $order_id = $insert_order_stmt->insert_id;

        // Insert items into the order_items table
        $insert_order_items_query = "INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)";
        $insert_order_items_stmt = $conn->prepare($insert_order_items_query);
        $insert_order_items_stmt->bind_param("iii", $order_id, $product_id, $quantity);

        while ($cart_item = $cart_items_result->fetch_assoc()) {
            $product_id = $cart_item['product_id'];
            $quantity = $cart_item['quantity'];
            if (!$insert_order_items_stmt->execute()) {
                throw new Exception("Error inserting into order_items table: " . $conn->error);
            }
        }

        // Delete items from the cart
        $delete_cart_items_query = "DELETE FROM cart WHERE user_id = ?";
        $delete_cart_stmt = $conn->prepare($delete_cart_items_query);
        $delete_cart_stmt->bind_param("i", $user_id);
        if (!$delete_cart_stmt->execute()) {
            throw new Exception("Error deleting cart items: " . $conn->error);
        }

        // Commit the transaction
        $conn->commit();
    } catch (Exception $e) {
        // Rollback the transaction if any operation fails
        $conn->rollback();
        echo "Transaction failed: " . $e->getMessage();
        // Optionally log the error
    }
} else {
    echo "User not logged in.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout Success</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script src="https://js.stripe.com/v3/"></script>
    <meta http-equiv="Content-Security-Policy">
</head>
<body style="background-color:#4286f4;justify-content: center;">
    <div class="payment-success" style="color:white;">
        <h1>Payment Successful!</h1>
        <p>Your payment has been processed successfully. Thank you for your purchase!</p>
        <strong><p><a href="/" style="color:white;">Go Back to Shop</a></p></strong>
    </div>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const params = new URLSearchParams(window.location.search);
        const userId = params.get("user_id");
        const darkMode = params.get("dark_mode");

        const root = document.documentElement;
        if (darkMode === "true") {
            root.classList.add("dark-theme");
        }
    });
    </script>
</body>
</html>
